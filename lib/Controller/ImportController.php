<?php
declare(strict_types=1);
/**
 * @copyright Copyright (c) 2020 Florian Steffens <flost-dev@mailbox.org>
 *
 * @author Florian Steffens <flost-dev@mailbox.org>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\Health\Controller;

use OCA\Health\Exception\ExceptionOnOpeningDatabase;
use OCA\Health\Services\GadgedtbridgeImportService;
use OCA\Health\Services\ImportJobService;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\AppFramework\Controller;

class ImportController extends Controller
{
	protected $userId;
	private ImportJobService $importJobService;
	private GadgedtbridgeImportService $gadgedtbridgeImportService;

	public function __construct($appName,
								ImportJobService $importJobService,
								IRequest $request,
								GadgedtbridgeImportService $gadgedtbridgeImportService,
								$userId)
	{
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->importJobService = $importJobService;
		$this->gadgedtbridgeImportService = $gadgedtbridgeImportService;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function createInportJob(int $personId, string $filePath)
	{
		try {
			$this->gadgedtbridgeImportService->import($this->userId, $personId, $filePath);
		} catch (ExceptionOnOpeningDatabase $exception) {
			return new JSONResponse([
				'error' => $exception->getMessage()
			],
			Http::STATUS_BAD_REQUEST);
		}
		$this->importJobService->addImportJob($this->userId, $personId, $filePath);
		return new JSONResponse([
			'userId' => $this->userId,
			'fileId' => $filePath,
			'personId' => $personId,
		]);
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function hasImportJob(int $personId)
	{
		return new JSONResponse([
			'isConfigured' => !$this->importJobService->hasImportJobConfigured($this->userId, $personId)]);
	}

}
