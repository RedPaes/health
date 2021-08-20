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

use OCA\Health\Services\MeasurementdataService;
use OCP\IRequest;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http\DataResponse;

class MeasurementdataController extends Controller {

	protected $userId;
	protected $measurementdataService;

	public function __construct($appName, IRequest $request, MeasurementdataService $mS, $userId) {
		parent::__construct($appName, $request);
		$this->measurementdataService = $mS;
		$this->userId = $userId;
	}

	/**
	 * @NoAdminRequired
	 * @NoCSRFRequired
	 *
	 * @param int $personId
	 * @return DataResponse
	 */
	public function findByPerson(int $personId): DataResponse
	{
        return new DataResponse($this->measurementdataService->getAllByPersonId($personId));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $personId
	 * @param string $datetime
	 * @param float|null $temperature
	 * @param int|null $heartRate
	 * @param int|null $bloodPressureS
	 * @param int|null $bloodPressureD
	 * @param float|null $bloodSugar
	 * @param float|null $oxygenSat
	 * @param int|null $defecation
	 * @param int|null $appetite
	 * @param int|null $allergies
	 * @param int|null $cigarettes
	 * @param int|null $alcohol
	 * @param string $comment
	 * @return DataResponse
	 */
	public function create(int $personId, string $datetime, float $temperature = null, int $heartRate = null, int $bloodPressureS = null, int $bloodPressureD = null, float $bloodSugar = null, float $oxygenSat = null, int $defecation = null, int $appetite = null, int $allergies = null, int $cigarettes = null, int $alcohol = null, string $comment = ''): DataResponse
	{
		return new DataResponse($this->measurementdataService->create($personId, $datetime, $temperature, $heartRate, $bloodPressureS, $bloodPressureD, $bloodSugar, $oxygenSat, $defecation, $appetite, $allergies, $cigarettes, $alcohol, $comment));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @return DataResponse
	 */
	public function delete(int $id): DataResponse
	{
		return new DataResponse($this->measurementdataService->delete($id));
	}

	/**
	 * @NoAdminRequired
	 *
	 * @param int $id
	 * @param string $datetime
	 * @param float|null $temperature
	 * @param int|null $heartRate
	 * @param int|null $bloodPressureS
	 * @param int|null $bloodPressureD
	 * @param float|null $bloodSugar
	 * @param float|null $oxygenSat
	 * @param int|null $defecation
	 * @param int|null $appetite
	 * @param int|null $allergies
	 * @param int|null $cigarettes
	 * @param int|null $alcohol
	 * @param string $comment
	 * @return DataResponse
	 */
	public function update(int $id, string $datetime, float $temperature = null, int $heartRate = null, int $bloodPressureS = null, int $bloodPressureD = null, float $bloodSugar = null, float $oxygenSat = null, int $defecation = null, int $appetite = null, int $allergies = null, int $cigarettes = null, int $alcohol = null, string $comment = ''): DataResponse
	{
		return new DataResponse($this->measurementdataService->update($id, $datetime, $temperature, $heartRate, $bloodPressureS, $bloodPressureD, $bloodSugar, $oxygenSat, $defecation, $appetite, $allergies, $cigarettes, $alcohol, $comment));
	}
}
