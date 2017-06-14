<?php

namespace Kcloze\Jobs;

class Queue
{
	const TOPIC_LIST_NAME = 'topic_list';

	private $redis = null;

	public function __construct(array $config)
	{
		$this->redis = new \Redis();
		try {
			$this->redis->connect($config['host'], $config['port']);
		} catch (Exception $e) {
			echo $e->getMessage() . "\n";
		}
	}

	public function push($key, $value)
	{
		return $this->redis->lPush($key, $value);
	}

	public function addTopic($key)
	{
		return $this->redis->sAdd(self::TOPIC_LIST_NAME, $key);
	}

	public function getTopics()
	{
		return $this->redis->sort(self::TOPIC_LIST_NAME);
	}
}
