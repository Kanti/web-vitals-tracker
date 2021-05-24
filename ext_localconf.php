<?php

call_user_func(
    static function () {
        $extensionKey = 'web_vitals_tracker';

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            $extensionKey,
            'setup',
            "@import 'EXT:" . $extensionKey . "/Configuration/TypoScript/setup.typoscript'"
        );

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][]
            = \Kanti\WebVitalsTracker\Hooks\PageHeaderHook::class . '->render';

        if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('dashboard')) {
            // Add module configuration
            \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScriptSetup(
                'module.tx_dashboard {
    view {
        templateRootPaths.1621758692 = EXT:web_vitals_tracker/Resources/Private/Templates/
        partialRootPaths.1621758692 = EXT:web_vitals_tracker/Resources/Private/Partials/
        layoutRootPaths.1621758692 = EXT:web_vitals_tracker/Resources/Private/Layouts/
    }
}'
            );
        }
    }
);
