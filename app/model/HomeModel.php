<?php

/**
 * 至简PHP开源框架
 * 版本：至简纯净版
 * 官方网站：https://github.com/dengdaiderizi521/zhijian-pure
 * 日期：2015-05-07
 * 示例文件，如果执行请修改方法中的table_name为真实表名
 */
class HomeModel extends ZJ_Model
{
    function getTestData()
    {
        $result = $this->get_row("test");
        if (!$result) {
            return false;
        }
        return $result;
    }
}
