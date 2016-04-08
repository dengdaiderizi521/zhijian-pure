<?php

/*
 *--------------------------------------------------------------------------
 * 至简基础控制器
 *--------------------------------------------------------------------------
 */

class ZJ_Controller
{

    private $viewVariable = array(); //定义类属性，用来保存视图变量
    protected $uri = '';
    protected $class = '';
    protected $method = '';

    public function __construct()
    {
        $route = _get_route();
        $this->uri = $route['uri'];
        $this->class = $route['class'];
        $this->method = $route['method'];
    }

    /**
     * 获取请求参数
     * @param $name int|string|array 参数名
     * @param mixed $default 默认值,如果没有获取到参数就返回默认值
     * @return array|bool|int|null|string
     */
    protected function get($name, $default = null)
    {
        if (empty($name)) {
            return $default;
        }
        if (is_string($name) || is_int($name)) {
            if (!empty($_GET[$name])) {
                return $_GET[$name];
            } elseif (!empty($_POST[$name])) {
                return $_POST[$name];
            } else {
                return $default;
            }
        } elseif (is_array($name)) {
            $data = [];
            foreach ($name as $k => $v) {
                if (!empty($_GET[$v])) {
                    $data[$k] = $_GET[$v];
                } elseif (!empty($_POST[$v])) {
                    $data[$k] = $_POST[$v];
                } elseif (is_string($default) || is_int($default) || is_bool($default) || is_null($default)) {
                    $data[$k] = $default;
                } elseif (is_array($default) && !empty($default[$v])) {
                    $data[$k] = $default[$v];
                } else {
                    $data[$k] = null;
                }
            }
            return !empty($data) ? $data : $default;
        } else {
            return $default;
        }
    }

    /**
     * 设置需要抛到模版上的变量
     * @param $name 视图接收时的变量名
     * @param $value 视图变量的值
     */
    protected function set($name, $value)
    {
        $this->viewVariable[$name] = $value;
    }

    /**
     * 加载视图
     * @param $fileName 视图文件名不包含文件扩展名，可包含路径
     */
    protected function view($fileName)
    {
        foreach ($this->viewVariable as $k => $v) {
            $$k = $v;
        }
        //判断控制器文件是否存在
        if (!file_exists(VIEWPATH . $fileName . VIEWEXT)) {
            die("视图文件不存在：" . VIEWPATH . $fileName . VIEWEXT);
        }
        require_once VIEWPATH . $fileName . VIEWEXT;
    }

    /**
     * 加载模型
     * @param $modelName 模型文件名不包含文件扩展名，可包含路径
     * @return object 返回实例化对象
     */
    protected function getModel($modelName)
    {
        $model = $modelName;
        if (isset($this->$model)) {
            return $this->$model;
        }
        $modelName = ucfirst(strtolower($modelName)) . 'Model';
        //判断控制器文件是否存在
        if (!file_exists(MODELPATH . $modelName . '.php')) {
            die("模型文件不存在：" . MODELPATH . $modelName . '.php');
        }
        require_once SYSTEMPATH . 'database/DB.php';
        require_once MODELPATH . $modelName . '.php';
        return $this->$model = new $modelName();
    }

    /**
     * 加载配置文件
     * @param $configFileName 配置文件名不包含文件扩展名，可包含路径
     * @param $key 配置数组的key 可选参数
     * @return array OR value  返回配置数组或者对应key的value
     */
    protected function loadConfig($configFileName, $key = false)
    {
        return _get_config($configFileName, $key);
    }

    /**
     * 显示404页面
     */
    protected function show_404()
    {
        _page_error_404();
    }
}
