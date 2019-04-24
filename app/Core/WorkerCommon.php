<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-23
 * Time: 21:04
 */

namespace App\Core;


use App\Common\Log;
use App\Common\Storage;

/**
 * 领导者和跟随者相同处理业务
 *
 * @package App\Services\Core
 */
class WorkerCommon extends CoreService
{
    /**
     * zookeeper的集群
     *
     * @var
     */
    protected $cluster;

    /**
     * 数据存储
     *
     * @var
     */
    protected $storage;

    /**
     * 节点名称
     *
     * @var
     */
    protected $node;

    /**
     * 初始化数据
     */
    public function initData($node)
    {
        $this->node = $node;

        $this->cluster = new Cluster();

        $this->storage = Storage::getInstance();
    }

    /**
     * 对任务进行工作进程的分配
     */
    protected function doTaskerJob()
    {
        // 1、检查所有集群中的节点任务执行数量
        $tasker = $this->cluster->getClusterTasker();

        // 计算每个节点需要多少个worker
        $nodeNeedWorkerNum = $this->handleWorker();

        foreach ($tasker as $task) {

            // 获得当前节点当前通道进程数量
            $currentNeedWorkerNum = $nodeNeedWorkerNum[$task['name']][$this->node] ?? 0;

            $this->handleTaskWorker($task, $currentNeedWorkerNum);
        }
    }

    /**
     * 处理任务的worker
     *
     * @param $task
     * @param $currentNeedWorkerNum
     */
    protected function handleTaskWorker($task, $currentNeedWorkerNum)
    {
        // 检查当前节点有多少个worker
        $processList = $this->storage->get('processList');
        $taskerProcessList = $processList[$task['name']] ?? [];

        $currentWorkerNum = count($taskerProcessList);

        if ($currentWorkerNum) {
            Log::info("当前进程：" . $currentWorkerNum . " 需求进程：" . $currentNeedWorkerNum);

            if ($currentWorkerNum == $currentNeedWorkerNum) {
                Log::info("数量一致，不做处理");

            } else if ($currentNeedWorkerNum > $currentWorkerNum) {
                Log::info("worker不足，需要增加进程");

                $needAdd = $currentNeedWorkerNum - $currentWorkerNum;
                for ($i = 0; $i < $needAdd; $i++) {
                    $this->createProcess($task);
                }
            } else if ($currentNeedWorkerNum < $currentWorkerNum) {
                Log::info("worker太多，需要关闭进程");

                $needClose = $currentWorkerNum - $currentNeedWorkerNum;

                for ($i = 0; $i < $needClose; $i++) {
                    $process = array_pop($taskerProcessList);
                    posix_kill($process->pid, SIGINT);
                }

                $processList[$task['name']] = $taskerProcessList;
                $this->storage->set('processList', $processList);
            }

        } else {
            Log::info("当前进程：" . $currentWorkerNum . " 需求进程：" . $currentNeedWorkerNum);
            Log::info("需要创建进程：" . $currentNeedWorkerNum);

            //没有任何workerNum,需要创建
            for ($i = 0; $i < $currentNeedWorkerNum; $i++) {
                $this->createProcess($task);
            }
        }
    }

    /**
     * 创建进程
     *
     * @param $task
     */
    protected function createProcess($task)
    {
        $processService = new TaskerProcess();
        $processService->initData($this->node, $task['name']);
        $process = $processService->run();

        $processList = $this->storage->get('processList');
        $processList[$task['name']][] = $process;
        $this->storage->set('processList', $processList);
    }
}