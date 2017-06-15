<?php

namespace Kcloze\Jobs;

class Queue
{
	public function push($key, $value)
    {
    }

    public function pop($key)
    {
    }

    public function addTopic($key)
    {
    }

    public function getTopics()
    {
    }

    public function uuid()
    {
        $len = 20;
        $hashStr = substr(str_shuffle(str_repeat('abcdefghijklmnopqrstuvwxyz0123456789', $len)), 0, $len);

        $uuid = md5(uniqid($hashStr, true) . microtime(true) . mt_rand(0, 1000));
        return $uuid;
    }
}
