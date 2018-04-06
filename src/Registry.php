<?php

namespace registry;

/**
 * Class Registry
 * @package beastozavr/registry
 */
class Registry
{
    public static $configs;

    public static function parseModules($appConfig)
    {
        $modules = self::getModules();
        $moduleConfig = $routesConfig = [];
        foreach ($modules as $key => $module) {
            if ($module['config']) {
                $config = [];
                include $module['config'];
                $config['class'] = $module['class'];
                $moduleConfig[$key]=$config;
            }
            if ($module['routes']) {
                $routes = [];
                include $module['routes'];
                $routesConfig = array_merge_recursive($routesConfig, $routes);
            }
        }
        $appConfig = array_merge_recursive(
            $appConfig,
            self::$configs,
            ['components' => $moduleConfig]
        );

        $appConfig = array_merge_recursive($appConfig, ['components' => ['urlManager' => ['rules' => $routesConfig]]]);
        return $appConfig;
    }

    public static function getModules()
    {
        $modules = [];
        foreach (new \DirectoryIterator(dirname(__DIR__) . '/modules') as $path) {
            if (!$path->isDot()) {
                if (file_exists($path->getPathInfo() . '/' . $path->getFilename() . '/' . ucfirst($path->getFilename()) . 'Class.php')) {
                    $modulePath = $path->getPathInfo() . '/' .  $path->getFilename();
                    $modules[$path->getFilename()] = [
                        'path' => $modulePath,
                        'class' => $modulePath . '/' . ucfirst($path->getFilename()) . 'Class.php',
                        'config' => file_exists($modulePath . '/config/config.php') ? $modulePath . '/config/config.php' : false,
                        'routes' => file_exists($modulePath . '/config/routes.php') ? $modulePath . '/config/routes.php' : false,
                        'container' => file_exists($modulePath . '/config/container.php') ? $modulePath . '/config/container.php' : false,
                    ];
                }
            }
        }
        return $modules;
    }

    public static function registerConfig($key, $config)
    {
        self::$configs[$key] = $config;
    }
}