<?php
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

namespace OCA\Health\Db;

use OCP\AppFramework\Db\Entity;
use OCP\IDBConnection;
use OCP\AppFramework\Db\QBMapper;

class ActivitiesdataMapper extends QBMapper {

    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'health_activitiesdata', Activitiesdata::class);
    }

    public function find(int $id): Entity
	{
        $qb = $this->db->getQueryBuilder();

                    $qb->select('*')
                             ->from($this->getTableName())
                             ->where(
                                     $qb->expr()->eq('id', $qb->createNamedParameter($id))
                             );

        return $this->findEntity($qb);
    }

    public function findAll(int $personId): array
	{
        $qb = $this->db->getQueryBuilder();

        $qb->select('*')
           ->from($this->getTableName())
           ->where(
            $qb->expr()->eq('person_id', $qb->createNamedParameter($personId))
           );

        return $this->findEntities($qb);
    }

	public function findByDateTime(int $personId, \DateTime $dateTime): Entity
	{
		$qb = $this->db->getQueryBuilder();
		$qb->select('*')
			->from($this->getTableName())
			->where(
				$qb->expr()->eq('datetime', $qb->createNamedParameter($dateTime, $qb::PARAM_DATE))
			)->andWhere(
				$qb->expr()->eq('person_id', $qb->createNamedParameter($personId))
			);
		return $this->findEntity($qb);
	}
}
