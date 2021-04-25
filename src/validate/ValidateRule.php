<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\validate;

/**
 * Class ValidateRule.
 * @method ValidateRule confirm(mixed $field, string $msg = '')             static 验证是否和某个字段的值一致
 * @method ValidateRule different(mixed $field, string $msg = '')           static 验证是否和某个字段的值是否不同
 * @method ValidateRule egt(mixed $value, string $msg = '')                 static 验证是否大于等于某个值
 * @method ValidateRule gt(mixed $value, string $msg = '')                  static 验证是否大于某个值
 * @method ValidateRule elt(mixed $value, string $msg = '')                 static 验证是否小于等于某个值
 * @method ValidateRule lt(mixed $value, string $msg = '')                  static 验证是否小于某个值
 * @method ValidateRule eg(mixed $value, string $msg = '')                  static 验证是否等于某个值
 * @method ValidateRule in(mixed $values, string $msg = '')                 static 验证是否在范围内
 * @method ValidateRule notIn(mixed $values, string $msg = '')              static 验证是否不在某个范围
 * @method ValidateRule between(mixed $values, string $msg = '')            static 验证是否在某个区间
 * @method ValidateRule notBetween(mixed $values, string $msg = '')         static 验证是否不在某个区间
 * @method ValidateRule length(mixed $length, string $msg = '')             static 验证数据长度
 * @method ValidateRule max(mixed $max, string $msg = '')                   static 验证数据最大长度
 * @method ValidateRule min(mixed $min, string $msg = '')                   static 验证数据最小长度
 * @method ValidateRule after(mixed $date, string $msg = '')                static 验证日期
 * @method ValidateRule before(mixed $date, string $msg = '')               static 验证日期
 * @method ValidateRule expire(mixed $dates, string $msg = '')              static 验证有效期
 * @method ValidateRule allowIp(mixed $ip, string $msg = '')                static 验证IP许可
 * @method ValidateRule denyIp(mixed $ip, string $msg = '')                 static 验证IP禁用
 * @method ValidateRule regex(mixed $rule, string $msg = '')                static 使用正则验证数据
 * @method ValidateRule token(mixed $token, string $msg = '')               static 验证表单令牌
 * @method ValidateRule is(mixed $rule = null, string $msg = '')            static 验证字段值是否为有效格式
 * @method ValidateRule isRequire(mixed $rule = null, string $msg = '')     static 验证字段必须
 * @method ValidateRule isNumber(mixed $rule = null, string $msg = '')      static 验证字段值是否为数字
 * @method ValidateRule isArray(mixed $rule = null, string $msg = '')       static 验证字段值是否为数组
 * @method ValidateRule isInteger(mixed $rule = null, string $msg = '')     static 验证字段值是否为整形
 * @method ValidateRule isFloat(mixed $rule = null, string $msg = '')       static 验证字段值是否为浮点数
 * @method ValidateRule isMobile(mixed $rule = null, string $msg = '')      static 验证字段值是否为手机
 * @method ValidateRule isIdCard(mixed $rule = null, string $msg = '')      static 验证字段值是否为身份证号码
 * @method ValidateRule isChs(mixed $rule = null, string $msg = '')         static 验证字段值是否为中文
 * @method ValidateRule isChsDash(mixed $rule = null, string $msg = '')     static 验证字段值是否为中文字母及下划线
 * @method ValidateRule isChsAlpha(mixed $rule = null, string $msg = '')    static 验证字段值是否为中文和字母
 * @method ValidateRule isChsAlphaNum(mixed $rule = null, string $msg = '') static 验证字段值是否为中文字母和数字
 * @method ValidateRule isDate(mixed $rule = null, string $msg = '')        static 验证字段值是否为有效格式
 * @method ValidateRule isBool(mixed $rule = null, string $msg = '')        static 验证字段值是否为布尔值
 * @method ValidateRule isAlpha(mixed $rule = null, string $msg = '')       static 验证字段值是否为字母
 * @method ValidateRule isAlphaDash(mixed $rule = null, string $msg = '')   static 验证字段值是否为字母和下划线
 * @method ValidateRule isAlphaNum(mixed $rule = null, string $msg = '')    static 验证字段值是否为字母和数字
 * @method ValidateRule isAccepted(mixed $rule = null, string $msg = '')    static 验证字段值是否为yes, on, 或是 1
 * @method ValidateRule isEmail(mixed $rule = null, string $msg = '')       static 验证字段值是否为有效邮箱格式
 * @method ValidateRule isUrl(mixed $rule = null, string $msg = '')         static 验证字段值是否为有效URL地址
 * @method ValidateRule activeUrl(mixed $rule = null, string $msg = '')     static 验证是否为合格的域名或者IP
 * @method ValidateRule ip(mixed $rule = null, string $msg = '')            static 验证是否有效IP
 * @method ValidateRule fileExt(mixed $ext, string $msg = '')               static 验证文件后缀
 * @method ValidateRule fileMime(mixed $mime, string $msg = '')             static 验证文件类型
 * @method ValidateRule fileSize(mixed $size, string $msg = '')             static 验证文件大小
 * @method ValidateRule image(mixed $rule, string $msg = '')                static 验证图像文件
 * @method ValidateRule method(mixed $method, string $msg = '')             static 验证请求类型
 * @method ValidateRule dateFormat(mixed $format, string $msg = '')         static 验证时间和日期是否符合指定格式
 * @method ValidateRule unique(mixed $rule, string $msg = '')               static 验证是否唯一
 * @method ValidateRule behavior(mixed $rule, string $msg = '')             static 使用行为类验证
 * @method ValidateRule filter(mixed $rule, string $msg = '')               static 使用filter_var方式验证
 * @method ValidateRule requireIf(mixed $rule, string $msg = '')            static 验证某个字段等于某个值的时候必须
 * @method ValidateRule requireCallback(mixed $rule, string $msg = '')      static 通过回调方法验证某个字段是否必须
 * @method ValidateRule requireWith(mixed $rule, string $msg = '')          static 验证某个字段有值的情况下必须
 * @method ValidateRule must(mixed $rule = null, string $msg = '')          static 必须验证
 */
class ValidateRule
{
    // 验证字段的名称
    protected $title;

    // 当前验证规则
    protected $rule = [];

    // 验证提示信息
    protected $message = [];

    public function __call($method, $args)
    {
        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_unshift($args, lcfirst($method));

        return call_user_func_array([$this, 'addItem'], $args);
    }

    public static function __callStatic($method, $args)
    {
        $rule = new static();

        if ('is' == strtolower(substr($method, 0, 2))) {
            $method = substr($method, 2);
        }

        array_unshift($args, lcfirst($method));

        return call_user_func_array([$rule, 'addItem'], $args);
    }

    /**
     * 获取验证规则.
     * @return array
     */
    public function getRule()
    {
        return $this->rule;
    }

    /**
     * 获取验证字段名称.
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * 获取验证提示.
     * @return array
     */
    public function getMsg()
    {
        return $this->message;
    }

    /**
     * 设置验证字段名称.
     * @param  mixed $title
     * @return $this
     */
    public function title($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * 添加验证因子.
     * @param  string $name 验证名称
     * @param  mixed  $rule 验证规则
     * @param  string $msg  提示信息
     * @return $this
     */
    protected function addItem($name, $rule = null, $msg = '')
    {
        if ($rule || 0 === $rule) {
            $this->rule[$name] = $rule;
        } else {
            $this->rule[] = $name;
        }

        $this->message[] = $msg;

        return $this;
    }
}
