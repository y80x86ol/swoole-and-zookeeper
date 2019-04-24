<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-21
 * Time: 17:50
 */

namespace App\Core;

/**
 * follower执行任务
 *
 * @package App\Services
 */
class FollowerWorker extends WorkerCommon
{
    /**
     * 执行follower任务
     */
    public function run()
    {
        $this->doTaskerJob();
    }

    /**
     * 从集群中获取所有tasker的worker分布
     *
     * @return array
     */
    protected function handleWorker()
    {
        $nodeNeedWorkerNum = $this->cluster->getClusterNodes();

        return $nodeNeedWorkerNum;
    }
}