<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Symfony\Config\EasyLoggingConfig;

return static function (EasyLoggingConfig $easyLoggingConfig): void {
    $easyLoggingConfig->defaultChannel('my-app');

    $easyLoggingConfig->sensitiveDataSanitizer()
        ->enabled(true);
};
