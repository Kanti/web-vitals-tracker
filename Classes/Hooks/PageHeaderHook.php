<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Hooks;

use Kanti\WebVitalsTracker\Domain\Repository\MeasureRepository;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Page\PageRenderer;
use TYPO3\CMS\Fluid\View\StandaloneView;

final class PageHeaderHook
{
    public function __construct(
        private MeasureRepository $measureRepository,
        private StandaloneView $templateView,
        private PageRenderer $pageRenderer
    ) {

        $this->templateView->getRenderingContext()->getTemplatePaths()->fillDefaultsByPackageName('web_vitals_tracker');
        $this->templateView->setTemplate('PageHeaderHook');
    }

    public function render(): string
    {
        $pageId = (int)$_GET['id'];
//        $sysLanguageUid = (int)BackendUtility::getModuleData(['language'], [], 'web_layout')['language'];
        $hasAccess = $GLOBALS['BE_USER']->check('non_exclude_fields', 'pages:tx_webvitalstracker_measure');
//        $disableWarnings = (bool)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['web_vitals_tracker']['disableWarnings'] ?? false);
        $disableInfo = (bool)($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['web_vitals_tracker']['disableInfo'] ?? false);

        if (!$hasAccess || $disableInfo) {
            return '';
        }

        $data = $this->measureRepository->findData($pageId, /*$sysLanguageUid*/ null);
        if (empty($data)) {
            return '';
        }

        $this->pageRenderer->addCssFile('EXT:web_vitals_tracker/Resources/Public/Css/DrawHeaderHook.css');

        $this->templateView->assignMultiple($data);

        return $this->templateView->render();
    }
}
