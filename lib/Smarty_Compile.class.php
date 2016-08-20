<?php
/**
 * 解析模版标签类 ，替换所有模版标签，包括{$value} <foreach> <if><elseif></if> 为PHP代码
 * @Author   罗江涛
 * @DateTime 2016-08-12T16:54:47+0800
 */
class Smarty_Compile
{
	/**
	 * 替换全部模版标签为PHP代码
	 * {$name} -> <?php echo $name ?>
	 * @Author   罗江涛
	 * @DateTime 2016-08-09T17:13:37+0800
	 * @return   [type]                   [替换后的html]
	 */
	public function replace_all($html){
		// 替换include标签为引入文件的类容
		$html = $this->replace_include($html);
		// 替换普通变量标签为PHP代码
		$html = $this->replace_value($html);
		// 替换if标签为PHP代码
		$html = $this->replace_if($html);
		// 替换elseif标签为PHP代码
		$html = $this->replace_elseif($html);
		// 替换else标签为PHP代码
		$html = $this->replace_else($html);
		// 替换endif标签为PHP代码
		$html = $this->replace_endif($html);
		// 替换foreach 循环标签为PHP代码
		$html = $this->replace_foreach($html);
		// 替换endforeach 循环标签为PHP代码
		$html = $this->replace_endforeach($html);
		return $html;
	}
    
    /**
     * 替换普通变量标签为PHP代码
     * {$name} -> <?php echo $name ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_value($html){
        // 普通变量 {$name}
        $preg = '/\{\$(.+?)\}/';
        $rep='<?php echo $$1; ?>';
        // 标签替换，替换所有 {} 包含的内容
        $html = preg_replace($preg, $rep, $html);

        // 使用函数 {:U('Index/index')}
        $preg = '/\{:(.+?)\}/';
        $rep='<?php echo $1; ?>';
        // 标签替换，替换所有 {} 包含的内容
        $html = preg_replace($preg, $rep, $html);

        // 替换常量 __ROOT__
        $preg = '/__(.+?)__/';
        $rep='<?php echo __$1__; ?>';
        // 标签替换，替换所有 {} 包含的内容
        $html = preg_replace($preg, $rep, $html);

        return $html;
    }

    /**
     * 替换foreach 循环标签为PHP代码
     * <foreach name='person' item='v' key='k'>  ->  <?php if(is_array($person)):  foreach($person as $k=>$v): ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_foreach($html){
        // 找出判断条件
        $preg = '/<foreach.+?name=(\'|\")(.+?)(\'|\").+?>/';
        preg_match_all($preg, $html, $matches);
        $count = count($matches[0]);
        // 统计匹配到的次数并循环单次替换，防止第一个匹配的值把后面的覆盖了
        while ($count) {
            $preg = '/<foreach.+?name=(\'|\")(.+?)(\'|\").+?>/';
            preg_match($preg,$html, $match);
            $name = empty($match[2]) ? '' : $match[2];

            // 找出键名 默认 k
            $preg = '/<foreach.+?key=(\'|\")(.+?)(\'|\").+?>/';
            preg_match($preg,$html, $match);
            $key = empty($match[2]) ? 'k' : $match[2];

            // 找出值名 默认 v
            $preg = '/<foreach.+?item=(\'|\")(.+?)(\'|\").+?>/';
            preg_match($preg,$html, $match);
            $item = empty($match[2]) ? 'v' : $match[2];

            $preg = '/<foreach(.+?)>/';
            // 标签替换
            $rep='<?php if(is_array($'.$name.')):  foreach($'.$name.' as $'.$key.'=>$'.$item.'): ?>';
            $html = preg_replace($preg, $rep, $html, 1);
            $count--;
        }
        return $html;
    }

    /**
     * 替换endforeach 循环标签为PHP代码
     * </foreach>  ->  <?php endforeach; endif; ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_endforeach($html){
        $preg = '/<\/foreach>/';
        $rep='<?php endforeach; endif; ?>';
        // 标签替换
        $html = preg_replace($preg, $rep, $html);
        return $html;
    }

    /**
     * 替换if标签为PHP代码
     * <if condition="$person[0]['name']=='taotao'">  -> <?php if($person[0]['name']=='taotao'): ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_if($html){
        // 找出判断条件
        $preg = '/<if condition=(\'|\")(.+?)(\'|\")>/';
        preg_match_all($preg, $html, $matches);
        $count = count($matches[0]);
        // 统计匹配到的次数并循环单次替换，防止第一个匹配的值把后面的覆盖了
        while ($count) {
            preg_match($preg,$html, $match);
            $condition = empty($match[2]) ? '' : $match[2];
            $rep="<?php if(".$condition."): ?>";
            // 标签替换
            $html = preg_replace($preg, $rep, $html, 1);
            $count--;
        }
        return $html;
    }

    /**
     * 替换elseif标签为PHP代码
     * <elseif condition="$person[0]['name']=='taotao2'"/> -> <?php elseif($person[0]['name']=='taotao2'): ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_elseif($html){
        // 找出判断条件
        $preg = '/<elseif condition=(\'|\")(.+?)(\'|\")\s?\/>/';
        preg_match_all($preg, $html, $matches);
        $count = count($matches[0]);
        // 统计匹配到的次数并循环单次替换，防止第一个匹配的值把后面的覆盖了
        while ($count) {
            preg_match($preg,$html, $match);
            $condition = empty($match[2]) ? '' : $match[2];
            $rep="<?php elseif(".$condition."): ?>";
            // 标签替换
            $html = preg_replace($preg, $rep, $html, 1);
            $count--;
        }
        return $html;
    }

    /**
     * 替换else标签为PHP代码
     * <else /> -> <?php else: ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_else($html){
        $preg = '/<else\s?\/>/';
        $rep="<?php else: ?>";
        // 标签替换
        $html = preg_replace($preg, $rep, $html);
        return $html;
    }
    /**
     * 替换endif标签为PHP代码
     * </if> -> <?php endif; ?>
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_endif($html){
        $preg = '/<\/if>/';
        $rep="<?php endif; ?>";
        // 标签替换
        $html = preg_replace($preg, $rep, $html);
        return $html;
    }

    /**
     * 替换include标签为被引入文件的内容
     * @Author   罗江涛
     * @DateTime 2016-08-09T17:13:37+0800
     * @return   [type]                   [替换后的html]
     */
    private function replace_include($html){
        // 找到被引入的文件名
        $preg = '/<include\s{1}file=(\'|\")(.+?)(\'|\")\s?\/>/';
        preg_match_all($preg, $html, $matches);
        $count = count($matches[0]);
        // 统计匹配到的次数并循环单次替换，防止第一个匹配的值把后面的覆盖了
        while ($count) {
            preg_match($preg,$html, $match);
            $include = empty($match[2]) ? '' : $match[2];
            if(!empty($include)){
                $include_file = file_get_contents($include);
                // 标签替换
                $html = preg_replace($preg, $include_file, $html, 1);
            }
            $count--;
        }
        return $html;
    }
}
?>


