<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-21
 * Time: 09:12
 */

namespace App\Core;

use App\Common\Log;

/**
 * swoole业务类
 *
 * @package App\Services
 */
class SwooleServer extends CoreService
{
    /**
     * swoole服务
     *
     * @var \Swoole\Server
     */
    private $server;

    /**
     * swoole配置文件
     *
     * @var
     */
    private $swooleConfig;

    /**
     * swoole初始化
     */
    public function __construct()
    {
        $this->swooleConfig = config('swoole');

        $this->server = $this->startSwoole($this->swooleConfig['ip'], $this->swooleConfig['port']);

        $this->server->set([
            'reactor_num' => $this->swooleConfig['reactor_num'], //reactor thread num
            'worker_num' => $this->swooleConfig['worker_num'],    //worker process num
            'backlog' => $this->swooleConfig['backlog'],   //listen backlog
            'dispatch_mode' => $this->swooleConfig['dispatch_mode'],
        ]);
    }

    /**
     * 启动swoole
     *
     * @param $ip
     * @param $port
     * @return \Swoole\Server
     */
    public function startSwoole($ip, $port)
    {
        $swooleService = new \Swoole\Server($ip, $port);

        Log::info("swoole启动成功：" . $ip . ":" . $port);

        return $swooleService;
    }
}