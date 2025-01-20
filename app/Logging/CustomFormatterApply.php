<?php

namespace App\Logging;

use App\Logging\LineExFormatter;

class CustomFormatterApply {
    public function __invoke($logging) {
        if (env('APP_ENV') == 'production') {
            $exFormatter = new LineExFormatter();
    
            foreach ($logging->getHandlers() as $handler) {
                $handler->setFormatter($exFormatter);
            }
        }
        
    }
}
