<?php
/**
 * Nextcloud - Nuiteq
 *
 * This file is licensed under the Affero General Public License version 3 or
 * later. See the COPYING file.
 *
 * @author Julien Veyssier <eneiluj@posteo.net>
 * @copyright Julien Veyssier 2022
 */

return [
	'routes' => [
		['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],

		['name' => 'nuiteqAPI#newBoard', 'url' => '/new', 'verb' => 'POST'],
		['name' => 'nuiteqAPI#getBoards', 'url' => '/list', 'verb' => 'GET'],
		['name' => 'nuiteqAPI#deleteBoard', 'url' => '/delete', 'verb' => 'POST'],

		['name' => 'config#setAdminConfig', 'url' => '/admin-config', 'verb' => 'PUT'],
		['name' => 'config#setConfig', 'url' => '/config', 'verb' => 'PUT'],
	]
];
