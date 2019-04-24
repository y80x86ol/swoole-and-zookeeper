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

    /**
     * 开始运行swoole
     */
    public function run()
    {
        $server = $this->server;

        /**
         * worker连接事件
         */
        $server->on('connect', function ($server, $fd) {
            Log::info("connection open: {$fd}\n");
        });

        /**
         * 接受来自worker事件
         */
        $server->on('receive', function ($server, $fd, $reactor_id, $data) {
            $server->send($fd, "Swoole: {$data}");
            $server->close($fd);
        });

        /**
         * worker关闭事件
         */
        $server->on('close', function ($server, $fd) {
            Log::info("connection close: {$fd}\n");
        });

        $server->on('start', function ($server) {
            Log::info("=======worker start========");
        });

        $server->on('shutdown', function ($server) {
            Log::info("=======worker shutdown========");
        });

        $server->on('workerExit', function ($server) {
            Log::info("=======worker exit========");
        });

        $server->on('workerError', function ($server, int $worker_id, int $worker_pid, int $exit_code, int $signal) {
            Log::info("=======worker error========");
            $server->stop();
        });

        $server->on('workerStop', function ($server, int $worker_id) {
            Log::info("=======worker stop========");
            $server->shutdown();
        });

        /**
         * 添加一个子进程，用于处理zookeeper的监听操作
         *
         * 因为process不能在worker里面操作
         * Swoole\Process::__construct(): swoole_process can't be used in master process
         */
        $process = new \Swoole\Process(function ($process) use ($server) {
            $zooService = new ZooServer();
            $zooService->initData();
            $zooService->run();
        });

        $server->addProcess($process);

        //启动swoole
        $server->start();
    }
}