<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Helpers;

use EonX\EasySwoole\Bridge\EasySchedule\EasyScheduleSwooleRunner;
use EonX\EasySwoole\Runtime\EasySwooleRuntime;

final class AppRuntimeHelper
{
    private const APP_RUNTIME = 'APP_RUNTIME';

    private const APP_RUNTIME_OPTIONS = 'APP_RUNTIME_OPTIONS';

    /**
     * @param mixed[] $options
     */
    public static function addOptions(array $options): void
    {
        $_SERVER[self::APP_RUNTIME_OPTIONS] = \array_merge(
            $_SERVER[self::APP_RUNTIME_OPTIONS] ?? [],
            $options
        );
    }

    public static function enableEasyScheduleRunner(): void
    {
        self::addOptions([EasyScheduleSwooleRunner::ENABLED => true]);
    }

    public static function enableRuntime(): void
    {
        self::setRuntime(EasySwooleRuntime::class);
    }

    public static function getOption(string $name, mixed $default = null): mixed
    {
        return $_SERVER[self::APP_RUNTIME_OPTIONS][$name] ?? $default;
    }

    public static function setCacheClearAfterTickCount(int $cacheClearAfterTickCount): void
    {
        self::addOptions(['cache_clear_after_tick_count' => $cacheClearAfterTickCount]);
    }

    /**
     * @param mixed[] $cacheTables
     */
    public static function setCacheTables(array $cacheTables): void
    {
        self::addOptions(['cache_tables' => $cacheTables]);
    }

    /**
     * @param callable[] $callbacks
     */
    public static function setCallbacks(array $callbacks): void
    {
        self::addOptions(['callbacks' => $callbacks]);
    }

    public static function setEnvVarName(string $envVarName): void
    {
        self::addOptions(['env_var_name' => $envVarName]);
    }

    public static function setHost(string $host): void
    {
        self::addOptions(['host' => $host]);
    }

    /**
     * @param string[] $hotReloadDirs
     */
    public static function setHotReloadDirs(array $hotReloadDirs): void
    {
        self::addOptions(['hot_reload_dirs' => $hotReloadDirs]);
    }

    public static function setHotReloadEnabled(bool $hotReloadEnabled): void
    {
        self::addOptions(['hot_reload_enabled' => $hotReloadEnabled]);
    }

    /**
     * @param string[] $hotReloadExtensions
     */
    public static function setHotReloadExtensions(array $hotReloadExtensions): void
    {
        self::addOptions(['hot_reload_extensions' => $hotReloadExtensions]);
    }

    /**
     * @param string[] $jsonSecrets
     */
    public static function setJsonSecrets(array $jsonSecrets): void
    {
        self::addOptions(['json_secrets' => $jsonSecrets]);
    }

    public static function setMode(int $mode): void
    {
        self::addOptions(['mode' => $mode]);
    }

    public static function setPort(int $port): void
    {
        self::addOptions(['port' => $port]);
    }

    public static function setResponseChunkSize(int $responseChunkSize): void
    {
        self::addOptions(['response_chunk_size' => $responseChunkSize]);
    }

    public static function setRuntime(string $runtime): void
    {
        $_SERVER[self::APP_RUNTIME] = $runtime;
    }

    /**
     * @param mixed[] $settings
     */
    public static function setSettings(array $settings): void
    {
        self::addOptions(['settings' => $settings]);
    }

    public static function setSockType(int $sockType): void
    {
        self::addOptions(['sock_type' => $sockType]);
    }

    public static function setUseDefaultCallbacks(bool $useDefaultCallbacks): void
    {
        self::addOptions(['use_default_callbacks' => $useDefaultCallbacks]);
    }
}
