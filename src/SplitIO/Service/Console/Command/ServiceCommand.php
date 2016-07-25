<?php
namespace SplitIO\Service\Console\Command;

use SplitIO\Service\Console\OptionsEnum;

class ServiceCommand extends Command
{
    const BIN_PHP = '/usr/bin/env php';
    const BIN_HHVM = '/usr/bin/hhvm';

    private $process = array();

    protected function configure()
    {
        $this->setName('service')
            ->setDescription('Running Split as service');
    }

    private function registerProcess($cmd, $rate)
    {
        $this->process[] = array('rate' => $rate, 'last' => time(), 'process' => new Process($cmd));
    }

    private function cmd($processCmd)
    {
        $bin = self::BIN_PHP;

        //By default is php development bin script
        $splitBin = '/bin/splitio';

        //If the app is running as phar, the binary script is the phar file
        if (extension_loaded('phar') && ($uri = \Phar::running())) {
            $splitBin = '/splitio.phar';
        }

        return $bin . ' ' .SPLITIO_SERVICE_HOME . $splitBin . ' ' . $processCmd . ' '
                    .'--config-file='.getenv('SPLITIO_SERVICE_CONFIGFILE');
    }

    public function execute()
    {
        $this->info("Running Split Synchronizer Service ...");

        exit;





        $seconds = 0.5;
        $micro = $seconds * 1000000;

        $this->registerProcess($this->cmd('process:fetch-splits'), $this->get(OptionsEnum::RATE_FETCH_SPLITS));
        $this->registerProcess($this->cmd('process:fetch-segments'), $this->get(OptionsEnum::RATE_FETCH_SEGMENTS));
        $this->registerProcess($this->cmd('process:send-impressions'), $this->get(OptionsEnum::RATE_SEND_IMPRESSIONS));
        $this->registerProcess($this->cmd('process:send-metrics'), $this->get(OptionsEnum::RATE_SEND_METRICS));

        $process = $this->process;
        $numOfProcess = count($process);
        while (true) {

            for ($i=0; $i < $numOfProcess; $i++) {

                $gap = time() - $process[$i]['last'];

                if ($gap >= $process[$i]['rate'] ) {

                    if (!$process[$i]['process']->isRunning()) {
                        try {

                            $process[$i]['process']->start();
                            if ($process[$i]['process']->isStarted()) {
                                if ($output->isVerbose()) {
                                    $this->comment("Process started successfully: ".$process[$i]['process']->getCommandLine());
                                }
                            }

                        } catch (ProcessFailedException $e) {

                            $this->logger()->critical($e->getMessage());
                            $this->error($e->getMessage());

                        } catch (\Exception $e) {

                            $this->logger()->critical($e->getMessage());
                            $this->error($e->getMessage());

                        }

                    }
                    $process[$i]['last'] = time();
                }

            }

            usleep($micro);
        }
    }

}
