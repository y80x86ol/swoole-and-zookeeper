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


            $this->createSignalSIGCHLD($currentProcess);

            $this->createSignalSIGINT($currentProcess);

            $this->createSignalSIGTEM($currentProcess);

            $this->createSignalSIGKILL($currentProcess);

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

    /**
     * 创建SIGCHLD监听
     *
     * 用来释放子进程，不然子进程出现僵尸进程
     */
    private function createSignalSIGCHLD($currentProcess)
    {
        \Swoole\Process::signal(SIGCHLD, function ($sig) use ($currentProcess) {
            //必须为false，非阻塞模式
            while ($ret = \Swoole\Process::wait(false)) {
                Log::info("======== [SIGCHLD] master get SIGCHLD ========", $ret);
            }
        });
    }

    /**
     * 创建SIGINT监听
     *
     * 2 SIGINT 进程终端，CTRL+C
     *
     * @param $currentProcess
     */
    private function createSignalSIGINT($currentProcess)
    {
        \Swoole\Process::signal(SIGINT, function ($signo) use ($currentProcess) {
            Log::info("======== [SIGINT] master get SIGINT ========", [$signo]);

            foreach ($this->process as $process) {
                //向子进程发送请求退出信号
                \Swoole\Process::kill($process->pid, SIGTERM);
                Log::info("======== [SIGINT] kill children ========", [$process->pid]);
            }

            //当前进程正常退出,不进行退出则会造成僵尸进程存在
            $currentProcess->exit(0);
        });
    }

    /**
     * 创建SIGTEM监听
     *
     * 15 SIGTEM 请求中断
     *
     * @param $currentProcess
     */
    private function createSignalSIGTEM($currentProcess)
    {
        // todo:处理sigtem信号
    }

    /**
     * 创建SIGKILL监听
     *
     * 9 SIGKILL 强制终端
     *
     * @param $currentProcess
     */
    private function createSignalSIGKILL($currentProcess)
    {
        // todo:处理sigkill信号
//        \Swoole\Process::signal(SIGKILL, function ($signo) use ($currentProcess) {
//            Log::info("======== [SIGKILL] master get SIGKILL ========", [$signo]);
//
//            foreach ($this->process as $process) {
//                //向子进程发送请求退出信号
//                \Swoole\Process::kill($process->pid, SIGTERM);
//                Log::info("======== [SIGINT] kill children ========", [$process->pid]);
//            }
//
//            //当前进程正常退出,不进行退出则会造成僵尸进程存在
//            $currentProcess->exit(0);
//        });
    }
}