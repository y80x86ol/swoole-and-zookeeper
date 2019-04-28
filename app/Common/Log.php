<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-21
 * Time: 19:02
 */

namespace App\Common;


use Monolog\Handler\FirePHPHandler;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * 应用日志
 *
 * @package App\Common
 */
class Log
{
    /**
     * monolog日志
     *
     * @var Logger
     */
    private static $log;

    /**
     * 单列对象
     *
     * @var
     */
    private static $instance;

    /**
     * 初始化日志信息
     *
     * @throws \Exception
     */
    public function __construct()
    {
        $logPath = dirname(dirname(dirname(__FILE__))) . '/storage/logs/app-' . date("Y-m-d") . '.log';

        self::$log = new Logger('app');
        self::$log->pushHandler(new StreamHandler($logPath, self::level()));
        $firephp = new FirePHPHandler();
        self::$log->pushHandler($firephp);
    }

    /**
     * 初始化对象
     *
     * @return Log
     * @throws \Exception
     */
    public static function getInstance()
    {
        if (!(self::$instance instanceof self)) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * 获取日志等级
     *
     * @return int
     */
    private static function level()
    {
        switch (config('app.logs.level')) {
            case 'debug':
                return Logger::DEBUG;
            case 'info':
                return Logger::INFO;
            case 'notice':
                return Logger::NOTICE;
            case 'warning':
                return Logger::WARNING;
            case 'error':
                return Logger::ERROR;
            case 'critical':
                return Logger::CRITICAL;
            case 'alert':
                return Logger::ALERT;
            case 'emergency':
                return Logger::EMERGENCY;
            default:
                return Logger::INFO;
        }
    }

    /**
     * 静态魔术方法，调用底层各种方法
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws \Exception
     */
    public static function __callStatic($method, $arguments)
    {
        print_r($arguments[0] . PHP_EOL);
        if (isset($arguments[1])) {
            print_r($arguments[1]);
            print_r(PHP_EOL);
        }

        self::getInstance();
        return self::$log->$method($arguments[0] ?? '', $arguments[1] ?? [], $arguments[2] ?? []);
    }
}