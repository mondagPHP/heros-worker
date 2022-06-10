<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Database\Filter;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static Builder filter(string $filter)
 */
trait FilterableTrait
{
    /**
     * Applies filters to the scoped query
     *
     * @param Builder $query
     * @param string $filterClazz
     * @return Builder
     */
    public function scopeFilter(Builder $query, string $filterClazz): Builder
    {
        $filters = new $filterClazz;
        if (! $filters instanceof BaseFilters) {
            throw new \RuntimeException('filters must extends BaseFilter');
        }
        return $filters->apply($query);
    }
}
