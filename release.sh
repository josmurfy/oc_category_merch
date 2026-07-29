#!/usr/bin/env bash
# release.sh — cut a new release of oc_category_merch.
#
# Usage: ./release.sh <version> [release-notes]
#   ./release.sh 0.3.2 "Fix menu cache invalidation"
#
# What it does:
#   1. Bumps install.json "version"
#   2. Commits + tags + pushes to origin/main
#   3. Builds oc_category_merch.ocmod.zip in /tmp/
#   4. Creates the GitHub Release and uploads the zip as asset
#
# Requires: git-credentials with a PAT that has repo:write scope
# (same one used for `git push`).

set -euo pipefail

VERSION="${1:-}"
NOTES="${2:-Release v${VERSION}}"
REPO_SLUG="josmurfy/oc_category_merch"

if [ -z "$VERSION" ]; then
	echo "Usage: $0 <version> [release-notes]" >&2
	echo "Example: $0 0.3.2 \"Fix menu cache invalidation\"" >&2
	exit 1
fi

if ! [[ "$VERSION" =~ ^[0-9]+\.[0-9]+\.[0-9]+$ ]]; then
	echo "Error: version must be x.y.z (got: $VERSION)" >&2
	exit 1
fi

SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$SCRIPT_DIR"

# --- pre-flight -----------------------------------------------------------

if [ ! -f install.json ]; then
	echo "Error: install.json not found in $SCRIPT_DIR" >&2
	exit 1
fi

if [ -n "$(git status --porcelain --untracked-files=no | grep -v install.json || true)" ]; then
	echo "Error: repo has uncommitted changes (other than install.json). Commit or stash first." >&2
	git status --short
	exit 1
fi

TOKEN=$(grep -oP 'https://[^:]+:\K[^@]+' ~/.git-credentials 2>/dev/null | head -1 || true)
if [ -z "$TOKEN" ]; then
	echo "Error: no GitHub token found in ~/.git-credentials" >&2
	exit 1
fi

# --- 1. bump version ------------------------------------------------------

CURRENT=$(grep -oP '"version"\s*:\s*"\K[^"]+' install.json)
echo ">> current version: $CURRENT"
echo ">> new version:     $VERSION"

if [ "$CURRENT" = "$VERSION" ]; then
	echo "Error: install.json already at $VERSION" >&2
	exit 1
fi

sed -i "s/\"version\"\s*:\s*\"[^\"]*\"/\"version\": \"$VERSION\"/" install.json
grep '"version"' install.json

# --- 2. commit + tag + push ----------------------------------------------

git add install.json
git commit -m "v${VERSION} — ${NOTES}"
git tag "v${VERSION}"
git push origin main
git push origin "v${VERSION}"

# --- 3. build zip ---------------------------------------------------------

BUILD_DIR=$(mktemp -d)
ZIP="/tmp/oc_category_merch.ocmod.zip"
rm -f "$ZIP"

# Copy source (exclude repo meta and dev files)
rsync -a --exclude=".git" --exclude=".gitignore" --exclude="README.md" \
	--exclude="release.sh" --exclude="*.DS_Store" --exclude="*.backup" \
	./ "$BUILD_DIR/"

( cd "$BUILD_DIR" && zip -qr "$ZIP" . )
rm -rf "$BUILD_DIR"

ZIP_SIZE=$(stat -c%s "$ZIP")
ZIP_SHA=$(sha256sum "$ZIP" | awk '{print $1}')
echo ">> zip: $ZIP ($ZIP_SIZE bytes, sha256=$ZIP_SHA)"

# --- 4. GitHub release + asset upload ------------------------------------

API="https://api.github.com/repos/${REPO_SLUG}"

RELEASE_JSON=$(curl -sS -X POST \
	-H "Authorization: Bearer $TOKEN" \
	-H "Accept: application/vnd.github+json" \
	-H "X-GitHub-Api-Version: 2022-11-28" \
	"${API}/releases" \
	-d "$(cat <<JSON
{
  "tag_name": "v${VERSION}",
  "target_commitish": "main",
  "name": "v${VERSION} — ${NOTES}",
  "body": "${NOTES}\n\n**SHA-256:** \`${ZIP_SHA}\`",
  "draft": false,
  "prerelease": false
}
JSON
)")

RELEASE_ID=$(echo "$RELEASE_JSON" | grep -oP '"id":\s*\K[0-9]+' | head -1)
if [ -z "$RELEASE_ID" ]; then
	echo "Error: release creation failed:" >&2
	echo "$RELEASE_JSON" | grep -iE '"message"|"errors"'
	exit 1
fi
echo ">> release id: $RELEASE_ID"

UPLOAD=$(curl -sS -X POST \
	-H "Authorization: Bearer $TOKEN" \
	-H "Accept: application/vnd.github+json" \
	-H "Content-Type: application/zip" \
	--data-binary "@${ZIP}" \
	"https://uploads.github.com/repos/${REPO_SLUG}/releases/${RELEASE_ID}/assets?name=oc_category_merch.ocmod.zip&label=OpenCart+installer+zip")

DOWNLOAD_URL=$(echo "$UPLOAD" | grep -oP '"browser_download_url":\s*"\K[^"]+' | head -1)
if [ -z "$DOWNLOAD_URL" ]; then
	echo "Error: asset upload failed:" >&2
	echo "$UPLOAD" | grep -iE '"message"|"errors"'
	exit 1
fi

echo ""
echo "============================================================"
echo "Release v${VERSION} published"
echo "  Tag:      https://github.com/${REPO_SLUG}/releases/tag/v${VERSION}"
echo "  Download: ${DOWNLOAD_URL}"
echo "  SHA-256:  ${ZIP_SHA}"
echo "============================================================"
