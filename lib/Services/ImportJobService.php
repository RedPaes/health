<?php

namespace OCA\Health\Services;


use OCA\Health\Job\GadgedbridgeImportJob;
use OCP\BackgroundJob\IJobList;

class ImportJobService
{
	protected IJobList $jobList;

	public function __construct(IJobList $jobList)
	{
		$this->jobList = $jobList;
	}

	public function hasImportJobConfigured(string $userId, int $personId): bool
	{
		return $this->jobList->has(GadgedbridgeImportJob::class, ['personId' => $personId, 'userId' => $userId]);
	}

	public function addImportJob(string $userId, int $personId, string $filePath): void
	{
		if ($this->jobList->has(GadgedbridgeImportJob::class, ['personId' => $personId, 'userId' => $userId,])) {
			$this->jobList->remove(GadgedbridgeImportJob::class, ['personId' => $personId, 'userId' => $userId,]);
		}
		$this->jobList->add(GadgedbridgeImportJob::class, [
			'personId' => $personId,
			'filePath' => $filePath,
			'userId' => $userId,
		]);
	}
}
