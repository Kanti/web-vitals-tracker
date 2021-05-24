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

    private ConnectionPool $connectionPool;

    public function __construct(?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
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
