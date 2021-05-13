<?php

defined('TYPO3') or die();

call_user_func(
    function () {
        $extensionKey = 'web_vitals_tracker';

        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTypoScript(
            $extensionKey,
            'setup',
            "@import 'EXT:" . $extensionKey . "/Configuration/TypoScript/setup.typoscript'"
        );

        $GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/db_layout.php']['drawHeaderHook'][]
            = \Kanti\WebVitalsTracker\Hooks\PageHeaderHook::class . '->render';
    }
);
