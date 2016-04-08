# 至简PHP开源框架纯净版

©www.zhijian.cc(官网正在构建当中)版权所有

最后更新时间2016-04-08

2016-04-08 更新内容:

1、增加了路由相关信息，可以在基础控制器中获取到``uri``、``class``、``method``参数

2、增加对nginx环境的支持


2016-04-07 更新内容：

1、增加了访问限制，POST请求的方法名必须是``_``开头

2、增加了Hook机制，开发者可以在项目目录下增加``hook``目录并在``hook``目录下增加ZJHook.php，每次脚本执行结束都都用调用ZJHook。ZJHook需要有构造方法。
````php
	<?php

		class ZJHook {
			pulic function ZJHook()
			{
				//这里书写钩子的内容
			}
		}
````

3、增加了``Exception``的监控，如果程序抛出异常，这里会接收并给出提示，并且会引用``app/error/exception.php``文件，开发者可以在此文件中增加后续处理逻辑。
````php
	//可用参数
	$errcode; //Exception内容
	$errmsg; //Exception追踪结果
	$debug; //是否开启了调试模式
	$logPath; //用于存储日志的路径，如果为空表示未开启日志记录功能
````

4、其他优化

# 授权信息：

至简PHP开源框架遵循Apache2开源协议发布。Apache Licence是著名的非盈利开源组织Apache采用的协议。

该协议和BSD类似，鼓励代码共享和尊重原作者的著作权，同样允许代码修改，再作为开源或商业软件发布。

如果您再次作为开源或者商业软件发布，请保留至简官网的著作权：©www.zhijian.cc版权所有
