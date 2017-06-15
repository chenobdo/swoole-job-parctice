<?php
date_default_timezone_set('Asia/Shanghai');

require __DIR__ . '/../vendor/autoload.php';

$config = [
    'queue'   => ['type' => 'redis', 'host' => '127.0.0.1', 'port' => 6379],
    'logPath' => __DIR__ . '/../log',
    'topics'  => ['MyJob'],
];

$jobs = new Kcloze\Jobs\Jobs($config);
if (!$jobs->queue) {
    die("queue object is null\n");
}

$topics = $jobs->queue->getTopics();

for ($i = 0; $i < 100; $i++) {
    $topicName = 'MyJob';
    $uuid      = $jobs->queue->uuid();
    $data      = [
        'uuid' => $uuid,
        'jobAction' => 'helloAction',
        'title' => 'kcloze',
        'time' => time()
    ];
    $jobs->queue->push($topicName, $data);
    echo $uuid . " ok\n";
}
for ($i = 0; $i < 100; $i++) {
    $topicName = 'MyJob';
    $uuid      = $jobs->queue->uuid();
    $data      = [
        'uuid' => $uuid,
        'jobAction' => 'errorAction',
        'title' => 'kcloze',
        'time' => time()
    ];
    $jobs->queue->push($topicName, $data);
    echo $uuid . " ok\n";
}
