<?php

/** @var string $_EXTKEY */
$EM_CONF[$_EXTKEY] = [
    'title' => 'Web Vitals Tracker 🔺🟨🟢',
    'description' => 'Real Measurement Web Vitals Statistic for your TYPO3',
    'category' => 'fe',
    'author' => 'Matthias Vogel',
    'author_email' => 'typo3@kanti.de',
    'author_company' => '',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '9.5.0 - 11.5.99'
        ],
        'conflicts' => [],
        'suggests' => [
            'dashboard' => '10.3.0 - 11.5.99'
        ],
    ],
];
