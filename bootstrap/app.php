<?php

use App\Core\SwooleServer;

/**
 * 应用入口
 */
class app
{
    /**
     * 初始化打印记录
     */
    public function __construct()
    {
        echo "======== app run ========" . PHP_EOL;
    }
    /**
     * 执行应用
     */
    public function run()
    {
        $swooleServer = new SwooleServer();

        $swooleServer->run();
    }

    /**
     * 应用销毁打印记录
     */
    public function __destruct()
    {
        echo "======== app stop ========" . PHP_EOL;
    }
}

$app = new app();

return $app;