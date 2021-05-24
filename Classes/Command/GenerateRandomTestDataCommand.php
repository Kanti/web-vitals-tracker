<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Command;

use Doctrine\DBAL\Driver\Statement;
use Doctrine\DBAL\FetchMode;
use Kanti\WebVitalsTracker\Domain\Repository\AnalyticalRepository;
use Kanti\WebVitalsTracker\Domain\Repository\MeasureRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;

final class GenerateRandomTestDataCommand extends Command
{
    private ConnectionPool $connectionPool;

    public function __construct(string $name = null, ?ConnectionPool $connectionPool = null)
    {
        $this->connectionPool = $connectionPool ?? GeneralUtility::makeInstance(ConnectionPool::class);
        parent::__construct($name);
    }

    /**
     * @throws \Exception
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 1_000_000;
        $pageUids = $this->getPageUids();
        $sysLanguageUid = 0;
        $daysBack = 90;

        $progressBar = new ProgressBar($output, $count);
        for ($i = 0; $i < $count; $i++) {
            $uuid = md5(random_bytes(50));

            $queryBuilder = $this->connectionPool->getQueryBuilderForTable(MeasureRepository::TABLENAME);
            $queryBuilder->insert(MeasureRepository::TABLENAME)
                ->values(
                    [
                        'uuid' => $uuid,
                        'page_id' => $pageUids[array_rand($pageUids)],
                        'sys_language' => $sysLanguageUid,
                        'cls' => random_int(0, (int)(AnalyticalRepository::WEB_VITALS_ZONES['cls'][1] * 2 * 100)) / 100,
                        'fcp' => random_int(0, AnalyticalRepository::WEB_VITALS_ZONES['fcp'][1] * 2 * 100) / 100,
                        'fid' => random_int(0, AnalyticalRepository::WEB_VITALS_ZONES['fid'][1] * 2 * 100) / 100,
                        'lcp' => random_int(0, AnalyticalRepository::WEB_VITALS_ZONES['lcp'][1] * 2 * 100) / 100,
                        'ttfb' => random_int(0, AnalyticalRepository::WEB_VITALS_ZONES['ttfb'][1] * 2 * 100) / 100,
                        'date' => date("Y-m-d H:i:s", random_int(time() - ($daysBack * 24 * 60 * 60), time())),
                    ]
                )->execute();
            $progressBar->advance();
        }
        $progressBar->finish();
        return 0;
    }

    /**
     * @return array<int, int>
     */
    private function getPageUids(): array
    {
        $queryBuilder = $this->connectionPool->getQueryBuilderForTable('pages');
        $statement = $queryBuilder->select('uid')
            ->from('pages')
            ->andWhere($queryBuilder->expr()->eq('sys_language_uid', 0))
            ->execute();
        assert($statement instanceof Statement);
        return $statement->fetchAll(FetchMode::COLUMN);
    }
}
