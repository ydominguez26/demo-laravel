<?php

namespace App\Logging;

use Monolog\Formatter\LineFormatter;

class LineExFormatter extends LineFormatter
{

    public function format($record): string
    {
        return json_encode($record) . PHP_EOL;
    }
}
