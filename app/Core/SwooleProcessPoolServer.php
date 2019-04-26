<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-25
 * Time: 08:47
 */

namespace App\Core;

use App\Common\Log;

/**
 * swoole的process进程池方式启动应用
 *
 * @package App\Core
 */
class SwooleProcessPoolServer
{
    /**
     * 开始运行swoole
     */
    public function run()
    {
        $workerNum = 1;
        $pool = new \Swoole\Process\Pool($workerNum);

        $pool->on("WorkerStart", function ($pool, $workerId) {
            Log::info("======== 进程池启动成功 ========");

            $httpServer = new HttpServer();
            $httpProcess = $httpServer->run();

            $pingServer = new PingServer();
            $pingProcess = $pingServer->run();

            $zooService = new ZooServer();
            $zooService->initData();
            $zooService->run();
        });

        $pool->on("WorkerStop", function ($pool, $workerId) {
            Log::info("======== 有进程停止了工作 ========" . $workerId);

        });

        $pool->start();
    }
}