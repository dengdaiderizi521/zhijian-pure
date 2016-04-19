<?php

/*
 *--------------------------------------------------------------------------
 * 至简基础模型
 * 更新时间: 2016-04-06
 *--------------------------------------------------------------------------
 */

class ZJ_Model
{

    private $_link; //定义类属性
    private $_where = '';
    private $_table = '';
    private $_select = '*';
    private $_limit = '';
    private $_order_by = '';
    private $_data = array();
    private $_last_sql = '';
    private $_in = array();
    const SELECT_TYPE = 'SELECT ';
    const UPDATE_TYPE = 'UPDATE ';
    const INSERT_TYPE = 'INSERT INTO ';
    const DELETE_TYPE = 'DELETE FROM ';
    const COUNT_TYPE = 'SELECT COUNT(*) as count ';

    //构造方法，初始化数据库连接，获取数据库连接资源
    public function __construct()
    {
        require_once SYSTEMPATH . 'database/DB_init.php';
        $this->_link = ZJ_Init::getInstance();
        $this->_link = $this->_link->_link;
    }

    /**
     * 设置表名
     * @param $key 可以是字符串或者数组，如果传递$value则表示where条件是$key=$value
     */
    public function table($table)
    {
        $this->_table = $table;
        return $this;
    }

    /**
     * 设置WHERE条件
     * @param $key 可以是字符串或者数组，如果传递$value则表示where条件是$key=$value
     */
    public function where($key, $value = false)
    {
        if (!is_array($key)) {
            $key = array($key => $value);
        }
        foreach ($key as $k => $v) {
            $this->_where[$k] = $v;
        }
        return $this;
    }

    /**
     * 设置IN条件
     * @param $field string 字段名
     * @param $data array 数据
     */
    public function in($field, $data)
    {
        if (is_string($field) && is_array($data)) {
            $this->_in[$field] = $data;
        }
        return $this;
    }

    /**
     * 设置select字段
     * @param $field 可以是字符串或者数组
     */
    public function select($field)
    {
        $this->_select = array();
        if (is_string($field)) {
            $this->_select[] = $field;
        }
        if (is_array($field)) {
            foreach ($field as $k => $v) {
                $this->_select[] = $v;
            }
        }
        return $this;
    }

    /**
     * 设置insert或者update的值
     * @param $data 需要设置的数组 key是field;value是值
     * @param $escape 是否转义数组，默认TRUE
     */
    public function set($data, $escape = true)
    {
        if (empty($data)) {
            return $this;
        }
        if ($escape) {
            foreach ($data as $k => $v) {
                $this->_data['`' . $k . '`'] = "UNHEX('" . bin2hex($v) . "')";
            }
        } else {
            foreach ($data as $k => $v) {
                $this->_data['`' . $k . '`'] = "'" . $v . "'";
            }
        }
        return $this;
    }

    /**
     * 设置LIMIT
     * @param $start 开始位置
     * @param $limit 限制条目数
     */
    public function limit($start, $limit)
    {
        if (empty($start) && empty($limit)) {
            return $this;
        }
        if (!empty($start)) {
            $start = (int)$start . ',';
        } else {
            $start = '';
        }
        $this->_limit = ' LIMIT ' . $start . (int)$limit;
        return $this;
    }

    /**
     * 设置ORDER BY
     * @param $field 可以是完整的order by字符串或者字段名
     * @param $sort 排序
     */
    public function order_by($field, $sort = null)
    {
        if (is_null($sort)) {
            $this->_order_by = $field;
        }
        if (is_bool($sort)) {
            $this->_order_by = ' ORDER BY ' . $field . ' ' . ($sort ? 'ASC ' : 'DESC ');
        }
        return $this;
    }

    /**
     * 返回最后执行的一条sql语句
     */
    public function last_sql()
    {
        return $this->_last_sql;
    }

    /**
     * 获取执行结果
     */
    public function get($table = false, $limit = null, $offset = null)
    {
        if (!empty($table)) {
            $this->_table = $table;
        }
        if (empty($this->_table)) {
            die('以外错误：表名为空');
        }
        $this->limit($offset, $limit);
        $sql = $this->_getSql(self::SELECT_TYPE);
        $result = $this->_result($sql);
        $this->_initParam();
        return !empty($result) ? $result : array();
    }

    /**
     * 获取执行结果
     */
    public function get_row($table = false)
    {
        $result = $this->get($table, 1);
        $this->_initParam();
        return !empty($result) ? $result[0] : array();
    }

    /**
     * 获取执行结果的条目数
     */
    public function count($table = false)
    {
        if (!empty($table)) {
            $this->_table = $table;
        }
        if (empty($this->_table)) {
            die('以外错误：表名为空');
        }
        $sql = $this->_getSql(self::COUNT_TYPE);
        $result = $this->_result($sql);
        $this->_initParam();
        return !empty($result) ? (int)$result[0]['count'] : 0;
    }

    /**
     * 插入一条数据
     */
    public function insert($table = false, $data = array())
    {
        if (!empty($table)) {
            $this->_table = $table;
        }
        if (empty($this->_table)) {
            die('以外错误：表名为空');
        }
        if (!empty($data)) {
            $this->set($data);
        }
        if (empty($this->_data)) {
            die('以外错误：插入数据为空');
        }
        $sql = $this->_getSql(self::INSERT_TYPE);
        $result = $this->query($sql);
        $this->_initParam();
        if ($result) {
            return mysqli_insert_id($this->_link);
        }
        return false;
    }

    /**
     * 修改数据
     */
    public function update($table = false, $data = array(), $where = null)
    {
        if (!empty($table)) {
            $this->_table = $table;
        }
        if (empty($this->_table)) {
            die('以外错误：表名为空');
        }
        if (!empty($data)) {
            $this->set($data);
        }
        if (empty($this->_data)) {
            die('以外错误：修改数据为空');
        }
        if (!is_null($where)) {
            $this->where($where);
        }
        $sql = $this->_getSql(self::UPDATE_TYPE);
        $result = $this->query($sql);
        $this->_initParam();
        return $result;
    }

    /**
     * 删除数据
     */
    public function delete($table = false, $where = null)
    {
        if (!empty($table)) {
            $this->_table = $table;
        }
        if (empty($this->_table)) {
            die('以外错误：表名为空');
        }
        if (!is_null($where)) {
            $this->where($where);
        }
        $sql = $this->_getSql(self::DELETE_TYPE);
        $result = $this->query($sql);
        $this->_initParam();
        return $result;
    }

    /**
     * 获取整理后的SQL语句
     */
    private function _getSql($type)
    {
        switch ($type) {
            case self::SELECT_TYPE:
                return self::SELECT_TYPE . $this->_select() . ' FROM `' . $this->_table . '`' . $this->_where() . $this->_order_by() . $this->_limit();
                break;
            case self::COUNT_TYPE:
                return self::COUNT_TYPE . ' FROM `' . $this->_table . '`' . $this->_where();
                break;
            case self::UPDATE_TYPE:
                return self::UPDATE_TYPE . '`' . $this->_table . '`' . $this->_updateBody() . $this->_where();
                break;
            case self::INSERT_TYPE:
                return self::INSERT_TYPE . '`' . $this->_table . '`' . $this->_insertBody();
                break;
            case self::DELETE_TYPE:
                return self::DELETE_TYPE . '`' . $this->_table . '`' . $this->_where();
                break;
            default:
                return false;
                break;
        }
    }

    /**
     * 获取WHERE条件
     */
    private function _where()
    {
        if (!empty($this->_where)) {
            foreach ($this->_where as $k => $v) {
                if (is_bool($v)) {
                    $where[] = $k;
                } elseif (strpos($k, '>') !== false || strpos($k, '<') !== false || strpos($k, '=') !== false) {
                    $where[] = "$k UNHEX('" . bin2hex($v) . "')";
                } else {
                    $where[] = "`$k` = UNHEX('" . bin2hex($v) . "')";
                }
            }
        }
        $this->_where = '';
        if (! empty($this->_in)) {
            foreach ($this->_in as $k => $v) {
                $tmp = [];
                foreach ($v as $val) {
                    if (is_int($val) || is_string($val)) {
                        $tmp[] = "UNHEX('" . bin2hex($val) . "')";
                    }
                }
                if (! empty($tmp)) {
                    $where[] = "`$k` IN (" . implode(',', $tmp) . ')';
                }
            }
        }
        if (!empty($where)) {
            return ' WHERE ' . implode(' AND ', $where);
        }
    }

    /**
     * 获取SELECT的field
     */
    private function _select()
    {

        if (!empty($this->_select)) {
            if (is_array($this->_select)) {
                $select = implode(',', $this->_select);
            } else {
                $select = $this->_select;
            }
            $this->_select = '*';
            return $select;
        }
    }

    /**
     * 执行SQL语句获取结果集，返回数组
     */
    private function _result($sql)
    {
        $result = $this->query($sql);
        if (!empty($return) || $result === false) {
            return false;
        }
        while ($row = mysqli_fetch_assoc($result)) {        //把查询结果重组成一个二维数组
            $arr[] = $row;
        }
        mysqli_free_result($result);
        return isset($arr) ? $arr : array();
    }

    /**
     * 返回ORDER BY语句 如果没有则返回空
     */
    private function _order_by()
    {
        if (empty($this->_order_by)) {
            return;
        }
        $order_by = $this->_order_by;
        $this->_order_by = '';
        return $order_by;
    }

    /**
     * 返回LIMIT语句 如果没有则返回空
     */
    private function _limit()
    {
        if (empty($this->_limit)) {
            return;
        }
        $limit = $this->_limit;
        $this->_limit = '';
        return $limit;
    }

    /**
     * 返回insert的BODY
     */
    private function _insertBody()
    {
        if (empty($this->_data)) {
            return;
        }
        $insertBody = '(' . implode(',', array_keys($this->_data)) . ')';
        $insertBody .= ' VALUES(' . implode(',', $this->_data) . ')';
        $this->_data = array();
        return $insertBody;
    }

    /**
     * 返回uodate的BODY
     */
    private function _updateBody()
    {
        if (empty($this->_data)) {
            return;
        }
        foreach ($this->_data as $k => $v) {
            $updateData[] = $k . ' = ' . $v;
        }
        $updateBody = ' SET ' . implode(',', $updateData);
        $this->_data = array();
        return $updateBody;
    }

    /**
     * 初始化参数
     */
    private function _initParam()
    {
        $_where = '';
        $_table = '';
        $_select = '*';
        $_limit = '';
        $_order_by = '';
        $_data = array();
    }

    /**
     * 功能：开启事务
     * 参数：无
     * 返回：无
     */
    public function begintransaction()
    {
        mysqli_query($this->_link, "BEGIN");
    }

    /**
     * 功能：事物回滚
     * 参数：无
     * 返回：无
     */
    public function rollback()
    {
        mysqli_query($this->_link, "ROLLBACK");
    }

    /**
     * 功能：执行事务
     * 参数：无
     * 返回：无
     */
    public function commit()
    {
        mysqli_query($this->_link, "COMMIT");
    }

    /**
     * 执行SQL语句
     * 返回结果集
     */
    final public function query($sql)
    {
        if (empty($this->_link)) {
            return false;
        }            //如果连接为空则返回FALSE
        $this->_last_sql = $sql;
        $result = mysqli_query($this->_link, $sql);
        $error = mysqli_error($this->_link);
        if (empty($error)) {
            return $result;
        } else {
            throw new Exception($error);
        }

    }
}
