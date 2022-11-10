<?php

namespace NP\Gridtocontainer\Domain\Repository;

use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Repository;

class MigrationRepository extends Repository
{

    /**
     * @return void
     * @throws \Doctrine\DBAL\Driver\Exception
     */
    public function findGridelementRecords()
    {
        $queryBuilder = $this->getQueryBuilder();

        $records = $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('gridelements_pi1'))
            )
            ->execute()
            ->fetchAllAssociative();
        return $records;
    }

    /**
     * @param string $layout
     * @param int $limit
     * @return void
     */
    public function findByGridelementLayout($layout, $limit = 0)
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('CType', $queryBuilder->createNamedParameter('gridelements_pi1')),
                $queryBuilder->expr()->eq('tx_gridelements_backend_layout',
                    $queryBuilder->createNamedParameter($layout))
            );
        if ($limit > 0) {
            $queryBuilder->setMaxResults($limit);
        }
        $records = $queryBuilder->execute()->fetchAllAssociative();
        return $records;
    }

    /**
     * @param int $parentUid
     * @return void
     */
    public function findByParentGridelement($parentUid)
    {
        $queryBuilder = $this->getQueryBuilder();

        $queryBuilder
            ->select('*')
            ->from('tt_content')
            ->where(
                $queryBuilder->expr()->eq('tx_gridelements_container', $queryBuilder->createNamedParameter($parentUid, \PDO::PARAM_STR))
            );

        $records = $queryBuilder->execute()->fetchAllAssociative();
        return $records;
    }

    /**
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tt_content');
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        return $queryBuilder;
    }


}
