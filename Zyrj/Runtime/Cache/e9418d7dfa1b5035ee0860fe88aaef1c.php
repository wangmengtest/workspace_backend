<?php if (!defined('THINK_PATH')) exit();?>﻿<!DOCTYPE html><html><head><link rel="shortcut icon" href="/favicon.ico" type="image/x-icon"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"><meta charset="utf-8"><meta http-equiv="X-UA-Compatible" content="IE=edge"><title><?php echo ($System_namex); ?></title><meta name="viewport" content="width=device-width,initial-scale=1,maximum-scale=1,user-scalable=no"><link href="__PUBLIC__/Css/login.css" rel="stylesheet" type="text/css" media="screen,projection" charset="utf-8"/></head><script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/jquery-1.7.2.min.js\"></sc"+"ript>")</script><script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/Base.js\"></sc"+"ript>")</script><script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/prototype.js\"></sc"+"ript>")</script><script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/mootools.js\"></sc"+"ript>")</script><script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/Ajax/ThinkAjax.js\"></sc"+"ript>")</script><script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/Form/CheckForm.js\"></sc"+"ript>")</script><!--<script type="text/javascript">document.write("<scr"+"ipt src=\"__PUBLIC__/Js/common.js\"></sc"+"ript>")</script>--><script type="text/JavaScript" language="JavaScript" src="__PUBLIC__/Js/jquery.SuperSlide.2.1.1.source.js"></script><script type="text/javascript">var bLogin=false;
(function($){ 
$(document).ready(function(){ 
	$('.input_0').live("focus",function(){
		if($(this).val()=="您的账号"){
			$(this).val('');
			};
		if($(this).val()=="......"){
			$(this).val('');
			};
		if($(this).val()=="验证码" ){
			$(this).val('');
			};
	});
});
});

</script><script>(function($){ 
$(document).ready(function(){ 
	
   $(".wutd").click(function(){
		$("#tis").fadeIn(200);
		});
     
}); 
})(jQuery); 
</script><body style="background-color:#fff"><div class="site-wrapper"><header id="header" ><div id="header-global" class="header-main"><div class="wrapper header-global-wrapper"><a href="__URL__" class="logo-header logo center">新买卖宝</a><h1 style="color: #eb0000">旧数据整理中......</h1></div></div></header><div class="no-login-wrapper" ><div id="main" class="wrapper" ><section data-cid="view48" class="center login-form" style="margin-top:50px"><div class="login-container"><form method='post' name="login" id="form1"  class="form"  onsubmit="return false" ><legend><h3 class="form-title" style="font-weight600">登录-mmobar.com.cn</h3></legend><div class="form-controls"><div data-cid="view58" class="control-group"><label class="assistive-text" style="font-size:14px">用户名</label><div class="controls"><input value="" name="account" id="account" type="text" maxlength="16" class="width-full user-success" placeholder="用户名 / 手机号" autocapitalize="none"/></div><div class="help-hint"></div></div><div data-cid="view61" class="control-group"><label class="assistive-text" style="font-size:14px">登录密码</label><div class="controls"><input value="" name="password" id="password" type="password" class="width-full" placeholder="登录密码"  /></div><div class="help-hint"></div></div><div class="code"><label class="assistive-text" style="font-size:14px">验证码</label><input value="" name="verify" id="verify" type="text" onKeyDown="keydown(event)" check="Require" warning="请输入验证码"  maxlength="16" class="user-codes" placeholder="请输入验证码"/><input type="hidden" name="ajax" value="1"><img id="verifyImg" src="__URL__/verify/" width="78" onClick="fleshVerify()" border="0" title="点击刷新"  class="verifyimg"></div><!--<div data-cid="view64" class="control-group"><div class="controls"><label class="checkbox"><input type="checkbox" class="width-full" name="remember_me"> 记住我</label></div></div>--><div data-cid="view67" class="control-group"><div class="form-actions"><button type="submit" class="btn btn-success btn-large width-full" onClick="ThinkAjax.sendForm('form1','__URL__/checkLogin/',loginHandle,'result')">确认登录</button></div></div><div class="error"><span id="result"></span></div><div data-cid="view70" class="control-group"><div class="row"><span class="pull-left"><span id="siteseal"></span></span><img src="/Public/images/sss.gif"   > &nbsp;&nbsp; <a class="pull-right" href="/index.php/Reg/find_pw" data-page="forgot-password" data-module="login">忘记密码？</a></div></div></div></form><div></section></div></div><br /><footer id="footer" class="footer"><div class="wrapper" style="text-align:center;font-size:13px;"> &copy;2016 All Rights Reserved. </div></footer></div><div id="overlays"></div><script language="JavaScript"><!--
var PUBLIC	 =	 '__PUBLIC__';
ThinkAjax.image = [	 '__PUBLIC__/Images/loading2.gif', '__PUBLIC__/Images/ok.gif','__PUBLIC__/Images/update.gif' ]
ThinkAjax.updateTip	=	'<?php echo xstr("hint004");?>';
function loginHandle(data,status){
if (status==1)
{
$('result').innerHTML	=	'<span style="color:blue"><img SRC="__PUBLIC__/Images/ok.gif" WIDTH="20" HEIGHT="20" BORDER="0" ALT="" align="absmiddle" ><?php echo xstr("login_success"); echo xstr("hint005");?></span>';
$('form1').reset();
window.location = '__URL__';
}
}
function keydown(e){
	var e = e || event;
	if (e.keyCode==13)
	{
	ThinkAjax.sendForm('form1','__URL__/checkLogin/',loginHandle,'result');
	}
}
function fleshVerify(type){
//重载验证码
var timenow = new Date().getTime();
if (type)
{
$('verifyImg').src= '__URL__/verify/adv/1/'+timenow;
}else{
$('verifyImg').src= '__URL__/verify/'+timenow;
}

}
function checkRadio(){
	alert("<?php echo xstr('hint006');?>");
	window.location = '__URL__/login';
}
</script></body></html>