<?php


namespace App\Library;
use App\Library\Utils;
use App\Model\LoggerActivityModel;
use Psr\Container\ContainerInterface;
use Respect\Validation\Validator as V;

/** @SuppressWarnings(PHPMD.StaticAccess) */
class LogActivity extends Library
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct($container);
    }
    public function addToLog($subject)
    {
      $ipAddress = $this->container->request->getAttribute('ip_address');
    	$log = [];
    	$log['description'] = $subject;
        $log['route'] = $this->container->request->getUri();
    	$log['methodType'] = $this->container->request->getMethod();
    	$log['ipAddress'] = $this->container->request->getServerParam('REMOTE_ADDR');
    	$log['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
        $log['locale'] = $_SERVER['HTTP_ACCEPT_LANGUAGE'];
        $log['referer'] = $_SERVER['HTTP_REFERER'];
        $log['userType'] = $this->container->auth->check() ? 'Registered' : 'Guest';
        $log['username'] = $this->container->auth->check() ? $this->container->auth->check()->username : NULL;
    	$log['userId'] = $this->container->auth->check() ? $this->container->auth->check()->id : NULL;
    	LoggerActivityModel::create($log);
    }
    public static function logActivityLists()
    {
    	return LoggerActivityModel::latest()->get();
    }
}
