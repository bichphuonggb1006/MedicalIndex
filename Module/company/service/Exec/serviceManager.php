<?php

$ROOT_PATH = dirname(__DIR__, 4);
require_once $ROOT_PATH . '/Docroot/index.php';

use Company\Cache\CacheDriver;
use Company\Service\Lib\Process;
use Company\Service\Model\ServiceMapper;

const SERVICE_STOPPED = 0;
const SERVICE_CRASHED = -1;

$sleepTime = 5;

$cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
$ip = getHostName();

$services = ServiceMapper::makeInstance()->getAll()->toArray();
$services = array_combine(array_column($services, "id"), array_values($services));

function get_numerics($str) {
    preg_match_all('/\d+/', $str, $matches);
    return $matches[0];
}

function getPID($command) {
    $output = shell_exec("ps aux | grep '$command' | grep -v grep | awk {'print $2'}");
    $output = get_numerics($output);

    if (count($output) > 0)
        return $output[0];

    return null;
}

function runCommand($command, $callback=null) {
    $p = new Process($command);
    if (is_callable($callback))
        call_user_func($callback, $p);
    return [$p->getPid(), $p->status()];
}

$expiredSecond = 30;

while (true) {

    // remove trash ips
    $allContainers = $cacheInstance->hGetAll(ServiceMapper::CONTAINER_SERVICE_HASH_KEY);
    foreach ($allContainers as $key => $value) {
        $expiredTime = json_decode($value, true)["expiredTime"];
        if ($expiredTime <= time()) {
            $cacheInstance->hDel(ServiceMapper::CONTAINER_SERVICE_HASH_KEY, $key);
            unset($allContainers[$key]);
        }
    }

    $thisContainer = null;
    if (array_key_exists($ip, $allContainers)) {
        $thisContainer = json_decode($allContainers[$ip], true)["data"];
        $thisContainer = array_combine(array_column($thisContainer, "serviceID"), array_values($thisContainer));
    }

    // check request start/stop

    foreach ($cacheInstance->hGetAll(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY) as $key => $value) {
        list($containerIP, $serviceID) = explode("|", $key);
        if ($ip == $containerIP) {
            $command = arrData($services, [$serviceID, "command"]);
            if ($command)
                if ($value == "start") {
                    // run command if process not exists
                    if (is_null(getPID($command))) {
                        $p = new Process($command);
                        echo "$serviceID running with pid " . $p->getPid() . "\n";
                        if (!$p->status()) {
                            echo "Failed!\n";
                            $thisContainer[$serviceID]["pid"] = SERVICE_CRASHED;
                        }
                        $cacheInstance->hDel(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $key);
                    }

                } else if ($value == "stop") {

                    // stop process if exists
                    if ($pid = getPID($command)) {
                        $p = new Process();
                        $p->setPid($pid);
                        $p->stop();
                        echo "$serviceID stopping with pid " . $pid . "\n";
                        $thisContainer[$serviceID]["pid"] = SERVICE_STOPPED;
                        $cacheInstance->hDel(ServiceMapper::SERVICE_CONTROLLER_HASH_KEY, $key);
                    }
                }
        }
    }

    // Update service status, restart if service crashed
    $res = [];

    foreach ($services as $service) {
        $serviceID = $service["id"];
        $serviceName = $service["name"];
        $command = $service["command"];

        $pid = getPID($command);

        if (is_null($pid))
            if (!is_null($thisContainer)) {
                $currentPID = $thisContainer[$serviceID]["pid"];

                // if crashed, retry service
                if ($currentPID == SERVICE_CRASHED or $currentPID > 0) {
                    list($resPID, $status) = runCommand($command, function($p) use ($serviceID) {echo "$serviceID retrying with pid " . $p->getPid() . "\n";});
                    if (!$status) {
                        $pid = SERVICE_CRASHED;
                        echo "Retry failed!\n";
                    } else {
                        echo "Done!\n";
                        $pid = $resPID;
                    }
                } else
                    $pid = SERVICE_STOPPED;


            } else /* init service */ {
                $attrs = json_decode($service["attrs"], true);
                $autoStart = $attrs["autoStart"];
                if ($autoStart === 1) {
                    list($resPID, $status) = runCommand($command, function($p) use ($serviceID) {echo "$serviceID init with pid " . $p->getPid() . "\n";});
                    if (!$status) {
                        $pid = SERVICE_CRASHED;
                        echo  "Init failed!\n";
                    } else {
                        echo "Done!\n";
                        $pid = $resPID;
                    }
                } else
                    $pid = SERVICE_STOPPED;
            }

        $res[] =  [
            "serviceID" => $serviceID,
            "serviceName" => $serviceName,
            "pid" => $pid
        ];

    }

    $now = time();
    echo "Running at $now\n";
    try {
        $cacheInstance->hSet(ServiceMapper::CONTAINER_SERVICE_HASH_KEY, $ip, json_encode([
            "expiredTime" => $now + $expiredSecond,
            "data" => $res
        ]));

    } catch (RedisException $exception) {
        echo "Lost connection\n";
        while (true) {
            try {
                echo "Reconnecting ...\n";
                $cacheInstance = CacheDriver::getInstance(CacheDriver::SHARE_CACHE);
                $cacheInstance->ping();
                echo "Connected!\n";
                break;
            } catch (RedisException $exception) {
                sleep(5);
            }
        }
    }

    sleep($sleepTime);
}
