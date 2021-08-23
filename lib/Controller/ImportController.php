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

use OCA\Health\Services\ActivitiesdataService;
use OCA\Health\Services\MeasurementdataService;
use OCP\AppFramework\Http\JSONResponse;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use OCP\IRequest;
use OCP\AppFramework\Controller;

class ImportController extends Controller
{

	protected $userId;
	protected $storage;
	protected $rootFolder;
	protected $measurementdataService;
	protected $activityDataService;

	public function __construct($appName,
								IRequest $request,
								IRootFolder $rootFolder,
								MeasurementdataService $measurementdataService,
								ActivitiesdataService $activitiesdataService,
								$userId)
	{
		parent::__construct($appName, $request);
		$this->userId = $userId;
		$this->rootFolder = $rootFolder;
		$this->measurementdataService = $measurementdataService;
		$this->activityDataService = $activitiesdataService;
	}

	private function getLocalFile(File $file): string
	{
		$useTempFile = $file->isEncrypted() || !$file->getStorage()->isLocal();
		if ($useTempFile) {
			// todo implement improt from external or encrypted storage
			throw new \Exception("External or Encrypted files are not supported");
			// $absPath = \OC::$server->getTempManager()->getTemporaryFile();
			// $content = $file->fopen('r');
			// file_put_contents($absPath, $content);
			// $this->tmpFiles[] = $absPath;
			// return $absPath;
		} else {
			return $file->getStorage()->getLocalFile($file->getInternalPath());
		}
	}

	public function getFile(string $filePath)
	{
		return $this->getLocalFile($this->rootFolder->getUserFolder($this->userId)->get($filePath));
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 */
	public function gadgetbridge(int $personId, string $filePath)
	{
		$db = new \SQLite3($this->getFile($filePath));
		$rows = $db->query('select * from "MI_BAND_ACTIVITY_SAMPLE"');
		while ($result = $rows->fetchArray(SQLITE3_ASSOC)) {
			if ($this->isHearthbeatMesurement($result)) {
				$this->createHearthbeatMesurement(
					$personId, $this->createDateTimeFromTimestamp($result['TIMESTAMP']),
					$result['HEART_RATE']
				);
			}
			if ($this->isStepMeasurement($result)) {
				$this->createActivityStep(
					$personId, $this->createDateTimeFromTimestamp($result['TIMESTAMP']),
					$result['STEPS']
				);
			}
		}

		return new JSONResponse([
			'userId' => $this->userId,
			'fileId' => $filePath,
			'personId' => $personId,
			'path' => $this->getFile($filePath),
		]);
	}

	private function isStepMeasurement($result): bool
	{
		return ($result['STEPS'] > 0);
	}

	private function isHearthbeatMesurement($result): bool
	{
		return ($result['HEART_RATE'] < 255 && $result['HEART_RATE'] > 20);
	}

	private function createHearthbeatMesurement(
		int $personId, \DateTime $dateTime,
		int $hearthbeat): void
	{
		if (!$this->measurementdataService->exists($personId, $dateTime)) {
			$this->measurementdataService->create(
				$personId,
				$this->formatDateTime($dateTime),
				null,
				$hearthbeat,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
				null,
			);
		}

	}

	private function createDateTimeFromTimestamp(int $timestamp): \DateTime
	{
		return new \DateTime("@" . $timestamp);
	}

	private function formatDateTime(\DateTime $dateTime): string
	{
		return $dateTime->format(DATE_ATOM);
	}

	private function createActivityStep(int $personId, \DateTime $dateTime, $steps)
	{
		if (!$this->activityDataService->exists($personId, $dateTime)) {
			$this->activityDataService->create(
				$personId,
				$this->formatDateTime($dateTime),
				null,
				null,
				null,
				null,
				null,
				$steps,
				null
			);
		}

	}
}
