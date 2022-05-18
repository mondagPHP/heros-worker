<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\client;

use framework\exception\CurlException;
use framework\string\StringUtils;
use Workerman\Http\Client;

class HttpClient
{
    /**
     * 发送 http GET 请求
     * @param $url
     * @param $params
     * @param  array $headers      请求头信息
     * @param  bool  $returnHeader 是否返回头信息
     * @return mixed
     */
    public static function get($url, $params = null, $headers = null, $returnHeader = false)
    {
        if (is_array($params)) {
            $params = http_build_query($params);
            if (false === strpos($url, '?')) {
                $url .= '?' . $params;
            } else {
                $url .= '&' . $params;
            }
        }
        $curl = self::_curlInit($url, $headers);
        curl_setopt($curl, CURLOPT_HTTPGET, true);
        return self::_doRequest($curl, $returnHeader);
    }

    /**
     * @param $url
     * @param $params
     * @return mixed
     */
    public static function getWithHeader($url, $params)
    {
        return self::get($url, $params, null, true);
    }

    /**
     * 使用代理访问.
     * @param $url
     * @param mixed $proxy 代理配置
     * @param $params
     * @param  bool  $returnHeader
     * @return mixed
     */
    public static function getProxy($url, $proxy, $params, $returnHeader = false)
    {
        if (is_array($params)) {
            $params = http_build_query($params);
            if (false === strpos($url, '?')) {
                $url .= '?' . $params;
            } else {
                $url .= '&' . $params;
            }
        }
        $curl = self::_curlInit($url, null);
        curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_HTTPGET, true);

        return self::_doRequest($curl, $returnHeader);
    }

    /**
     * 发送http POST 请求
     * @param $url
     * @param $params
     * @param  null       $headers
     * @return bool|mixed
     */
    public static function post($url, $params, $headers = null)
    {
        if (is_array($params)) {
            $params = http_build_query($params);
        }
        $curl = self::_curlInit($url, $headers);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);

        return self::_doRequest($curl, false);
    }

    /**
     * 发送restful PUT请求
     * @param $url
     * @param $params
     * @return mixed
     */
    public static function put($url, $params)
    {
        if (is_array($params)) {
            $params = StringUtils::jsonEncode($params);
        }
        $curl = self::_curlInit($url, ['Content-Type' => 'application/json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PUT');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        return self::_doRequest($curl, false);
    }

    /**
     * 发送restful DELETE请求
     * @param $url
     * @param $params
     * @return mixed
     */
    public static function delete($url, $params)
    {
        if (is_array($params)) {
            $params = StringUtils::jsonEncode($params);
        }
        $curl = self::_curlInit($url, ['Content-Type' => 'application/json']);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        return self::_doRequest($curl, false);
    }

    /**
     * @param null|\Closure  $success
     * @param null|\Closure  $error
     * @param array|string[] $headers
     *                                异步请求
     */
    public static function asyRequest(string $url, \Closure $success, \Closure $error, string $method = 'get', array $params = [], array $headers = ['Connection' => 'keep-alive'])
    {
        $http = new Client();
        $http->request(
            $url,
            [
                'method' => strtoupper($method),
                'version' => '1.1',
                'headers' => $headers,
                'data' => $params,
                'success' => function ($response) use ($success) {
                    $success(new HttpClientResponse($response));
                },
                'error' => $error,
            ]
        );
    }

    /**
     * 发送Http请求
     * @param $curl
     * @param $return_header
     * @return array|bool|string
     */
    private static function _doRequest($curl, $return_header = false)
    {
        $ret = curl_exec($curl);
        if (false === $ret) {
            curl_close($curl);
            throw new CurlException('接口网络异常，请稍候再试');
        }
        $info = curl_getinfo($curl);

        curl_close($curl);
        if (false === $ret) {
            throw new CurlException('cURLException:' . curl_error($curl));
        }
        if ($return_header) {
            return ['header' => $info, 'body' => $ret];
        }
        return $ret;
    }

    /**
     * 创建curl对象
     * @param $url
     * @param $headers
     * @return resource
     */
    private static function _curlInit($url, $headers)
    {
        $curl = curl_init();
        if (false !== stripos($url, 'https://')) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        if (is_array($headers)) {
            $_headers = [];
            foreach ($headers as $key => $value) {
                $_headers[] = "{$key}:{$value}";
            }
            curl_setopt($curl, CURLOPT_HTTPHEADER, $_headers);
        }
        return $curl;
    }
}