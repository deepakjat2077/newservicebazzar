<?php

namespace App\Support\Tracking;

/**
 * Local, offline User-Agent parser for device type / browser / OS.
 *
 * A composer package (jenssegers/agent) was the original plan, but wasn't
 * installable in this environment — this is a self-contained substitute.
 * Patterns are checked most-specific-first with early return, since most
 * UA strings contain multiple overlapping tokens (e.g. Chromium Edge's UA
 * contains both "Chrome" and "Edg/" — checking Chrome first would
 * misclassify every Edge visitor as Chrome).
 */
class UserAgentParser
{
    public static function parse(?string $userAgent): array
    {
        $ua = (string) $userAgent;

        return [
            'device_type' => self::deviceType($ua),
            'browser' => self::browser($ua),
            'os' => self::os($ua),
        ];
    }

    protected static function deviceType(string $ua): string
    {
        if (preg_match('/iPad|Android(?!.*Mobile)|Tablet|Kindle|Silk/i', $ua)) {
            return 'tablet';
        }

        if (preg_match('/Mobi|iPhone|iPod|Android.*Mobile|BlackBerry|IEMobile|Opera Mini/i', $ua)) {
            return 'mobile';
        }

        if ($ua === '') {
            return 'unknown';
        }

        return 'desktop';
    }

    protected static function browser(string $ua): string
    {
        $checks = [
            'Edge' => '/Edg\/|EdgA\/|EdgiOS\//i',
            'Opera' => '/OPR\/|Opera\//i',
            'Samsung Internet' => '/SamsungBrowser\//i',
            'Firefox' => '/Firefox\/|FxiOS\//i',
            'Internet Explorer' => '/MSIE |Trident\//i',
            'Chrome' => '/Chrome\/|CriOS\//i',
            'Safari' => '/Safari\//i',
        ];

        foreach ($checks as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return 'Unknown';
    }

    protected static function os(string $ua): string
    {
        $checks = [
            'iOS' => '/iPhone|iPad|iPod/i',
            'Android' => '/Android/i',
            'Windows' => '/Windows NT/i',
            'macOS' => '/Macintosh|Mac OS X/i',
            'Linux' => '/Linux/i',
            'Chrome OS' => '/CrOS/i',
        ];

        foreach ($checks as $name => $pattern) {
            if (preg_match($pattern, $ua)) {
                return $name;
            }
        }

        return 'Unknown';
    }
}
