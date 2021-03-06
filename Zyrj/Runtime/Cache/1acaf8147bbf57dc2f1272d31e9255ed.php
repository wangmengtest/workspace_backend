<?php if (!defined('THINK_PATH')) exit();?><style>    body tr td {
        font-size: 12px;
    }

    /* remove rounds from all elements */
    div, input, select, textarea, span, img, table, td, th, p, a, button, ul, li {
        -webkit-border-radius: 0 !important;
        -moz-border-radius: 0 !important;
        border-radius: 0 !important;
    }

    a:focus {
        outline: none !important;
    }

    a:hover, a:active {
        outline: 0 !important;
    }

    select:focus {
        outline: none !important;
    }

    .btn {
        background-color: #e5e5e5;
        background-image: none;
        filter: none;
        border: 0;
        padding: 7px 14px;
        text-shadow: none;
        font-family: "Segoe UI", Helvetica, Arial, sans-serif;
        font-size: 14px;
        color: #333333;
        cursor: pointer;
        outline: none;
        -webkit-box-shadow: none !important;
        -moz-box-shadow: none !important;
        box-shadow: none !important;
        -webkit-border-radius: 0 !important;
        -moz-border-radius: 0 !important;
        border-radius: 0 !important;
    }

    .btn:hover,
    .btn:focus,
    .btn:active,
    .btn.active,
    .btn[disabled],
    .btn.disabled {
        color: #333333;
        background-color: #d8d8d8 !important;
    }

    .btn.red-stripe {
        border-left: 3px solid #d84a38;
    }

    .btn.blue-stripe {
        border-left: 3px solid #4d90fe;
    }

    .btn.purple-stripe {
        border-left: 3px solid #852b99;
    }

    .btn.green-stripe {
        border-left: 3px solid #35aa47;
    }

    .btn.yellow-stripe {
        border-left: 3px solid #ffb848;
    }

    /*  Red */
    .btn.red {
        color: white;
        text-shadow: none;
        background-color: #d84a38;
    }

    .btn.red:hover,
    .btn.red:focus,
    .btn.red:active,
    .btn.red.active,
    .btn.red[disabled],
    .btn.red.disabled {
        background-color: #bb2413 !important;
        color: #fff !important;
    }

    /*  Blue */
    .btn.transparent {
        color: black;
        text-shadow: none;
        background-color: transparent;
    }

    .btn.blue {
        color: white;
        text-shadow: none;
        background-color: #4d90fe;
    }

    .btn.blue:hover,
    .btn.blue:focus,
    .btn.blue:active,
    .btn.blue.active,
    .btn.blue[disabled],
    .btn.blue.disabled {
        background-color: #0362fd !important;
        color: #fff !important;
    }

    /*  Green */
    .btn.green {
        color: white;
        text-shadow: none;
        background-color: #35aa47;
    }

    .btn.green:hover,
    .btn.green:focus,
    .btn.green:active,
    .btn.green.active,
    .btn.green.disabled,
    .btn.green[disabled] {
        background-color: #1d943b !important;
        color: #fff !important;
    }
</style><!-- BEGIN CONTAINER --><div class="page-container"><!-- BEGIN SIDEBAR --><!-- END SIDEBAR --><!-- BEGIN PAGE --><div class="page-content"><!-- BEGIN PAGE CONTAINER--><div class="container-fluid"><!-- BEGIN PAGE HEADER--><!-- END PAGE HEADER--><!-- 提供帮助确认打款的弹出框 --><div class="row-fluid"><div class="span12" style="font-size:12px; line-height:1.8;; margin-left:10px;margin-right:10px"><?php if(is_array($yixiu)): $i = 0; $__LIST__ = $yixiu;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$voo): $mod = ($i % 2 );++$i; if($voo["is_pay"] == 1): if($voo["type"] == 0): ?><!-- star 投资明细 --><label>帮助ID：<?php echo ($voo['id']); ?> 编号：<span><?php echo ($voo['x1']); ?></span></label><div class="mole"><p><strong>打款推荐人联系电话: <font id="wechat"><?php echo (tel(re_id($voo['bid']))); ?></font></strong></p><table class="table-bordered table-striped table-condensed flip-content" width="100%"
                           style="font-size:12px; line-height:2"><tbody style="font-size:12px"><tr><td><?php
 $sell_id=explode(",", $voo['sid']); $m_id=explode(",", $voo['match_id']); foreach ($sell_id as $key => $value) { ?><div style="border-bottom:#000 1px solid;"><table border="0" width="100%" id="table1" style="font-size:12px; line-height:1.5"><tr><td width="18%">收款人ID：<?=user_id($value);?></td></tr><tr><td width="18%">收款金额：<?=t_money($m_id[$key]);?></td></tr><tr><td width="18%">收款人：<?=bank_user($value);?></td></tr><tr><td>开户银行：<?=bank_name($value);?></td></tr><tr><td>银行账号：<?=bank_number($value);?></td></tr><tr><td>收款人支付宝：<?=zhifuPay($value);?></td></tr><tr><td>收款人电话：<?=tel($value);?></td></tr><tr><td>收款确认：<?php  if(is_done($m_id[$key])==1){?>已确认<?php  }else{?>                                                未确认<?php  };?></tr><tr><td>上传打款凭证：
                                                <form method='post' action='__APP__/Mavro/uploadImg/tid/<?php echo ($m_id[$key]); ?>'
                                                      enctype='multipart/form-data'><input type='file' name='filename'
                                                                                           class='span5 m-wrap'
                                                                                           style='width:70px; margin-top:10px'>&nbsp;<input
                                                        type='submit' value='上传'/></form></td></tr><tr><td><?php if(viewImg($m_id[$key])){ ?><br/><a
                                                    href="http://imagebackend.oss-cn-qingdao.aliyuncs.com/<?=viewImg($m_id[$key]);?>"
                                                    target='_blank'>付款凭证已上传，点击查看</a><?php  }else{?>(未上传)<?php  };?></td></tr></table><br/></div><?php
 } ?><form name="form1" method="post" action="__APP__/Mavro/confirm_pay"><input type="hidden" name="t_id" value="<?php echo ($voo['id']); ?>"/></form></td></tr></tbody></table></div><div class="foots"><?php if(($voo['is_done']) == "0"): ?><p><font color="#FF0000">请汇款并上传凭证后再确认收款，确认后无法撤销，如没有及时汇款，系统将自动封号。</font></p><div class="btns" style="padding-bottom:16px;"><form name="form1" method="post" action="__APP__/Mavro/confirm_pay"><input type="hidden" name="t_id" value="<?php echo ($voo['id']); ?>"/><?php
 if($voo['is_get']==0) { echo "<div align='center'><button class='btn blue' type='submit' style='border-radius:10px'>确认打款</button></div>                        "; } else { echo "
                        <div align='center'><input readonly class='btn gray'
                                                   style='border-radius:10px; text-align:center;width:140px'
                                                   value='您已确认打款'></button></div>                        "; } ?></form><!--  buyaoquxiaogongnneg
                        <?php if(($voo['is_pay']) == "0"): ?><form action="__APP__/Mavro/Delect" method="post"><input type="hidden" name="del_id" value="<?php echo ($voo['id']); ?>" /><input type="submit" value="取消" class="btn gray"  id="cancel" onclick="if(confirm('确定要取消该账户吗？')) return true; else return false;"/></form><?php endif; ?>--></div><?php else: { echo "<br /><div align='center'><input readonly class='btn gray' style='border-radius:10px; text-align:center;'
                                           value='已确认'></button></div>                "; } endif; ?></div><!-- end 投资明细 --><?php else: ?><!-- star 融资明细 --><label>受助ID：<?php echo ($voo['id']); ?> 编号：<span><?php echo ($voo['x1']); ?></span></label><form name="form1" method="post" action="__APP__/Mavro/confirm_get"><div class="mole"><p><strong>打款推荐人联系电话: <font id="wechat"><?php echo (tel(re_id($voo['bid']))); ?></font></strong></p><table class="table-bordered table-striped table-condensed flip-content" width="100%" style="font-size:12px"><tbody><?php
 $sell_id=explode(",", $voo['bid']); $m_id=explode(",", $voo['match_id']); foreach ($sell_id as $key => $value) { ?><tr><td><div style="border-bottom:#000 1px solid;"><table border="0" width="100%" id="table1" style="font-size:12px; line-height:1.5"><tr><td width="15%">打款人账号：<?=user_id($value);?></td></tr><tr><td>收款金额：<?=t_money($m_id[$key]);?></td></tr><tr><td>打款人：<?=bank_user($value);?></td></tr><tr><td>打款人银行：<?=bank_name($value);?></td></tr><tr><td>打款人账户：<?=bank_number($value);?></td></tr><tr><td>打款人qq：<?=qq($value);?></td></tr><tr><td>打款人电话：<?=tel($value);?></td></tr><tr><td>打款订单序号：<?=$m_id[$key];?></td></tr><tr><td>                                    打款凭证：<?php if($voo['img']) {echo "<a href=http://imagebackend.oss-cn-qingdao.aliyuncs.com/".$voo['img']." target='_blank'>                                    已上传，点击查看</a>";} else{echo '未上传';} ?></td></tr></table></div></td></tr><?php } ?></tbody></table><input type="hidden" name="p_id" value="<?php echo ($voo['id']); ?>"/></div><div class="foots" style="font-size:12px"><?php if(($voo['is_done']) == "0"): if(($voo['is_get']) == "1"): if(($voo['is_ts']) != "1"): ?><p><input type="radio" value="1" name="is_ts" id="is_ts1"/>未收到款，投诉

                    </p><?php endif; ?><p><input type="radio" value="2" name="is_ts" id="is_ts2" checked="checked"/>已收到款，确认
                </p><?php endif; endif; if(($voo['is_done']) == "0"): ?><p><font color="#FF0000">警告!</font>在末收到资金之前不要点击确认收款，因为确认了就不能撤销了，系统会默认你已经收到钱了！！</p><div class="btns" style="padding-bottom:16px;"><?php
 if($voo['is_ts']==1) {echo "<br /><input readonly class='btn gray' value='投诉受理中'
                                                             style='text-align:center; width:120px'>";} if($voo['is_get']==1) { if(!$voo['bdt']) echo "<button class='btn red' id='sss' onclikk='qqq();' type='submit' >                提交 </button>"; if($voo['bdt']) { echo "<input readonly class='btn gray' style='border-radius:10px; text-align:center;'
                               value='等待回流'></button>";}} else {echo "<br/><input readonly class='btn gray'
                                                                                  value=' 等待对方打款 '
                                                                                  style='text-align:center;width:140px'>";}?><script>                    function ttss() {
                        document.getElementById("ttss").submit();
                    }
                    function qqq() {
                        if (window.confirm('确定要取提交吗？')) {
                            //alert("确定");
                            return true;
                        } else {
                            //alert("取消");
                            return false;
                        }
                    }
                </script><script>                    function sss() {
                        var a = document.getElementById("is_ts1");
                        var a = document.getElementById("is_ts1");
                        if (a) {
                            document.getElementById("sss").innerHTML = "投诉";

                        }
                    }
                </script><!--

                                  <?php if($voo['img']) {echo " <button class='btn red' type='submit'>确认收款 </button><button class='btn gray'  id='cancel' type='button'>取消 </button>";} else{echo '<span style="color:red">对方未上传图片，您无法确认，如果对方确实已经提供帮助，请您联系对方上传执行图片</span>';} ?>                 --></div><?php else: { echo "<br /><div align='center'><input readonly class='btn gray' style='border-radius:10px; text-align:center;'
                                       value='已完成'></button></div>            "; } endif; ?></div></form><!-- END 融资明细 --><?php endif; else: ?><br/><br/><div align="center" style="font-size:12px; color:#333">您的订单尚未匹配，请匹配后再进行下一步操作.</div><?php endif; endforeach; endif; else: echo "" ;endif; ?></div></div></div><!-- END PAGE --></div><!-- END CONTAINER -->