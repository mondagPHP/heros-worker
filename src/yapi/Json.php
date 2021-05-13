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
                $this->apiMap[$class]['list'][] = [
                    'status' => 'undone',
                    'method' => $method->getMethod(),
                    'title' => $method->getMsg(),
                    'path' => $method->getUri(),
                    'project_id' => 924,
                    'req_params' => $method->getUriParamsJson(),
                    'req_query' => $method->getQueryParamsJson(),
                ];
            }
        }
        sort($this->apiMap);
        $fileName = $this->path . '/yapi.json';
        file_put_contents($fileName, StringUtils::jsonEncode($this->apiMap));
        echo $fileName . PHP_EOL;
    }
}
