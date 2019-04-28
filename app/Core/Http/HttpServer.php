<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-26
 * Time: 17:38
 */

namespace App\Core\Http;

use App\Common\Log;
use App\Core\CoreService;

/**
 * http的服务
 * @package App\Core
 */
class HttpServer extends CoreService
{
    /**
     * 配置
     *
     * @var array
     */
    private $config = [];

    public function __construct()
    {
        $this->config = config('swoole');
    }

    /**
     * 开启进程
     *
     * @return \Swoole\Process
     */
    public function run()
    {
        $processHttp = new \Swoole\Process(function ($process) {
            Log::info("【http】这是一个http模块，用于接受http请求");
            Log::info("【http】启动成功 " . $this->config['ip'] . ":" . $this->config['port']);

            $this->createHttpServer();

        }, true);

        $processHttp->start();

        echo $processHttp->read(); // 输出进程基本信息

        return $processHttp;
    }

    /**
     * 创建http服务
     */
    private function createHttpServer()
    {
        $http = new \Swoole\Http\Server($this->config['ip'], $this->config['port']);

        $http->on('request', function ($request, $response) {
            var_dump($request->get, $request->post);

            $httpService = new HttpService();
            $responseContent = $httpService->run($request, $response);

            $response->header($responseContent['header']);
            $response->end($responseContent['body']);
        });

        $http->start();
    }
}