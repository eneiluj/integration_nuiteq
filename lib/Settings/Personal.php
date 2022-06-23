<?php
namespace OCA\Nuiteq\Settings;

use OCP\AppFramework\Http\TemplateResponse;
use OCP\AppFramework\Services\IInitialState;
use OCP\IConfig;
use OCP\Settings\ISettings;

use OCA\Nuiteq\AppInfo\Application;

class Personal implements ISettings {

	/**
	 * @var IConfig
	 */
	private $config;
	/**
	 * @var IInitialState
	 */
	private $initialStateService;
	private ?string $userId;

	public function __construct(IConfig $config,
								IInitialState $initialStateService,
								?string $userId) {
		$this->config = $config;
		$this->initialStateService = $initialStateService;
		$this->userId = $userId;
	}

	/**
	 * @return TemplateResponse
	 */
	public function getForm(): TemplateResponse {
		$adminBaseUrl = $this->config->getAppValue(Application::APP_ID, 'base_url');
		$baseUrl = $this->config->getUserValue($this->userId, Application::APP_ID, 'base_url', $adminBaseUrl) ?: $adminBaseUrl;

		$apiKey = $this->config->getUserValue($this->userId, Application::APP_ID, 'api_key');
		$userName = $this->config->getUserValue($this->userId, Application::APP_ID, 'user_name');

		$userConfig = [
			'base_url' => $baseUrl,
			'user_name' => $apiKey ? $userName : '',
		];
		$this->initialStateService->provideInitialState('user-config', $userConfig);
		return new TemplateResponse(Application::APP_ID, 'personalSettings');
	}

	public function getSection(): string {
		return 'connected-accounts';
	}

	public function getPriority(): int {
		return 10;
	}
}
