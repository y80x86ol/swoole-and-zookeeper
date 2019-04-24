<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-24
 * Time: 09:49
 */

namespace App\Services;


use App\Common\Log;

/**
 * task任务worker
 *
 * @package App\Services
 */
class TaskWorkService extends Service
{
    /**
     * 开始执行
     */
    public function run()
    {
        while (true) {
            //这里是当前进程的具体业务执行逻辑
            sleep(5);
            Log::info("[" . $this->node . "][" . $this->taskName . "]task的work开始处理工作，此消息只能从日志中查看到:", [$this->process->pid]);
        }
    }
}