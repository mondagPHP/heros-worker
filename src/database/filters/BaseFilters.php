<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\database\filters;

use framework\http\Request;
use framework\server\HttpServer;
use Illuminate\Database\Eloquent\Builder;

/**
 * baseFilter
 */
class BaseFilters
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @var array
     *
     * Used to store the name and values for filters
     * computed from fields and values in request parameters
     * or added programmatically.
     * The keys of this array corresponds to methods declared in
     * a subclass of this class.
     */
    protected $globals = [];

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
            if (! method_exists($this, $name)) {
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
        foreach ($this->globals as $value) {
            $globalFilters[$value] = '';
        }
        return array_merge($this->request()->getParams(), $globalFilters);
    }

    /**
     * @return Request
     */
    public function request(): Request
    {
        return HttpServer::request();
    }
}
