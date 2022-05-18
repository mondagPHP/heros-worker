<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Queue;

interface ConsumerInterface
{
    public function consume(array $data): void;
}
