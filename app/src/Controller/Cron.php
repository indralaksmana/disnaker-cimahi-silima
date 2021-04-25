<?php

namespace App\Controller;

use Carbon\Carbon;
use App\Model\AK1Model;
use App\Library\Email as E;
use Jobby\Jobby;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * @SuppressWarnings(PHPMD.StaticAccess)
 */
class Cron extends Controller
{
    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function run(Request $request, Response $response)
    {
        // if (!$request->getParam('token') ||
        //     $request->getparam('token') !=
        //     $this->settings['cron']['token'] ||
        //     $this->settings['cron']['token'] == "") {
        //     throw new \Slim\Exception\NotFoundException($request, $response);
        // }

        $jobby = new \Jobby\Jobby();

        // Sample Job
        $jobby->add('SampleCron', [
            /* 'command' => 'ls', //Run a shell command */
            // Run a PHP Closure

            'closure'  => function () {

                $text = "I'm a function!\n";
                echo $text;
                return true;
            },
            // Define Schedule
            'schedule' => '* * * * *',
            // This will add a log for this cron job
            'output'   => __DIR__ . '/../../../storage/log/cron/sample.log',
            // Enable/Dsable Cron Job
            'enabled' => true
        ]);

        $jobby->run();

        if ($jobby->getJobs()) {
            $now = Carbon::now()->format('Y-m-d');
            $ak1 = AK1Model::get();
            foreach($ak1 as $key => $value){
                    $sendEmail = new E($this->container);
                    $sendEmail = $sendEmail->sendTemplate(
                    array(
                      118
                    ),
                    'lowongan-kerja-baru',
                    array(
                    )
                  );
            }

            return "1";
        }

        throw new \Exception("Cron Failed", 500);
    }
}
