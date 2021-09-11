<?php


namespace OCA\Health\Services;


use OCA\Health\Db\Activitiesdata;
use OCA\Health\Db\ActivitiesdataMapper;
use OCA\Health\Db\Measurementdata;
use OCA\Health\Db\MeasurementdataMapper;
use OCA\Health\Exception\ExceptionOnOpeningDatabase;
use OCP\Files\File;
use OCP\Files\IRootFolder;

class GadgedtbridgeImportService
{
	protected IRootFolder $rootFolder;
	protected MeasurementdataService $measurementdataService;
	protected MeasurementdataMapper $measurementdataMapper;
	protected ActivitiesdataService $activitiesdataService;
	protected ActivitiesdataMapper $activitiesdataMapper;

	/**
	 * GadgedtbridgeImportService constructor.
	 * @param IRootFolder $rootFolder
	 * @param MeasurementdataService $measurementdataService
	 * @param MeasurementdataMapper $measurementdataMapper
	 * @param ActivitiesdataService $activitiesdataService
	 * @param ActivitiesdataMapper $ActivitiesdataMapper
	 */
	public function __construct(IRootFolder $rootFolder, MeasurementdataService $measurementdataService, MeasurementdataMapper $measurementdataMapper, ActivitiesdataService $activitiesdataService, ActivitiesdataMapper $ActivitiesdataMapper)
	{
		$this->rootFolder = $rootFolder;
		$this->measurementdataService = $measurementdataService;
		$this->measurementdataMapper = $measurementdataMapper;
		$this->activitiesdataService = $activitiesdataService;
		$this->activitiesdataMapper = $ActivitiesdataMapper;
	}


	public function import(string $userId, int $personId, string $filePath): void
	{
		try {
			$db = new \SQLite3($this->getFile($userId, $filePath));
			$rows = $db->query('select * from "MI_BAND_ACTIVITY_SAMPLE"');
			if (!$rows){
				throw new ExceptionOnOpeningDatabase('This is not a suported database file');
			}
			while ($result = $rows->fetchArray(SQLITE3_ASSOC)) {
				if ($this->isHearthbeatMesurement($result)) {
					$this->createHearthbeatMesurement(
						$personId,
						$this->createDateTimeFromTimestamp($result['TIMESTAMP']),
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

		} catch (\Exception | \Error $exception){
			throw new ExceptionOnOpeningDatabase($exception->getMessage());
		}

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
			// insert directly since is a background job
			$d = new Measurementdata();
			$d->setDatetime($dateTime->format('Y-m-d H:i:s'));
			$d->setHeartRate($hearthbeat);
			$d->setPersonId($personId);
			$this->measurementdataMapper->insert($d);
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
		if (!$this->activitiesdataService->exists($personId, $dateTime)) {
			// insert directly since is a background job
			$d = new Activitiesdata();
			$d->setDatetime($dateTime->format('Y-m-d H:i:s'));
			$d->setDistance($steps);
			$d->setPersonId($personId);
			$this->activitiesdataMapper->insert($d);
		}

	}

	private function getFile(string $userId, string $filePath): string
	{
		return $this->getLocalFile($this->rootFolder->getUserFolder($userId)->get($filePath));
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

}
