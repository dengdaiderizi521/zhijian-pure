<?php
/**
  * 至简PHP开源框架
  * 版本：至简纯净版
  * 官方网站：https://github.com/dengdaiderizi521/zhijian-pure
  * 日期：2015-05-07
  * 示例文件，如果需要测试数据库，请先配置数据库信息[config/datebase.php]
  */
class Home extends ZJ_Controller{
	function index(){

		//调用模型有两种方法第一种
		//$obj = $this->getModel('home');
		//$data = $obj->getTestData();
		//var_dump($data);die;
		
		//第二种调用模型方法
		// $this->getModel('home');
		// $data = $this->home->getTestData();
		
		//设置模版变量
		$this->set('variableName','至简PHP开源框架纯净版');
		//载入模版
		$this->view('home');
	}
}
?>