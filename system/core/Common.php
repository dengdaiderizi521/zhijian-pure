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
    header('system error', 1, 500);
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
    $logPath = _get_config('config', 'log_path');
    if (!empty($logPath)) {
        if (!is_dir($logPath)) {
            @mkdir($logPath, 0777);
        }
        $logPath .= '/error-' . date('Y-m-d') . '.log';
        //此处定义日志格式
        $errormessage = date('Y-m-d H:i:s') . ' ' . $levels[$errno] . ':' . strip_tags($errstr) . ' in ' . $filepath . ' on line ' . $line . ' uri:' . $_SERVER['REQUEST_URI'] . "\n";
        file_put_contents($logPath, $errormessage, FILE_APPEND);
    }

    if (file_exists(APPPATH . 'error/error.php')) {
        $debug = _get_config('config', 'debug');
        require_once APPPATH . 'error/error.php';
    }
}

/*
|---------------------------------------------------------------
| 异常处理函数
|---------------------------------------------------------------
*/

function _exception($ex)
{
    restore_exception_handler();
    $errcode = $ex->getMessage();
    $errmsg = str_ireplace("\n", '<br>', $ex->__toString());
    $logPath = _get_config('config', 'log_path');
    if (!empty($logPath)) {
        if (!is_dir($logPath)) {
            @mkdir($logPath, 0777);
        }
        $logPath .= '/exception-' . date('Y-m-d') . '.log';
        file_put_contents($logPath, $errmsg, FILE_APPEND);
    }

    if (file_exists(APPPATH . 'error/exception.php')) {
        $debug = _get_config('config', 'debug');
        require_once APPPATH . 'error/exception.php';
    } elseif (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest") {
        echo json_encode(['code' => 0, 'msg' => $errcode]);
    }
}

/*
|---------------------------------------------------------------
| 每个脚本执行结束时调用
| 此方法无论在任何情况下都会得到执行,即便使用了die或exit亦或程序异常/错误
|---------------------------------------------------------------
*/

function _shutdown()
{
    if (file_exists(APPPATH . 'hook/ZJHook.php')) {
        require_once APPPATH . 'hook/ZJHook.php';
        new ZJHook();
    }
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
    if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && $function[0] !== '_') {
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

    $class = ucfirst(strtolower($uriArr['controller']));
    $function = strtolower($uriArr['function']);

    if (!file_exists(APPPATH . 'controller/' . $class . '.php')) {
        _page_error_404();
    }

    if (strtolower($_SERVER['REQUEST_METHOD']) === 'post' && $function[0] !== '_') {
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
        $default_controller = _get_config('config', 'default_controller');
        if (!$default_controller) {
            _page_error_404();
        }
        $uriArr['controller'] = ucfirst(strtolower($default_controller));
        $uriArr['function'] = 'index';
        return false;
    }
    $uriStr = htmlspecialchars($_GET['uri']);
    $urlSuffix = _get_config('config', 'url_suffix');
    if (!empty($urlSuffix)) {
        $uriStr = preg_replace('/^(.*)' . preg_quote($urlSuffix) . '$/', '$1', $uriStr);
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

/*
|---------------------------------------------------------------
| 获取路由信息
|---------------------------------------------------------------
*/

function _get_route()
{
    $data['uri'] = !empty($_GET['uri']) ? $_GET['uri'] : '/';
    $config = _get_config('config');
    if (!$config['rewrite']) {
        $data['class'] = !empty($_GET['c']) ? $_GET['c'] : $config['default_controller'];
        $data['method'] = !empty($_GET['m']) ? $_GET['m'] : 'index';
    } else {
        if (empty($data['uri']) || $data['uri'] == '/') {
            $data['class'] = !empty($_GET['c']) ? $_GET['c'] : $config['default_controller'];
            $data['method'] = !empty($_GET['m']) ? $_GET['m'] : 'index';
        } else {
            $uri = explode('/', trim($data['uri'], '/'));
            $data['class'] = $uri[0];
            $data['method'] = !empty($uri[1]) ? $uri[1] : 'index';
        }
    }
    return $data;
}