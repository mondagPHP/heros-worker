<?php
/**
 * This file is part of monda-worker.
 *
 * @contact  mondagroup_php@163.com
 *
 */
namespace framework\sse;

use framework\http\Request;
use framework\string\StringUtils;
use Workerman\Connection\TcpConnection;
use Workerman\Lib\Timer;
use Workerman\Protocols\Http\Response;
use Workerman\Protocols\Http\ServerSentEvents;

/**
 * Class PushDataClient
 * @package framework\sse
 * 当浏览器出现推送请求自动断开时，检查一下nginx配置
 * 增加以下配置，保持长连接
 * proxy_http_version 1.1;
 * proxy_set_header Connection "";
 * # 将客户端的 Host 和 IP 信息一并转发到对应节点
 * proxy_set_header Host $http_host;
 * proxy_set_header X-Real-IP $remote_addr;
 * proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
 */
class PushDataClient
{
    /**
     * @var array 推送数据数组
     */
    protected static $eventData = [];

    /**
     * @param Request $request
     * @param AbstractPushData $object 推送数据对象
     * @param int $time 推送间隔
     * @param int $limitTime 最长连接时间
     * @return Response
     */
    public static function pushData(Request $request, AbstractPushData $object, int $time = 5, int $limitTime = 300): Response
    {
        $connection = $request->getConnection();
        if (! isset(self::$eventData[$connection->id])) {
            $object->start = time();
            self::$eventData[$connection->id] = $object;
        }
        if ($request->getHeaderByName('accept', '') === 'text/event-stream') {
            $timerId = Timer::add($time, function () use ($connection, &$timerId, $object, $limitTime) {
                if (time() - $object->start >= $limitTime) {
                    unset(self::$eventData[$connection->id]);
                    $connection->close();
                }
                if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                    Timer::del($timerId);
                    return;
                }
                $data = $object->pushData();
                $connection->send(new ServerSentEvents([
                    'id' => uniqid('', true),
                    'event' => 'message',
                    'data' => StringUtils::jsonEncode($data),
                ]));
                $connection->baseWrite();
            });
            $response = new Response(200, ['Content-Type' => 'text/event-stream', 'access-control-allow-origin' => '*']);
            $response->withHeader('Cache-Control', 'no-cache');
            $response->withHeader('X-Accel-Buffering', 'no');
            $response->withBody((string)new ServerSentEvents([
                'id' => uniqid('', true),
                'event' => 'message',
                'data' => StringUtils::jsonEncode($object->pushData()),
            ]));
            $connection->send($response);
            $connection->baseWrite();
            return $response;
        }
        return new Response(200, ['Content-Type' => 'application/json;charset=utf-8', 'access-control-allow-origin' => '*']);
    }
}
