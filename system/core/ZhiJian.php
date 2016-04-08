<?php
/*
|---------------------------------------------------------------
| 载入框架公共文件
|---------------------------------------------------------------
 */
require_once SYSTEMPATH . 'core/Common.php';
/*
|---------------------------------------------------------------
| 获取用户配置信息
|---------------------------------------------------------------
 */
$config = _get_config();

/*
|---------------------------------------------------------------
| 定义项目文件目录
|---------------------------------------------------------------
 */
define('CONTROLLERPATH', APPPATH . 'controller/');
define('VIEWPATH', APPPATH . 'view/');
define('MODELPATH', APPPATH . 'model/');
if (empty($config['view_file_suffix'])) {
    define('VIEWEXT', '.php');
} else {
    define('VIEWEXT', $config['view_file_suffix']);
}

/*
|---------------------------------------------------------------
| debug处理
|---------------------------------------------------------------
*/
set_exception_handler('_exception');
register_shutdown_function('_shutdown');

set_error_handler('_error_log');
if (!$config['debug']) {
    error_reporting(0);
}

/*
|---------------------------------------------------------------
| 执行请求
|---------------------------------------------------------------
*/
if (!$config['rewrite']) {
    _no_rewrite_run($config['controller_trigger'], $config['function_trigger']);
} else {
    if (function_exists('apache_get_modules')) {
        $result = apache_get_modules();
        if (!in_array('mod_rewrite', $result)) {
            _no_rewrite_run($config['controller_trigger'], $config['function_trigger']);
        } else {
            _rewrite_run();
        }
    } elseif (!empty($_GET['uri'])) {
        _rewrite_run();
    } else {
        _no_rewrite_run($config['controller_trigger'], $config['function_trigger']);
    }
}
