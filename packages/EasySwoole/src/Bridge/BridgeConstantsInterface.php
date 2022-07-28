<?php

declare(strict_types=1);

namespace EonX\EasySwoole\Bridge;

interface BridgeConstantsInterface
{
    /**
     * @var string
     */
    public const PARAM_ACCESS_LOG_TIMEZONE = 'easy_swoole.access_log_timezone';

    /**
     * @var string
     */
    public const PARAM_REQUEST_LIMITS_MAX = 'easy_swoole.request_limits.max';

    /**
     * @var string
     */
    public const PARAM_REQUEST_LIMITS_MIN = 'easy_swoole.request_limits.min';

    /**
     * @var string
     */
    public const PARAM_RESET_EASY_BATCH_PROCESSOR = 'easy_swoole.reset_easy_batch_processor';

    /**
     * @var string
     */
    public const PARAM_RESET_DOCTRINE_DBAL_CONNECTIONS = 'easy_swoole.reset_doctrine_dbal_connections';

    /**
     * @var string
     */
    public const PARAM_STATIC_PHP_FILES_ALLOWED_DIRS = 'easy_swoole.static_php_files_allowed_dirs';

    /**
     * @var string
     */
    public const PARAM_STATIC_PHP_FILES_ALLOWED_FILENAMES = 'easy_swoole.static_php_files_allowed_filenames';

    /**
     * @var string
     */
    public const SERVICE_ACCESS_LOG_LOGGER = 'easy_swoole.access_log_logger';

    /**
     * @var string
     */
    public const SERVICE_FILESYSTEM = 'easy_swoole.filesystem';

    /**
     * @var string
     */
    public const TAG_APP_STATE_CHECKER = 'easy_swoole.app_state_checker';

    /**
     * @var string
     */
    public const TAG_APP_STATE_INITIALIZER = 'easy_swoole.app_state_initializer';

    /**
     * @var string
     */
    public const TAG_APP_STATE_RESETTER = 'easy_swoole.app_state_resetter';
}
