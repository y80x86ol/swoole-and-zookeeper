<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-25
 * Time: 08:47
 */

namespace App\Core;

/**
 * swoole的process进程池方式启动应用
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
            $zooService = new ZooServer();
            $zooService->initData();
            $zooService->run();
        });

        $pool->on("WorkerStop", function ($pool, $workerId) {
            echo "Worker#{$workerId} is stopped\n";
        });

        $pool->start();
    }
}