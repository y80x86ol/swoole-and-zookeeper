<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-22
 * Time: 16:48
 */

namespace App\Core\Cluster;

use App\Core\CoreService;

/**
 * zookeeper集群
 *
 * @package App\Services
 */
class Cluster extends CoreService
{
    /**
     * zookeeper对象
     *
     * @var null
     */
    private $zookeeper = null;

    /**
     * zookeeper的配置文件
     *
     * @var array
     */
    private $zooConfig = [];

    /**
     * 初始化数据
     */
    public function __construct()
    {
        $this->initZookeeperConfig();
        $this->initZookeeper();
    }

    /**
     * 初始化zookeeper的配置
     */
    private function initZookeeperConfig(): void
    {
        $this->zooConfig = config('zookeeper');
    }

    /**
     * 初始化zookeeper
     */
    private function initZookeeper(): void
    {
        $this->zookeeper = new \Zookeeper($this->zooConfig['hosts']);
    }

    /**
     * 设置集群的值
     *
     * @param $value
     */
    public function setCluster(array $value)
    {
        $value = json_encode($value);

        $this->zookeeper->set($this->getClusterPath(), $value);
    }

    /**
     * 获取集群的值
     *
     * @return mixed
     */
    public function getCluster()
    {
        $value = $this->zookeeper->get($this->getClusterPath());
        return json_decode($value, true);
    }

    /**
     * 获取集群中所有子节点
     *
     * @return mixed
     */
    public function getNodesChildren(): array
    {
        return $this->zookeeper->getChildren($this->getClusterNodesPath());
    }

    /**
     * 设置集群节点信息
     *
     * @param array $value
     */
    public function setClusterNodes(array $value)
    {
        $value = json_encode($value);

        $this->zookeeper->set($this->getClusterNodesPath(), $value);
    }

    /**
     * 获取集群节点信息
     *
     * @return mixed
     */
    public function getClusterNodes()
    {
        $value = $this->zookeeper->get($this->getClusterNodesPath());

        return json_decode($value, true);
    }

    /**
     * 获取集群tasker信息
     *
     * @return mixed
     */
    public function getClusterTasker()
    {
        $value = $this->zookeeper->get($this->getClusterTaskerPath());

        return json_decode($value, true);
    }

    /**
     * ===================================================
     *
     * 集群各种路径
     *
     * ===================================================
     */
    /**
     * 获取zookeeper的acl权限配置
     *
     * @return mixed
     */
    public function getAcl()
    {
        return $this->zooConfig['acl'];
    }

    /**
     * 获取集群路径
     *
     * @return string
     */
    public function getClusterPath(): string
    {
        return '/' . $this->zooConfig['cluster'];
    }

    /**
     * 获取集群节点的路径
     *
     * @return string
     */
    public function getClusterNodesPath(): string
    {
        return $this->getClusterPath() . '/nodes';
    }

    /**
     * 获取tasker路径
     *
     * @return string
     */
    public function getClusterTaskerPath(): string
    {
        return $this->getClusterPath() . '/tasker';
    }

    /**
     * 获取节点路径
     *
     * @param string $node
     * @return string
     */
    public function getNodePath(string $node): string
    {
        return $this->getClusterPath() . '/nodes/' . $node;
    }
}