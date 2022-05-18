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
        return $this->request()->getParams();
    }

    public function request(): HttpRequest
    {
        return Application::$request;
    }
}
