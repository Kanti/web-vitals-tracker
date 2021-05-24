<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Hooks;

use Kanti\WebVitalsTracker\Domain\Repository\AnalyticalRepository;
use Kanti\WebVitalsTracker\Domain\Repository\MeasureRepository;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class PageHeaderHook
{
    private AnalyticalRepository $analyticalRepository;
    private StandaloneView $templateView;
    private PageRenderer $pageRenderer;

    public function __construct(
        ?AnalyticalRepository $analyticalRepository = null,
        ?StandaloneView $templateView = null,
        ?PageRenderer $pageRenderer = null
    ) {
        $this->analyticalRepository = $analyticalRepository ?? GeneralUtility::makeInstance(AnalyticalRepository::class);
        $this->templateView = $templateView ?? GeneralUtility::makeInstance(StandaloneView::class);
        $this->pageRenderer = $pageRenderer ?? GeneralUtility::makeInstance(PageRenderer::class);

        $this->templateView->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('web_vitals_tracker');
        $this->templateView->setTemplate('PageHeaderHook');
    }

    /**
     * @throws \Doctrine\DBAL\Exception
     */
    public function render(): string
    {
        $pageId = (int)$_GET['id'];
        $hasAccess = $GLOBALS['BE_USER']->check('non_exclude_fields', 'pages:tx_webvitalstracker_measure');
        $disableInfo = (bool)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['web_vitals_tracker']['disableInfo'] ?? false);

        if (!$hasAccess || $disableInfo) {
            return '';
        }

        $this->pageRenderer->addCssFile('EXT:web_vitals_tracker/Resources/Public/Css/DrawHeaderHook.css');

        $percentages = $this->analyticalRepository->getPageAnalytics($pageId);
        if ($percentages === null) {
            return '';
        }

        $this->templateView->assignMultiple($percentages);

        return $this->templateView->render();
    }
}
