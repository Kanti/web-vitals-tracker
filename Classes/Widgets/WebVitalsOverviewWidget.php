<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Widgets;

use Kanti\WebVitalsTracker\Domain\Repository\AnalyticalRepository;
use TYPO3\CMS\Dashboard\Widgets\AdditionalCssInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetConfigurationInterface;
use TYPO3\CMS\Dashboard\Widgets\WidgetInterface;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class WebVitalsOverviewWidget implements WidgetInterface, AdditionalCssInterface
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
        $this->view->setTemplate('Widget/WebVitalsOverview');
        $percentages = $this->analyticalRepository->getPageAnalytics(null);
        if ($percentages === null) {
            return 'no data found';
        }
        $this->view->assignMultiple($percentages);
        return $this->view->render();
    }

    /**
     * @return string[]
     */
    public function getCssFiles(): array
    {
        return ['EXT:web_vitals_tracker/Resources/Public/Css/DrawHeaderHook.css'];
    }
}
