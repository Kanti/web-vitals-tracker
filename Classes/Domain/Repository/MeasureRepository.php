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
    public const WEB_VITALS = ['cls', 'fcp', 'fid', 'lcp', 'ttfb'];
    public const WEB_VITALS_ZONES = [
        'cls' => [0.1, 0.25],
        'fcp' => [1000, 3000],
        'fid' => [100, 300],
        'lcp' => [2500, 4000],
        'ttfb' => [100, 600],
    ];

    private ConnectionPool $connectionPool;

    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param int $pageId
     * @return array<string, mixed>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function findData(int $pageId): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME)->getConcreteQueryBuilder();
        $queryBuilder->select(
            'COUNT(*) as requestCount',
            'AVG(cls)*100 as cls',
            'AVG(fcp)/1000 as fcp',
            'AVG(fid) as fid',
            'AVG(lcp)/1000 as lcp',
            'AVG(ttfb) as ttfb',
        )->from(self::TABLENAME)
            ->where($queryBuilder->expr()->eq('page_id', $queryBuilder->createNamedParameter($pageId)))
            ->groupBy('page_id');

        $statement = $queryBuilder->execute();
        assert($statement instanceof Statement);
        return $statement->fetch() ?: null;
    }

    /**
     * @param int $pageId
     * @return array<string, array<string, float>>
     */
    public function findPercentageData(int $pageId): array
    {
        $results = [];
        foreach (self::WEB_VITALS as $webVital) {
            [$low, $mid] = self::WEB_VITALS_ZONES[$webVital];
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(self::TABLENAME)->getConcreteQueryBuilder();
            $queryBuilder->from(self::TABLENAME)
                ->select(
                    'COUNT(*) as count',
                    "IF($webVital >= $low, IF($webVital >= $mid, 'high', 'medium'), 'low') as cat"
                )
                ->where($queryBuilder->expr()->eq('page_id', $queryBuilder->createNamedParameter($pageId)))
                ->andWhere($queryBuilder->expr()->isNotNull($webVital))
                ->groupBy('cat');
            $statement = $queryBuilder->execute();
            assert($statement instanceof Statement);
            $rows = $statement->fetchAll() ?: [];
            $total = array_sum(array_column($rows, 'count'));
            $results[$webVital] = [
                'low' => 0.0,
                'medium' => 0.0,
                'high' => 0.0,
            ];
            foreach ($rows as $row) {
                $results[$webVital][(string)$row['cat']] = (float)($row['count'] / $total);
            }
        }
        return $results;
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function insertOrUpdateMeasure(string $uuid, string $name, float $value, int $counter, int $pageUid, int $sysLanguageUid): void
    {
        if (!in_array($name, self::WEB_VITALS, true)) {
            throw new InvalidArgumentException(sprintf('measure name must be one of %s got %s', implode(',', self::WEB_VITALS), $name));
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
