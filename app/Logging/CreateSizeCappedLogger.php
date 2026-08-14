<?php

namespace App\Logging;

use Illuminate\Log\Logger;
use Monolog\Formatter\LineFormatter;
use Monolog\Logger as Monolog;

/**
 * Channel factory for SizeCappedFileHandler — see that class for why the cap
 * exists. Used by the "daily" and "slow" channels in config/logging.php via
 * the custom driver.
 */
class CreateSizeCappedLogger
{
    public function __invoke(array $config): Logger
    {
        $handler = new SizeCappedFileHandler(
            $config['path'],
            (int) ($config['days'] ?? 1),
            $config['level'] ?? 'debug'
        );

        // Same format Laravel's own daily channel produces, so existing log
        // readers and greps keep working.
        $handler->setFormatter(new LineFormatter(null, null, true, true));

        return new Logger(
            new Monolog($config['name'] ?? 'sizecapped', [$handler])
        );
    }
}
