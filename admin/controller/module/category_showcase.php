<?php
namespace Opencart\Admin\Controller\Extension\CategoryMerch\Module;

class CategoryShowcase extends \Opencart\System\Engine\Controller {
	private array $error = [];

	public function index(): void {
		$this->load->language('extension/category_merch/module/category_showcase');
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
			'href' => $this->url->link('extension/category_merch/module/category_showcase', 'user_token=' . $this->session->data['user_token'])
		];

		if ($this->request->server['REQUEST_METHOD'] === 'POST' && $this->validate()) {
			$this->load->model('setting/setting');

			$manual_ids = array_filter(array_map('intval', explode(',', (string)($this->request->post['module_category_showcase_manual_ids'] ?? ''))));

			$this->model_setting_setting->editSetting('module_category_showcase', [
				'module_category_showcase_status' => !empty($this->request->post['module_category_showcase_status']) ? 1 : 0,
				'module_category_showcase_limit' => max(1, min(12, (int)($this->request->post['module_category_showcase_limit'] ?? 8))),
				'module_category_showcase_manual_ids' => implode(',', $manual_ids)
			]);

			$json['success'] = $this->language->get('text_success');

			$this->response->addHeader('Content-Type: application/json');
			$this->response->setOutput(json_encode($json));
			return;
		}

		$data['save'] = $this->url->link('extension/category_merch/module/category_showcase', 'user_token=' . $this->session->data['user_token']);
		$data['back'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=module');

		$data['module_category_showcase_status'] = (int)$this->config->get('module_category_showcase_status');
		$data['module_category_showcase_limit'] = (int)$this->config->get('module_category_showcase_limit') ?: 8;
		$data['module_category_showcase_manual_ids'] = (string)$this->config->get('module_category_showcase_manual_ids');

		$data['heading_title'] = $this->language->get('heading_title');
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_limit'] = $this->language->get('entry_limit');
		$data['help_limit'] = $this->language->get('help_limit');
		$data['entry_manual_ids'] = $this->language->get('entry_manual_ids');
		$data['help_manual_ids'] = $this->language->get('help_manual_ids');
		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('extension/category_merch/module/category_showcase', $data));
	}

	private function validate(): bool {
		if (!$this->user->hasPermission('modify', 'extension/category_merch/module/category_showcase')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}

	public function install(): void {
		if (!$this->user->hasPermission('modify', 'extension/module')) {
			return;
		}

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('category_showcase');
		$this->model_setting_event->addEvent([
			'code' => 'category_showcase',
			'trigger' => 'catalog/view/common/home/after',
			'action' => 'extension/category_merch/events.appendShowcase',
			'description' => 'OC4 Category Merch: homepage best-collections showcase',
			'status' => 1,
			'sort_order' => 1
		]);

		$this->load->model('setting/setting');
		$this->model_setting_setting->editSetting('module_category_showcase', [
			'module_category_showcase_status' => 0,
			'module_category_showcase_limit' => 8,
			'module_category_showcase_manual_ids' => ''
		]);
	}

	public function uninstall(): void {
		if (!$this->user->hasPermission('modify', 'extension/module')) {
			return;
		}

		$this->load->model('setting/event');
		$this->model_setting_event->deleteEventByCode('category_showcase');

		$this->load->model('setting/setting');
		$this->model_setting_setting->deleteSetting('module_category_showcase');
	}
}
