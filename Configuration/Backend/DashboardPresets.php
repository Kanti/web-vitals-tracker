<?php

return [
    'dashboardPreset-webvitals' => [
        'title' => 'LLL:EXT:web_vitals_tracker/Resources/Private/Language/locallang.xlf:dashboardPresets.webvitals',
        'description' => 'LLL:EXT:web_vitals_tracker/Resources/Private/Language/locallang.xlf:dashboardPresets.webvitals.description',
        'iconIdentifier' => 'content-dashboard',
        'defaultWidgets' => [
            'webvitalsOverview',
            'webvitalsSlowestPages',
            'webvitalsFastestPages',
        ],
        'showInWizard' => true
    ],
];
