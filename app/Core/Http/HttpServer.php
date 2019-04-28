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

    private function createHttpServer()
    {
        try {
            $http = new \Swoole\Http\Server($this->config['ip'], $this->config['port']);

            $http->on('request', function ($request, $response) {
                var_dump($request->get, $request->post);

                $httpService = new HttpService();
                $responseContent = $httpService->run($request, $response);

                $response->header($responseContent['header']);
                $response->end($responseContent['body']);
            });

            $http->start();
            Log::info("【http】222，尝试下一个端口" . ($this->config['port'] + 1));
        } catch (\Exception $exception) {
            Log::info("【http】启动失败，尝试下一个端口" . ($this->config['port'] + 1));
            $this->config['port']++;
            return $this->createHttpServer();
        }
        return '';
    }
}