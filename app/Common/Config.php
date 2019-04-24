<?php

namespace App\Common;

/**
 * 简易配置文件解析
 *
 * @package App\Common
 */
class Config
{
    /**
     * 配置数组
     *
     * @var array
     */
    private static $config = [];

    /**
     * 单列静态对象
     *
     * @var
     */
    private static $instance;

    /**
     * 构造函数初始化
     */
    public function __construct()
    {
        $configListPath = dirname(dirname(dirname(__FILE__))) . '/config';
        $configList = scandir($configListPath);

        // 循环遍历文件
        $configListData = [];
        foreach ($configList as $config) {
            $configPath = dirname(dirname(dirname(__FILE__))) . '/config/' . $config;

            if (is_file($configPath)) {
                $configName = substr($config, 0, -4);
                $configData = require_once($configPath);

                $configListData[$configName] = $configData;
            }
        }

        self::$config = $configListData;
    }

    /**
     * 初始化对象
     *
     * @return Config
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取所有配置
     *
     * @return array
     */
    public static function getAll()
    {
        return self::$config;
    }

    /**
     * 获取单个配置
     *
     * Config::get('app')
     *
     * 可以采用Config::get('app.domain');的方式获取子配置
     *
     * @param string $name 配置名字
     * @return bool|array
     */
    public static function get($name)
    {
        $nameArry = explode('.', $name);
        if (count($nameArry) == 1) {
            $config = self::$config[$name];
            if ($config) {
                return $config;
            }
        } elseif (count($nameArry) > 1) {
            $config = self::$config[$nameArry[0]];
            if ($config && isset($config[$nameArry[1]])) {
                return $config[$nameArry[1]];
            }
        }
        return false;
    }
}