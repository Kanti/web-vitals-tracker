<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Widgets;

use Kanti\WebVitalsTracker\Domain\Repository\AnalyticalRepository;
use Kanti\WebVitalsTracker\Domain\Repository\MeasureRepository;
use TYPO3\CMS\Backend\Backend\Avatar\DefaultAvatarProvider;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class FastestPagesWidget implements WidgetInterface
{
    private StandaloneView $view;
    private WidgetConfigurationInterface $configuration;
    private AnalyticalRepository $analyticalRepository;

    public function __construct(
        WidgetConfigurationInterface $configuration,
        StandaloneView $view,
        AnalyticalRepository $analyticalRepository
    ) {
        $this->configuration = $configuration;
        $this->view = $view;
        $this->analyticalRepository = $analyticalRepository;
    }

    public function renderWidgetContent(): string
    {
        $this->view->setTemplate('Widget/FastestPages');
        $averageLcp = $this->analyticalRepository->getAverages(null)['lcp'] ?? 0.0;
        $data = $this->analyticalRepository->getPagesOrderedByLcp('ASC');
        $this->view->assignMultiple(
            [
                'averageLcp' => $averageLcp,
                'data' => $data,
                'referer' => $_SERVER['HTTP_REFERER'] ?? null,
            ]
        );
        return $this->view->render();
    }
}
