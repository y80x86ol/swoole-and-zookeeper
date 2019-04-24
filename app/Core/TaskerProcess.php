<?php

namespace App\Core;

use App\Common\Log;
use App\Services\TaskWorkService;

/**
 * swoole process进程处理
 *
 * @package App\Services
 */
class TaskerProcess extends CoreService
{
    /**
     * 节点信息
     *
     * @var
     */
    private $node;

    /**
     * 任务名称
     *
     * @var
     */
    private $taskName;

    /**
     * 初始化一些数据
     */
    public function initData($node, $taskName)
    {
        $this->node = $node;
        $this->taskName = $taskName;
    }

    /**
     * 创建worker进程
     */
    public function run()
    {
        $process = new \Swoole\Process(function ($process) {
            Log::info("进程创建成功，开始执行任务");

            $taskWorkerService = new TaskWorkService($process, $this->node, $this->taskName);
            $taskWorkerService->run();
        }, true);

        $process->start();

        echo $process->read(); // 输出进程基本信息

        return $process;
    }
}