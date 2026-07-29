<?php
namespace Opencart\Admin\Model\Extension\CategoryMerch\Module;

class CategoryMerch extends \Opencart\System\Engine\Model {
	/**
	 * Full category tree with total + score, sorted by score DESC.
	 *
	 * Supports server-side pagination + search for large catalogs.
	 * Uses cache to avoid re-running the heavy aggregate query on every request.
	 *
	 * @param string $search  case-insensitive substring on category name
	 * @param int    $limit   0 = no limit
	 * @param int    $offset
	 * @return array{rows: array, total: int}
	 */
	public function getCategoryTreeWithScore(string $search = '', int $limit = 0, int $offset = 0): array {
		$all = $this->loadTreeRowsCached();

		if ($search !== '') {
			$needle = mb_strtolower($search);
			$all = array_values(array_filter($all, function ($r) use ($needle) {
				return mb_strpos(mb_strtolower((string)$r['name']), $needle) !== false;
			}));
		}

		$total = count($all);

		if ($limit > 0) {
			$all = array_slice($all, $offset, $limit);
		}

		return ['rows' => $all, 'total' => $total];
	}

	/**
	 * Top-level categories with total + score, sorted by total DESC.
	 */
	public function getTopCategoriesWithScore(): array {
		$totals = $this->getAllCategoryTotalsCached();
		$language_id = (int)$this->config->get('config_language_id');

		$sql = "SELECT c.category_id, cd.name
			FROM " . DB_PREFIX . "category c
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
			WHERE c.parent_id = '0'
			AND c.status = '1'
			AND cd.language_id = '" . $language_id . "'
			ORDER BY cd.name ASC";

		$rows = $this->db->query($sql)->rows;
		$max = 0;

		foreach ($rows as $row) {
			$max = max($max, (int)($totals[(int)$row['category_id']] ?? 0));
		}

		foreach ($rows as &$row) {
			$total = (int)($totals[(int)$row['category_id']] ?? 0);
			$row['total'] = $total;
			$row['score'] = $max > 0 ? (int)round(($total / $max) * 100) : 0;
		}
		unset($row);

		usort($rows, function (array $a, array $b) {
			return ((int)$b['total'] <=> (int)$a['total']) ?: strcmp((string)$a['name'], (string)$b['name']);
		});

		return $rows;
	}

	/**
	 * Full flat tree loaded once per (language + cache_version), sorted by score DESC.
	 * Cached ~5 minutes.
	 */
	private function loadTreeRowsCached(): array {
		$language_id = (int)$this->config->get('config_language_id');
		$cache_version = (int)$this->config->get('module_category_merch_cache_version');
		$cache_key = 'category_merch.admin.tree.' . $language_id . '.' . $cache_version;

		$cached = $this->cache->get($cache_key);
		if (is_array($cached) && isset($cached['expires'], $cached['rows']) && (int)$cached['expires'] >= time()) {
			return $cached['rows'];
		}

		$totals = $this->getAllCategoryTotalsCached();

		$sql = "SELECT c.category_id, c.parent_id, c.status, cd.name,
			(SELECT COUNT(*) - 1 FROM " . DB_PREFIX . "category_path cp2 WHERE cp2.category_id = c.category_id) AS level
			FROM " . DB_PREFIX . "category c
			LEFT JOIN " . DB_PREFIX . "category_description cd ON (c.category_id = cd.category_id)
			WHERE cd.language_id = '" . $language_id . "'";

		$rows = $this->db->query($sql)->rows;
		$max = 0;

		foreach ($rows as $row) {
			$max = max($max, (int)($totals[(int)$row['category_id']] ?? 0));
		}

		foreach ($rows as &$row) {
			$total = (int)($totals[(int)$row['category_id']] ?? 0);
			$row['category_id'] = (int)$row['category_id'];
			$row['parent_id'] = (int)$row['parent_id'];
			$row['status'] = (int)$row['status'];
			$row['level'] = (int)$row['level'];
			$row['total'] = $total;
			$row['score'] = $max > 0 ? (int)round(($total / $max) * 100) : 0;
		}
		unset($row);

		// Sort by score DESC, then by total DESC, then by name ASC
		usort($rows, function (array $a, array $b) {
			return ((int)$b['score'] <=> (int)$a['score'])
				?: ((int)$b['total'] <=> (int)$a['total'])
				?: strcmp((string)$a['name'], (string)$b['name']);
		});

		$this->cache->set($cache_key, [
			'expires' => time() + 300,
			'rows' => $rows
		]);

		return $rows;
	}

	/**
	 * Aggregated active-product counts per category subtree, cached ~5 minutes.
	 */
	private function getAllCategoryTotalsCached(): array {
		$cache_version = (int)$this->config->get('module_category_merch_cache_version');
		$cache_key = 'category_merch.admin.totals.' . $cache_version;

		$cached = $this->cache->get($cache_key);
		if (is_array($cached) && isset($cached['expires'], $cached['totals']) && (int)$cached['expires'] >= time()) {
			return $cached['totals'];
		}

		$sql = "SELECT cp.path_id AS category_id, COUNT(DISTINCT p2c.product_id) AS total
			FROM " . DB_PREFIX . "category_path cp
			LEFT JOIN " . DB_PREFIX . "product_to_category p2c ON p2c.category_id = cp.category_id
			LEFT JOIN " . DB_PREFIX . "product p ON p.product_id = p2c.product_id
			WHERE p.status = '1'
			AND p.date_available <= NOW()
			GROUP BY cp.path_id";

		$rows = $this->db->query($sql)->rows;
		$totals = [];

		foreach ($rows as $row) {
			$totals[(int)$row['category_id']] = (int)$row['total'];
		}

		$this->cache->set($cache_key, [
			'expires' => time() + 300,
			'totals' => $totals
		]);

		return $totals;
	}
}
