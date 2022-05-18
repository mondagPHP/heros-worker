<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */

namespace Framework\Database\Filter;

use Framework\Application;
use Framework\Http\HttpRequest;
use Illuminate\Database\Eloquent\Builder;

/**
 * baseFilter
 */
class BaseFilters
{
    /**
     * @var Builder
     */
    protected Builder $builder;

    /**
     * @var array
     *
     * Used to store the name and values for filters
     * computed from fields and values in request parameters
     * or added programmatically.
     * The keys of this array corresponds to methods declared in
     * a subclass of this class.
     */
    protected static array $globals = [];

    /**
     * Applies respective filter methods declared in the subclass
     * that correspond to fields in request query parameters.
     *
     * @param Builder $builder
     * @return Builder
     */
    public function apply(Builder $builder): Builder
    {
        $this->builder = $builder;
        $filters = $this->filters();
        foreach ($filters as $name => $value) {
            if (!method_exists($this, $name)) {
                continue;
            }
            if (isset($value)) {
                $this->$name($value);
            } else {
                $this->$name();
            }
        }
        return $this->builder;
    }

    /**
     * Gets filters from request query parameters.
     *
     * @return array
     */
    public function filters(): array
    {
        $globalFilters = [];
        foreach (static::$globals as $key => $value) {
            $globalFilters[$value] = '';
        }
        return array_merge($this->request()->getParams(), $globalFilters);
    }

    /**
     * @return HttpRequest
     */
    public function request(): HttpRequest
    {
        return Application::$request;
    }
}
