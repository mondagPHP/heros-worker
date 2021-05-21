<?php


namespace framework\yapi;

use framework\string\StringUtils;

class Json
{
    private $path;

    private $apiMap = [];

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param array $controllerMap
     * @param array $methodMap
     */
    public function export(array $controllerMap, array $methodMap): void
    {
        foreach ($controllerMap ?? [] as $class => $msg) {
            if (! isset($this->apiMap[$class])) {
                $this->apiMap[$class] = [
                    'name' => $msg,
                    'list' => [],
                ];
            }
            /** @var Method $method */
            foreach ($methodMap[$class] ?? [] as $method) {
                $json = [
                    'method' => $method->getMethod(),
                    'title' => $method->getMsg(),
                    'path' => $method->getUri(),
                    'req_params' => $method->getUriParamsJson(),
                ];
                $this->apiMap[$class]['list'][] = array_merge($json, $this->buildParams($method));
            }
        }
        sort($this->apiMap);
        $fileName = $this->path . '/yapi.json';
        file_put_contents($fileName, StringUtils::jsonEncode($this->apiMap));
        echo 'json文件位置：' . $fileName . PHP_EOL;
    }

    /**
     * @param Method $method
     * @return array
     */
    private function buildParams(Method $method): array
    {
        $json = [];
        switch ($method->getMethod()) {
            case 'GET':
            case 'DELETE':
                $json = [
                    'req_query' => $method->getQueryParamsJson(),
                ];
                break;
            case 'POST':
            case 'PUT':
                $json = [
                    'req_headers' => [['name' => 'Content-Type', 'value' => 'application/x-www-form-urlencoded']],
                    'req_body_form' => $method->getQueryParamsJson(),
                    'req_body_type' => 'form',
                ];
                break;
            default:
        }
        return $json;
    }
}
