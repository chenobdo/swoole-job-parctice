<?php

namespace Kcloze\Jobs;

class Jobs
{
	const MAX_POP = 10; //单个topic每次最多取多少次
    const MAX_REQUEST = 100;

	protected $logger = null;
    protected $queue  = null;

    public function __construct($config)
    {
    	$this->logger = new Logs($config['logPath']);
        $this->queue = $this->getQueue($config['queue']);
        $this->queue->addTopics($config['topics']);
    }

	public function run()
	{
        //循环次数计数
        $req = 0;
		while (true) {
			$topics = $this->queue->getTopics();
			if ($topics) {
				// 遍历topic任务列表
				foreach ($topics as $key => $jobName) {
					// 每次最多取MAX_POP个任务执行
					for ($i = 0; $i < self::MAX_POP; $i++) {
						$data = $this->queue->pop($jobName);
                        $this->logger->log(print_r($data, true), 'info');
                        if (!empty($data) && isset($data['jobAction'])) {
                            $jobAction = $data['jobAction']; 
                            $this->logger->log(print_r([$jobName, $jobAction], true), 'info');
                        	//业务代码
                            $this->loadFramework($jobName, $jobAction, $data);
                        } else {
                            $this->logger->log($jobName . " no work to do!", 'info');
                            break;
                        }
					}
				}
			} else {
				$this->logger->log("All no work to do!", 'info');
			}
			$this->logger->log("sleep 3 second!", 'info');
            $this->logger->flush();
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
        if ($config['type'] == 'redis') {
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
    protected function loadFramework($jobName, $jobAction, $data)
    {
        $jobName = "Kcloze\MyJob\\" . ucfirst($jobName);
        if (method_exists($jobName, $jobAction)) {
            try {
                $job = new $jobName();
                $job->$jobAction($data);
                $this->logger->log("uuid: " . $data['uuid'] . " one job has been done!", 'trace', 'jobs');
            } catch (Exception $e) {
                $this->logger->log($e->getMessage(), 'error');
            }
        } else {
            $this->logger->log($jobAction . " action not find!", 'warning');
        }
    }
}
