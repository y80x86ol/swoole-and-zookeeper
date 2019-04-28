<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-26
 * Time: 16:36
 */

namespace App\Core\Ping;


use App\Common\Log;
use App\Core\CoreService;

/**
 * ping模块
 * @package App\Core
 */
class PingServer extends CoreService
{
    private $running = true;

    /**
     * 开始执行
     */
    public function run()
    {
        $processPing = new \Swoole\Process(function ($process) {
            Log::info("【ping】这是一个ping模块，用于节点发现");

            \Swoole\Process::signal(SIGTERM, function ($signo) use ($process) {
                $process->close(0);
                $this->running = false;
                Log::info("======== [SIGTERM]ping get SIGTERM ========");
            });
//
//
//            \Swoole\Process::signal(SIGKILL, function ($signo) use ($process) {
////                $process->exit();
////                $this->running = false;
//                Log::info("======== [SIGTERM]ping get SIGTERM ========");
//            });

            while ($this->running) {
                Log::info("【ping】我正在发现其他节点，3秒执行一次");
                sleep(3);
            }
        }, true);

        $processPing->start();

        echo $processPing->read(); // 输出进程基本信息

        return $processPing;
    }
}