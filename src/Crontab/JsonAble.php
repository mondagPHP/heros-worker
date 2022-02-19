<?php
declare(strict_types=1);

namespace Framework\Crontab;

interface JsonAble
{
    public function toJson(): string;
}
