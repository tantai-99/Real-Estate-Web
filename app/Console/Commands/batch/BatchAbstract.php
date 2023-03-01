<?php
namespace App\Console\Commands\batch;
define('APPLICATION_PATH', storage_path('data/publish/view'));
class BatchAbstract
{
    protected $_info;
    protected $_error;
    protected $_path_data;

    static public function validParamater($argv, $log) {
        $envs = array('production', 'staging', 'testing', 'development', 'local');
        if (!isset($argv['env']) || !in_array($argv['env'], $envs)) {
            echo 'first param is not evn param ' . ' [ ' . implode(' | ', $envs) . ' ]' . PHP_EOL;
            exit();
        }

        if (!isset($argv['app'])) {
            echo "app param is empty" . PHP_EOL;
            exit();
        }

        if (!isset($argv['controller'])) {
            echo "controller param is empty" . PHP_EOL;
            exit();
        }

        $batch = $argv['controller'];
        $file = app_path('Console/Commands/batch/controllers/') . $batch . '.php';
        $class = "\App\Console\Commands\batch\controllers\\" . $batch;

        if (!file_exists($file)) {
            echo "controller file is not exists" . PHP_EOL;
            exit();
        }

        require_once($file);
        if (!class_exists($class, false)) {
            echo "controller class is not exists" . PHP_EOL;
            exit();
        }

        set_time_limit(0);
        // エラー関連の上書き
        error_reporting(E_COMPILE_ERROR | E_RECOVERABLE_ERROR | E_ERROR | E_CORE_ERROR); // <- エラーレベル設定
        ini_set('display_errors', "On");    // <- エラーを標準出力にも表示
        
        // バッチ用のログ初期化
        $writerConfig = array('error', 'info');
        foreach ($writerConfig as $type) {
            $logName = 'Batch_' . $batch . '_' . $type;
            $filename = storage_path('logs/') . $logName . '.log';
            $logtype = '_' . $type;
            $log->$logtype = \Log::channel($logName);
            // パーミッション変更
            if (!@file_exists($filename)) {
                file_put_contents($filename, '', FILE_APPEND);
            }
            if (@file_exists($filename) || false !== @file_put_contents($filename, '', FILE_APPEND)) {
                @chmod($filename, 0777);
            }
        }

        $log->_path_data = config('environment.path_data_batch');

        $client = \Aws\S3\S3Client::factory(array(
            'key'    => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => 'latest'
          ));
        $client->registerStreamWrapper();
    }
}