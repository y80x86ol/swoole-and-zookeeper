<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-24
 * Time: 09:49
 */

namespace App\Services;


class Service
{
    /**
     * 进程信息
     *
     * @var
     */
    protected $process;

    /**
     * 当前节点
     *
     * @var
     */
    protected $node;

    /**
     * tasker名字
     *
     * @var
     */
    protected $taskName;

    /**
     * 初始化数据
     *
     * @param $process
     * @param $node
     * @param $taskName
     */
    public function __construct($process, $node, $taskName)
    {
        $this->process = $process;
        $this->node = $node;
        $this->taskName = $taskName;
    }
}