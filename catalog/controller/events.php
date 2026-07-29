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
