<?php

/*
|---------------------------------------------------------------
| 定义配置文件目录
|---------------------------------------------------------------
 */

define('CONFIGPATH', APPPATH . 'config/');

/*
|---------------------------------------------------------------
| 获取用户配置
|---------------------------------------------------------------
 */
function _get_config($filename = 'config', $key = false)
{
    if (!file_exists(CONFIGPATH . $filename . '.php')) {
        die("配置文件未找到");
    }
    require CONFIGPATH . $filename . '.php';
    if (!$key) {
        return $config;
    }
    return isset($config[$key]) ? $config[$key] : false;
}

/*
|---------------------------------------------------------------
| 系统错误处理，开启日志记录下生效
|---------------------------------------------------------------
 */

function _error_log($errno, $errstr, $filepath, $line)
{
    $levels = array(
        E_ERROR => 'Error',
        E_WARNING => 'Warning',
        E_PARSE => 'Parsing Error',
        E_NOTICE => 'Notice',
        E_CORE_ERROR => 'Core Error',
        E_CORE_WARNING => 'Core Warning',
        E_COMPILE_ERROR => 'Compile Error',
        E_COMPILE_WARNING => 'Compile Warning',
        E_USER_ERROR => 'User Error',
        E_USER_WARNING => 'User Warning',
        E_USER_NOTICE => 'User Notice',
        E_STRICT => 'Runtime Notice'
    );
    if ($errno == E_STRICT) {
        die;
    }
    $log_path = _get_config('config', 'log_path');
    if (empty($log_path)) {
        die;
    }
    if (!is_dir($log_path)) {
        @mkdir($log_path, 0777);
    }
    $log_path .= '/error-' . date('Y-m-d') . '.log';
    $errormessage = '';
    if (file_exists($log_path)) {
        $errormessage = file_get_contents($log_path);
    }
    $errormessage .= date('Y-m-d H:i:s') . ' ' . $levels[$errno] . ':' . strip_tags($errstr) . ' in ' . $filepath . ' on line ' . $line . ' uri:' . $_SERVER['REQUEST_URI'] . "\n";
    file_put_contents($log_path, $errormessage);
    die;
}

/*
|---------------------------------------------------------------
| 非伪静态下执行请求
|---------------------------------------------------------------
*/

function _no_rewrite_run($controller, $function)
{
    if (!isset($_GET[$controller])) {
        $default_controller = _get_config('config', 'default_controller');
        if (!$default_controller) {
            _page_error_404();
        }
        $class = ucfirst(strtolower($default_controller));
    } else {
        $class = ucfirst(strtolower($_GET[$controller]));
    }
    if (!isset($_GET[$function])) {
        $function = 'index';
    } else {
        $function = strtolower($_GET[$function]);
    }
    if (!file_exists(APPPATH . 'controller/' . $class . '.php')) {
        _page_error_404();
    }
    require_once SYSTEMPATH . 'core/Controller.php';
    require_once CONTROLLERPATH . $class . '.php';
    if (!class_exists($class)) {
        _page_error_404();
    }
    $obj = new $class();
    if (!method_exists($obj, $function)) {
        _page_error_404();
    }
    $obj->$function();
}

/*
|---------------------------------------------------------------
| 伪静态下执行请求
|---------------------------------------------------------------
*/
function _rewrite_run()
{
    $uriArr = _fetch_uri_string();
    if (!isset($uriArr['controller'])) {
        $default_controller = _get_config('config', 'default_controller');
        if (!$default_controller) {
            _page_error_404();
        }
        $class = ucfirst(strtolower($default_controller));
    } else {
        $class = ucfirst(strtolower($uriArr['controller']));
    }
    if (!isset($uriArr['function'])) {
        $function = 'index';
    } else {
        $function = strtolower($uriArr['function']);
    }
    if (!file_exists(APPPATH . 'controller/' . $class . '.php')) {
        _page_error_404();
    }
    require_once SYSTEMPATH . 'core/Controller.php';
    require_once CONTROLLERPATH . $class . '.php';
    if (!class_exists($class)) {
        _page_error_404();
    }
    $obj = new $class();
    if (!method_exists($obj, $function)) {
        _page_error_404();
    }
    if (!empty($uriArr['params'])) {
        call_user_func_array(array($obj, $function), $uriArr['params']);
    } else {
        $obj->$function();
    }
}

/*
|---------------------------------------------------------------
| 获取分割后的URI
|---------------------------------------------------------------
*/
function _fetch_uri_string()
{
    if (empty($_GET['uri'])) {
        return false;
    }
    $uriStr = htmlspecialchars($_GET['uri']);
    $url_suffix = _get_config('config', 'url_suffix');
    if (!empty($url_suffix)) {
        $uriStr = preg_replace('/^(.*)' . preg_quote($url_suffix) . '$/', '$1', $uriStr);
    }
    $tmpArr = explode('/', $uriStr);
    $uriArr['controller'] = $tmpArr[0];
    unset($tmpArr[0]);
    if (!isset($tmpArr[1])) {
        $uriArr['function'] = 'index';
        return $uriArr;
    }
    $uriArr['params'] = array();
    if ((int)$tmpArr[1] != 0) {
        $uriArr['function'] = 'index';
        $uriArr['params'][0] = $tmpArr[1];
        unset($tmpArr[1]);
    } else {
        $uriArr['function'] = $tmpArr[1];
        unset($tmpArr[1]);
    }
    if (empty($tmpArr)) {
        return $uriArr;
    }
    foreach ($tmpArr as $k => $v) {
        $uriArr['params'][count($uriArr['params'])] = $v;
    }
    return $uriArr;
}

/*
|---------------------------------------------------------------
| 404错误处理
|---------------------------------------------------------------
*/
function _page_error_404()
{
    header('HTTP/1.1 404 Not Found');
    if (file_exists(APPPATH . 'error/404.php')) {
        require_once APPPATH . 'error/404.php';
    }
    die;
}