<?php

declare(strict_types=1);

namespace Framework\Bootstrap;

use Framework\Contract\BootstrapInterface;
use Framework\Core\Log;
use Framework\Database\HeroDB;
use Illuminate\Support\Str;
use Workerman\Worker;

class LaravelLog implements BootstrapInterface
{
    public static function start(?Worker $worker): void
    {
        HeroDB::listen(function ($query) {
            $sql = $query->sql;
            $bindings = [];
            if ($query->bindings) {
                foreach ($query->bindings as $v) {
                    if (is_numeric($v)) {
                        $bindings[] = $v;
                    } else {
                        $bindings[] = '"'.strval($v).'"';
                    }
                }
            }
            $execute = Str::replaceArray('?', $bindings, $sql);
            Log::info('SQL '.$execute);
        });
    }
}
