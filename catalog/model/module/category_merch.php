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

	private function loadTotals(): void {
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

		$this->loaded = true;
	}
}
