<?php
namespace Opencart\Admin\Controller\Extension\OcCategoryMerch\Module;

class CategoryMerch extends \Opencart\System\Engine\Controller {
	private array $error = [];

	public function index(): void {
		$this->load->language('extension/oc_category_merch/module/category_merch');
		$this->document->setTitle($this->language->get('heading_title'));

		$data['breadcrumbs'] = [];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'])
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_extension'),
			'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module')
		];
		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('extension/oc_category_merch/module/category_merch', 'user_token=' . $this->session->data['user_token'])
		];

		$data['save'] = $this->url->link('extension/oc_category_merch/module/category_merch.save', 'user_token=' . $this->session->data['user_token']);
		$data['recalculate'] = $this->url->link('extension/oc_category_merch/module/category_merch.recalculate', 'user_token=' . $this->session->data['user_token']);
		$data['check_updates'] = $this->url->link('extension/oc_category_merch/module/category_merch.checkUpdates', 'user_token=' . $this->session->data['user_token']);
		$data['install_update'] = $this->url->link('extension/oc_category_merch/module/category_merch.installUpdate', 'user_token=' . $this->session->data['user_token']);
		$data['overrides_url'] = $this->url->link('extension/oc_category_merch/module/category_merch.overrides', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		$data['module_oc_category_merch_status'] = (int)$this->config->get('module_oc_category_merch_status');
		$data['module_oc_category_merch_hide_empty'] = (int)$this->config->get('module_oc_category_merch_hide_empty');
		$data['module_oc_category_merch_hide_empty_subs'] = (int)$this->config->get('module_oc_category_merch_hide_empty_subs');
		$data['module_oc_category_merch_sort_by_score'] = (int)$this->config->get('module_oc_category_merch_sort_by_score');
		$data['module_oc_category_merch_weight_volume'] = (int)$this->config->get('module_oc_category_merch_weight_volume');
		$data['module_oc_category_merch_cache_ttl'] = (int)$this->config->get('module_oc_category_merch_cache_ttl');
		$data['module_oc_category_merch_overrides'] = $this->config->get('module_oc_category_merch_overrides');

		if (!is_array($data['module_oc_category_merch_overrides'])) {
			$data['module_oc_category_merch_overrides'] = [];
		}

		if (!$data['module_oc_category_merch_weight_volume']) {
			$data['module_oc_category_merch_weight_volume'] = 100;
		}

		if ($data['module_oc_category_merch_cache_ttl'] <= 0) {
			$data['module_oc_category_merch_cache_ttl'] = 300;
		}

		$this->load->model('extension/oc_category_merch/module/category_merch');
		$data['dashboard_rows'] = $this->model_extension_oc_category_merch_module_category_merch->getTopCategoriesWithScore();

		$tree_page = $this->model_extension_oc_category_merch_module_category_merch->getCategoryTreeWithScore('', 300, 0);
		$data['dashboard_tree'] = $tree_page['rows'];
		$data['dashboard_tree_total'] = (int)$tree_page['total'];
		$data['dashboard_tree_page_size'] = 300;

		// Chart data (sorted desc by total, filter out 0-total categories)
		$chart_rows = array_values(array_filter($data['dashboard_rows'], function ($r) {
			return (int)($r['total'] ?? 0) > 0;
		}));
		$data['chart_labels'] = array_map(function ($r) { return (string)$r['name']; }, $chart_rows);
		$data['chart_totals'] = array_map(function ($r) { return (int)$r['total']; }, $chart_rows);
		$data['chart_scores'] = array_map(function ($r) { return (int)$r['score']; }, $chart_rows);

		// install.json metadata
		$meta = $this->readManifest();
		$data['module_version'] = $meta['version'] ?? '0.0.0';
		$data['module_repository'] = $meta['repository'] ?? '';
		$data['module_link'] = $meta['link'] ?? '';

		$data['error_warning'] = $this->error['warning'] ?? '';

		// i18n strings
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_dashboard'] = $this->language->get('text_dashboard');
		$data['text_no_results'] = $this->language->get('text_no_results');
		$data['tab_settings'] = $this->language->get('tab_settings');
		$data['tab_dashboard'] = $this->language->get('tab_dashboard');
		$data['tab_overrides'] = $this->language->get('tab_overrides');
		$data['tab_updates'] = $this->language->get('tab_updates');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_hide_empty'] = $this->language->get('entry_hide_empty');
		$data['entry_hide_empty_subs'] = $this->language->get('entry_hide_empty_subs');
		$data['entry_sort_by_score'] = $this->language->get('entry_sort_by_score');
		$data['entry_weight_volume'] = $this->language->get('entry_weight_volume');
		$data['entry_cache_ttl'] = $this->language->get('entry_cache_ttl');
		$data['entry_override'] = $this->language->get('entry_override');
		$data['help_hide_empty'] = $this->language->get('help_hide_empty');
		$data['help_hide_empty_subs'] = $this->language->get('help_hide_empty_subs');
		$data['help_sort_by_score'] = $this->language->get('help_sort_by_score');
		$data['help_cache_ttl'] = $this->language->get('help_cache_ttl');
		$data['text_auto'] = $this->language->get('text_auto');
		$data['text_force_show'] = $this->language->get('text_force_show');
		$data['text_force_hide'] = $this->language->get('text_force_hide');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');
		$data['button_recalculate'] = $this->language->get('button_recalculate');
		$data['button_check_updates'] = $this->language->get('button_check_updates');
		$data['button_download'] = $this->language->get('button_download');
		$data['column_name'] = $this->language->get('column_name');
		$data['column_total'] = $this->language->get('column_total');
		$data['column_score'] = $this->language->get('column_score');
		$data['column_override'] = $this->language->get('column_override');
		$data['column_status'] = $this->language->get('column_status');
		$data['text_updates_intro'] = $this->language->get('text_updates_intro');
		$data['text_current_version'] = $this->language->get('text_current_version');
		$data['text_latest_version'] = $this->language->get('text_latest_version');
		$data['text_up_to_date'] = $this->language->get('text_up_to_date');
		$data['text_update_available'] = $this->language->get('text_update_available');
		$data['text_repository'] = $this->language->get('text_repository');
		$data['text_no_repository'] = $this->language->get('text_no_repository');
		$data['text_checking'] = $this->language->get('text_checking');
		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/oc_category_merch/module/category_merch', $data));
	}

	public function save(): void {
		$this->load->language('extension/oc_category_merch/module/category_merch');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/oc_category_merch/module/category_merch')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			// Prefer JSON payload (comprehensive state), fall back to legacy array form
			$overrides = [];
			$raw_json = (string)($this->request->post['overrides_json'] ?? '');
			if ($raw_json !== '') {
				$decoded = json_decode($raw_json, true);
				if (is_array($decoded)) {
					$overrides = $decoded;
				}
			} else {
				$overrides = $this->request->post['module_oc_category_merch_overrides'] ?? [];
				if (!is_array($overrides)) {
					$overrides = [];
				}
			}

			$clean_overrides = [];

			foreach ($overrides as $category_id => $mode) {
				$cid = (int)$category_id;
				if ($cid <= 0) {
					continue;
				}

				$imode = (int)$mode;
				if (!in_array($imode, [-1, 0, 1], true)) {
					$imode = 0;
				}

				// Auto (0) is the default; don't persist to keep the setting small.
				if ($imode === 0) {
					continue;
				}

				$clean_overrides[$cid] = $imode;
			}

			$post = [
				'module_oc_category_merch_status' => (int)($this->request->post['module_oc_category_merch_status'] ?? 0),
				'module_oc_category_merch_hide_empty' => (int)($this->request->post['module_oc_category_merch_hide_empty'] ?? 0),					'module_oc_category_merch_hide_empty_subs' => (int)($this->request->post['module_oc_category_merch_hide_empty_subs'] ?? 0),				'module_oc_category_merch_sort_by_score' => (int)($this->request->post['module_oc_category_merch_sort_by_score'] ?? 0),
				'module_oc_category_merch_weight_volume' => max(0, min(100, (int)($this->request->post['module_oc_category_merch_weight_volume'] ?? 100))),
				'module_oc_category_merch_cache_ttl' => max(30, min(86400, (int)($this->request->post['module_oc_category_merch_cache_ttl'] ?? 300))),
				'module_oc_category_merch_overrides' => $clean_overrides,
				'module_oc_category_merch_cache_version' => (int)$this->config->get('module_oc_category_merch_cache_version') + 1
			];

			$this->model_setting_setting->editSetting('module_oc_category_merch', $post);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function recalculate(): void {
		$this->load->language('extension/oc_category_merch/module/category_merch');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/oc_category_merch/module/category_merch')) {
			$json['error'] = $this->language->get('error_permission');
		}

		if (!$json) {
			$this->load->model('setting/setting');

			$current = $this->model_setting_setting->getSetting('module_oc_category_merch');
			$current['module_oc_category_merch_cache_version'] = (int)($current['module_oc_category_merch_cache_version'] ?? 0) + 1;

			$this->model_setting_setting->editSetting('module_oc_category_merch', $current);

			$json['success'] = $this->language->get('text_success');
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function checkUpdates(): void {
		$this->load->language('extension/oc_category_merch/module/category_merch');
		$this->response->addHeader('Content-Type: application/json');

		$json = [];

		if (!$this->user->hasPermission('modify', 'extension/oc_category_merch/module/category_merch')) {
			$json['error'] = $this->language->get('error_permission');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$meta = $this->readManifest();
		$current_version = (string)($meta['version'] ?? '0.0.0');
		$manifest_url = trim((string)($meta['update_manifest_url'] ?? ''));
		$repo = trim((string)($meta['repository'] ?? ''));

		$json['current_version'] = $current_version;

		// Prefer static manifest URL (simpler, no GitHub required)
		if ($manifest_url !== '' && preg_match('#^https://#i', $manifest_url)) {
			$body = $this->httpGet($manifest_url, ['Accept: application/json']);

			if ($body === null) {
				$json['error'] = $this->language->get('error_update_check');
				$this->response->setOutput(json_encode($json));
				return;
			}

			$manifest = json_decode($body, true);

			if (!is_array($manifest) || empty($manifest['version']) || empty($manifest['download_url'])) {
				$json['error'] = $this->language->get('error_update_check');
				$this->response->setOutput(json_encode($json));
				return;
			}

			$latest_version = ltrim((string)$manifest['version'], 'vV');
			$download_url = (string)$manifest['download_url'];

			$json['latest_version'] = $latest_version;
			$json['download_url'] = $download_url;
			$json['release_url'] = (string)($manifest['release_url'] ?? '');
			$json['notes'] = (string)($manifest['notes'] ?? '');
			$json['update_available'] = version_compare($latest_version, $current_version, '>');

			$this->response->setOutput(json_encode($json));
			return;
		}

		// Fallback: GitHub Releases
		if ($repo === '' || !preg_match('#^[A-Za-z0-9._-]+/[A-Za-z0-9._-]+$#', $repo)) {
			$json['error'] = $this->language->get('text_no_repository');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$body = $this->httpGet('https://api.github.com/repos/' . $repo . '/releases/latest', ['Accept: application/vnd.github+json']);

		if ($body === null) {
			$json['error'] = $this->language->get('error_update_check');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$release = json_decode($body, true);

		if (!is_array($release)) {
			$json['error'] = $this->language->get('error_update_check');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$tag = (string)($release['tag_name'] ?? '');
		$latest_version = ltrim($tag, 'vV');
		$download_url = '';

		if (!empty($release['assets']) && is_array($release['assets'])) {
			foreach ($release['assets'] as $asset) {
				if (!empty($asset['browser_download_url']) && str_ends_with((string)$asset['name'], '.ocmod.zip')) {
					$download_url = (string)$asset['browser_download_url'];
					break;
				}
			}
		}

		if ($download_url === '' && !empty($release['zipball_url'])) {
			$download_url = (string)$release['zipball_url'];
		}

		$json['latest_version'] = $latest_version;
		$json['download_url'] = $download_url;
		$json['release_url'] = (string)($release['html_url'] ?? '');
		$json['update_available'] = $latest_version !== '' ? version_compare($latest_version, $current_version, '>') : false;

		$this->response->setOutput(json_encode($json));
	}

	public function installUpdate(): void {
		$this->load->language('extension/oc_category_merch/module/category_merch');
		$this->response->addHeader('Content-Type: application/json');

		if (!$this->user->hasPermission('modify', 'extension/oc_category_merch/module/category_merch')) {
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_permission')]));
			return;
		}

		$download_url = (string)($this->request->get['download_url'] ?? $this->request->post['download_url'] ?? '');

		// Validate: must come from a trusted origin (same domain as manifest, or github.com)
		$meta = $this->readManifest();
		$manifest_url = trim((string)($meta['update_manifest_url'] ?? ''));
		$allowed_prefixes = ['https://github.com/', 'https://api.github.com/', 'https://codeload.github.com/'];

		if ($manifest_url !== '') {
			$parts = parse_url($manifest_url);
			if (!empty($parts['scheme']) && !empty($parts['host'])) {
				$allowed_prefixes[] = $parts['scheme'] . '://' . $parts['host'] . '/';
			}
		}

		$trusted = false;
		foreach ($allowed_prefixes as $prefix) {
			if (str_starts_with($download_url, $prefix)) {
				$trusted = true;
				break;
			}
		}

		if (!$trusted) {
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_untrusted_url')]));
			return;
		}

		// Download ZIP
		$tmp_file = tempnam(sys_get_temp_dir(), 'ocm_update_') . '.zip';
		$ch = curl_init($download_url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_MAXREDIRS => 5,
			CURLOPT_USERAGENT => 'oc_category_merch-updater'
		]);
		$zip_data = curl_exec($ch);
		$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($http !== 200 || !$zip_data || strlen($zip_data) < 500) {
			@unlink($tmp_file);
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_update_download')]));
			return;
		}

		file_put_contents($tmp_file, $zip_data);

		// Extract
		$zip = new \ZipArchive();
		if ($zip->open($tmp_file) !== true) {
			@unlink($tmp_file);
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_update_extract')]));
			return;
		}

		$tmp_extract = sys_get_temp_dir() . '/ocm_update_extract_' . time() . '_' . bin2hex(random_bytes(4)) . '/';
		if (!@mkdir($tmp_extract, 0755, true)) {
			$zip->close();
			@unlink($tmp_file);
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_update_extract')]));
			return;
		}

		$zip->extractTo($tmp_extract);
		$zip->close();
		@unlink($tmp_file);

		// Some zips (github zipball) wrap content in a single top-level folder — detect and unwrap
		$entries = array_values(array_diff(scandir($tmp_extract) ?: [], ['.', '..']));
		if (count($entries) === 1 && is_dir($tmp_extract . $entries[0]) && !file_exists($tmp_extract . 'install.json')) {
			$tmp_extract = rtrim($tmp_extract, '/') . '/' . $entries[0] . '/';
		}

		$ext_dir = DIR_EXTENSION . 'oc_category_merch/';

		if (!is_dir($ext_dir)) {
			$this->rrmdir(sys_get_temp_dir() . '/ocm_update_extract_' . basename(dirname($tmp_extract)));
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_ext_dir_missing')]));
			return;
		}

		// Copy files over
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($tmp_extract, \RecursiveDirectoryIterator::SKIP_DOTS),
			\RecursiveIteratorIterator::SELF_FIRST
		);

		$copied = 0;
		$failed = [];
		$src_base = rtrim($tmp_extract, '/') . '/';
		foreach ($iterator as $item) {
			$relative = ltrim(str_replace($src_base, '', $item->getPathname()), '/');
			if ($relative === '') continue;
			$target = $ext_dir . $relative;
			if ($item->isDir()) {
				if (!is_dir($target)) {
					@mkdir($target, 0755, true);
				}
			} else {
				if (is_file($target)) {
					@unlink($target);
				}
				if (@copy($item->getPathname(), $target)) {
					@chmod($target, 0644);
					$copied++;
				} else {
					$failed[] = $relative;
				}
			}
		}

		// Cleanup extract dir (walk up to the root the tmp created)
		$root_tmp = $tmp_extract;
		while (dirname($root_tmp) !== sys_get_temp_dir() && dirname($root_tmp) !== '/' && str_starts_with($root_tmp, sys_get_temp_dir())) {
			$parent = dirname(rtrim($root_tmp, '/'));
			if ($parent === $root_tmp || !str_starts_with(basename($parent), 'ocm_update_extract_')) break;
			$root_tmp = $parent . '/';
		}
		$this->rrmdir($root_tmp);

		if ($copied === 0) {
			$this->response->setOutput(json_encode(['error' => true, 'message' => $this->language->get('error_update_write') . ' (' . implode(', ', array_slice($failed, 0, 3)) . ')']));
			return;
		}

		// Bump cache version to invalidate front-end caches
		$this->load->model('setting/setting');
		$settings = $this->model_setting_setting->getSetting('module_oc_category_merch');
		$settings['module_oc_category_merch_cache_version'] = (int)($settings['module_oc_category_merch_cache_version'] ?? 1) + 1;
		$this->model_setting_setting->editSetting('module_oc_category_merch', $settings);

		// Read new version from freshly-installed manifest
		$new_meta = $this->readManifest();

		$this->response->setOutput(json_encode([
			'success' => true,
			'copied' => $copied,
			'failed' => $failed,
			'new_version' => (string)($new_meta['version'] ?? '')
		]));
	}

	private function httpGet(string $url, array $headers = []): ?string {
		$ch = curl_init($url);
		curl_setopt_array($ch, [
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_TIMEOUT => 10,
			CURLOPT_CONNECTTIMEOUT => 5,
			CURLOPT_USERAGENT => 'oc_category_merch-updater',
			CURLOPT_HTTPHEADER => $headers
		]);
		$body = curl_exec($ch);
		$http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($http !== 200 || !$body) {
			return null;
		}
		return $body;
	}

	private function rrmdir(string $dir): void {
		if (!is_dir($dir)) return;
		$items = array_diff(scandir($dir) ?: [], ['.', '..']);
		foreach ($items as $item) {
			$path = $dir . (str_ends_with($dir, '/') ? '' : '/') . $item;
			if (is_dir($path)) {
				$this->rrmdir($path);
			} else {
				@unlink($path);
			}
		}
		@rmdir($dir);
	}

	public function overrides(): void {
		$this->load->language('extension/oc_category_merch/module/category_merch');

		$json = [];

		if (!$this->user->hasPermission('access', 'extension/oc_category_merch/module/category_merch')) {
			$json['error'] = $this->language->get('error_permission');
			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$search = trim((string)($this->request->get['search'] ?? ''));
		$page = max(1, (int)($this->request->get['page'] ?? 1));
		$limit = 300;
		$offset = ($page - 1) * $limit;

		$this->load->model('extension/oc_category_merch/module/category_merch');
		$result = $this->model_extension_oc_category_merch_module_category_merch->getCategoryTreeWithScore($search, $limit, $offset);

		$overrides = $this->config->get('module_oc_category_merch_overrides');
		if (!is_array($overrides)) {
			$overrides = [];
		}

		$rows = [];
		foreach ($result['rows'] as $r) {
			$cid = (int)$r['category_id'];
			$rows[] = [
				'category_id' => $cid,
				'name' => (string)$r['name'],
				'level' => (int)$r['level'],
				'total' => (int)$r['total'],
				'score' => (int)$r['score'],
				'status' => (int)$r['status'],
				'mode' => isset($overrides[$cid]) ? (int)$overrides[$cid] : 0
			];
		}

		$json['rows'] = $rows;
		$json['total'] = (int)$result['total'];
		$json['page'] = $page;
		$json['pages'] = (int)ceil($result['total'] / $limit);

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}

	public function install(): void {
		if (!$this->user->hasPermission('modify', 'extension/module')) {
			return;
		}

		$this->load->model('setting/event');

		$this->model_setting_event->deleteEventByCode('oc_category_merch');
		$this->model_setting_event->addEvent([
			'code' => 'oc_category_merch',
			'trigger' => 'catalog/view/common/menu/before',
			'action' => 'extension/oc_category_merch/events.filterMenu',
			'description' => 'OC4 Category Merch menu filtering',
			'status' => 1,
			'sort_order' => 1
		]);

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_oc_category_merch', [
			'module_oc_category_merch_status' => 0,
			'module_oc_category_merch_hide_empty' => 1,
			'module_oc_category_merch_hide_empty_subs' => 1,
			'module_oc_category_merch_sort_by_score' => 1,
			'module_oc_category_merch_weight_volume' => 100,
			'module_oc_category_merch_cache_ttl' => 300,
			'module_oc_category_merch_overrides' => [],
			'module_oc_category_merch_cache_version' => 1
		]);
	}

	public function uninstall(): void {
		if (!$this->user->hasPermission('modify', 'extension/module')) {
			return;
		}

		$this->load->model('setting/event');
		$this->load->model('setting/setting');

		$this->model_setting_event->deleteEventByCode('oc_category_merch');
		$this->model_setting_setting->deleteSetting('module_oc_category_merch');
	}

	private function readManifest(): array {
		$path = DIR_EXTENSION . 'oc_category_merch/install.json';

		if (!is_file($path)) {
			return [];
		}

		$raw = @file_get_contents($path);
		if (!$raw) {
			return [];
		}

		$decoded = json_decode($raw, true);
		return is_array($decoded) ? $decoded : [];
	}
}
