<?php
declare(strict_types=1);
/**
 * This file is part of Heros-Worker.
 * @contact  chenzf@pvc123.com
 */
namespace Framework\Casbin;

use Casbin\Exceptions\InvalidFilterTypeException;
use Casbin\Model\Model;
use Casbin\Persist\Adapter;
use Casbin\Persist\AdapterHelper;
use Casbin\Persist\Adapters\Filter;
use Casbin\Persist\BatchAdapter;
use Casbin\Persist\FilteredAdapter;
use Casbin\Persist\UpdatableAdapter;
use Framework\Database\HeroDB;

class DatabaseAdapter implements Adapter, FilteredAdapter, BatchAdapter, UpdatableAdapter
{
    use AdapterHelper;

    protected bool $filtered = false;

    public function __construct()
    {
        $this->checkTable();
    }

    /**
     * Returns true if the loaded policy has been filtered.
     *
     * @return bool
     */
    public function isFiltered(): bool
    {
        return $this->filtered;
    }

    /**
     * Sets filtered parameter.
     *
     * @param bool $filtered
     */
    public function setFiltered(bool $filtered): void
    {
        $this->filtered = $filtered;
    }

    /**
     * Filter the rule.
     *
     * @param array $rule
     * @return array
     */
    public function filterRule(array $rule): array
    {
        $rule = array_values($rule);

        $i = count($rule) - 1;
        for (; $i >= 0; $i--) {
            if ($rule[$i] != '' && ! is_null($rule[$i])) {
                break;
            }
        }

        return array_slice($rule, 0, $i + 1);
    }

    public static function newAdapter(): static
    {
        return new static();
    }

    public function loadPolicy(Model $model): void
    {
        $rows = CasbinRule::query()->select(['ptype', 'v0', 'v1', 'v2', 'v3', 'v4', 'v5'])->get()->toArray();

        foreach ($rows as $row) {
            $row = array_values($row);
            $this->loadPolicyArray($this->filterRule($row), $model);
        }
    }

    public function savePolicy(Model $model): void
    {
        foreach ($model['p'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }

        foreach ($model['g'] as $ptype => $ast) {
            foreach ($ast->policy as $rule) {
                $this->savePolicyLine($ptype, $rule);
            }
        }
    }

    public function savePolicyLine($ptype, array $rule)
    {
        $col['ptype'] = $ptype;
        foreach ($rule as $key => $value) {
            $col['v' . (string)$key] = $value;
        }
        CasbinRule::query()->updateOrCreate($col);
    }

    public function addPolicy(string $sec, string $ptype, array $rule): void
    {
        $this->savePolicyLine($ptype, $rule);
    }

    public function removePolicy(string $sec, string $ptype, array $rule): void
    {
        $where = [
            ['ptype', '=', $ptype]
        ];
        foreach ($rule as $key => $value) {
            $where[] = ['v' . (string)$key, '=', $value];
        }

        CasbinRule::query()->where($where)->delete();
    }

    public function removeFilteredPolicy(string $sec, string $ptype, int $fieldIndex, string ...$fieldValues): void
    {
        $where = [['ptype', '=', $ptype]];
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues) && '' != $fieldValues[$value - $fieldIndex]) {
                $where[] = ['v' . (string)$value, '=', $fieldValues[$value - $fieldIndex]];
            }
        }
        CasbinRule::query()->where($where)->delete();
    }

    public function addPolicies(string $sec, string $ptype, array $rules): void
    {
        foreach ($rules as $rule) {
            $this->savePolicyLine($ptype, $rule);
        }
    }

    public function removePolicies(string $sec, string $ptype, array $rules): void
    {
        foreach ($rules as $rule) {
            $this->removePolicy($sec, $ptype, $rule);
        }
    }

    public function updatePolicy(string $sec, string $ptype, array $oldRule, array $newPolicy): void
    {
        $where = [['ptype', '=', $ptype]];
        foreach ($oldRule as $key => $value) {
            $where[] = ['w' . (string)$key, '=', $value];
        }

        $update = [];
        foreach ($newPolicy as $key => $value) {
            $update['v' . (string)$key] = $value;
        }

        CasbinRule::query()->where($where)->update($update);
    }

    public function updatePolicies(string $sec, string $ptype, array $oldRules, array $newRules): void
    {
        foreach ($oldRules as $i => $oldRule) {
            $this->updatePolicy($sec, $ptype, $oldRule, $newRules[$i]);
        }
    }

    public function updateFilteredPolicies(string $sec, string $ptype, array $newPolicies, int $fieldIndex, string ...$fieldValues): array
    {
        $where = [['ptype', '=', $ptype]];
        foreach (range(0, 5) as $value) {
            if ($fieldIndex <= $value && $value < $fieldIndex + count($fieldValues) && '' != $fieldValues[$value - $fieldIndex]) {
                $where[] = ['v' . (string)$value, '=', $fieldValues[$value - $fieldIndex]];
            }
        }
        $oldP = CasbinRule::query()->where($where)->get()->makeHidden(['created_at','updated_at', 'id', 'ptype'])->toArray();
        foreach ($newPolicies as $policy) {
            $update = [];
            foreach ($policy as $key => $value) {
                $update['v' . (string)$key] = $value;
            }

            CasbinRule::query()->where($where)->update($update);
        }
        return $oldP;
    }

    /**
     * @throws InvalidFilterTypeException
     */
    public function loadFilteredPolicy(Model $model, $filter): void
    {
        $where = [];
        if (is_string($filter)) {
            $filter = str_replace(' ', '', $filter);
            $filter = str_replace('\'', '', $filter);
            $filter = explode('=', $filter);

            $where[] = [$filter[0], '=', $filter[1]];
        } elseif ($filter instanceof Filter) {
            foreach ($filter->p as $k => $v) {
                $where[] = [$v, '=', $filter->g[$k]];
            }
        } elseif ($filter instanceof \Closure) {
            $w = '';
            $filter($w);
            $w = str_replace(' ', '', $w);
            $w = str_replace('\'', '', $w);
            $w = explode('=', $w);

            $where[] = [$w[0], '=', $w[1]];
        } else {
            throw new InvalidFilterTypeException('invalid filter type');
        }

        $rows = CasbinRule::query()->where($where)->get()->toArray();
        foreach ($rows as $row) {
            unset($row['id']);
            $row = array_filter($row, function ($value) {
                return ! is_null($value) && $value !== '';
            });
            $line = implode(', ', array_filter($row, function ($val) {
                return '' != $val && ! is_null($val);
            }));
            $this->loadPolicyLine(trim($line), $model);
        }

        $this->setFiltered(true);
    }

    protected function checkTable(): void
    {
        HeroDB::statement('CREATE TABLE IF NOT EXISTS casbin_rule (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `ptype` varchar(255) NOT NULL,
            `v0` varchar(255) DEFAULT NULL,
            `v1` varchar(255) DEFAULT NULL,
            `v2` varchar(255) DEFAULT NULL,
            `v3` varchar(255) DEFAULT NULL,
            `v4` varchar(255) DEFAULT NULL,
            `v5` varchar(255) DEFAULT NULL,
            `created_at` datetime DEFAULT NULL,
            `updated_at` datetime DEFAULT NULL,
            PRIMARY KEY (`id`)
        );');
    }
}
