<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-26
 * Time: 16:36
 */

namespace App\Core\Cluster;


use App\Common\Log;
use App\Core\CoreService;

/**
 * cluster模块
 * @package App\Core
 */
class ZooServer extends CoreService
{
    /**
     * 开始执行
     */
    public function run()
    {
        $processPing = new \Swoole\Process(function ($process) {
            Log::info("【cluster】这是一个cluster模块，用于节点选举和工作");

            $zooService = new ZookeeperServer();
            $zooService->initData();
            $zooService->run();
        }, true);

        $processPing->start();

        echo $processPing->read(); // 输出进程基本信息

        return $processPing;
    }
}