<?php
/*
 *--------------------------------------------------------------------------
 * 至简基础控制器
 *--------------------------------------------------------------------------
 */
class ZJ_Controller{
	
	private $viewVariable = array(); //定义类属性，用来保存视图变量

	/**
	  * 设置需要抛到模版上的变量
	  *	@param $name 视图接收时的变量名
	  *	@param $value 视图变量的值
	  */
	function set($name,$value){
		$this->viewVariable[$name] = $value;
	}
	/**
	  * 加载视图
	  *	@param $fileName 视图文件名不包含文件扩展名，可包含路径
	  */
	function view($fileName){
		foreach ($this->viewVariable as $k => $v) {
			$$k = $v;
		}
		//判断控制器文件是否存在
		if(!file_exists(VIEWPATH.$fileName.VIEWEXT)){
			die("视图文件不存在：".VIEWPATH.$fileName.VIEWEXT);
		}
		require_once VIEWPATH.$fileName.VIEWEXT;
	}
	/**
	  * 加载模型
	  *	@param $modelName 模型文件名不包含文件扩展名，可包含路径
	  * @return object 返回实例化对象
	  */
	function getModel($modelName){
		$model = $modelName;
		if(isset($this->$model)){
			return $this->$model;
		}
		$modelName = ucfirst(strtolower($modelName)).'Model';
		//判断控制器文件是否存在
		if(!file_exists(MODELPATH.$modelName.'.php')){
			die("模型文件不存在：".MODELPATH.$modelName.'.php');
		}
		require_once SYSTEMPATH.'database/DB.php';
		require_once MODELPATH.$modelName.'.php';
		return $this->$model = new $modelName();
	}
	/**
	  * 加载配置文件
	  *	@param $configFileName 配置文件名不包含文件扩展名，可包含路径
	  *	@param $key 配置数组的key 可选参数
	  * @return array OR value  返回配置数组或者对应key的value
	  */
	function loadConfig($configFileName,$key=FALSE){
		_get_config($configFileName,$key);
	}
} 
?>