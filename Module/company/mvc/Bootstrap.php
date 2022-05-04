<?php

namespace Company\MVC;

use Company\SQL\DB;
use Config;

class Bootstrap extends \Macroable
{

    static protected $instance;

    /** @var \Slim\Slim */
    public $slim;
    public $rewriteBase;
    public $config;
    protected $beginTime;
    protected $isRest = false;

    /** @return Bootstrap */
    static function getInstance()
    {
        return static::$instance;
    }

    function __construct()
    {
        $this->beginTime = microtime();
        $start = microtime(true);

        static::$instance = $this;
        $config = getConfig('Enviroments/enviroment.config.php');

        $config += getConfig("Enviroments/" . $config['enviroment'] . ".config.php");
        $this->config = &$config;
        //debug mode
        $debug = $config['debugMode'];

        if ($config["production"] == 0 && isset($_GET['debug'])) $debug = 10;
        if ($debug) {
            ini_set('display_errors', 1);
            error_reporting(E_ALL);
        } else {
            ini_set('display_errors', 0);
            error_reporting(0);
        }

        //register exception handler
        //read rewritebase
        $this->rewriteBase = apcu_fetch("REWRITE_BASE");
        if (!$this->rewriteBase) {
            $htaccess = file_get_contents(BASE_DIR . '/Docroot/.htaccess');
            preg_match('@RewriteBase\s*(.*)$@m', $htaccess, $matches);
            $this->rewriteBase = str_replace(["\n", "\r"], "", $matches[1]);
            apcu_store("REWRITE_BASE", $this->rewriteBase, 60);
        }

        if (php_sapi_name() != "cli") {
            //isRest
            $contentType = arrData(getallheaders(), 'Content-Type');
            $this->isRest = strtolower($contentType) == 'application/json' || strpos($_SERVER['REQUEST_URI'], '/rest/') !== false;
        }

        $this->registerComponentHook();

        Trigger::execute('Bootstrap/begin', $config);

        //nếu PHP chạy dạng web
        //= không chạy CLI
        if (php_sapi_name() != "cli") {
            //create slim instance
            \Slim\Slim::registerAutoloader();
            //encrypt cookie
            $this->slim = new Slim(array('cookies.encrypt' => true, 'cookies.lifetime' => 20 * 365 * 24 * 60 . ' minutes', 'cookies.path' => $this->rewriteBase, 'cookies.secure' => false, 'cookies.secret_key' => $config['cryptSecret'], 'debug' => $debug));

            //config session
            $this->slim->add(new \Slim\Middleware\SessionCookie(array('expires' => 60 . ' minutes', 'path' => $this->rewriteBase, 'domain' => null, 'secure' => false, 'name' => 'session', 'secret' => $config['cryptSecret'],)));


            //routing

            $this->appendRoute(Router::getInstance()->getRoutes());
        } else {

        }

        //database
        foreach ($config['db'] as $connName => $connInfo) {
            DB::setConfig($connName, $connInfo['type'], $connInfo['hosts'], $connInfo['user'], $connInfo['pass'], $connInfo['name'], $debug);
        }
        DB::Connect();

        Trigger::execute('Bootstrap/completed', $config);
        if (php_sapi_name() != "cli") {
            //run slim application
            $this->slim->run();
        }
    }

    function isRest()
    {
        return $this->isRest;
    }

    protected function appendRoute($routes, $prefix = '')
    {
        $bootstrap = $this;
        //sắp xếp các route dài lên đầu để ko bị route ngắn hơn override
        usort($routes, function ($a, $b) {
            $path1 = is_array($a->path) ? $a->path[0] : $a->path;
            $path2 = is_array($b->path) ? $b->path[0] : $b->path;
            return strlen($path1) < strlen($path2);
        });

        foreach ($routes as $item) {
            if (is_object($item)) {
                /* @var $item MvcContext */
                $context = $item;
                $context->app = $this;
                $context->rewriteBase = $this->rewriteBase;
                $context->config = $this->config;
                if (!is_array($item->path)) {
                    $item->path = array($item->path);
                }

                foreach ($item->path as $path) {
                    $map = $this->slim->map($prefix . $path, function () use ($bootstrap, $context) {
                        $bootstrap->executeAction($context, func_get_args());
                    });
                    //via method
                    $methods = array();
                    if ($context->method == '*') {
                        $methods = array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH');
                    } else {
                        $methods = explode(',', strtoupper($context->method));
                    }
                    call_user_func_array(array($map, 'via'), $methods);
                }
            } else {
                if (!is_array($item->path)) {
                    $item->path = array($item->path);
                }

                foreach ($item->path as $path) {
                    $this->appendRoute($item, $prefix . $path);
                }
            }
        }
    }

    function isCLI()
    {
        return (php_sapi_name() === 'cli' or defined('STDIN'));
    }

    /**
     * Quét thư mục component để khởi tạo các hook
     */
    protected function registerComponentHook()
    {
        //lấy từ danh sach file tu cache
        $installLock = [];
        if ($this->config['production']) {
            $installLock = json_decode(apcu_fetch('install.lock'), true);
        }
        if (!$installLock || empty($installLock)) {
            $installLock = [];
            foreach (scandir(BASE_DIR . '/Module') as $vendor) {
                if ($vendor[0] == '.') continue; //skip directory
                $vendor = BASE_DIR . '/Module/' . $vendor;
                if (!is_dir($vendor)) {
                    continue;
                }
                foreach (scandir($vendor) as $comp) {
                    if ($comp[0] == '.') continue; //skip directory
                    $dir = $vendor . '/' . $comp;
                    if (!file_exists($dir . '/construct.php')) {
                        continue;
                    }
                    $installLock[] = $dir . '/construct.php';
                }
            }
            apcu_store('install.lock', json_encode($installLock), 60);
        }

        foreach ($installLock as $file) {

            if ($this->isRest() && strpos($file, 'ui/') !== false) {
                continue; //not load ui construct in rest
            }
            require_once $file;
        }
    }

    function executeAction(MvcContext $context, $args)
    {
        try {
            Trigger::execute('Bootstrap/action');
            //create controller
            $controller = $this->createController($context);
            if (!$controller) {
                throw new \Exception("Controller not found<br>
                Route: {$context->controller}:{$context->action}");
            }
            //execute action
            if (!is_callable(array($controller, $context->action))) {
                throw new \Exception("Action not found<br>
                Route: {$context->controller}:{$context->action}");
            }
            call_user_func_array(array($controller, $context->action), $args);
            unset($controller);
            Trigger::execute('Bootstrap/shutdown');
        } catch (\Exception $argument) {
            require __DIR__ . "/error.php";
        }

    }

    protected function createController(MvcContext $context)
    {
        $class = $context->controller;
        if (class_exists($class)) {
            $controller = new $class($context);
            return $controller;
        }
    }

    // lấy thời gian xử lý xong dữ liệu tính bằng ms (mili giây)
    function excuteTime()
    {
        $time = microtime() - $this->beginTime;
        return $time * 1000;
    }

}
