<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Web Vitals Tracker',
    'description' => 'Real Measurement Web Vitals Statistic for your TYPO3',
    'category' => 'fe',
    'author' => 'Matthias Vogel',
    'author_email' => 'typo3@kanti.de',
    'author_company' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => \Kanti\WebVitalsTracker\Utility\VersionUtility::getVersion(),
    'constraints' => [
        'depends' => [
            'typo3' => '11.0.0 - 11.2.99',
        ],
        'conflicts' => [],
        'suggests' => [
            'dashboard' => '11.0.0 - 11.2.99',
        ],
    ],
];
