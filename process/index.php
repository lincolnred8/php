<?php
// 为了阅读方便, 将类写在同一个文件中

class Push
{
    private $_redis;
    private $_multi_key     = 'multi_process';
    private $_single_key    = 'single_process';
    private $_url = 'http://weibo.com/?page=%u';
    public function __construct()
    {
        $this->_redis = RedisClient::getInstance();
    }
    
    public function lpush($multi = true)
    {
        $key = $multi ? $this->_multi_key : $this->_single_key;
        
        $num = $multi ? 50000 : 500000;
        for($i = 0; $i < $num; $i++)
        {
           $data = sprintf($this->_url, $i);
           $this->_redis->lpush($key, $data);
        }
        return true;
    }
}


$load_files = dirname(dirname(__FILE__)) . '/autoload/loader.class.php';
if (file_exists($load_files))
{
    include_once($load_files);
}
else
{
    throw new Exception('自动加载文件不存在');
}

spl_autoload_register(array('Loader', 'loadClass'));

$act = isset($argv[1]) ? $argv[1] : false;

if($act == 'multi')
{
    // 多进程执行
    $multi_start_time = microtime('sec');

    $multi_conf = array(
        'class'     => 'Push',
        'method'    => 'lpush',
        'params'    => array('multi' => true),
        'counts'    => 10
    );
    $daemon = Multidaemon::getInstance($multi_conf);
    $daemon->execute();

    $multi_end_time = microtime('sec');
    echo $multi_end_time - $multi_start_time . PHP_EOL;
}
else
{
    // 单个进程执行
    $single_start_time = microtime('sec');

    $push = new Push();
    $push->lpush(false);

    $single_end_time = microtime('sec');
    echo $single_end_time - $single_start_time . PHP_EOL;
}
