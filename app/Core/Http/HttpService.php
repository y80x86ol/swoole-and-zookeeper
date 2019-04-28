<?php
/**
 * Created by PhpStorm.
 * User: ken
 * Date: 2019-04-26
 * Time: 17:50
 */

namespace App\Core\Http;

use App\Common\Log;

/**
 * http 服务类
 * @package App\Services
 */
class HttpService
{
    /**
     * http请求处理
     */
    public function run($request)
    {
        Log::info("【http】我接受到了一个请求");
        Log::info(json_encode($request->get));

        $header = "Content-Type" . "text/html; charset=utf-8";
        $body = "<h1>you send a request. #" . rand(1000, 9999) . "</h1>";

        return ['header' => $header, 'body' => $body];
    }
}