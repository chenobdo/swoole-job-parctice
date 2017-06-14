<?php

namespace Kcloze\Jobs;

class Process
{
	private $reserveProcess;
	private $workers;
	private $workNum = 5;
	private $config  = [];

	public function start($config)
	{
		// \swoole_process::daemon();
		$this->config = $config;
		// 开启多个进程消费队列
		for ($i = 0; $i < $this->workNum; $i++) {
			$this->reserveQueue($i);
		}

		$this->registSignal($this->workers);
		\swoole_process::wait();
	}

	public function reserveQueue($workNum)
	{
		$self = $this;
		$ppid = getmygid();
		file_put_contents($self->config['logPath'] . '/master.pid.log', $ppid . "\n");
        \swoole_set_process_name("job master " . $ppid . " : reserve process");

    	$reserveProcess = new \swoole_process(function () use ($self, $workNum) {
            // $self->init();

            //设置进程名字
            swoole_set_process_name("job " . $workNum . ": reserve process");
            try {
                $job = new Jobs();
                $job->run($self->config);
            } catch (Exception $e) {
                echo $e->getMessage();
            }

            echo "reserve process " . $workNum . " is working ...\n";

        });
		$pid = $reserveProcess->start();
		$this->workers[$pid] = $reserveProcess;
		echo "reserve start ...\n";
	}

	public function registSignal($workers)
	{
		\swoole_process::signal(SIGTERM, function ($signo) use (&$workers) {
			$this->exitMaster("收到退出信号,退出主进程");
		});

		\swoole_process::signal(SIGCHLD, function ($signo) use (&$workers)) {
			while (true) {
				$ret = \swoole_process::wait(false);
				if ($ret) {
					$pid           = $ret['pid'];
                    $child_process = $workers[$pid];
                    //unset($workers[$pid]);
                    echo "Worker Exit, kill_signal={$ret['signal']} PID=" . $pid . PHP_EOL;
                    $new_pid           = $child_process->start();
                    $workers[$new_pid] = $child_process;
                    unset($workers[$pid]);
				}
			}
		}
	}

	private function exitMaster()
    {
        @unlink($this->config['logPath'] . '/master.pid.log');
        $this->log("Time: " . microtime(true) . "主进程退出" . "\n");
        exit();
    }

	private function setProcessName($name)
	{
		if (function_exists('swoole_set_process_name') && PHP_OS != 'Darwin') {
			swoole_set_process_name($name);
		}
	}

	private function log($text)
	{
		file_put_contents($this->config['logPath'] . '/worker.log', $txt . "\n", FILE_APPEND);
	}
}
