<?php

namespace OCA\Health\Job;

use OCA\Health\Services\GadgedtbridgeImportService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

class GadgedbridgeImportJob extends TimedJob
{

	protected GadgedtbridgeImportService $gadgedtbridgeImportService;

	/**
	 * GadgedbridgeImportJob constructor.
	 * @param GadgedtbridgeImportService $gadgedtbridgeImportService
	 */
	public function __construct(ITimeFactory $time, GadgedtbridgeImportService $gadgedtbridgeImportService)
	{
		parent::__construct($time);
		$this->gadgedtbridgeImportService = $gadgedtbridgeImportService;
		parent::setInterval(3600);
	}

	protected function run($argument)
	{
		$this->gadgedtbridgeImportService->import($argument['userId'], $argument['personId'], $argument['filePath']);
	}
}
