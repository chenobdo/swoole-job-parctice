<?php

namespace Kcloze\Jobs;

class Jobs
{
	const MAX_POP = 100; //单个topic每次最多取多少次

	public function run($config)
	{
		$queue = $this->getQueue($config['queue']);
		$queue->addTopics($config['topics']);
        $log   = new Logs($config['logPath']);
        //循环次数计数
        $req = 0;
		while (true) {
			$topics = $queue->getTopics();
			if ($topics) {
				// 遍历topic任务列表
				foreach ($topics as $key => $jobName) {
					// 每次最多取MAX_POP个任务执行
					for ($i = 0; $i < self::MAX_POP; $i++) {
						$data = $queue->pop($jobName);
						$log->log(print_r($data, true), 'info');
						if (!empty($data) && isset($data['jobAction'])) {
							//嵌入框架
                            $this->loadFramework();
							$jobName   = "Kcloze\MyJob\\" . ucfirst($jobName);
							$jobAction = $data['jobAction'];
							$log->log(print_r([$jobName, $jobAction], true), 'info');
							if (method_exists($jobName, $jobAction)) {
								try {
                                    $job = new $jobName();
                                    $job->$jobAction($data);
                                    $log->log("uuid: " . $data['uuid'] . " one job has been done!", 'info');
                                } catch (Exception $e) {
                                    $log->log($e->getMessage(), 'error');
                                }
							} else {
								$log->log($jobAction . " action not find!", 'warning');
							}
						} else {
							$log->log($jobName . " no work to do!", 'info');
							break;
						}
					}
				}
			} else {
				$log->log("All no work to do!", 'info');
			}
			$log->log("sleep 3 second!", 'info');
            $log->flush();
            sleep(3);
            $req++;
            //达到最大循环次数，退出循环，防止内存泄漏
            if ($req >= self::MAX_REQUEST) {
                echo "达到最大循环次数，让子进程退出，主进程会再次拉起子进程\n";
                break;
            }
		}
	}

	protected function getQueue($config)
    {
        if (isset($config['name']) && $config['name'] == 'redis') {
            $queue = new Redis($config);
        } else {
        	echo "you must add queue config\n";
            $queue = null;
        }
        return $queue;
    }

    // 载框架

    /**
     * 载入框架
     * @return void
     */
    protected function loadFramework()
    {
        // defined('YII_DEBUG') or define('YII_DEBUG', true);
        // defined('YII_ENV') or define('YII_ENV', 'dev');
        // require __DIR__ . '/vendor/autoload.php';
        // require __DIR__ . '/vendor/yiisoft/yii2/Yii.php';
        // $config = require __DIR__ . '/config/console.php';
        // $application = new yii\console\Application($config);
        // $exitCode    = $application->run();
    }
}
