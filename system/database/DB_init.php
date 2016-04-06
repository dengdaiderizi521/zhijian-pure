<?php

/*
 *--------------------------------------------------------------------------
 * 至简基础模型
 *--------------------------------------------------------------------------
 */

class ZJ_Init
{
    public $_link;
    private static $_instance;

    private function __construct()
    {
        $config = _get_config('database');
        if (empty($config)) {
            die("未找到数据库配置");
        }
        @$link = mysqli_connect($config['hostname'], $config['username'], $config['password']) or die('数据库服务器连接失败');
        if ($link) {
            mysqli_query($link, 'set names  ' . $config['char_set']);
            if (mysqli_select_db($link, $config['database'])) {
                $this->_link = $link;
            } else {
                die('没有找到对应的数据库');
            }
        } else {
            $this->_link = '';
            die('数据库服务器连接失败');
        }
    }

    static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    private function __clone()
    {
    }
}
