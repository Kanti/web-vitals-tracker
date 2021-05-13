<?php

return [
    'frontend' => [
        'Kanti/web_vitals_tracker/measure' => [
            'target' => \Kanti\WebVitalsTracker\Middleware\MeasureMiddleware::class,
            'after' => [
                'typo3/cms-frontend/page-resolver'
            ]
        ],
    ],
];
