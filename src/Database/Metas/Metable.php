<?php

declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 *
 * @contact  chenzf@pvc123.com
 */

namespace Framework\Database\Metas;

trait Metable
{
    /**
     * Get all meta.
     *
     * @return object
     */
    public function getAllMeta()
    {
        return collect($this->meta()->pluck('value', 'key'));
    }

    /**
     * Has meta.
     *
     * @param string $key
     * @return bool
     */
    public function hasMeta(string $key)
    {
        $meta = $this->meta()->where('key', $key)->get();

        return (bool)count($meta);
    }

    /**
     * Get meta.
     *
     * @param string $key
     * @param mixed $default
     * @return object
     */
    public function getMeta(string $key, $default = null)
    {
        if ($meta = $this->meta()->where('key', $key)->first()) {
            return $meta;
        }

        return $default;
    }

    /**
     * Get meta value.
     *
     * @param string $key
     * @return object
     */
    public function getMetaValue(string $key)
    {
        return $this->hasMeta($key) ? $this->getMeta($key)->value : null;
    }

    /**
     * Add meta.
     *
     * @param string $key
     * @param mixed $value
     * @return mixed
     */
    public function addMeta(string $key, mixed $value): mixed
    {
        if (!$this->meta()->where('key', $key)->count()) {
            return $this->meta()->create([
                'key' => $key,
                'value' => $value,
            ]);
        }
        return false;
    }

    /**
     * Update meta.
     *
     * @param string $key
     * @param mixed $value
     * @return object|bool
     */
    public function updateMeta(string $key, $value)
    {
        if ($meta = $this->getMeta($key)) {
            $meta->value = $value;

            return $meta->save();
        }

        return false;
    }

    /**
     * Add or update meta if it already exists.
     *
     * @param string $key
     * @param mixed $value
     * @return object|bool
     */
    public function addOrUpdateMeta(string $key, $value)
    {
        return $this->hasMeta($key) ? $this->updateMeta($key, $value) : $this->addMeta($key, $value);
    }

    /**
     * Delete meta.
     *
     * @param string $key
     * @param mixed $value
     * @return bool
     */
    public function deleteMeta(string $key, $value = null)
    {
        return $value
            ? $this->meta()->where('key', $key)->where('value', $value)->delete()
            : $this->meta()->where('key', $key)->delete();
    }

    /**
     * Delete all meta.
     *
     * @return bool
     */
    public function deleteAllMeta()
    {
        return $this->meta()->delete();
    }

    /**
     * Meta relation.
     *
     * @return object
     */
    public function meta()
    {
        return $this->morphMany(config('meta.clazz', Meta::class), 'metable');
    }
}
