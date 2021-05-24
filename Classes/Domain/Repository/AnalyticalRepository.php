<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Domain\Repository;

use Doctrine\DBAL\Driver\Statement;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class AnalyticalRepository
{
    public const WEB_VITALS_ZONES = [
        'cls' => [0.1, 0.25],
        'fcp' => [1000, 3000],
        'fid' => [100, 300],
        'lcp' => [2500, 4000],
        'ttfb' => [100, 600],
    ];
    private ConnectionPool $connectionPool;
    /** @var array<string, mixed> */
    private array $firstLevelCache = [];

    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
    }

    /**
     * @param int|null $pageUid
     * @return array<string, float|int>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function getAverages(?int $pageUid): ?array
    {
        if ($pageUid === null) {
            $pageUid = -1;
        }
        if (!isset($this->firstLevelCache[__FUNCTION__][$pageUid])) {
            $this->firstLevelCache[__FUNCTION__][$pageUid] = $this->getAveragesDb($pageUid);
        }
        return $this->firstLevelCache[__FUNCTION__][$pageUid];
    }

    /**
     * @param int $pageUid
     * @return array<string, float|int>|null
     * @throws \Doctrine\DBAL\Exception
     */
    private function getAveragesDb(int $pageUid): ?array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(MeasureRepository::TABLENAME)->getConcreteQueryBuilder();
        $queryBuilder->select(
            'COUNT(*) as requestCount',
            'AVG(cls)*100 as cls',
            'AVG(fcp)/1000 as fcp',
            'AVG(fid) as fid',
            'AVG(lcp)/1000 as lcp',
            'AVG(ttfb) as ttfb',
        )->from(MeasureRepository::TABLENAME);
        if ($pageUid !== -1) {
            $queryBuilder
                ->where($queryBuilder->expr()->eq('page_id', $queryBuilder->createNamedParameter($pageUid)))
                ->groupBy('page_id');
        }

        $statement = $queryBuilder->execute();
        assert($statement instanceof Statement);
        return $statement->fetch() ?: null;
    }

    /**
     * @param int|null $pageUid
     * @return array<string, float|int|array<string, float|int>>|null
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPageAnalytics(?int $pageUid): ?array
    {
        if ($pageUid === null) {
            $pageUid = -1;
        }
        if (!isset($this->firstLevelCache[__FUNCTION__][$pageUid])) {
            $this->firstLevelCache[__FUNCTION__][$pageUid] = $this->getPageAnalyticsDb($pageUid);
        }
        return $this->firstLevelCache[__FUNCTION__][$pageUid];
    }

    /**
     * @param int $pageUid
     * @return array<string, float|int|array<string, float|int>>|null
     * @throws \Doctrine\DBAL\Exception
     */
    private function getPageAnalyticsDb(int $pageUid): ?array
    {
        $data = $this->getAverages($pageUid);
        if (!$data || $data['requestCount'] === 0) {
            return null;
        }
        $requestCount = $data['requestCount'];
        unset($data['requestCount']);
        $percentages = [];
        if ($requestCount <= 40_000) {
            $percentages = $this->getPercentageDb($pageUid);
        }
        foreach ($data as $webVital => $average) {
            $percentages[$webVital]['avg'] = $average;
        }
        $percentages['requestCount'] = $requestCount;
        return $percentages;
    }

    /**
     * @param int $pageUid
     * @return array<string, array<string, float|int>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPercentageDb(int $pageUid): array
    {
        $results = [];
        foreach (self::WEB_VITALS_ZONES as $webVital => [$low, $mid]) {
            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(MeasureRepository::TABLENAME)->getConcreteQueryBuilder();
            $queryBuilder->from(MeasureRepository::TABLENAME)
                ->select(
                    'COUNT(*) as count',
                    "IF($webVital >= $low, IF($webVital >= $mid, 'high', 'medium'), 'low') as cat"
                );
            if ($pageUid !== -1) {
                $queryBuilder->where($queryBuilder->expr()->eq('page_id', $queryBuilder->createNamedParameter($pageUid)));
            }
            $queryBuilder->andWhere($queryBuilder->expr()->isNotNull($webVital))
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
     * @param string $order
     * @return array<int, array<string, float|int>>
     * @throws \Doctrine\DBAL\Exception
     */
    public function getPagesOrderedByLcp(string $order): array
    {
        if (!isset($this->firstLevelCache[__FUNCTION__][$order])) {
            $averages = $this->getAverages(null);
            $averageLcp = $averages['lcp'] ?? 0;
            $result = $this->getPagesOrderedByLcpDb($order);
            foreach ($result as $key => $page) {
                $result[$key]['percentage'] = (($page['lcp'] / $averageLcp) - 1) * 100;
                $result[$key]['diff'] = $page['lcp'] - $averageLcp;
            }
            $this->firstLevelCache[__FUNCTION__][$order] = $result;
        }
        return $this->firstLevelCache[__FUNCTION__][$order];
    }

    /**
     * @param string $order
     * @return array<int, array<string, float|int|string>>
     * @throws \Doctrine\DBAL\Exception
     */
    private function getPagesOrderedByLcpDb(string $order): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable(MeasureRepository::TABLENAME)->getConcreteQueryBuilder();
        $queryBuilder->select(
            'm.page_id as uid',
            'p.title as title',
            'COUNT(*) as requestCount',
            'AVG(m.lcp)/1000 as lcp',
        )->from(MeasureRepository::TABLENAME, 'm')
            ->join('m', 'pages', 'p', 'm.page_id = p.uid')
            ->where('p.deleted = 0')
            ->groupBy('m.page_id')
            ->orderBy('lcp', $order)
            ->setMaxResults(10);
        $statement = $queryBuilder->execute();
        assert($statement instanceof Statement);
        return $statement->fetchAll();
    }
}
