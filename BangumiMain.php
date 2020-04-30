<?php
/**
 * Plugin Name: ZM_Bangumi_Index
 * Plugin URI: https://www.azimiao.com
 * Description: 一个WP用的追番页面插件，使用短代码[bangumi]即可显示相应目录
 * Version: 2.0.0b1
 * Author: 野兔#梓喵出没
 * Author URI: https://www.azimiao.com
 */
require_once("BangumiAPI.php");

class ZM_Bangumi{
    //构造方法
    function __construct(){
        //创建菜单
        add_action("admin_menu",array($this,"initBangumi"));
        //注册短代码
        add_action( 'init', array($this,"register_shortcodes"));
    }

    public function register_shortcodes(){
        add_shortcode('bangumi',array($this,'outPut'));
    }

    function getOption(){
        //获取配置
        $options = get_option('zm_bangumi');
        //判断
        if(!is_array($options))
        {
            $options['bangumiAccount'] = '';
            $options['bangumiPwd'] = '';
            $options['isJQuery'] = false;
            $options["color"] = "#ff8c83";
            $options["isCache"] = false;
            $options["isProxy"] = false;
            $options["singleItemNum"] = 6;
            $options["singleNavNum"] = 3;
            update_option('zm_bangumi', $options);
        }
        return $options;
    }

    function initBangumi(){

        $options = $this->getOption();
        //添加子菜单页面
        add_options_page("Bangumi追番页面","Bangumi追番页面","manage_options","bangumi_page_setting",array($this,"optionPage"));
        //获取参数
        if(isset($_POST['zm_bangumi_save'])) {
            $options['bangumiAccount'] = stripslashes($_POST['bangumiAccount']);
            $options['bangumiPwd'] = stripslashes($_POST['bangumiPwd']);
            if ($_POST['isJQuery']) { 
                $options['isJQuery'] = (bool)true; } else { $options['isJQuery'] = (bool)false; 
            }
            if ($_POST['isCache']) { 
                $options['isCache'] = (bool)true; } else { $options['isCache'] = (bool)false; 
            }
            if ($_POST["isProxy"]) { 
                $options['isProxy'] = (bool)true; } else { $options['isProxy'] = (bool)false;
            }
            if($_POST["singleItemNum"]){
                $tempItemNum = stripslashes($_POST["singleItemNum"]);
                if(is_numeric($tempItemNum))
                {
                    $options["singleItemNum"] = (intval($tempItemNum) <= 0 ? 6:intval($tempItemNum));
                }else{
                    $options["singleItemNum"] = 6;
                }
            }
            if($_POST["singleNavNum"]){
                $tempNavNum = stripslashes($_POST["singleNavNum"]);
                if(is_numeric($tempNavNum))
                {
                    $options["singleNavNum"] = (intval($tempNavNum) <=0 ? 3: intval($tempNavNum));
                }else{
                    $options["singleNavNum"] = 3;
                }
            }
            $options["color"] = stripslashes($_POST["color"]);
            update_option('zm_bangumi', $options);
            echo "<div id='message' class='updated fade'><p><strong>数据已更新</strong></p></div>";
        }else if(isset($_POST["zm_bangumi_clear"])){
            //删除
            $cachePath = plugin_dir_path( __FILE__ ) . "BangumiCache/";
            if(is_dir($cachePath)){
            //echo $cachePath;
                $allCaches = scandir($cachePath);
                foreach($allCaches as $val){
                    if($val != "." && $val != "..")
                    {
                        if(!is_dir($cachePath.$val)){
                            unlink($cachePath.$val);
                        }
                    }
                }
            }
            echo "<div id='message' class='error fade'><p><strong>缓存已清除</strong></p></div>";
        }
    }

    //输出后台页面
    function optionPage(){

		$options = $this->getOption();

        ?>


        <style type="text/css">

        #pure_form{font-family:"Century Gothic", "Segoe UI", Arial, "Microsoft YaHei",Sans-Serif;}
        .wrap{padding:10px; font-size:12px; line-height:24px;color:#383838;}
        .otakutable td{vertical-align:top;text-align: left;border:none ;font-size:12px; }
        .top td{vertical-align: middle;text-align: left; border:none;font-size:12px;}
        table{border:none;font-size:12px;}
        pre{white-space: pre;overflow: auto;padding:0px;line-height:19px;font-size:12px;color:#898989;}
        strong{ color:#666}
        .none{display:none;}
        fieldset{ width: 800px;margin: 5px 0 10px;
        padding: 10px 10px 20px 10px;
        -moz-border-radius: 5px;
        -khtml-border-radius: 5px;
        -webkit-border-radius: 5px;
        border-radius: 5px;
        border-radius: 0 0 0 15px;
        border: 3px solid #39f;}
        fieldset:hover{border-color:#bbb;}
        fieldset legend{color: #777;
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        display: block;
        text-shadow: 1px 1px 1px #fff;
        min-width: 90px;
        padding: 0 3px 0 3px;
        border: 1px solid #95abff;
        text-align: center;
        line-height: 30px;}
        fieldset .line{border-bottom:1px solid #e5e5e5;padding-bottom:15px;}
        
        </style>


        <script type="text/javascript">


        jQuery(document).ready(function($){


        $(".toggle").click(function(){$(this).next().slideToggle('normal')});


        });


        </script>


        <form action="#" method="post" enctype="multipart/form-data" name="pure_form" id="pure_form" />


        <div class="wrap">


        <div id="icon-options-general" class="icon32"><br></div>


        <h2>Bangumi追番目录插件配置</h2><br>


        <fieldset>


        <legend class="toggle">插件配置</legend>


            <div>


                <table width="800" border="1" class="otakutable">

                <tr>
                    <td>没有Bangumi账号？</td>
                    <td><label><a href="https://bgm.tv/signup" target="_blank">去注册一个！</a></label></td>
                </tr>

                <tr>
                    <td>Bangumi账号：</td>
                    <td><label><input type="text" name="bangumiAccount" rows="1" style="width:410px;" value = "<?php echo($options['bangumiAccount']); ?>"></label></td>
                </tr>

                <tr>
                    <td>Bangumi密码：</td>
                    <td><label><input  type="password" name="bangumiPwd" rows="1" style="width:410px;" value = "<?php echo($options['bangumiPwd']); ?>"></label></td>
                </tr>

                <tr>
                    <td>主颜色(进度条及标签颜色)：</td>
                    <td><label><input  type="color" name="color" rows="1"  value = "<?php echo($options['color']); ?>"></label></td>
                </tr>
                <tr>
                    <td>单页番剧数量(int)：</td>
                    <td><label><input  type="number" name="singleItemNum" rows="1"  value = "<?php echo(intval($options['singleItemNum']) <= 0 ? 6:$options['singleItemNum']); ?>"></label></td>
                </tr>
                <tr>
                    <td>单页导航标签数量(若当前页为头尾，数量可能多于该数值)：</td>
                    <td><label><input  type="number" name="singleNavNum" rows="1"  value = "<?php echo(intval($options['singleNavNum']) <= 0? 3:$options['singleNavNum']); ?>"></label></td>
                </tr>
                <tr>
                    <td>使用<a href="//xjh.me" target="_blank">岁月小筑</a>提供的API接口：</td>
                    <td><label><input name="isProxy" type="checkbox" value="checkbox" <?php if($options['isProxy']) echo "checked='checked'"; ?> /> 是的，即使它不可用</label></td>
                </tr>                
                <tr>
                    <td><del>是否由本插件引入JQuery库?</del><span style="color:red">V2.0不再需要JQuery,此选项失效</span></td>
                    <td><label><input name="isJQuery" type="checkbox" value="checkbox" <?php if($options['isJQuery']) echo "checked='checked'"; ?> /> 我需要</label></td>
                </tr>

                <tr>
                    <td>是否开启每日缓存?(推荐开启,加速MAX)</td>
                    <td><label><input name="isCache" type="checkbox" value="checkbox" <?php if($options['isCache']) echo "checked='checked'"; ?> /> 开启</label></td>
                </tr>
                <tr>
                    <td>插件版本</td>
                    <td><label><a href="//www.azimiao.com" target="_blank">v2.0.0b1</a></label></td>
                </tr>
                </table>
            </div>


        </fieldset>

        <!-- 提交按钮 -->
        <p class="submit">
                <input type="submit" name="zm_bangumi_save" value="保存信息" />&nbsp;
                <input type="submit" name="zm_bangumi_clear" value="清空缓存" />
        </p>

        <fieldset>
        <legend class="toggle">Bug反馈与联系作者</legend>
            <div>
            <table width="800" border="1" class="otakutable">
                <tr>
                    <td>邮箱</td>
                    <td><label><a href="mailto:admin@azimiao.com" target="_blank">admin@azimiao.com</a></label></td>
                </tr>
                <tr>
                    <td>博客</td>
                    <td><label><a href="//www.azimiao.com" target="_blank">梓喵出没(www.azimiao.com)</a></label></td>
                </tr>
            </div>
        </fieldset>

        </div>
        </form>
        <?php

        }



    public function outPut($atts,$content = ""){
        //TODO 修改为文件读取形式
        echo "<script src='" . plugins_url('js/zm_bangumi.js',__FILE__) . " '></script>";
        echo '<link rel="stylesheet" type="text/css" href="' . plugins_url('css/zm_bangumi.css',__FILE__) . ' " />';
        echo '<div id="zm_bangumi_content">
        <div class="bangumi_loading">
            <div class="loading-anim">
                <div class="border out"></div>
                <div class="border in"></div>
                <div class="border mid"></div>
                <div class="circle">
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                    <span class="dot"></span>
                </div>
                <div class="bangumi_loading_text">追番数据加载中...</div>
            </div>
        </div>
    </div>
    <div style="clear:both"></div>
    <div id="bangumi_nav"><ui id="zm_bangumi_nav"></ui></div>';
    echo "
    <script>
        let xmlhttp;
        function getBangumiData(){
            if(window.XMLHttpRequest)
            {
                xmlhttp=new XMLHttpRequest();
            }else{
                alert('where is my xmlreq?');
                return;
            }
            xmlhttp.onreadystatechange=function()
            {
                if (xmlhttp.readyState==4 && xmlhttp.status==200)
                {
                    if(parseBangumiData)
                    {   
                        let bangumiData;
                        try{
                            bangumiData = JSON.parse(xmlhttp.responseText);
                            parseBangumiData(bangumiData);
                        }catch(e){
                            console.log(xmlhttp.responseText);
                            console.log(e);
                        }

                    }
                }
            }
            xmlhttp.open('get','". admin_url('admin-ajax.php') ."?action=GetBangumiData',true);
            xmlhttp.send();
        }
        getBangumiData();
    </script>
    ";
    }
}


new ZM_Bangumi();

