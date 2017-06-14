<?php
date_default_timezone_set('Asia/Shanghai');
define('DS', DIRECTORY_SEPARATOR);
define('APP_PATH', realpath(dirname(__FILE__)).DS.'..'.DS);

require_once APP_PATH . 'src/Jobs.php';
require_once APP_PATH . 'src/Queue.php';
require_once APP_PATH . 'src/Process.php';

$config = ['host' => '127.0.0.1', 'port' => 6379];

$queue = new Kcloze\Jobs\Queue($config);

$jobAction = 'hello';
$queue->addTopic($jobAction);

for ($i = 0; $i < 100; $i++) {
	$data = ['title' => 'kcloze', 'time' => tiem()];
	$queue->push($jobAction, $data);
	echo "ok\n";
}
