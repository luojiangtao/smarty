<?php
/**
 * [Smarty模版引擎]
 * @Author   罗江涛
 * @DateTime 2016-08-12T16:54:47+0800
 */
class Smarty
{
    // 存放assign分配的变量
    public $array = array();

    // 模版文件目录
    public $template_dir = 'templates';
    // 编译文件目录
    public $compile_dir  = 'templates_c';
    // 缓存文件目录
    public $cache_dir    = 'cache';
    // 是否开启缓存
    // public $caching = true;
    public $caching = false;

    // 构造方法
    public function __construct()
    {
    	// 检查文件夹是否存在，不存在则创建
        $this->check_dir();
    }
    /**
     * 检查文件夹是否存在，不存在则创建
     * @Author   罗江涛
     * @DateTime 2016-08-12T17:20:52+0800
     * @return   [type]                   [description]
     */
    private function check_dir()
    {
        is_dir($this->template_dir) || mkdir($this->template_dir, 0777, true);
        is_dir($this->compile_dir) || mkdir($this->compile_dir, 0777, true);
        is_dir($this->cache_dir) || mkdir($this->cache_dir, 0777, true);
    }

    /**
     * 分配变量到模版页面
     * @Author   罗江涛
     * @DateTime 2016-08-12T17:21:42+0800
     * @param    [type]                   $key   [变量的名称]
     * @param    [type]                   $value [变量的值]
     */
    public function assign($key, $value)
    {	
    	// 变量保存到数组中
        $this->array["$key"] = $value;
    }

    /**
     * 载入模版
     * @Author   罗江涛
     * @DateTime 2016-08-12T17:22:17+0800
     * @param    [type]                   $file [模版文件名]
     * @return   [type]                         [description]
     */
    public function display($file)
    {
        // 将assign分配的变量，保存在数组中的转化为变量
        extract($this->array);
        // 组合模版文件目录
        $file = $this->template_dir . '/' . $file;
        if (!file_exists($file)) {
            die('模版文件: ' . $file . ' 不存在');
        }

        // 编译文件路径
        $compile_file = $this->compile_dir . '/' . md5($file) . ".html";
        //只有当编译文件不存在或者是模板文件被修改过了才重新编译文件
        if (!file_exists($compile_file) || filemtime($compile_file) < filemtime($file)) {
        	// 引入模版解析类
            require "lib/Smarty_Compile.class.php";
            $smarty_compile = new Smarty_Compile();
            // 获取模版文件
            $html           = file_get_contents($file);
        	// 替换所有模版标签，包括{$value} <foreach> <if><elseif></if> 为PHP代码
            $html           = $smarty_compile->replace_all($html);
            // 保存缓存文件
            file_put_contents($compile_file, $html);
        }

        //开启了缓存才加载缓存文件，否则直接加载编译文件
        if($this->caching){
        	// 编译文件路径
        	$cache_file = $this->cache_dir . '/' . md5($file) . ".html";
        	//只有当缓存文件不存在，或者编译文件已被修改过,则重新生成缓存文件
        	if(!file_exists($cache_file) || filemtime($cache_file)<filemtime($compile_file)){
        		// 载入编译文件并执行
        		include $compile_file;
        		// 执行$compile_file编译文件后，内容输出到缓存区，不会从输出到屏幕。
        		$content = ob_get_clean();
        		// 保存缓存文件
        		if(!file_put_contents($cache_file, $content)){
        			die('保存缓存文件出错，请检查缓存文件夹写权限');
        		}
        	}
        	// 开启缓存，引入缓存文件,并执行
        	include $cache_file;
        }else{
        	// 没开启缓存，引入编译文件,并执行
        	include $compile_file;
        }
    }

}
