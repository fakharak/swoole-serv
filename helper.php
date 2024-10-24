<?php

if (!function_exists('hdump')){
    function hdump($var)
    {
        return highlight_string("<?php\n\$array = " . var_export($var, true) . ";", true);
    }
}

function sw_exit($server=null, $var=null) {
    if (!is_null($var)) {
        if (is_array($var)) {
            print_r($var);
        } else {
            echo PHP_EOL.$var.PHP_EOL;
        }
    }

    if (is_null($server)) {
        // Case: For coroutine\run (when swoole is not running a Server), but not tested
        try {
            \Swoole\Coroutine::sleep(.001);
            exit(911);
        } catch (\Swoole\ExitException $e) {
            var_dump($e->getMessage());
            var_dump($e->getStatus() === 1);
            var_dump($e->getFlags() === SWOOLE_EXIT_IN_COROUTINE);
        }
    } else {
        // Tested: When Swoole is running a server
        $server->shutdown();
        sleep(3);
        exit(1);
    }
}

function makePoolKey($id, $dbEngine) {
    $swoole_pg_db_key = config('app_config.swoole_pg_db_key');
    $swoole_mysql_db_key = config('app_config.swoole_mysql_db_key');
    $dbEngine = strtolower($dbEngine);
    if ($dbEngine == 'postgres') {
        return $swoole_pg_db_key.$id;
    } else if ($dbEngine == 'mysql') {
        return $swoole_mysql_db_key.$id;
    } else {
        throw new \RuntimeException('Inside helper.php->function makePoolKey(), Argument $dbEngine should either be \'postgres\' or \'mysql\'.');
    }
}

// Config Helper Function
if (!function_exists('config')) {
    /**
     * Get a configuration value from the config files.
     *
     * @param  string  $key  The configuration key in "dot" notation (e.g., 'app.timezone').
     * @param  mixed   $default  The default value to return if the key doesn't exist.
     * @return mixed   The configuration value or the default value if the key is not found.
     */
    function config(string $key, mixed $default = null): mixed
    {
        static $config = [];

        // Define the config directory path
        $configPath = __DIR__ . '/config/';

        // Load all configuration files once
        if (empty($config)) {
            foreach (glob($configPath . '*.php') as $file) {
                $filename = basename($file, '.php');
                $config[$filename] = include $file;
            }
        }

        // Parse the key, if key exists than return the value otherwise return default
        $keys = explode('.', $key);
        $result = $config;

        foreach ($keys as $segment) {
            if (isset($result[$segment])) {
                $result = $result[$segment];
            } else {
                return $default;
            }
        }

        return $result;
    }
}

// Get all the directories and their sub-directories recurrsivly
if (!function_exists('getAllDirectories')) {
    /**
     * Get all the directories and their sub-directories recurrsivly
     *
     * @param string $dir The base directory to start scanning from.
     * @param array $skipDirs An array of directory names to skip during scanning.
     * 
     * @return mixed A list of final directories
     */
    function getAllDirectories($dir, $skipDirs = []): mixed
    {
        // Get first-level directories
        $dirs = glob($dir . '/*', GLOB_ONLYDIR);
        $allDirs = [];

        foreach ($dirs as $d) {

            // Skip directories that are in the $skipDirs array
            // Basename() example /var/www/html/muasherat/swoole-serv/app/Core/Enum  -> will return "Enum"
            if (in_array(basename($d), $skipDirs)) {
                continue;
            }

            // Add the current directory
            $allDirs[] = $d;

            // Recursively get subdirectories
            $subDirs = getAllDirectories($d, $skipDirs);

            // Merge subdirectories into the final list
            $allDirs = array_merge($allDirs, $subDirs);
        }

        return $allDirs;
    }
}