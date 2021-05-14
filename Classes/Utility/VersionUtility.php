<?php

declare(strict_types=1);

namespace Kanti\WebVitalsTracker\Utility;

use PackageVersions\Versions;
use Throwable;

final class VersionUtility
{
    private function __construct()
    {
    }

    public static function getVersion(): string
    {
        $str = 'dev';
        try {
            return explode('@', Versions::getVersion('kanti/web-vitals-tracker'))[0] ?? $str;
        } catch (Throwable $e) {
            return $str;
        }
    }
}
