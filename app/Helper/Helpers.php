<?php

/**
 * 获取配置文件信息
 *
 * @param string $key
 * @return array|bool
 */
function config(string $key = '')
{
    $config = \App\Common\Config::getInstance();
    return $config::get($key);
}

/**
 * swoole断点调试
 *
 * @param $msg
 */
function swoole_exit($msg)
{
    if (!is_string($msg)) {
        $msg = json_encode($msg);
    }

    throw new Swoole\ExitException($msg);
}

/*
 * 生成随机字符串
 *
 * @param int $length 生成随机字符串的长度
 * @param string $char 组成随机字符串的字符串
 * @return string $string 生成的随机字符串
 */
function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    if (!is_int($length) || $length < 0) {
        return false;
    }

    $string = '';
    for ($i = $length; $i > 0; $i--) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }

    return $string;
}