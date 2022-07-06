<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Database\Metas;

/*
CREATE TABLE `metas` (
    `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '自增ID',
    `metable_id` char(18) DEFAULT NULL COMMENT 'metable关联ID',
    `metable_type` varchar(255) DEFAULT NULL COMMENT '类型',
    `key` varchar(255) DEFAULT NULL COMMENT '键',
    `value` text COMMENT '值',
    PRIMARY KEY (`id`),
    KEY `idx_metable_id` (`metable_id`) USING BTREE,
    KEY `idx_key` (`key`) USING BTREE
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COMMENT='metas表';
 */

use Framework\Database\HeroModel;

/**
 * 额外表
 */
class Meta extends HeroModel
{
    /**
     * No timestamps for meta data.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * table name
     *
     * @var string
     */
    protected $table = 'metas';

    /**
     * Casts.
     *
     * @var array
     */
    protected $casts = [
        'value' => 'json',
    ];

    /**
     * Defining fillable attributes on the model.
     *
     * @var array
     */
    protected $fillable = [
        'metable_id',
        'metable_type',
        'key',
        'value',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        if ($connection = config('meta.connection', '')) {
            $this->setConnection($connection);
        }
        if ($table = config('meta.table', '')) {
            $this->setTable($table);
        }
    }

    /**
     * Relation.
     *
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function metable()
    {
        return $this->morphTo();
    }
}
