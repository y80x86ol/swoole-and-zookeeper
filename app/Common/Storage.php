<?php

namespace App\Common;

/**
 * 简易配置文件解析
 *
 * @package App\Common
 */
class Storage
{
    /**
     * 数据存储
     *
     * @var array
     */
    private static $data = [];

    /**
     * 单列模式对象
     *
     * @var
     */
    private static $instance;

    /**
     * 初始化对象
     *
     * @return Storage
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 设置数据
     *
     * @param string $name
     * @param $value
     */
    public function set(string $name, $value)
    {
        self::$data[$name] = $value;
    }

    /**
     * 获取指定数据
     *
     * @param $name
     * @return mixed|null
     */
    public function get($name)
    {
        return self::$data[$name] ?? null;
    }
}