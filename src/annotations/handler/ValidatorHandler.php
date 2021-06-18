<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\annotations\Validator;
use framework\exception\HeroException;
use framework\exception\ValidateException;
use framework\util\ModelTransformUtils;
use framework\validate\Validate;
use framework\vo\RequestVoInterface;

return [
    Validator::class => function ($instance, Validator $self) {
        if (! class_exists($self->class)) {
            throw new HeroException($self->class . ' 类不存在，请检查');
        }
        $validFun = static function ($params, $method) use ($self) {
            if ($params instanceof RequestVoInterface) {
                $params = ModelTransformUtils::model2Map($params);
            }
            if ($self->scene !== '') {
                $method = $self->scene;
            }
            /** @var Validate $validator */
            $validateClass = new \ReflectionClass($self->class);
            $validator = $validateClass->newInstance();
            if (! $validator->hasScene($method)) {
                return;
            }
            if (! $validator->scene($method)->check($params)) {
                throw new ValidateException($validator->getError());
            }
        };
        $voClass = new ReflectionClass($instance);
        container()->set(getLoadVoClosureName($voClass->getName()), $validFun);
    },
];
