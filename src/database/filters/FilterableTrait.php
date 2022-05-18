<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\database\filters;

use Illuminate\Database\Eloquent\Builder;

/**
 * @method static filter(string $filter)
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
            throw new \RuntimeException('filters must extends BaseFilters');
        }
        return $filters->apply($query);
    }
}
