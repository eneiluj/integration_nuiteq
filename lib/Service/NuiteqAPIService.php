<?php
/**
 * Nextcloud - Nuiteq integration
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier
 * @copyright Julien Veyssier 2022
 */

namespace OCA\Nuiteq\Service;

use Exception;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use OCA\Nuiteq\AppInfo\Application;
use OCP\AppFramework\Http;
use OCP\IConfig;
use OCP\IL10N;
use Psr\Log\LoggerInterface;
use OCP\Http\Client\IClientService;
use Throwable;

class NuiteqAPIService {
	/**
	 * @var string
	 */
	private $appName;
	/**
	 * @var LoggerInterface
	 */
	private $logger;
	/**
	 * @var IL10N
	 */
	private $l10n;
	/**
	 * @var \OCP\Http\Client\IClient
	 */
	private $client;
	private IConfig $config;

	/**
	 * Service to make requests to Nuiteq Stage API
	 */
	public function __construct (string $appName,
								LoggerInterface $logger,
								IL10N $l10n,
								IConfig $config,
								IClientService $clientService) {
		$this->appName = $appName;
		$this->logger = $logger;
		$this->l10n = $l10n;
		$this->client = $clientService->newClient();
		$this->config = $config;
	}

	/**
	 * @param string $url
	 * @param string $accessToken
	 * @param string $endPoint
	 * @param array $params
	 * @param string $method
	 * @return array
	 */
	public function request(string $url, string $accessToken, string $endPoint, array $params = [],
							string $method = 'GET'): array {
		try {
			$url = $url . '/api/v4/' . $endPoint;
			$options = [
				'headers' => [
					'Authorization'  => 'Bearer ' . $accessToken,
					'User-Agent' => 'Nextcloud Nuiteq integration'
				],
			];

			if (count($params) > 0) {
				if ($method === 'GET') {
					// manage array parameters
					$paramsContent = '';
					foreach ($params as $key => $value) {
						if (is_array($value)) {
							foreach ($value as $oneArrayValue) {
								$paramsContent .= $key . '[]=' . urlencode($oneArrayValue) . '&';
							}
							unset($params[$key]);
						}
					}
					$paramsContent .= http_build_query($params);

					$url .= '?' . $paramsContent;
				} else {
					$options['body'] = $params;
				}
			}

			if ($method === 'GET') {
				$response = $this->client->get($url, $options);
			} else if ($method === 'POST') {
				$response = $this->client->post($url, $options);
			} else if ($method === 'PUT') {
				$response = $this->client->put($url, $options);
			} else if ($method === 'DELETE') {
				$response = $this->client->delete($url, $options);
			} else {
				return ['error' => $this->l10n->t('Bad HTTP method')];
			}
			$body = $response->getBody();
			$respCode = $response->getStatusCode();

			if ($respCode >= 400) {
				return ['error' => $this->l10n->t('Bad credentials')];
			} else {
				return json_decode($body, true);
			}
		} catch (Exception $e) {
			$this->logger->warning('Nuiteq API error : '.$e->getMessage(), array('app' => $this->appName));
			return ['error' => $e->getMessage()];
		}
	}

	public function login(string $baseUrl, string $login, string $password): array {
		try {
			$url = $baseUrl . '/api/v1/login';
			$defaultClientKey = 'EJZBdijln5TcgjAbzxDwm8Ms0AQa99RsBPiWVEhoMMg0dnsLYZiCS0R4C6pmspt';
			$adminClientKey = $this->config->getAppValue(Application::APP_ID, 'client_id', $defaultClientKey) ?: $defaultClientKey;
			$clientKey = $this->config->getAppValue(Application::APP_ID, 'client_key', $adminClientKey) ?: $adminClientKey;
			$options = [
				'headers' => [
					'User-Agent'  => 'Nextcloud NUITEQ integration',
//					'Content-Type' => 'application/x-www-form-urlencoded',
					'Content-Type' => 'application/json',
				],
				'json' => [
					'username' => $login,
					'password' => $password,
					'clientKey' => $clientKey,
				],
			];
			$response = $this->client->post($url, $options);
			$body = $response->getBody();
			$respCode = $response->getStatusCode();

			if ($respCode >= 400) {
				return ['error' => $this->l10n->t('Invalid credentials')];
			} else {
				try {
					return json_decode($body, true);
				} catch (Exception | Throwable $e) {
					$this->logger->warning('NUITEQ invalid login response : '.$e->getMessage(), ['app' => $this->appName]);
					return ['error' => $this->l10n->t('Invalid response')];
				}
			}
		} catch (ClientException | ServerException $e) {
			$response = $e->getResponse();
			if ($response->getStatusCode() === Http::STATUS_FORBIDDEN) {
				$body = $response->getBody();
				try {
					return json_decode($body, true);
				} catch (Exception | Throwable $e2) {
				}
			}
			return ['error' => $e->getMessage()];
		} catch (Exception $e) {
			$this->logger->warning('NUITEQ login error : '.$e->getMessage(), ['app' => $this->appName]);
			return ['error' => $e->getMessage()];
		}
	}
}
