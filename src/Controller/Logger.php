<?php
namespace VVC\Controller;

use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\ErrorHandler;
/**
 * Logs various information, used for debugging
 */
class Logger
{
    private static $logger;

    public static function init()
    {
        // default "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n"
        $output = "[%datetime%] %channel% %level_name% > %message%\n\n";
        $formatter = new LineFormatter($output, null, true);

        $stream = new StreamHandler('../debug/main.log');

        $stream->setFormatter($formatter);

        self::$logger['main'] = new \Monolog\Logger('Main');
        self::$logger['main']->pushHandler($stream);

        self::$logger['db'] = self::$logger['main']->withName('Database');
        self::$logger['auth'] = self::$logger['main']->withName('Auth');
        self::$logger['upload'] = self::$logger['main']->withName('Uploader');

        self::$logger['exception']
            = self::$logger['main']->withName('Unhandled');
        ErrorHandler::register(self::$logger['exception']);
    }

    public static function log(
        string     $logType,
        string     $errType,
        string     $desc,
        \Exception $e = null,
        array      $extra = []
    ) {
        $out = $desc;

        if ($e) {
            $out .= "\nmsg:  " . $e->getMessage();
            $loc = strstr($e->getFile(), "src/");
            if (!$loc) {
                $loc = strstr($e->getFile(), "src\\");
            }
            $out .= "\nloc:  " . $loc . " [line " .  $e->getLine() . "]";
        }

        foreach ($extra as $key => $val) {
            $out .= "\n$key:  $val";
        }

        self::$logger[$logType]->$errType($out);
    }
}
