<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-25
 * Time: 08:47
 */

namespace App\Core;

use App\Common\Log;
use App\Core\Cluster\ZooServer;
use App\Core\Http\HttpServer;
use App\Core\Ping\PingServer;

/**
 * swoole的process进程池方式启动应用
 *
 * @package App\Core
 */
class SwooleProcessPoolServer
{
    /**
     * 记录开启的子进程信息
     *
     * @var array
     */
    private $process = [];

    /**
     * 开始运行swoole
     */
    public function run()
    {
        $workerNum = 1;
        $pool = new \Swoole\Process\Pool($workerNum);

        $pool->on("WorkerStart", function ($pool, $workerId) {
            $currentProcess = $pool->getProcess();

            Log::info("======== 进程池启动成功 ========");

            \Swoole\Process::signal(SIGINT, function ($signo) use ($currentProcess) {
                Log::info("======== [SIGINT]master get SIGINT ========");

                //当前进程正常退出
                $currentProcess->exit(0);
            });

            \Swoole\Process::signal(SIGCHLD, function ($sig) {
                //必须为false，非阻塞模式
                while ($ret = \Swoole\Process::wait(true)) {
                    Log::info("======== [SIGCHLD]master get SIGINT ========", $ret);
                }
            });

            // 启动http模块
            $httpServer = new HttpServer();
            $this->process['http'] = $httpServer->run();

            // 启动ping模块
            $pingServer = new PingServer();
            $this->process['ping'] = $pingServer->run();

            // 启动zookeeper模块
            $pingServer = new ZooServer();
            $this->process['zoo'] = $pingServer->run();
        });

        $pool->on("WorkerStop", function ($pool, $workerId) {
            Log::info("======== 有进程停止了工作 ========" . $workerId);

        });

        $pool->start();
    }
}