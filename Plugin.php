<?php
/**
 * 网站访问密码验证
 *
 * @package NeedPasswd
 * @author zhinianblog
 * @version 1.0.0
 * @link https://zhinianboke.com
 */
class NeedPasswd_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     */
    public static function activate()
    {
        
        Typecho_Plugin::factory('Widget_Archive')->header = array('NeedPasswd_Plugin', 'header');
        Typecho_Plugin::factory('Widget_Archive')->footer = array('NeedPasswd_Plugin', 'footer');
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     */
    public static function deactivate()
    {
    }

    /**
     * 获取插件配置面板
     *
     * @param Form $form 配置面板
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $passwd = new Typecho_Widget_Helper_Form_Element_Text('passwd', NULL, '123',
        _t('访问口令'), _t('请填写网站访问对应的口令'));
        $form->addInput($passwd->addRule('required', _t('必须填写访问口令')));
        
        $needPasswd_cookietime = new Typecho_Widget_Helper_Form_Element_Text('needPasswd_cookietime', array('value'), 1, _t('免登录Cookie保存时间，可填写小数(小时)'), _t('输入正确口令后多久需要重新验证，默认为1小时。'));
        $form->addInput($needPasswd_cookietime);
    }

    /**
     * 个人用户的配置面板
     *
     * @param Form $form
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 插件实现方法
     *
     * @access public
     * @return void
     */
    public static function render()
    {
    }
    
    /**
     * 输出头部css
     * 
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function header() {
        echo '<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css" />';
        echo '<style>
            #passwd-shade {
                background: grey;
                z-index: 20;
                opacity: 1;
                position: fixed;
                pointer-events: auto;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                transition: opacity .25s linear;
            }
        </style>';
    }
    
    /**
     * 输出底部js
     * 
     * @access public
     * @param unknown $header
     * @return unknown
     */
    public static function footer() {
        $options = Typecho_Widget::widget('Widget_Options')->plugin('NeedPasswd');
        echo '<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>';
        echo '<script type="text/javascript">
            function setCookie(name, value, daysToLive) {
                // 对 cookie 值进行编码以转义其中的分号、逗号和空格
                var cookie = name + "=" + encodeURIComponent(value);
                if(!isNaN(daysToLive)) {
                    /* 设置 max-age 属性 */
                    cookie += "; max-age=" + (daysToLive*60*60);
                }
                document.cookie = cookie;
            }
        
            function getCookie(name) {
                // 拆分 cookie 字符串
                var cookieArr = document.cookie.split(";");
                // 循环遍历数组元素
                for(var i = 0; i < cookieArr.length; i++) {
                    var cookiePair = cookieArr[i].split("=");
                    
                    /* 删除 cookie 名称开头的空白并将其与给定字符串进行比较 */
                    if(name == cookiePair[0].trim()) {
                        // 解码cookie值并返回
                        return decodeURIComponent(cookiePair[1]);
                    }
                }
                // 如果未找到，则返回null
                return null;
            }
            if(getCookie("NeedPasswd_cookie") != '.$options->passwd.') {
                var mask_bg = document.createElement("div");
                mask_bg.id = "passwd-shade";
                document.body.appendChild(mask_bg);
                swal({
                	title:"请输入访问口令",
                	text:"该网页需要口令才能访问，请填写访问口令：",
                	type:"input",
                	closeOnConfirm: false,
                	closeOnCancel: false,
                	confirmButtonText: "确 认",
                	inputPlaceholder:"请填写访问口令",
                	showLoaderOnConfirm:true,
                	},function(inputValue){
                		if (inputValue != "'.$options->passwd.'") {
                			swal.showInputError("口令错误，请重新输入");
                			return;
                		}
                		else{
                			swal.close("'.$options->passwd.'");
                			var mask_bg = document.getElementById("passwd-shade");
                            if (mask_bg != null) mask_bg.parentNode.removeChild(mask_bg);
                            setCookie("NeedPasswd_cookie", "'.$options->passwd.'", "'.$options->needPasswd_cookietime.'");
                			return;
                		}			
                });
            }
        </script>';
    }
}
