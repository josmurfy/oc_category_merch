<?php
namespace Opencart\Catalog\Controller\Extension\CategoryMerch;

class Events extends \Opencart\System\Engine\Controller {
	private array $seo_map = [];
	private bool $seo_loaded = false;

	/**
	 * View before-render event for common/menu.
	 *
	 * OpenCart 4 fires view/*_/before with args: [&$route, &$data, &$code, &$output].
	 *
	 * @param string $route
	 * @param array  $data
	 * @param string $code
	 * @param string $output
	 */
	public function filterMenu(string &$route, array &$data, string &$code, string &$output): void {
		if ($route !== 'common/menu') {
			return;
		}

		if (!(int)$this->config->get('module_category_merch_status')) {
			return;
		}

		if (!isset($data['categories']) || !is_array($data['categories'])) {
			return;
		}

		$hide_empty = (int)$this->config->get('module_category_merch_hide_empty');
		$hide_empty_subs = (int)$this->config->get('module_category_merch_hide_empty_subs');
		$sort_by_score = (int)$this->config->get('module_category_merch_sort_by_score');
		$cache_ttl = (int)$this->config->get('module_category_merch_cache_ttl');
		$cache_version = (int)$this->config->get('module_category_merch_cache_version');
		$language_id = (int)$this->config->get('config_language_id');
		$overrides = $this->config->get('module_category_merch_overrides');

		if (!is_array($overrides)) {
			$overrides = [];
		}

		if ($cache_ttl <= 0) {
			$cache_ttl = 300;
		}

		$cache_key = $this->buildCacheKey($data['categories'], $hide_empty, $hide_empty_subs, $sort_by_score, $cache_version, $language_id, $overrides);
		$cached = $this->cache->get($cache_key);

		if (is_array($cached) && isset($cached['expires'], $cached['data']) && (int)$cached['expires'] >= time()) {
			if (is_array($cached['data'])) {
				$data['categories'] = $cached['data'];
				return;
			}
		}

		$this->load->model('extension/category_merch/module/category_merch');

		$categories = [];

		foreach ($data['categories'] as $category) {
			$category_id = $this->extractCategoryId($category['href'] ?? '');

			if (!$category_id) {
				$categories[] = $category;
				continue;
			}

			$child_rows = [];

			if (!empty($category['children']) && is_array($category['children'])) {
				foreach ($category['children'] as $child) {
					$child_id = $this->extractCategoryId($child['href'] ?? '');
					$total = $child_id ? $this->model_extension_category_merch_module_category_merch->getActiveSubtreeTotal($child_id) : 0;
					$override = $child_id && isset($overrides[$child_id]) ? (int)$overrides[$child_id] : 0;

					if ($override === -1) {
						continue;
					}

					if ($hide_empty_subs && $total === 0 && $override !== 1) {
						continue;
					}

					$child['__total'] = $total;
					$child_rows[] = $child;
				}
			}

			if ($sort_by_score && $child_rows) {
				usort($child_rows, function (array $a, array $b) {
					return ((int)($b['__total'] ?? 0) <=> (int)($a['__total'] ?? 0)) ?: strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
				});
			}

			foreach ($child_rows as &$row) {
				unset($row['__total']);
			}
			unset($row);

			$total = $this->model_extension_category_merch_module_category_merch->getActiveSubtreeTotal($category_id);
			$override = isset($overrides[$category_id]) ? (int)$overrides[$category_id] : 0;

			if ($override === -1) {
				continue;
			}

			if ($hide_empty && $total === 0 && $override !== 1) {
				continue;
			}

			$category['children'] = $child_rows;
			$category['__total'] = $total;
			$categories[] = $category;
		}

		if ($sort_by_score && $categories) {
			usort($categories, function (array $a, array $b) {
				return ((int)($b['__total'] ?? 0) <=> (int)($a['__total'] ?? 0)) ?: strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
			});
		}

		foreach ($categories as &$category) {
			unset($category['__total']);
		}
		unset($category);

		$data['categories'] = $categories;

		$this->cache->set($cache_key, [
			'expires' => time() + $cache_ttl,
			'data' => $categories
		]);
	}

	/**
	 * View before-render event for product/category.
	 *
	 * Filters the direct-children listing that OpenCart's own category
	 * controller puts in $data['categories'] (visible on the category page
	 * itself, e.g. as a sub-menu/sidebar) — the menu filter only touches the
	 * top navigation, so a buyer landing on a category page could still see
	 * empty child categories listed there. Reuses the same
	 * hide_empty_subs/sort_by_score/overrides settings as the menu filter.
	 */
	public function filterCategoryPage(string &$route, array &$data, string &$code, string &$output): void {
		if ($route !== 'product/category') {
			return;
		}

		if (!(int)$this->config->get('module_category_merch_status')) {
			return;
		}

		if (!(int)$this->config->get('module_category_merch_hide_empty_subs')) {
			return;
		}

		if (!isset($data['categories']) || !is_array($data['categories'])) {
			return;
		}

		$sort_by_score = (int)$this->config->get('module_category_merch_sort_by_score');
		$overrides = $this->config->get('module_category_merch_overrides');

		if (!is_array($overrides)) {
			$overrides = [];
		}

		$this->load->model('extension/category_merch/module/category_merch');

		$rows = [];

		foreach ($data['categories'] as $category) {
			$category_id = $this->extractCategoryId($category['href'] ?? '');

			if (!$category_id) {
				$rows[] = $category;
				continue;
			}

			$total = $this->model_extension_category_merch_module_category_merch->getActiveSubtreeTotal($category_id);
			$override = isset($overrides[$category_id]) ? (int)$overrides[$category_id] : 0;

			if ($override === -1) {
				continue;
			}

			if ($total === 0 && $override !== 1) {
				continue;
			}

			$category['__total'] = $total;
			$rows[] = $category;
		}

		if ($sort_by_score && $rows) {
			usort($rows, function (array $a, array $b) {
				return ((int)($b['__total'] ?? 0) <=> (int)($a['__total'] ?? 0)) ?: strcmp((string)($a['name'] ?? ''), (string)($b['name'] ?? ''));
			});
		}

		foreach ($rows as &$row) {
			unset($row['__total']);
		}
		unset($row);

		$data['categories'] = $rows;
	}

	/**
	 * View after-render event for common/home.
	 *
	 * Appends the "Category Showcase" tile grid (top categories by active
	 * product count, or the admin's manual pick) to the homepage — WLD-013,
	 * built as an auto-injected block (single on/off toggle) rather than a
	 * Design > Layout module, matching the rest of this extension's UX.
	 */
	public function appendShowcase(string &$route, array &$data, string &$output): void {
		if ($route !== 'common/home') {
			return;
		}

		if (!(int)$this->config->get('module_category_showcase_status')) {
			return;
		}

		$limit = (int)$this->config->get('module_category_showcase_limit') ?: 8;
		$manual_ids = (string)$this->config->get('module_category_showcase_manual_ids');
		$cache_version = (int)$this->config->get('module_category_merch_cache_version');
		$language_id = (int)$this->config->get('config_language_id');

		$cache_key = 'category_merch.showcase.' . md5(json_encode(['v' => $cache_version, 'lang' => $language_id, 'limit' => $limit, 'manual' => $manual_ids]));
		$html = $this->cache->get($cache_key);

		// File cache adaptor returns [] on a miss (never null/false), so any
		// non-string result means "not cached yet" — a genuine cached value
		// is always the rendered HTML string (or '' for "nothing to show").
		if (!is_string($html)) {
			$this->load->model('extension/category_merch/module/category_merch');
			$categories = $this->model_extension_category_merch_module_category_merch->getShowcaseCategories($limit, $manual_ids);

			if (!$categories) {
				$this->cache->set($cache_key, '', 300);
				return;
			}

			$this->load->model('tool/image');
			$this->load->language('extension/category_merch/module/category_merch');

			$html = '<style>.cm-showcase-tile{transition:transform .15s ease,box-shadow .15s ease}.cm-showcase-tile:hover{transform:translateY(-3px);box-shadow:0 .5rem 1rem rgba(0,0,0,.1)}.cm-showcase-tile .card-img-top{object-fit:cover;height:160px}</style>'
				. '<div class="container-fluid mt-4 mb-2"><h2 class="h4 mb-3">'
				. htmlspecialchars($this->language->get('text_showcase_title'), ENT_QUOTES, 'UTF-8')
				. '</h2><div class="row g-3">';

			foreach ($categories as $category) {
				$href = $this->url->link('product/category', 'path=' . (int)$category['category_id']);

				if (!empty($category['image']) && is_file(DIR_IMAGE . html_entity_decode((string)$category['image'], ENT_QUOTES, 'UTF-8'))) {
					$image = $this->model_tool_image->resize((string)$category['image'], 300, 200);
				} else {
					$image = $this->model_tool_image->resize('placeholder.png', 300, 200);
				}

				$html .= '<div class="col-6 col-md-4 col-lg-3"><a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="card h-100 text-decoration-none text-body cm-showcase-tile">'
					. '<img src="' . htmlspecialchars($image, ENT_QUOTES, 'UTF-8') . '" class="card-img-top" alt="' . htmlspecialchars((string)$category['name'], ENT_QUOTES, 'UTF-8') . '">'
					. '<div class="card-body py-2"><div class="fw-semibold">' . htmlspecialchars((string)$category['name'], ENT_QUOTES, 'UTF-8') . '</div>'
					. '<div class="text-muted small">' . (int)$category['total'] . '</div></div>'
					. '</a></div>';
			}

			$html .= '</div></div>';

			$this->cache->set($cache_key, $html, 300);
		}

		if ($html !== '') {
			$output .= $html;
		}
	}

	/**
	 * View after-render event for product/category and product/product.
	 *
	 * Appends a small "related categories" block (children of the current
	 * category, or its siblings if it has none / a category derived from the
	 * current product) so buyers keep discovering stocked categories instead
	 * of dead-ending on a single page.
	 */
	public function appendRelatedCategories(string &$route, array &$data, string &$output): void {
		if (!in_array($route, ['product/category', 'product/product'], true)) {
			return;
		}

		if (!(int)$this->config->get('module_category_merch_status')) {
			return;
		}

		if (!(int)$this->config->get('module_category_merch_related_status')) {
			return;
		}

		$category_id = 0;

		if ($route === 'product/category' && !empty($this->request->get['path'])) {
			$parts = explode('_', (string)$this->request->get['path']);
			$category_id = (int)end($parts);
		} elseif ($route === 'product/product' && !empty($this->request->get['product_id'])) {
			$this->load->model('catalog/product');
			$rows = $this->model_catalog_product->getCategories((int)$this->request->get['product_id']);

			if ($rows) {
				$category_id = (int)$rows[0]['category_id'];
			}
		}

		if (!$category_id) {
			return;
		}

		$limit = (int)$this->config->get('module_category_merch_related_limit') ?: 6;

		$this->load->model('extension/category_merch/module/category_merch');
		$related = $this->model_extension_category_merch_module_category_merch->getRelatedCategories($category_id, $limit);

		if (!$related) {
			return;
		}

		$this->load->language('extension/category_merch/module/category_merch');

		$html = '<div class="card mt-4"><div class="card-header"><i class="fa-solid fa-compass"></i> '
			. htmlspecialchars($this->language->get('text_related_categories'), ENT_QUOTES, 'UTF-8')
			. '</div><div class="card-body"><div class="d-flex flex-wrap gap-2">';

		foreach ($related as $row) {
			$href = $this->url->link('product/category', 'path=' . (int)$row['category_id']);

			$html .= '<a href="' . htmlspecialchars($href, ENT_QUOTES, 'UTF-8') . '" class="btn btn-outline-secondary btn-sm">'
				. htmlspecialchars((string)$row['name'], ENT_QUOTES, 'UTF-8')
				. ' <span class="badge bg-secondary">' . (int)$row['total'] . '</span></a>';
		}

		$html .= '</div></div></div>';

		$output .= $html;
	}

	private function buildCacheKey(array $categories, int $hide_empty, int $hide_empty_subs, int $sort_by_score, int $cache_version, int $language_id, array $overrides): string {
		$ids = [];

		foreach ($categories as $category) {
			$id = $this->extractCategoryId($category['href'] ?? '');
			if ($id) {
				$ids[] = $id;
			}

			if (!empty($category['children']) && is_array($category['children'])) {
				foreach ($category['children'] as $child) {
					$child_id = $this->extractCategoryId($child['href'] ?? '');
					if ($child_id) {
						$ids[] = $child_id;
					}
				}
			}
		}

		sort($ids);
		ksort($overrides);

		return 'category_merch.menu.' . md5(json_encode([
			'v' => $cache_version,
			'lang' => $language_id,
			'hide' => $hide_empty,
			'hide_subs' => $hide_empty_subs,
			'sort' => $sort_by_score,
			'ids' => $ids,
			'ovr' => $overrides
		]));
	}

	/**
	 * Extract category id from a menu item href.
	 * Handles both raw (?path=...) and SEO URLs (via oc_seo_url lookup).
	 */
	private function extractCategoryId(string $href): int {
		if (!$href) {
			return 0;
		}

		$normalized = str_replace('&amp;', '&', $href);

		// 1) Raw URL: ?...&path=1_2_3
		$query = parse_url($normalized, PHP_URL_QUERY);
		if ($query) {
			parse_str($query, $params);
			if (!empty($params['path'])) {
				$parts = explode('_', (string)$params['path']);
				return (int)end($parts);
			}
			// SEO-friendly with _route_ param
			if (!empty($params['_route_'])) {
				$id = $this->resolveSeoKeyword((string)$params['_route_']);
				if ($id) {
					return $id;
				}
			}
		}

		// 2) SEO URL: walk path segments from deepest to shallowest
		$path = parse_url($normalized, PHP_URL_PATH);
		if (!$path) {
			return 0;
		}

		$segments = array_values(array_filter(explode('/', trim($path, '/'))));

		// Try segments in reverse order (deepest first)
		for ($i = count($segments) - 1; $i >= 0; $i--) {
			$id = $this->resolveSeoKeyword($segments[$i]);
			if ($id) {
				return $id;
			}
		}

		// Also try joined path (some themes use slash-joined keywords)
		$joined = implode('/', $segments);
		if ($joined) {
			$id = $this->resolveSeoKeyword($joined);
			if ($id) {
				return $id;
			}
		}

		return 0;
	}

	private function resolveSeoKeyword(string $keyword): int {
		if ($keyword === '') {
			return 0;
		}

		$this->ensureSeoMap();

		return $this->seo_map[$keyword] ?? 0;
	}

	private function ensureSeoMap(): void {
		if ($this->seo_loaded) {
			return;
		}
		$this->seo_loaded = true;

		$store_id = (int)$this->config->get('config_store_id');
		$language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT `keyword`, `query` FROM " . DB_PREFIX . "seo_url
			WHERE store_id = '" . $store_id . "'
			AND language_id = '" . $language_id . "'
			AND `query` LIKE 'product/category=%'";

		$rows = $this->db->query($sql)->rows;

		foreach ($rows as $row) {
			$q = (string)$row['query'];
			if (strpos($q, 'product/category=') === 0) {
				$this->seo_map[(string)$row['keyword']] = (int)substr($q, strlen('product/category='));
			}
		}
	}
}
