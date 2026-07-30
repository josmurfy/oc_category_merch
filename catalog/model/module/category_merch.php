<?php
namespace Opencart\Catalog\Model\Extension\CategoryMerch\Module;

class CategoryMerch extends \Opencart\System\Engine\Model {
	private array $totals = [];
	private bool $loaded = false;

	public function getActiveSubtreeTotal(int $category_id): int {
		if (!$this->loaded) {
			$this->loadTotals();
		}

		return (int)($this->totals[$category_id] ?? 0);
	}

	/**
	 * Populated eBay-leaf categories under $root_category_id, grouped by their
	 * immediate parent (or by themselves, if that parent is the root — nothing
	 * meaningful to group under otherwise). Powers the menu's sub-level: pure
	 * leaves ("Girls") are contextless on their own and siblings like
	 * Girls/Boys/Men would otherwise appear as separate, disconnected entries;
	 * grouping by parent both gives context and merges them into one entry
	 * with the combined stock count. Sorted by total DESC.
	 */
	public function getLeafGroupsForRoot(int $root_category_id): array {
		if (!$this->loaded) {
			$this->loadTotals();
		}

		$language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT c.category_id, c.parent_id, cd.name AS leaf_name, pd.name AS parent_name
			FROM " . DB_PREFIX . "category_path cp
			JOIN " . DB_PREFIX . "category c ON (c.category_id = cp.category_id AND c.leaf = '1')
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (cd.category_id = c.category_id AND cd.language_id = '" . $language_id . "')
			LEFT JOIN " . DB_PREFIX . "category_description pd ON (pd.category_id = c.parent_id AND pd.language_id = '" . $language_id . "')
			WHERE cp.path_id = '" . (int)$root_category_id . "'
			AND cp.category_id != '" . (int)$root_category_id . "'";

		$rows = $this->db->query($sql)->rows;

		$groups = [];

		foreach ($rows as $row) {
			$leaf_id = (int)$row['category_id'];
			$total = $this->getActiveSubtreeTotal($leaf_id);

			if ($total === 0) {
				continue;
			}

			$parent_id = (int)$row['parent_id'];

			if ($parent_id === $root_category_id) {
				// Nothing meaningful to group under — the leaf itself is the entry.
				$group_id = $leaf_id;
				$group_name = (string)($row['leaf_name'] ?? '');
			} else {
				$group_id = $parent_id;
				$group_name = (string)($row['parent_name'] ?? '');
			}

			if (!isset($groups[$group_id])) {
				$groups[$group_id] = [
					'category_id' => $group_id,
					'name' => $group_name,
					'total' => 0
				];
			}

			$groups[$group_id]['total'] += $total;
		}

		$groups = array_values($groups);

		usort($groups, function (array $a, array $b) {
			return ((int)$b['total'] <=> (int)$a['total']) ?: strcmp((string)$a['name'], (string)$b['name']);
		});

		return $groups;
	}

	/**
	 * Children of $category_id (or, if it has none, its siblings) with active
	 * product totals, non-empty only, sorted by total DESC. Powers the
	 * "related categories" widget on category/product pages.
	 */
	public function getRelatedCategories(int $category_id, int $limit = 6): array {
		$rows = $this->getCategoriesByParent($category_id, $limit);

		if ($rows) {
			return $rows;
		}

		$parent_id = $this->getParentId($category_id);

		return $this->getCategoriesByParent($parent_id, $limit, $category_id);
	}

	/**
	 * Top categories by active product count, non-empty only — powers the
	 * homepage "Category Showcase" block. Manual IDs (if any) are forced
	 * first, in the given order; the rest is auto-filled by score.
	 *
	 * @param int    $limit
	 * @param string $manual_ids_csv comma-separated category IDs
	 */
	public function getShowcaseCategories(int $limit, string $manual_ids_csv = ''): array {
		$language_id = (int)$this->config->get('config_language_id');

		$manual_ids = array_values(array_filter(array_map('intval', explode(',', $manual_ids_csv))));

		$rows = [];
		$used = [];

		foreach ($manual_ids as $category_id) {
			if (isset($used[$category_id]) || count($rows) >= $limit) {
				continue;
			}

			$total = $this->getActiveSubtreeTotal($category_id);

			if ($total === 0) {
				continue;
			}

			$info = $this->getCategoryInfo($category_id, $language_id);

			if (!$info) {
				continue;
			}

			$rows[] = $info + ['total' => $total];
			$used[$category_id] = true;
		}

		if (count($rows) < $limit) {
			$sql = "SELECT c.category_id, cd.name, c.image
				FROM " . DB_PREFIX . "category c
				LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
				WHERE c.status = '1'
				AND cd.language_id = '" . $language_id . "'";

			$results = $this->db->query($sql)->rows;
			$candidates = [];

			foreach ($results as $result) {
				$category_id = (int)$result['category_id'];

				if (isset($used[$category_id])) {
					continue;
				}

				$total = $this->getActiveSubtreeTotal($category_id);

				if ($total === 0) {
					continue;
				}

				$candidates[] = [
					'category_id' => $category_id,
					'name' => (string)$result['name'],
					'image' => (string)$result['image'],
					'total' => $total
				];
			}

			usort($candidates, function (array $a, array $b) {
				return ((int)$b['total'] <=> (int)$a['total']) ?: strcmp((string)$a['name'], (string)$b['name']);
			});

			foreach ($candidates as $candidate) {
				if (count($rows) >= $limit) {
					break;
				}

				$rows[] = $candidate;
			}
		}

		return $rows;
	}

	private function getCategoryInfo(int $category_id, int $language_id): ?array {
		$query = $this->db->query("SELECT c.category_id, cd.name, c.image
			FROM " . DB_PREFIX . "category c
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
			WHERE c.category_id = '" . (int)$category_id . "'
			AND c.status = '1'
			AND cd.language_id = '" . (int)$language_id . "'");

		if (!$query->num_rows) {
			return null;
		}

		return [
			'category_id' => (int)$query->row['category_id'],
			'name' => (string)$query->row['name'],
			'image' => (string)$query->row['image']
		];
	}

	public function getParentId(int $category_id): int {
		$query = $this->db->query("SELECT parent_id FROM " . DB_PREFIX . "category WHERE category_id = '" . (int)$category_id . "'");

		return $query->num_rows ? (int)$query->row['parent_id'] : 0;
	}

	private function getCategoriesByParent(int $parent_id, int $limit, int $exclude_id = 0): array {
		$language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT c.category_id, cd.name
			FROM " . DB_PREFIX . "category c
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
			WHERE c.parent_id = '" . (int)$parent_id . "'
			AND c.status = '1'
			AND cd.language_id = '" . $language_id . "'";

		if ($exclude_id) {
			$sql .= " AND c.category_id != '" . (int)$exclude_id . "'";
		}

		$sql .= " ORDER BY cd.name ASC";

		$results = $this->db->query($sql)->rows;
		$rows = [];

		foreach ($results as $result) {
			$total = $this->getActiveSubtreeTotal((int)$result['category_id']);

			if ($total === 0) {
				continue;
			}

			$rows[] = [
				'category_id' => (int)$result['category_id'],
				'name' => (string)$result['name'],
				'total' => $total
			];
		}

		usort($rows, function (array $a, array $b) {
			return ((int)$b['total'] <=> (int)$a['total']) ?: strcmp((string)$a['name'], (string)$b['name']);
		});

		return array_slice($rows, 0, $limit);
	}

	private function loadTotals(): void {
		$cache_version = (int)$this->config->get('module_category_merch_cache_version');
		$cache_key = 'category_merch.catalog.totals.' . $cache_version;

		$cached = $this->cache->get($cache_key);
		if (is_array($cached) && isset($cached['expires'], $cached['totals']) && (int)$cached['expires'] >= time()) {
			$this->totals = $cached['totals'];
			$this->loaded = true;
			return;
		}

		$sql = "SELECT cp.path_id AS category_id, COUNT(DISTINCT p2c.product_id) AS total
			FROM " . DB_PREFIX . "category_path cp
			LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON p2c.category_id = cp.category_id
			LEFT JOIN " . DB_PREFIX . "product p ON p.product_id = p2c.product_id
			WHERE p.status = '1'
			AND p.date_available <= NOW()
			GROUP BY cp.path_id";

		$rows = $this->db->query($sql)->rows;

		foreach ($rows as $row) {
			$this->totals[(int)$row['category_id']] = (int)$row['total'];
		}

		$this->cache->set($cache_key, [
			'expires' => time() + 300,
			'totals' => $this->totals
		]);

		$this->loaded = true;
	}
}
