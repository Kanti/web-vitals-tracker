<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Command;

use Kanti\WebVitalsTracker\Domain\Repository\MeasureRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Database\ConnectionPool;

final class GenerateRandomTestDataCommand extends Command
{
    public function __construct(string $name = null, private ConnectionPool $connectionPool)
    {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $count = 1_000_000;
        $pageUid = 1;
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
                        'page_id' => $pageUid,
                        'sys_language' => $sysLanguageUid,
                        'cls' => random_int(0, 1 * 100) / 100,
                        'fcp' => random_int(0, 10_000 * 100) / 100,
                        'fid' => random_int(0, 10_000 * 100) / 100,
                        'lcp' => random_int(0, 10_000 * 100) / 100,
                        'ttfb' => random_int(0, 10_000 * 100) / 100,
                        'date' => date("Y-m-d H:i:s", random_int(time() - ($daysBack * 24 * 60 * 60), time())),
                    ]
                )->execute();
            $progressBar->advance();
        }
        $progressBar->finish();
        return 0;
    }
}
