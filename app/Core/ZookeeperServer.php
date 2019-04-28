<?php

namespace App\Core;

use App\Common\Log;
use App\Common\Storage;

/**
 * ZooService业务类
 *
 * @package App\Services
 */
class ZookeeperServer extends CoreService
{
    /**
     * zookeeper对象
     *
     * @var null
     */
    private $zookeeper = null;

    /**
     * 是否是主节点，true是主节点，false跟随节点
     *
     * @var bool
     */
    private $leader = false;

    /**
     * zookeeper的配置文件
     *
     * @var array
     */
    private $zooConfig = [];

    /**
     * 集群
     *
     * @var
     */
    private $cluster;

    /**
     * 节点名字
     *
     * @var
     */
    private $node;

    /**
     * 数据存储
     *
     * @var
     */
    private $storage;

    /**
     * 构造函数初始化
     */
    public function initData()
    {
        $this->storage = Storage::getInstance();

        $this->initZookeeperConfig();

        $this->initZookeeper();

        $this->checkCluster();

        $this->checkClusterNodes();

        $this->checkClusterTasker();

        $this->checkNode();

        $this->selectLeader();
    }

    /**
     * 初始化zookeeper的配置
     */
    private function initZookeeperConfig()
    {
        $this->zooConfig = config('zookeeper');

        $this->cluster = new Cluster();

        $this->node = $this->zooConfig['node'];
    }

    /**
     * 初始化zookeeper
     */
    private function initZookeeper()
    {
        $this->zookeeper = new \Zookeeper($this->zooConfig['hosts'], null, 10000);
    }

    /**
     * 检查集群状态
     */
    private function checkCluster()
    {
        $isExist = $this->zookeeper->exists($this->cluster->getClusterPath());
        if ($isExist) {
            Log::info("已经存在集群了");
        } else {
            $realPath = $this->zookeeper->create($this->cluster->getClusterPath(), "cluster", $this->cluster->getAcl());
            if ($realPath) {
                Log::info("集群创建成功:" . $realPath);
            } else {
                Log::info('【错误】集群创建失败');
            }
        }
    }

    /**
     * 检查集群状态
     */
    private function checkClusterNodes()
    {
        $isExist = $this->zookeeper->exists($this->cluster->getClusterNodesPath());
        if ($isExist) {
            Log::info("已经存在集群节点了:");
        } else {
            $realPath = $this->zookeeper->create($this->cluster->getClusterNodesPath(), "nodes", $this->cluster->getAcl());
            if ($realPath) {
                Log::info("集群节点创建成功:" . $realPath);
            } else {
                Log::info('【错误】集群节点创建失败');
            }
        }
    }

    /**
     * 检查tasker
     */
    private function checkClusterTasker()
    {
        $isExist = $this->zookeeper->exists($this->cluster->getClusterTaskerPath());
        if ($isExist) {
            Log::info("已经存在tasker了:");
        } else {
            $value = config('tasker.list');
            $realPath = $this->zookeeper->create($this->cluster->getClusterTaskerPath(), json_encode($value), $this->cluster->getAcl());
            if ($realPath) {
                Log::info("tasker创建成功:" . $realPath);
            } else {
                Log::info('【错误】tasker创建失败');
            }
        }
    }

    /**
     * 检查节点
     */
    private function checkNode()
    {
        $isExist = $this->zookeeper->exists($this->cluster->getNodePath($this->node));
        if ($isExist) {
            Log::info("节点已经存在：" . $this->cluster->getNodePath($this->node));

            $clientInfo = $this->zookeeper->get($this->cluster->getNodePath($this->node));
            $currentInfo = $this->storage->set('client', $clientInfo);
            if ($clientInfo == $currentInfo) {
                //说明是断线重连，继续进行之前的处理
                Log::info("网络波动，现已连接，继续处理");
            } else {
                //新的冲突节点
                Log::info("新的冲突节点");

//                Log::info("当前进程：" . posix_getpid());
//                posix_kill(posix_getppid(), SIGTERM);
//                sleep(10);

                //创建临时节点，当client退出的时候自动删除节点
                $this->setNode();

                Log::info("开始随机生成节点：" . $this->node);

                $this->createNode();
            }
        } else {
            //创建临时节点，当client退出的时候自动删除节点
            $this->createNode();
        }
    }

    /**
     * 创建节点
     */
    private function createNode()
    {
        //创建临时节点，当client退出的时候自动删除节点
        $realPath = $this->zookeeper->create($this->cluster->getNodePath($this->node), $this->node, $this->cluster->getAcl(), \Zookeeper::EPHEMERAL);
        if ($realPath) {
            Log::info("节点创建成功:" . $realPath);

            $clientInfo = $this->zookeeper->get($this->cluster->getNodePath($this->node));
            $this->storage->set('client', $clientInfo);
        } else {
            Log::info('【错误】节点创建失败');
        }
    }


    /**
     * 主节点的选择
     */
    private function selectLeader()
    {
        $node = $this->getClusterNodesChildren();
        if ($node == $this->node) {
            $this->setLeader(true);

            echo '我是最小节点，我自己当选主节点' . PHP_EOL;

            $this->zookeeper->getChildren($this->cluster->getClusterNodesPath(), [$this, 'watchNode']);
        } else {
            echo "现在我开始监听：" . $node . PHP_EOL;

            $this->setLeader(false);
        }
    }

    /**
     * 监听节点
     *
     * @param $watcher
     * @throws \Exception
     */
    public function watchNode($watcher)
    {
        Log::info(PHP_EOL . "监听到了节点变化，变化如下：");
        Log::info($watcher);

        $watching = $this->getClusterNodesChildren();
        if ($watching == $this->node) {
            Log::info("[" . $this->node . "]I am the new leader");

            $this->setLeader(true);

            $this->zookeeper->getChildren($this->cluster->getClusterNodesPath(), [$this, 'watchNode']);
        } else {
            Log::info("[" . $this->node . "]Now I am watching", [$watching]);

            $this->setLeader(false);
        }
    }

    /**
     * 获取集群中所有的子节点
     *
     * 主节点选择规则为集群下的第一个节点
     *
     * @return mixed
     * @throws \Exception
     */
    public function getClusterNodesChildren()
    {
        $children = $this->zookeeper->getChildren($this->cluster->getClusterNodesPath());
        sort($children);

        $childrenNum = count($children);

        for ($i = 0; $i < $childrenNum; $i++) {
            if ($this->node == $children[$i]) {
                if ($i > 0) {
                    //监听当前节点之前的节点，此时为非主节点
                    $this->zookeeper->get($this->cluster->getNodePath($children[$i - 1]), [$this, 'watchNode']);
                    return $children[$i - 1];
                }
                return $children[$i];
            }
        }

        throw new \Exception("没有在集群中找到自己");
    }

    /**
     * 开始运行zookeeper集群工作
     */
    public function run()
    {
        while (true) {
            if ($this->leader == true) {
                $this->doLeaderJob();
            } else {
                $this->doFollowJob();
            }
            sleep(5);
        }
    }

    /**
     * 做leader工作
     */
    private function doLeaderJob(): void
    {
        Log::info(PHP_EOL . "[" . $this->node . "]I am the leader");

        $zooLeaderService = new LeaderWorker();
        $zooLeaderService->initData($this->node);
        $zooLeaderService->run();
    }

    /**
     * 做worker工作
     */
    private function doFollowJob(): void
    {
        Log::info(PHP_EOL . "[" . $this->node . "]I am the follower");

        $zooLeaderService = new FollowerWorker();
        $zooLeaderService->initData($this->node);
        $zooLeaderService->run();
    }

    /**
     * 设置leader状态
     *
     * @param $status
     */
    private function setLeader($status): void
    {
        $this->leader = $status;
    }

    /**
     * 设置节点
     *
     * 用于当节点冲突的时候，随机生成节点名称
     */
    private function setNode()
    {
        $node = str_rand(8);
        $this->node = $node;
    }
}