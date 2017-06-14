<?php

namespace Kcloze\Jobs;

use Kcloze\Jobs\Queue;

class Redis extends Queue
{
	const TOPIC_LIST_NAME = 'topic_list';

	private $redis = null;

	public function __construct(array $config)
	{
		$this->redis = new \Redis();
		try {
			$this->redis->connect($config['queue']['host'], $config['queue']['port']);
		} catch (Exception $e) {
			echo $e->getMessage() . "\n";
		}
	}

	public function push($key, $value)
	{
		return $this->redis->lPush($key, serialize($value));
	}

	public function pop($key)
	{
		$result = $this->redis->lPop($key);
        return $result ? unserialize($result) : false;
	}

	public function addTopic($key)
	{
		return $this->redis->sAdd(self::TOPIC_LIST_NAME, $key);
	}

	public function getTopics()
	{
		return $this->redis->sMembers(self::TOPIC_LIST_NAME);
	}
}
