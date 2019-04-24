<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-21
 * Time: 17:50
 */

namespace App\Core;

/**
 * leader执行任务
 *
 * @package App\Services
 */
class leaderWorker extends WorkerCommon
{
    /**
     * 所有任务分布情况
     *
     * @var array
     */
    private $allNodeWorkerNum = [];

    /**
     * 执行leader任务
     */
    public function run()
    {
        $this->doTaskerJob();

        $this->storeToCluster();
    }

    /**
     * 计算所有tasker的worker在node上面的分布
     *
     * 这是一个平均计算算法，通过计算算法计算后发送到zookeeper集群中，通知其他节点
     *
     * @param $task
     * @return array
     */
    protected function handleWorker()
    {
        $tasker = $this->cluster->getClusterTasker();

        $nodeNeedWorkerNum = [];

        foreach ($tasker as $task) {
            $nodeList = $this->cluster->getNodesChildren();

            if (is_string($nodeList)) {
                $nodeList = [$nodeList];
            }
            sort($nodeList);

            $nodeListNum = count($nodeList);

            /**
             * 比如当前有3个节点，有8个通道数量（worker），则平均每个节点 int(8/3) + 1=3个节点，然后根据节点进行分配
             */
            $childNeedWorker = intval($task['num'] / $nodeListNum);//每个node节点应该有多少个worker
            $lastWorkerNoDeal = $task['num'] % $nodeListNum;//每个node节点应该有多少个worker

            //准确计算每个节点需要多少个worker进程
            for ($i = 0; $i < $nodeListNum; $i++) {
                if ($lastWorkerNoDeal > 0) {
                    $nodeNeedWorkerNum[$task['name']][$nodeList[$i]] = $childNeedWorker + 1;
                } else {
                    $nodeNeedWorkerNum[$task['name']][$nodeList[$i]] = $childNeedWorker;
                }
                $lastWorkerNoDeal--;
            }
        }
        $this->allNodeWorkerNum = $nodeNeedWorkerNum;

        return $nodeNeedWorkerNum;
    }

    /**
     * 将worker信息存储到到zookeeper集群中
     */
    private function storeToCluster()
    {
        $this->cluster->setClusterNodes($this->allNodeWorkerNum);
    }
}