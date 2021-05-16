<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Domain\Repository;

use Doctrine\DBAL\Driver\Statement;
use InvalidArgumentException;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class MeasureRepository
{
    public const TABLENAME = 'tx_webvitalstracker_measure';

    private ConnectionPool $connectionPool;

    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param int $pageId
     * @param int|null $sysLanguageUid
     * @return array<int, array<string, mixed>>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findData(int $pageId, ?int $sysLanguageUid = null): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME)->getConcreteQueryBuilder();
        $queryBuilder->select(
            'COUNT(*) as requestCount',
            'AVG(cls)*100 as cls',
            'AVG(fcp) as fcp',
            'AVG(fid) as fid',
            'AVG(lcp) as lcp',
            'AVG(ttfb) as ttfb',
        )->from(self::TABLENAME)
            ->where($queryBuilder->expr()->eq('page_id', $queryBuilder->createNamedParameter($pageId)))
            ->groupBy('page_id');

        if ($sysLanguageUid !== null) {
            $queryBuilder->andWhere($queryBuilder->expr()->eq('sys_language', $queryBuilder->createNamedParameter($sysLanguageUid)));
        }
        $statement = $queryBuilder->execute();
        assert($statement instanceof Statement);
        return $statement->fetch() ?: null;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function insertOrUpdateMeasure(string $uuid, string $name, float $value, int $counter, int $pageUid, int $sysLanguageUid): void
    {
        $possibleNames = ['cls', 'fcp', 'fid', 'lcp', 'ttfb'];
        if (!in_array($name, $possibleNames, true)) {
            throw new InvalidArgumentException(sprintf('measure name must be one of %s got %s', implode(',', $possibleNames), $name));
        }
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME);
        $sql = $queryBuilder->insert(self::TABLENAME)
            ->values(
                [
                    'uuid' => $uuid,
                    'counter_' . $name => $counter,
                    'page_id' => $pageUid,
                    'sys_language' => $sysLanguageUid,
                    $name => $value,
                ]
            )
            ->getSQL();

        $sql = preg_replace('/(INSERT INTO)/', 'INSERT IGNORE INTO', $sql);
        assert(is_string($sql));
        $inserted = (bool)$this->connectionPool->getConnectionForTable(self::TABLENAME)
            ->executeStatement($sql, $queryBuilder->getParameters());

        if (!$inserted) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME);
            $queryBuilder->update(self::TABLENAME)
                ->set($name, $queryBuilder->createNamedParameter($value), false)
                ->set('counter_' . $name, $queryBuilder->createNamedParameter($counter), false)
                ->where($queryBuilder->expr()->eq('uuid', $queryBuilder->createNamedParameter($uuid)))
                ->andWhere($queryBuilder->expr()->eq('page_id', $queryBuilder->createNamedParameter($pageUid)))
                ->andWhere($queryBuilder->expr()->eq('sys_language', $queryBuilder->createNamedParameter($sysLanguageUid)))
                ->andWhere($queryBuilder->expr()->lt('counter_' . $name, $queryBuilder->createNamedParameter($counter)))
                ->execute();
        }
    }
}
