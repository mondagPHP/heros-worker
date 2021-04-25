<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
use framework\annotations\Validator;
use framework\exception\ValidateException;
use framework\util\ModelTransformUtils;
use framework\validate\Validate;
use framework\vo\RequestVoInterface;

return [
    Validator::class => function ($instance, Validator $self) {
        if (empty($self->scene) || ! class_exists($self->class)) {
            return;
        }
        $validFun = static function ($params) use ($self) {
            if ($params instanceof RequestVoInterface) {
                $params = ModelTransformUtils::model2Map($params);
            }
            /** @var Validate $validator */
            $validateClass = new \ReflectionClass($self->class);
            $validator = $validateClass->newInstance();
            if (! $validator->scene($self->scene)->check($params)) {
                throw new ValidateException($validator->getError());
            }
        };
        $voClass = new ReflectionClass($instance);
        container()->set($voClass->getName(), $validFun);
    },
];
