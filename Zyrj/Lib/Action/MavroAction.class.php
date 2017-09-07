﻿<?php

class MavroAction extends CommonAction
{

    private $lockTableFile = NULL;

    public function _initialize()
    {
        parent::_initialize();
        header("Content-Type:text/html; charset=utf-8");
        $this->_Config_name();//调用参数
        $this->_checkUser();
        //matchBuySell();


    }

    public function cody()
    {
        //===================================二级验证
        $UrlID = (int)$_GET['c_id'];
        if (empty($UrlID)) {
            $this->error(xstr('second_password_error'));
            exit;
        }
        if (!empty($_SESSION['user_pwd2'])) {
            $url = __URL__ . "/codys/Urlsz/$UrlID";
            $this->_boxx($url);
            exit;
        }
        $cody = M('cody');
        $list = $cody->where("c_id=$UrlID")->field('c_id')->find();
        if ($list) {
            $this->assign('vo', $list);
            $this->display('../Public/cody');
            exit;
        } else {
            $this->error(xstr('second_password_error'));
            exit;
        }
    }

    public function duoyi()
    {
        $fee = M('fee');
        $cash = M('cash');
        $fee_rs = $fee->find();
        //进场时间（及多长时间求购记录可以参与匹配）
        $in_time = 24 * 60 * 60 * $fee_rs['s7'] + 60 * 60 * $fee_rs['s13'] + 60 * $fee_rs['s14'];
        $in_time_t = 3600 * $fee_rs['str47'];
        $nowtime = time();
        $where = "match_num=0 and is_pay=0 and is_cancel=0 and is_done=0 and type=0 and rdt<=" . ($nowtime - $in_time);
        $cash_srs = $cash->where($where)->order("rdt asc")->select();
        //找有资格参与匹配的挂卖记录
        // dump($cash_brs);
        $where = "is_pay=0 and is_cancel=0 and is_done=0 and type=1 and rdt<=" . ($nowtime - $in_time_t);
        $cash_brs = $cash->where($where)->field('*')->order("rdt asc")->select();
        //记录已经匹配的卖方ID
        $selled_id = array();
        foreach ($cash_brs as $key => $boo) {
            echo $boo['money'];
            //寻找是否有对应的挂卖信息
            $ss_id = array();
            $b_money = $boo['money'];  //求购金额
            $sum_s_money = 0;
            $sell_id = array();        //匹配到的挂卖人ID
            $sell_user_id = array();        //匹配到的挂卖人ID
//echo "<script>alert(".$b_money.");</script>";
            foreach ($cash_srs as $key => $soo) {
//echo "<script>alert('neibuxunhuan');</script>";
                $ok = in_array($soo['id'], $selled_id);
                if ($ok) continue;
                if ($soo['uid'] == $boo['uid']) continue;
                $s_money = $soo['money'];
                if ($s_money < $b_money) {
                    if ($s_money + $sum_s_money > $b_money) continue;

                    $sum_s_money += $s_money;
                    $sum_s_money += 0;
                    $ss_id[] = $soo['id'];  //挂卖记录id
                    $sell_id[] = $soo['bid'];
                    $sell_user_id[] = $soo['b_user_id'];
                    $sell_money[] = $soo['money'];
                    $sell_money1[] = $soo['money_two'];
                }
                //var_dump ($sell_id);
                if ($sum_s_money == $b_money) {
                    break;
                }
            }

            //若没有足够的挂卖，匹配成功
            if ($sum_s_money < $b_money || $sum_s_money > $b_money) {
                unset($ss_id);
                //echo "<script>alert('nonono');</script>";
            } else {

                $fck = M('fck');
                $i = 0;
                $w = 0;
                $fee_rss = $fee->field('s4,str12,str4')->find();
                $spID = $fee_rs['s4'];            //请根据自己的账户修改
                $password = $fee_rs['str12'];    //请根据自己的账户修改	
                $accessCode = $fee_rs['str4'];        //


                foreach ($ss_id as $yixiid) {

                    $dayixi = $cash->where("id=" . $boo['id'])->select();
                    foreach ($dayixi as $dayi) {
                        //echo $sell_id[$i];
                        //echo $sell_user_id[$i];
                        $aa['uid'] = $dayi['uid'];
                        $aa['user_id'] = $dayi['user_id'];
                        $aa['bid'] = $sell_id[$i];
                        $aa['b_user_id'] = $sell_user_id[$i];
                        $aa['sid'] = $dayi['sid'];
                        $aa['s_user_id'] = $dayi['s_user_id'];
                        $aa['rdt'] = $dayi['rdt'];
                        $aa['money'] = $sell_money[$i];
                        $aa['money_two'] = $sell_money1[$i];
                        $aa['epoint'] = $dayi['epoint'];
                        $aa['is_pay'] = 1;
                        $aa['user_name'] = $dayi['user_name'];
                        $aa['bank_name'] = $dayi['bank_name'];
                        $aa['bank_card'] = $dayi['bank_card'];
                        $aa['x1'] = $dayi['x1'];
                        $aa['x2'] = $dayi['x2'];
                        $aa['x3'] = $dayi['x3'];
                        $aa['x4'] = $dayi['x4'];
                        $aa['sellbz'] = $dayi['sellbz'];
                        $aa['s_type'] = $dayi['s_type'];
                        $aa['is_buy'] = $dayi['is_buy'];
                        $aa['bdt'] = $dayi['bdt'];
                        $aa['ldt'] = $dayi['ldt'];
                        $aa['okdt'] = $dayi['okdt'];
                        $aa['bz'] = $dayi['bz'];
                        $aa['is_sh'] = $dayi['is_sh'];
                        $aa['is_cancel'] = $dayi['is_cancel'];
                        $aa['type'] = $dayi['type'];
                        $aa['pdt'] = $nowtime;
                        $aa['match_id'] = $yixiid;
                        $aa['is_done'] = $dayi['is_done'];
                        $aa['is_get'] = $dayi['is_get'];
                        $aa['match_num'] = $dayi['match_num'];
                        $aa['finish_num'] = $dayi['finish_num'];
                        $aa['money_type'] = $dayi['money_type'];
                        $aa['is_out'] = $dayi['is_out'];
                        $aa['lock_time'] = $dayi['lock_time'];
                        $aa['img'] = $dayi['img'];
                        $aa['money_key'] = $dayi['money_key'];
                    }

                    $idss = $cash->add($aa);
                    $s_tels['user_id'] = $dayi['user_id'];    //卖方电话	
                    $tel = $fck->where($s_tels)->getField('user_tel');

                    $ss_tel['user_id'] = $sell_user_id[$i];
                    $sstel = $fck->where($ss_tel)->getField('user_tel');
                    //
                    $content = '您好，您的订单匹配成功,请及时登录查看并处理';
//$this->TXTmsg($accessCode,$spID,$password,$tel,$content);
//$this->TXTmsg($accessCode,$spID,$password,$sstel,$content);
                    $this->send_sms_new($content, $tel);
                    //   $this->send_sms_new($content, $sstel);

                    //卖方 @is_pay 是否匹配成功 @pdt 匹配时间 @b_id 匹配的求购id @b_user_id 匹配的求购会员编号

                    $map['id'] = $yixiid;
                    $data['is_pay'] = 1;
                    $data['pdt'] = $nowtime;
                    $data['sid'] = $boo['sid'];
                    $data['match_num'] = 1;
                    $data['s_user_id'] = $boo['s_user_id'];
                    $data['match_id'] = $idss; //匹配记录ID
                    $cash->where($map)->save($data);

                    $i += 1;
                    $w = 1;

                }
                if ($w == 1) {
                    $delw = $cash->where("id=" . $boo['id'])->delete();


                    foreach ($ss_id as $voo) {
                        $selled_id[] = $voo;
                    }

                }

            }

            unset($ss_id, $sell_id, $sell_user_id, $sell_money, $sell_money1);

        }
        unset($selled_id);
        $this->success('操作成功', '__APP__/Public/main');
    }


    public function jujue()
    {
        $sid = $_POST['jujue'];
        echo $sid;
        $cash = M('cash');
        $map['id'] = $sid;
        $data['is_pay'] = 0;
        $data['pdt'] = '';
        $data['bid'] = '';
        $data['match_num'] = 0;
        $data['b_user_id'] = '';
        $data['match_id'] = ''; //匹配记录ID
        $smap['match_id'] = $sid;
        $data['is_pay'] = 0;
        $sdata['pdt'] = '';
        $sdata['sid'] = '';
        $sdata['match_num'] = 0;
        $sdata['s_user_id'] = '';
        $sdata['match_id'] = ''; //匹配记录ID
        $res = $cash->where($map)->save($data);
        $res2 = $cash->where($smap)->delete();
        //echo $res."<br>";
        //echo $res2;
        $this->success("ok！已经解除订单关系");
    }

    public function manaul_match2()
    {

        if ($_SESSION['UrlPTPass'] == 'MyssBaiGuoJS') {

            $fee_rs = M('fee')->field('s4,str12,str4')->find();
            $spID = $fee_rs['s4'];            //请根据自己的账户修改
            $password = $fee_rs['str12'];    //请根据自己的账户修改	
            $accessCode = $fee_rs['str4'];        //

            $inCode = $_POST['inCode'];
            $outCode = $_POST['outCode'];
            if (count($inCode) != count(array_unique($inCode))) {
                $this->error("有重复订单!");
                exit;
            }
            $cash = M('cash');
            $zm = 0;
            $fck = M('fck');
            $out_rs = $cash->where("type=1 and is_pay=0 and is_cancel=0 and x1='" . $outCode . "'")->find();

//$cash->where('id='.$in_rs['id'])->delete();

            foreach ($inCode as $yixiwei) {
                $in_rs[] = $cash->where("type=0 and is_pay=0 and is_cancel=0 and x1='" . $yixiwei . "'")->find();
                if (empty($in_rs[0])) {
                    $this->error("该订单已匹配或不存在！");
                    exit;
                }
            }
            if (empty($outCode)) {
                $this->error("请输出场订单编号！");
                exit;
            }
            if (empty($out_rs)) {
                $this->error("该出场订单已匹配或不存在！");
                exit;
            }

            foreach($in_rs as $inRs){
                $zm = $zm + $inRs['money'];
                $whlocks['id'] = $inRs['uid'];
                $whlocks['is_lock'] = 1;
                $locs = $fck->where($whlocks)->find();
                if ($locs) {
                    $this->error("账号存在但无效，可能被锁定");
                    exit;
                }
            }

            $nowtime = time();

            if ($out_rs['money'] != $zm) {
                $this->error("金额不匹配，请重新选择！");
                exit;
            }
            for ($yi = 0; $yi < count($in_rs); $yi++) {
                if ($out_rs['uid'] == $in_rs[$yi]['uid']) {
                    echo("同一会员的订单不能进行匹配！");
                    exit;
                }
            }
            $i = 0;
            $w = 0;
            $dayixi = $cash->where("id=" . $out_rs['id'])->select();
            foreach ($in_rs as $inRsKey => $yixiid) {
                foreach ($dayixi as $dayi) {
                    //echo $sell_id[$i];
                    //echo $sell_user_id[$i];
                    $aa['uid'] = $dayi['uid'];
                    $aa['user_id'] = $dayi['user_id'];
                    $aa['bid'] = $yixiid['uid'];
                    $aa['b_user_id'] = $yixiid['user_id'];
                    $aa['sid'] = $dayi['sid'];
                    $aa['s_user_id'] = $dayi['s_user_id'];
                    $aa['rdt'] = $dayi['rdt'];
                    $aa['money'] = $yixiid['money'];
                    $aa['money_two'] = $yixiid['money_two'];
                    $aa['epoint'] = $dayi['epoint'];
                    $aa['is_pay'] = 1;
                    $aa['user_name'] = $dayi['user_name'];
                    $aa['bank_name'] = $dayi['bank_name'];
                    $aa['bank_card'] = $dayi['bank_card'];
                    $aa['x1'] = $dayi['x1'];
                    $aa['x2'] = $dayi['x2'];
                    $aa['x3'] = $dayi['x3'];
                    $aa['x4'] = $dayi['x4'];
                    $aa['sellbz'] = $dayi['sellbz'];
                    $aa['s_type'] = $dayi['s_type'];
                    $aa['is_buy'] = $dayi['is_buy'];
                    $aa['bdt'] = $dayi['bdt'];
                    $aa['ldt'] = $dayi['ldt'];
                    $aa['okdt'] = $dayi['okdt'];
                    $aa['bz'] = $dayi['bz'];
                    $aa['is_sh'] = $dayi['is_sh'];
                    $aa['is_cancel'] = $dayi['is_cancel'];
                    $aa['type'] = $dayi['type'];
                    $aa['pdt'] = $nowtime;
                    $aa['match_id'] = $yixiid['id'];
                    $aa['is_done'] = $dayi['is_done'];
                    $aa['is_get'] = $dayi['is_get'];
                    $aa['match_num'] = $dayi['match_num'];
                    $aa['finish_num'] = $dayi['finish_num'];
                    $aa['money_type'] = $dayi['money_type'];
                    $aa['is_out'] = $dayi['is_out'];
                    $aa['lock_time'] = $dayi['lock_time'];
                    $aa['img'] = $dayi['img'];
                    $aa['money_key'] = $dayi['money_key'];
                }
                $idss = $cash->add($aa);

                $s_tels['user_id'] = $dayi['user_id'];    //卖方电话	
                $tel = $fck->where($s_tels)->getField('user_tel');
                if($inRsKey == 0){
                    $contentOut = '您好，您的订单[' . $outCode . ']匹配成功,请及时登录查看并处理';
                    $this->send_sms_new($contentOut, $tel);
                }

                $ss_tel['user_id'] = $yixiid['user_id'];
                $sstel = $fck->where($ss_tel)->getField('user_tel');
                //
                $contentIn = '您好，您的订单[' . $yixiid["x1"] . ']匹配成功,请及时登录查看并处理';
//$this->TXTmsg($accessCode,$spID,$password,$tel,$content);
//$this->TXTmsg($accessCode,$spID,$password,$sstel,$content);
                $this->send_sms_new($contentIn, $sstel);
                //      $this->send_sms_new($content, $sstel);


                //卖方 @is_pay 是否匹配成功 @pdt 匹配时间 @b_id 匹配的求购id @b_user_id 匹配的求购会员编号

                $map['id'] = $yixiid['id'];
                $data['is_pay'] = 1;
                $data['pdt'] = $nowtime;
                $data['sid'] = $out_rs['sid'];
                $data['match_num'] = 1;
                $data['s_user_id'] = $out_rs['s_user_id'];
                $data['match_id'] = $idss; //匹配记录ID
                $cash->where($map)->save($data);
                $i += 1;
                $w = 1;
            }
            if ($w == 1) {
                $delw = $cash->where("id=" . $out_rs['id'])->delete();
            }


            $bUrl = __APP__ . '/YouZi/adminclearing';
            $this->_box(1, '匹配成功！', $bUrl, 1);

        }
    }


    public function mmm()
    {
//$this->matchBuySell();
//$bUrl = __APP__.'/Public/main';
//$this->_boxx($bUrl);
    }

    public function codys()
    {
        //=============================二级验证后调转页面
        $Urlsz = (int)$_POST['Urlsz'];
        if (empty($_SESSION['user_pwd2'])) {
            $pass = $_POST['oldpassword'];
            $fck = M('fck');
            if (!$fck->autoCheckToken($_POST)) {
                $this->error(xstr('page_expire_please_reflush'));
                exit();
            }
            if (empty($pass)) {
                $this->error(xstr('second_password_error'));
                exit();
            }

            $where = array();
            $where['id'] = $_SESSION[C('USER_AUTH_KEY')];
            $where['passopen'] = md5($pass);
            $list = $fck->where($where)->field('id,is_agent')->find();
            if ($list == false) {
                $this->error(xstr('second_password_error'));
                exit();
            }
            $_SESSION['user_pwd2'] = 1;
        } else {
            $Urlsz = $_GET['Urlsz'];
        }
        switch ($Urlsz) {
            case 1;
                $_SESSION['Urlszpass'] = 'Mysseb_sell';
                $bUrl = __URL__ . '/eb_sell';
                $this->_boxx($bUrl);
                break;
            case 2;
                $_SESSION['Urlszpass'] = 'Mysseb_buy';
                $bUrl = __URL__ . '/eb_buy';
                $this->_boxx($bUrl);
                break;
            case 3;
                $_SESSION['Urlszpass'] = 'Mysseb_list_b';
                $bUrl = __URL__ . '/eb_list_b';
                $this->_boxx($bUrl);
                break;
            case 4;
                $_SESSION['Urlszpass'] = 'Mysseb_list';
                $bUrl = __URL__ . '/eb_list';
                $this->_boxx($bUrl);
                break;
            case 5;
                $_SESSION['Urlszpass'] = 'Mysseb_history';
                $bUrl = __URL__ . '/eb_history';
                $this->_boxx($bUrl);
                break;
            case 6;
                $_SESSION['UrlPTPass'] = 'MyssEBlist';
                $bUrl = __URL__ . '/admin_eblist';//列表
                $this->_boxx($bUrl);
                break;
            default;
                $this->error(xstr('second_password_error'));
                exit;
        }
    }

    //求购处理（提供帮助）
    public function buyAC()
    {
        $password = I('password');

        //$map['user_id']=$_SESSION['loginUseracc'];
        $map['id'] = $_SESSION[C('USER_AUTH_KEY')];
        $map['passopen'] = md5($password);
        if (!$u = M('fck')->where($map)->find()) {
            $this->error('安全码错误');
        }
        unset($map);

        //$this->error("平台12月1号正式排单，预祝马到成功");
        //	exit;
        //订单编号
        $code_num = $this->_getUserCODE();
        $nowtime = time();
        $fck = D('Fck');
        $cash = M('cash');
        $history = M('xfhistory');
        $fee_rs = M('fee')->field('s2')->find();
        $s2 = explode("|", $fee_rs['s2']);
        $s9 = explode("|", $fee_rs['s9']);
        //求购金额检测
        $ss = (int)$_POST['money'];
        $money = $s2[$ss];
        //$money=$ss;

//每天买入限制，作者：波波专业互助开发QQ369757055
        $fee_rs2 = M('fee')->field('*')->limit(1)->find();
        $fck_rs2 = $fck->find($_SESSION[C('USER_AUTH_KEY')]);
        $bobo_buylimit = $fee_rs2['bobo_buylimit'];
        $bobo_bdlimit = (int)$fee_rs2['bobo_bdlimit'];
        $cha = time() - $bobo_buylimit * 24 * 60 * 60;
        $chaa = time() - 1 * 24 * 60 * 60;
        $count_d = $cash->where("is_cancel=0 and type=0 and uid=" . $fck_rs2['id'] . " and rdt>" . $cha)->count();

        if ($count_d > 0) {
            $this->error("抱歉，目前您每" . $bobo_buylimit . "天可以买入一次");
            exit;
        }

        $r_money = $cash->where("is_cancel=0 and type=0  and rdt>'" . $chaa . "'")->field("sum(money) as a")->find();

        $total_now = (int)($r_money['a'] + $money);
        $last_amount = $bobo_bdlimit - $r_money['a'];
        if ($last_amount < 0) {
            $last_amount = 0;
        }

        if ($bobo_bdlimit > 0) {

            if ($total_now > $bobo_bdlimit) {

                $this->error("大盘每日买入上限额为" . $bobo_bdlimit . "元，目前已总计买入" . $r_money['a'] . "元，还可买入" . $last_amount . "元");
                exit;
            }
        }
        ///买入限制完

        //不同会员级别投资额度限制，作者：波波专业互助开发QQ369757055

        $fee_rs3 = M('fee')->field('*')->limit(1)->find();
        $fck_rs3 = $fck->find($_SESSION[C('USER_AUTH_KEY')]);

        if ($fck_rs3['jingli'] == 1 && $fee_rs3['i31'] && $fee_rs3['i32']) {
            if ($money > $fee_rs3['i32'] || $money < $fee_rs3['i31']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i31'] . "至" . $fee_rs3['i32'] . "元");
                exit;
            }
        }

        if ($fck_rs3['zongjian'] == 1 && $fee_rs3['i33'] && $fee_rs3['i34']) {
            if ($money > $fee_rs3['i34'] || $money < $fee_rs3['i33']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i33'] . "至" . $fee_rs3['i34'] . "元");
                exit;
            }
        }

        if ($fck_rs3['dongshi'] == 1 && $fee_rs3['i35'] && $fee_rs3['i36']) {
            if ($money > $fee_rs3['i36'] || $money < $fee_rs3['i35']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i35'] . "至" . $fee_rs3['i36'] . "元");
                exit;
            }
        }

        if ($fck_rs3['h4'] == 1 && $fee_rs3['i37'] && $fee_rs3['i38']) {
            if ($money > $fee_rs3['i38'] || $money < $fee_rs3['i37']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i37'] . "至" . $fee_rs3['i38'] . "元");
                exit;
            }
        }

        if ($fck_rs3['h5'] == 1 && $fee_rs3['i39'] && $fee_rs3['i40']) {
            if ($money > $fee_rs3['i40'] || $money < $fee_rs3['i39']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i39'] . "至" . $fee_rs3['i40'] . "元");
                exit;
            }
        }
        if ($fck_rs3['h6'] == 1 && $fee_rs3['i41'] && $fee_rs3['i42']) {
            if ($money > $fee_rs3['i42'] || $money < $fee_rs3['i41']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i41'] . "至" . $fee_rs3['i42'] . "元");
                exit;
            }
        }
        if ($fck_rs3['h6'] == 0 && $fck_rs3['h5'] == 0 && $fck_rs3['h4'] == 0 && $fck_rs3['dongshi'] == 0 && $fck_rs3['zongjian'] == 0 && $fck_rs3['jingli'] == 0 && $fee_rs3['i43'] && $fee_rs3['i44']) {
            if ($money > $fee_rs3['i44'] || $money < $fee_rs3['i43']) {
                $this->error("您目前会员级别投资金额需为" . $fee_rs3['i43'] . "至" . $fee_rs3['i44'] . "元");
                exit;
            }
        }


        //$j_money=M('cash')->where("is_cancel=0 and type=0  and is_pay=0)->field("sum(money) as a")->find();


//不同会员级别投资额度 限制结束
        if (empty($money) || !is_numeric($money)) {
            $this->error("金额不能为空或非数字");
            exit;
        }
        if (strlen($money) > 12) {
            $this->error("金额数值过大");

            exit;
        }
        if ($money <= 0) {
            $this->error("输入金额错误");
            exit;
        }
        if (!is_int($money / 100)) {
            $this->error("输入金额错误");
            exit;
        }

        $buhu = $fck->where('id=' . $_SESSION[C('USER_AUTH_KEY')])->getfield('user_id');
        //支付类型检测
        $pay_type = $_POST['pay_type'];
        $s_type = implode(",", $pay_type); //

        //yixiyun  addzhucema 
        $card = M('card');
        //$Code = trim($_POST['zhucema']);  //获取诚信券
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $map['is_use'] = 0;
        $c_num = $money / 1000;
        $res = M('card')->where($map)->field('id,card_no')->group('id')->order('id asc')->limit($c_num)->select();

        $res_count = count($res);
        if ($res_count < $c_num) {
            $this->error('您的门票数不足，请先购买', U('/Change/FrontCode'));
        }
        /*
      if(!$res=M('card')->where($map)->field('card_no')->order('id asc')->find()){
        $this->error('您的门票已经使用完，请先购买',U('/Change/FrontCode'));
      }
	  */

        $code_arr = array();

        foreach ($res as $k => $v) {

            $code_arr[] = $v['id'];

            $Code = $res[$k]['card_no'];
            $mapp = array();
            $mapp['card_no'] = $Code;
            $mapp['is_use'] = array('eq', 0);

            $mapp['buser_id'] = array('eq', $buhu);
            $authInfoo = $card->where($mapp)->field('*')->find();
            if (!$authInfoo) {
                $this->error("诚信券不存在或已被使用，或者越权使用");
                exit;
            }
        }


        // var_dump($code_arr);die();
        //$lasttime=$nowtime-861112;
        //echo date('Y-m-d',$lasttime);
        //$maps['rdt'] = array('between',$lasttime,$nowtime);
        $maps['uid'] = array('eq', $_SESSION[C('USER_AUTH_KEY')]);
        $maps['is_cancel'] = array('eq', 0);
        $maps['is_done'] = array('eq', 0);
        $maps['type'] = array('eq', 0);
        $shumu = $cash->where($maps)->sum('money');
        $shumu += $money;
        if ($shumu > 30000) {
            //$this->error("排单金额超限，请等待下轮排单");
            //	exit; 
        }
//解除连续进场限制
        //有未出局的订单就不能买新的 


        $count = $cash->where("is_out=0 and is_done=0 and type=0 and uid=" . $_SESSION[C('USER_AUTH_KEY')])->count();
        $tequan_q = array('8000000', '888998566');
        $isin = in_array($_SESSION['loginUseracc'], $tequan_q);
        if ($isin) {
        } else {


            $wherep['id'] = $_SESSION[C('USER_AUTH_KEY')];
            $mj = $fck->where($wherep)->field('jingli,zongjian,dongshi,h4,h5,h6')->find();

            if (!$bobo_buylimit) // 1 perday check begin
            {

                /*
   if($mj['jingli']==0&&$mj['zongjian']==0&&$mj['dongshi']==0&&$mj['h4']==0&&$mj['h5']==0&&$mj['h6']==0)
{
	   if($count>1){
		$this->error ("排单超限，您是H0级别，只能排2单，暂无法购买");
		exit;}
}

   if($mj['jingli']==1&&$mj['zongjian']==0&&$mj['dongshi']==0&&$mj['h4']==0&&$mj['h5']==0&&$mj['h6']==0)
{
	   if($count>4){
		$this->error ("排单超限，您是H1级别，只能排5单，暂无法购买");
		exit;}
}
	
   if($mj['jingli']==0&&$mj['zongjian']==1&&$mj['dongshi']==0&&$mj['h4']==0&&$mj['h5']==0&&$mj['h6']==0)
{
	   if($count>9){
		$this->error ("排单超限，您是H2级别，只能排10单，暂无法购买");
		exit;}
}
   if($mj['jingli']==0&&$mj['zongjian']==0&&$mj['dongshi']==1&&$mj['h4']==0&&$mj['h5']==0&&$mj['h6']==0)

{
	   if($count>20){
		$this->error ("排单超限，您是H3级别，只能排20单，暂无法购买");
		exit;}
}

   if($mj['jingli']==0&&$mj['zongjian']==0&&$mj['dongshi']==0&&$mj['h4']==1&&$mj['h5']==0&&$mj['h6']==0)

{
	   if($count>30){
		$this->error ("排单超限，您是H4级别，只能排20单，暂无法购买");
		exit;}
}

   if($mj['jingli']==0&&$mj['zongjian']==0&&$mj['dongshi']==0&&$mj['h4']==0&&$mj['h5']==1&&$mj['h6']==0)

{
	   if($count>50){
		$this->error ("排单超限，您是H5级别，只能排50单，暂无法购买");
		exit;}
}

   if($mj['jingli']==0&&$mj['zongjian']==0&&$mj['dongshi']==0&&$mj['h4']==0&&$mj['h5']==0&&$mj['h6']==1)

{
	   if($count>100){
		$this->error ("排单超限，您是H6级别，只能排100单，暂无法购买");
		exit;}
}

*/
            }
        }

        $fck_rs = $fck->find($_SESSION[C('USER_AUTH_KEY')]);
        //插入交易表
        $data = array();
        $data['uid'] = $fck_rs['id'];
        $data['user_id'] = $fck_rs['user_id'];
        $data['bid'] = $fck_rs['id'];
        $data['b_user_id'] = $fck_rs['user_id'];
        $data['rdt'] = $nowtime;        //求购时间 重要
        $data['money'] = $money;
        $data['money_two'] = $money;
        $data['epoint'] = 0;            //存储国家，查询币值
        $data['is_pay'] = 0;            //是否匹配成功
        $data['is_cancel'] = 0;            //是否已取消
        $data['is_done'] = 0;            //是否已确认完成交易
        $data['bank_name'] = $fck_rs['bank_name'];  //银行名称
        $data['bank_card'] = $fck_rs['bank_card'];  //银行卡
        $data['user_name'] = $fck_rs['user_name'];  //开户名称
        // $data['sellbz']			= $bzcontent;  	//备注
        $data['s_type'] = $s_type;        //支付类型
        $data['type'] = 0;                //0为求购，1为挂卖
        $data['money_type'] = 0;                //0为求购，1为mavro挂卖,2为动态余额的挂卖
        $data['ldt'] = $nowtime;
        $data['money_key'] = $ss;
        $data['x1'] = $code_num;                //编号
        $rs2 = $cash->add($data);


        if ($rs2) {

            //预发奖金
            //推荐奖
            $fck->B2_tjj($fck_rs['re_path'], $fck_rs['user_id'], $money, 1);
            //报单奖
            $fck->B3_quyujiang($fck_rs['shop_id'], $fck_rs['user_id'], $money, 1);

            //yixiyun  gaibianzhucemazhuangtai 

            //$card_where['card_no']=$Code;
            $card_where['id'] = array('in', $code_arr);
            $card_data['b_time'] = time();
            $card_data['is_use'] = 1;
            $card_data['bid'] = $fck_rs['id'];
            $card_data['buser_id'] = $fck_rs['user_id'];
            $card->where($card_where)->save($card_data);

            //提供帮助消耗门票记录，写入orderlist表
            $data_p = array();
            $data_p['userid'] = $fck_rs['id'];
            $data_p['ordstatus'] = 5;
            $data_p['num'] = $c_num;
            $data_p['payment_notify_time'] = date("Y-m-d H:i:s", time());
            $data_p['payment_type'] = '订单' . $code_num . '提供帮助消耗门票';
            $rs_p = M('orderlist')->add($data_p);


            $aaa = A('Mavro');
            $aaa->single_in();

            $this->redirect('/Public/main', 5);


        } else {
            $this->error("操作失败321");
            exit;
        }
    }

    //挂卖处理（提供帮助）

    private function _getUserCODE()
    {
        $cash = M('cash');

        $a = 'S';
        $mynn = $a . rand(10000000, 99999999);
        $fwhere['x1'] = $mynn;
        $frss = $cash->where($fwhere)->field('id')->find();
        if ($frss) {
            return $this->_getUserCODE();
        } else {
            unset($cash, $fee);
            return $mynn;
        }
    }

    public function sellAC()
    {
        //订单编号
        $code_num = $this->_getUserCODE2();
        $nowtime = time();
        $money_type = $_POST['money_type'];
        $fck = D('Fck');
        $cash = M('cash');
        $history = M('xfhistory');
        $fee_rs = M('fee')->find(1);
        $fck_rs = $fck->find($_SESSION[C('USER_AUTH_KEY')]);
        //挂卖金额检测
        $money = $_POST['s_money'];

        if (empty($money) || !is_numeric($money)) {
            $this->error("金额不能为空或非数字");
            exit;
        }
        if (strlen($money) > 12) {
            $this->error("金额数值过大");
            exit;
        }
        if ($money <= 0) {
            $this->error("输入金额错误");
            exit;
        }
        //自己正在挂卖，没有完成的订单总额
        $r_money = $cash->where("is_buy=0 and is_cancel=0 and type=1 and uid=" . $_SESSION[C('USER_AUTH_KEY')])->field("sum(money) as a")->find();
        //已挂卖的MAVRO
        $m_r_money = $cash->where("money_type=1 and is_buy=0 and is_cancel=0 and type=1 and uid=" . $_SESSION[C('USER_AUTH_KEY')])->field("sum(money) as a")->find();
        //已挂卖的动态余额
        $y_r_money = $cash->where("money_type=2 and is_buy=0 and is_cancel=0 and type=1 and uid=" . $_SESSION[C('USER_AUTH_KEY')])->field("sum(money) as a")->find();

        //动态奖金冻结时间
        $nowt = time();
        $fee4 = M('fee');
        $fee_rs4 = $fee4->where("id=1")->field('*')->find();
        $gee = $nowt - $fee_rs4['i49'] * 3600;


        $ddd = M('chistory')->where("did=" . $_SESSION[C('USER_AUTH_KEY')] . " and time>" . $gee . " and action_type in (2,3)")->field("sum(money) as a")->find();
        //  $this->assign('ddd',$ddd);//数据输出到模板

        $perday = $nowt - 3600 * 24;
        $p_r_money = $cash->where("money_type=2 and is_buy=0 and is_cancel=0 and rdt>" . $perday . " and type=1 and uid=" . $_SESSION[C('USER_AUTH_KEY')])->field("sum(money) as a")->find();

//动态冻结时间

//提现冻结时间
        $nowt = time();
        $fee5 = M('fee');
        $fee_rs5 = $fee4->where("id=1")->field('*')->find();
        $geet = $nowt - $fee_rs5['str46'] * 3600;
        $jjj = M('chistory')->where("did=" . $_SESSION[C('USER_AUTH_KEY')] . " and time>" . $geet . " and type=1 and money>0")->field("sum(money) as a")->find();
        // $this->assign('jjj',$jjj);//数据输出到模板

//提现冻结时间


        // dump($r_money['a']);

        //二次开发限制连续挂卖
        //二次开发限制连续挂卖
        $count = $cash->where("is_out=0 and is_done=0 and type=1 and uid=" . $_SESSION[C('USER_AUTH_KEY')])->getfield('money_type');
        if ($count) {
            if ($count != $money_type) {
                //	$this->error ("您有未完成的订单，暂无法进行挂卖");
                //	exit;
            }
        }
        if ($money_type == 1) {
            if ($money + $m_r_money['a'] + $jjj['a'] > $fck_rs['agent_cash']) {
                //   if ($money > $jjj['a'] && $_SESSION[C('USER_AUTH_KEY')] != 1){
                $this->error("静态余额余额不足");
                exit;
            }
        } elseif ($money_type == 2) {
            //$this->error ("动态余额计算中");
            if ($money + $y_r_money['a'] + $ddd['a'] > $fck_rs['agent_use']) {
                $this->error("动态余额余额不足");
                exit;
            }

            if ($money % 500 > 0) {
                //   if ($money > $jjj['a'] && $_SESSION[C('USER_AUTH_KEY')] != 1){
                $this->error("动态金额需为500的整数倍！");
                exit;
            }
            if ($money + $p_r_money['a'] > 5000) {
                //   if ($money > $jjj['a'] && $_SESSION[C('USER_AUTH_KEY')] != 1){
                $this->error("动态金额每天最多5000元，您已排" . $p_r_money . "元！");
                exit;
            }


        }
        //每周提款上限
        $get_mm = $fck_rs['xx_money'] + $money;
        if ($get_mm > $fee_rs['s6'] && $money_type == 2) {
            $money = $fee_rs['s6'] - $fck_rs['xx_money'];
            if ($money < 0) $money = 0;
            $this->error("每周最高挂卖金额" . $fee_rs['s6'] . "!当前还可挂卖{$money}");
            exit;
        }

        // //间隔多天只能挂卖一次
        $str37 = $fee_rs['str37'];
        $cha = time() - $str37 * 24 * 60 * 60;
        $count_d = $cash->where("is_cancel=0 and type=1 and uid=" . $fck_rs['id'] . " and rdt>" . $cha)->count();
        if ($count_d > 0) {
            $this->error("抱歉，目前您每" . $str37 . "天可以卖出一次");
            exit;
        }


        //支付类型检测
        $pay_type = $_POST['pay_type'];
        $s_type = implode(",", $pay_type); //


        //插入交易表
        $data = array();
        $data['uid'] = $fck_rs['id'];
        $data['user_id'] = $fck_rs['user_id'];
        $data['sid'] = $fck_rs['id'];
        $data['s_user_id'] = $fck_rs['user_id'];
        $data['rdt'] = time();        //求购时间 重要
        $data['money'] = $money;
        $data['money_two'] = $money;
        $data['epoint'] = 0;            //存储国家，查询币值
        $data['is_pay'] = 0;            //是否匹配成功
        $data['is_cancel'] = 0;            //是否已取消
        $data['is_done'] = 0;            //是否已确认完成交易
        $data['bank_name'] = $fck_rs['bank_name'];  //银行名称
        $data['bank_card'] = $fck_rs['bank_card'];  //银行卡
        $data['user_name'] = $fck_rs['user_name'];  //开户名称
        // $data['sellbz']			= $bzcontent;  	//备注
        $data['s_type'] = $s_type;        //支付类型
        $data['type'] = 1;                //0为求购，1为挂卖
        $data['money_type'] = $money_type;                //0为求购，1为mavro挂卖,2为动态余额的挂卖
        $data['x1'] = $code_num;                //编号
        $rs2 = $cash->add($data);

        if ($rs2) {
            //记录每周挂卖金额
            if ($money_type == 2) {
                $fck->execute("update __TABLE__ set xx_money=xx_money+" . $money . " where id=" . $fck_rs['id']);
            }

            //只要有挂卖的行为，全部已匹配且已确认收款的挂买订单的状态都发完日息后改为出局
            $buyrs = $cash->where("is_pay=1 and is_cancel=0 and is_done=1 and type=0 and uid=" . $fck_rs['id'])->select();
            foreach ($buyrs as $voo) {
                //发完日息，然后出局
                //计算奖金
                //	$fck->B1_fh_perday();
                $cash->execute("update __TABLE__ set is_out=1 where id=" . $voo['id']);
                // dump($cash);exit;
            }

            // //只要有挂卖的行为，全部未匹配的挂买订单的出局时间改为当前时间
            // $cash->execute("update __TABLE__ set lock_time={$nowtime} where is_cancel=0 and type=0 and is_pay=0 and lock_time=0 and uid=".$fck_rs['id']);
            // dump($cash);exit;
            //只要有挂卖的行为，全部未匹配的挂买订单的出局时间改为当前时间
            // $cash->execute("update __TABLE__ set lock_time={$nowtime} where is_cancel=0 and type=0 and is_pay=1 and is_done=0 and lock_time=0 and uid=".$fck_rs['id']);
            // dump($cash);exit;

            $aaa = A('Mavro');
            $aaa->single_out();
            $this->redirect('Public/main');
        } else {
            $this->error("操作失败123,支付宝银行卡是否填写", '/index.php?s=/Change/changedata');
            exit;
        }
    }

    /***********single_in end**********************/

    private function _getUserCODE2()
    {
        $cash = M('cash');

        $a = 'G';
        $mynn = $a . rand(10000000, 99999999);
        $fwhere['x1'] = $mynn;
        $frss = $cash->where($fwhere)->field('id')->find();
        if ($frss) {
            return $this->_getUserCODE2();
        } else {
            unset($cash, $fee);
            return $mynn;
        }
    }
    /***********single_out end**********************/

    //自动匹配买卖记录（正常单子，没有锁定扣单的）
    /***********single_in star*************单进******/
    public function single_in()
    {
        $fee = M('fee');
        $cash = M('cash');
        $fee_rs = $fee->find();
        //进场时间（及多长时间求购记录可以参与匹配）
        $in_time = 24 * 60 * 60 * $fee_rs['s7'] + 60 * 60 * $fee_rs['s13'] + 60 * $fee_rs['s14'];
        $in_time_t = 3600 * $fee_rs['str47'];
        $nowtime = time();
        //找有资格参与匹配的求购记录
        $where = "match_num=0 and is_pay=0 and is_cancel=0 and is_done=0 and type=0 and rdt<=" . ($nowtime - $in_time);
        //	$where="match_num=0 and is_pay=0 and is_get=0 and is_cancel=0 and is_done=0 and type=0";
        $cash_brs = M('cash')->where($where)->field('*')->order('id asc')->limit(1)->find();
        //找有资格参与匹配的挂卖记录
        // dump($cash_brs);
        $where = "is_pay=0 and is_cancel=0 and is_done=0 and type=1 and rdt<=" . ($nowtime - $in_time_t);
        //$cash_srs=$cash->where($where)->order("money desc,id asc")->limit(3)->find();
        $cash_srs = $cash->where($where)->order("money desc,id asc")->limit(5)->select();
        //记录已经匹配的卖方ID
        $selled_id = array();
        $boo = $cash_brs;
        //$soo=$cash_srs;
        //寻找是否有对应的挂卖信息
        $ss_id = array();
        $b_money = $boo['money'];  //求购金额
        $sum_s_money = 0;
        $sell_id = array();        //匹配到的挂卖人ID
        $sell_user_id = array();        //匹配到的挂卖人ID

        /***********a star*****************/
        foreach ($cash_srs as $soo) {
            $ok = in_array($soo['id'], $selled_id);
            if ($ok) continue;
            //自己不能和自己匹配
            if ($soo['uid'] == $boo['uid']) continue;

            $s_money = $soo['money'];
            if ($s_money == $b_money) {
                //重置ID信息
                $ss_id = array();
                $sell_id = array();        //匹配到的挂卖人ID
                $sell_user_id = array();        //匹配到的挂卖人ID
                $sum_s_money = $s_money;
                $ss_id[] = $soo['id'];  //挂卖记录id
                $sell_id[] = $soo['sid'];
                $sell_user_id[] = $soo['s_user_id'];
                break;
                //匹配到一个人的，直接跳出
            } elseif ($s_money < $b_money) {
                $sum_s_money += $s_money;
                $sum_s_money += 0;
                $ss_id[] = $soo['id'];  //挂卖记录id
                $sell_id[] = $soo['sid'];
                $sell_user_id[] = $soo['s_user_id'];
            }
            //
            if ($sum_s_money == $b_money) break;
        }
        /***********a end*****************/

        //$this->error("错误99999".$sum_s_money);
        //若没有足够的挂卖，匹配成功
        /***********b star*****************/
        if ($sum_s_money < $b_money || $sum_s_money > $b_money) {
            unset($ss_id);

        } else {
            //匹配成功，更新买卖双方信息
            //买方 @is_pay 是否匹配成功 @pdt 匹配时间 @s_id 匹配的挂卖id @s_user_id 匹配的挂卖会员编号
            $s_id = implode(",", $sell_id);
            $s_userid = implode(",", $sell_user_id);
            $match_id = implode(",", $ss_id); //匹配记录ID
            $count_match = count($ss_id);      //匹配到的条数


            $cash->execute("update __TABLE__ set is_pay=1,pdt=" . $nowtime . ",sid='" . $s_id . "',s_user_id='" . $s_userid . "',match_id='" . $match_id . "',match_num=" . $count_match . " where id=" . $boo['id']);

            //卖方 @is_pay 是否匹配成功 @pdt 匹配时间 @b_id 匹配的求购id @b_user_id 匹配的求购会员编号
            $map['id'] = array('in', $ss_id);
            $data['is_pay'] = 1;
            $data['pdt'] = $nowtime;
            $data['bid'] = $boo['bid'];
            $data['b_user_id'] = $boo['b_user_id'];
            $data['match_id'] = $boo['id']; //匹配记录ID
            $cash->where($map)->save($data);

            //匹配成功给买卖双方发短信
            $b_tel = tel($boo['uid']);  //买方电话
            $spID = $fee_rs['s4'];            //请根据自己的账户修改
            $password = $fee_rs['str12'];    //请根据自己的账户修改
            $accessCode = $fee_rs['str4'];        //

            $contents = '您好，您的订单匹配成功,请及时登录查看并处理';
            //此处二次注释yixiyun
            //$this->TXTmsg($accessCode,$spID,$password,$b_tel,$contents);
            // $this->send_sms_new($contents, $b_tel);

            //卖方
            $map1 = array();
            $map1['id'] = array('in', $ss_id);

            $sell_pei = $cash->where($map1)->select();

            foreach ($sell_pei as $soo) {
                $s_tel = tel($soo['uid']);     //卖方电话
                $content = '接受帮助方' . $soo['user_id'] . '您好，您的订单匹配成功 请及时登录查看并处理';
                ////此处二次注释yixiyun
                //$this->TXTmsg($accessCode,$spID,$password,$s_tel,$content);
                //  $this->send_sms_new($content, $s_tel);
            }
            unset($sell_pei);


            //记录已经匹配的卖方ID
            foreach ($ss_id as $voo) {
                $selled_id[] = $voo;
            }
            unset($ss_id, $sell_id, $sell_user_id);
        }
        /***********b end*****************/
        unset($selled_id);
    }

    /***********single_out star*************单出******/
    public function single_out()
    {
        $fee = M('fee');
        $cash = M('cash');
        $fee_rs = $fee->find();
        //进场时间（及多长时间求购记录可以参与匹配）
        $in_time = 24 * 60 * 60 * $fee_rs['s7'] + 60 * 60 * $fee_rs['s13'] + 60 * $fee_rs['s14'];
        $in_time_t = 3600 * $fee_rs['str47'];
        $nowtime = time();
        //找有资格参与匹配的挂卖记录
        $where = "is_pay=0 and is_cancel=0 and is_done=0 and type=1 rdt<=" . ($nowtime - $in_time);
        $cash_srs = M('cash')->where($where)->field('*')->order('id asc')->limit(1)->find();
        //找有资格参与匹配的求购记录
        $where = "is_pay=0 and is_get=0 and is_cancel=0 and is_done=0 and type=0 rdt<=" . ($nowtime - $in_time_t);
        $cash_brs = $cash->where($where)->order("money desc,id asc")->limit(5)->select();
        //记录已经匹配的买方ID
        $buyed_id = array();
        $soo = $cash_srs;
        //$boo=$cash_brs;
        //寻找是否有对应的求购信息
        $bb_id = array();
        $s_money = $soo['money'];  //挂卖金额
        $sum_b_money = 0;
        $buy_id = array();        //匹配到的求购人ID
        $buy_user_id = array();        //匹配到的求购人ID

        /***********a star*****************/
        foreach ($cash_brs as $boo) {
            $ok = in_array($boo['id'], $buyed_id);
            if ($ok) continue;
            //自己不能和自己匹配
            if ($boo['uid'] == $soo['uid']) continue;

            $b_money = $boo['money'];
            if ($s_money == $b_money) {
                //重置ID信息
                $bb_id = array();
                $buy_id = array();        //匹配到的求购人ID
                $buy_user_id = array();        //匹配到的求购人ID
                $sum_b_money = $b_money;
                $bb_id[] = $boo['id'];  //求购记录id
                $buy_id[] = $boo['bid'];
                $buy_user_id[] = $boo['b_user_id'];
                break;
                //匹配到一个人的，直接跳出
            } elseif ($b_money < $s_money) {
                $sum_b_money += $b_money;
                $sum_b_money += 0;
                $bb_id[] = $boo['id'];  //求购记录id
                $buy_id[] = $boo['bid'];
                $buy_user_id[] = $boo['b_user_id'];
            }
            //
            if ($sum_b_money == $s_money) break;
        }
        /***********a end*****************/

        //$this->error("错误99999".$sum_s_money);
        //若没有足够的求购，匹配成功
        /***********b star*****************/
        if ($sum_b_money < $s_money || $sum_b_money > $s_money) {
            unset($bb_id);

        } else {
            //匹配成功，更新买卖双方信息
            //卖方 @is_pay 是否匹配成功 @pdt 匹配时间 @b_id 匹配的求购id @b_user_id 匹配的求购会员编号
            $b_id = implode(",", $buy_id);
            $b_userid = implode(",", $buy_user_id);
            $match_id = implode(",", $bb_id); //匹配记录ID
            $count_match = count($bb_id);      //匹配到的条数


            //$cash->execute("update __TABLE__ set is_pay=1,pdt=".$nowtime.",bid='".$b_id."',b_user_id='".$b_userid."',match_id='".$match_id."' where id=".$soo['id']);


            $map2 = array();
            $map2['id'] = array('in', $bb_id);
            $sell = $cash->where($map2)->select();
            foreach ($sell as $boo) {
                $aa['uid'] = $soo['sid'];
                $aa['user_id'] = $soo['s_user_id'];
                $aa['bid'] = $boo['uid'];
                $aa['b_user_id'] = $boo['user_id'];
                $aa['sid'] = $soo['sid'];
                $aa['s_user_id'] = $soo['s_user_id'];
                $aa['rdt'] = time();
                $aa['money'] = $boo['money'];
                $aa['money_two'] = $boo['money_two'];
                $aa['epoint'] = 0;
                $aa['is_pay'] = 1;
                $aa['user_name'] = $soo['user_name'];
                $aa['bank_name'] = $soo['bank_name'];
                $aa['bank_card'] = $soo['bank_card'];
                $aa['x1'] = $soo['x1'];
                $aa['x2'] = $soo['x2'];
                $aa['s_type'] = $soo['s_type'];
                $aa['is_buy'] = $soo['is_buy'];
                $aa['bdt'] = 0;
                $aa['ldt'] = 0;
                $aa['okdt'] = 0;
//$aa['bz']= 0;
                $aa['is_sh'] = 0;
                $aa['is_cancel'] = 0;
                $aa['type'] = 1;
                $aa['pdt'] = $boo['pdt'];
                $aa['match_id'] = $boo['id'];
                $aa['is_done'] = 0;
                $aa['is_get'] = 0;
                $aa['match_num'] = 0;
                $aa['finish_num'] = 0;
                $aa['money_type'] = 1;
                $aa['is_out'] = 0;
                $aa['lock_time'] = 0;
                $aa['money_key'] = 0;
                $idss = $cash->add($aa);
//$this->error("错误99999".$b_money);
                $map['id'] = $boo['id'];
                $data['is_pay'] = 1;
                $data['pdt'] = $nowtime;
                $data['sid'] = $soo['sid'];
                $data['s_user_id'] = $soo['s_user_id'];
                $data['match_id'] = $idss; //匹配记录ID
                $data['match_num'] = 1;
                $cash->where($map)->save($data);
            }

            //买方 @is_pay 是否匹配成功 @pdt 匹配时间 @s_id 匹配的挂卖id @s_user_id 匹配的挂卖会员编号
            //匹配成功给买卖双方发短信
            $s_tel = tel($soo['uid']);  //卖方电话
            $spID = $fee_rs['s4'];            //请根据自己的账户修改
            $password = $fee_rs['str12'];    //请根据自己的账户修改
            $accessCode = $fee_rs['str4'];        //

            $contents = ':您好，您的订单匹配成功,请及时登录查看并处理';
            //此处二次注释yixiyun
            //$this->TXTmsg($accessCode,$spID,$password,$s_tel,$contents);
            $this->send_sms_new($contents, $s_tel);

            //买方
            $map1 = array();
            $map1['id'] = array('in', $bb_id);

            $buy_pei = $cash->where($map1)->select();
            foreach ($buy_pei as $boo) {
                $b_tel = tel($boo['uid']);     //卖方电话
                $content = '接受帮助方' . $boo['user_id'] . '您好，您的订单匹配成功,请及时登录查看并处理';
                ////此处二次注释yixiyun
                //$this->TXTmsg($accessCode,$spID,$password,$b_tel,$content);
                $this->send_sms_new($content, $b_tel);
            }
            unset($buy_pei);
            unset($buy_sell);
            $delw = $cash->where("id=" . $soo['id'])->delete();


            //记录已经匹配的卖方ID
            foreach ($bb_id as $voo) {
                $buyed_id[] = $voo;
            }
            unset($bb_id, $buy_id, $buy_user_id);
        }
        /***********b end*****************/
        unset($buyed_id);
    }

    //手动匹配


    public function matchBuySell()
    {
        //单线程处理，只要有一个人正在处理匹配，其他人就都不需要进行这个工作，直接路过
        $filePath = _ABS_ROOT_ . APP_NAME . '/LockFiles/autoPairSingleProcess.lock';
        if (!file_exists($filePath)) {
            $fp = fopen($filePath, 'w');
            fclose($fp);
        }

        $fp = fopen($filePath, 'r+');
        if (!flock($fp, LOCK_EX | LOCK_NB))
            return;

        $lastTime = fgets($fp);
        $lastTime = intval($lastTime);
        if (time() - $lastTime <= 30) {
            flock($fp, LOCK_UN);
            return;
        }
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, strval(time()));

        set_time_limit(0);
        ignore_user_abort(true);
        $this->lockTable();
        $fee = M('fee');
        $cash = M('cash');
        $fee_rs = $fee->find();
        //进场时间（及多长时间求购记录可以参与匹配）
        $in_time = 24 * 60 * 60 * $fee_rs['s7'] + 60 * 60 * $fee_rs['s13'] + 60 * $fee_rs['s14'];
        $in_time_t = 3600 * $fee_rs['str47'];
        $nowtime = time();
        //找有资格参与匹配的求购记录
        $where = "match_num=0 and is_pay=0 and is_cancel=0 and is_done=0 and type=0 and rdt<=" . ($nowtime - $in_time);
        $cash_brs = $cash->where($where)->field('*')->order("rdt asc")->select();
        //找有资格参与匹配的挂卖记录
        // dump($cash_brs);
        $where = "is_pay=0 and is_cancel=0 and is_done=0 and type=1 and rdt<=" . ($nowtime - $in_time_t);
        $cash_srs = $cash->where($where)->order("money desc,rdt asc")->select();
        //记录已经匹配的卖方ID
        $selled_id = array();

        foreach ($cash_brs as $key => $boo) {

            //寻找是否有对应的挂卖信息
            $ss_id = array();
            $b_money = $boo['money'];  //求购金额
            $sum_s_money = 0;
            $sell_id = array();        //匹配到的挂卖人ID
            $sell_user_id = array();        //匹配到的挂卖人ID

            foreach ($cash_srs as $key => $soo) {
                //若已经匹配成功的ID，跳过本次循环
                // dump($soo['money']);
                // dump($soo['id']);

                $ok = in_array($soo['id'], $selled_id);
                // if($boo['id']==2334){
                // 	dump($ok);
                // 	}
                if ($ok) continue;
                //自己不能和自己匹配
                if ($soo['uid'] == $boo['uid']) continue;

                $s_money = $soo['money'];
                if ($s_money == $b_money) {
                    //重置ID信息
                    $ss_id = array();
                    $sell_id = array();        //匹配到的挂卖人ID
                    $sell_user_id = array();        //匹配到的挂卖人ID
                    $sum_s_money = $s_money;
                    $ss_id[] = $soo['id'];  //挂卖记录id
                    $sell_id[] = $soo['sid'];
                    $sell_user_id[] = $soo['s_user_id'];
                    break;
                    //匹配到一个人的，直接跳出
                } elseif ($s_money < $b_money) {
                    $sum_s_money += $s_money;
                    $sum_s_money += 0;
                    $ss_id[] = $soo['id'];  //挂卖记录id
                    $sell_id[] = $soo['sid'];
                    $sell_user_id[] = $soo['s_user_id'];
                }
                //
                if ($sum_s_money == $b_money) break;
            }

            //若没有足够的挂卖，匹配成功
            if ($sum_s_money < $b_money || $sum_s_money > $b_money) {
                unset($ss_id);

            } else {
                //匹配成功，更新买卖双方信息
                //买方 @is_pay 是否匹配成功 @pdt 匹配时间 @s_id 匹配的挂卖id @s_user_id 匹配的挂卖会员编号
                $s_id = implode(",", $sell_id);
                $s_userid = implode(",", $sell_user_id);
                $match_id = implode(",", $ss_id); //匹配记录ID
                $count_match = count($ss_id);      //匹配到的条数

                $cash->execute("update __TABLE__ set is_pay=1,pdt=" . $nowtime . ",sid='" . $s_id . "',s_user_id='" . $s_userid . "',match_id='" . $match_id . "',match_num=" . $count_match . " where id=" . $boo['id']);

                //卖方 @is_pay 是否匹配成功 @pdt 匹配时间 @b_id 匹配的求购id @b_user_id 匹配的求购会员编号
                $map['id'] = array('in', $ss_id);
                $data['is_pay'] = 1;
                $data['pdt'] = $nowtime;
                $data['bid'] = $boo['bid'];
                $data['b_user_id'] = $boo['b_user_id'];
                $data['match_id'] = $boo['id']; //匹配记录ID
                $cash->where($map)->save($data);

                //匹配成功给买卖双方发短信
                $b_tel = tel($boo['uid']);  //买方电话
                $spID = $fee_rs['s4'];            //请根据自己的账户修改
                $password = $fee_rs['str12'];    //请根据自己的账户修改
                $accessCode = $fee_rs['str4'];        //

                $contents = '您的提供帮助订单' . $boo['x1'] . '匹配成功，请及时登录查看并处理';
//此处二次注释yixiyun
                //	$this->TXTmsg($accessCode,$spID,$password,$b_tel,$contents);
                $this->send_sms_new($contents, $b_tel);

                //卖方
                $map1 = array();
                $map1['id'] = array('in', $ss_id);

                $sell_pei = $cash->where($map1)->select();

                foreach ($sell_pei as $soo) {
                    $s_tel = tel($soo['uid']);     //卖方电话
                    $content = '会员' . $soo['user_id'] . '您好，您的接受帮助订单' . $soo['x1'] . '匹配成功，请及时登录查看并处理';
                    ////此处二次注释yixiyun
                    //$this->TXTmsg($accessCode,$spID,$password,$s_tel,$content);
                    $this->send_sms_new($content, $s_tel);
                }
                unset($sell_pei);
            }

            //记录已经匹配的卖方ID
            foreach ($ss_id as $voo) {
                $selled_id[] = $voo;
            }
            unset($ss_id, $sell_id, $sell_user_id);
        }

        unset($selled_id);

        //已匹配，但有锁定的情况
        $this->spe_matchBuySell();

        $this->unlockTable();
        set_time_limit(30);
        ignore_user_abort(false);
        flock($fp, LOCK_UN);

    }

    //买方确认打款

    private function lockTable()
    {
        if ($this->lockTableFile == NULL) {
            $filePath = _ABS_ROOT_ . APP_NAME . '/LockFiles/vtradeTableLock.lock';
            if (!file_exists($filePath)) {
                $fp = fopen($filePath, 'w');
                fclose($fp);
            }
            $this->lockTableFile = fopen($filePath, 'w');
        }
        flock($this->lockTableFile, LOCK_EX);
    }

    //卖方确认收款
    //@ldt 上次那分红的时间

    public function spe_matchBuySell()
    {

        $fee = M('fee');
        $cash = M('cash');
        $fee_rs = $fee->find();
        //进场时间（及多长时间求购记录可以参与匹配）
        $in_time = 24 * 60 * 60 * $fee_rs['s7'] + 60 * 60 * $fee_rs['s13'] + 60 * $fee_rs['s14'];
        $in_time_t = 3600 * $fee_rs['str47'];
        $nowtime = time();
        //找有资格参与匹配的求购记录
        $where = "match_num>0 and is_pay=0 and is_cancel=0 and is_done=0 and type=0 and rdt<=" . ($nowtime - $in_time);
        $cash_brs = $cash->where($where)->field('*')->order("rdt asc")->select();
        //找有资格参与匹配的挂卖记录
        // dump($cash_brs);exit;
        $where = "is_pay=0 and is_cancel=0 and is_done=0 and type=1 and rdt<=" . ($nowtime - $in_time_t);
        $cash_srs = $cash->where($where)->order("money desc,rdt asc")->select();
        //记录已经匹配的卖方ID
        $selled_id = array();
        if (!$cash_brs) return;
        foreach ($cash_brs as $key => $boo) {
            //寻找是否有对应的挂卖信息

            // $b_money=$boo['money'];  //求购金额
            //已匹配到的金额
            $used_get = $cash->where("id in (" . $boo['match_id'] . ")")->field('sum(money) as a')->find();
            $b_money = $boo['money'] - $used_get['a'];

            $sum_s_money = 0;
            $ss_id = explode(",", $boo['match_id']);
            $sell_id = explode(",", $boo['sid']);//匹配到的挂卖人ID
            $sell_user_id = explode(",", $boo['s_user_id']);//匹配到的挂卖人ID

            foreach ($cash_srs as $key => $soo) {
                //若已经匹配成功的ID，跳过本次循环
                // dump($soo['id']);
                $ok = in_array($soo['id'], $selled_id);
                if ($ok) continue;
                //自己不能和自己匹配
                if ($soo['uid'] == $boo['uid']) continue;

                $s_money = $soo['money'];
                if ($s_money == $b_money) {
                    $sum_s_money = $s_money;
                    $ss_id[] = $soo['id'];  //挂卖记录id
                    $sell_id[] = $soo['sid'];
                    $sell_user_id[] = $soo['s_user_id'];
                    break;
                    //匹配到一个人的，直接跳出
                } elseif ($s_money < $b_money) {
                    $sum_s_money += $s_money;
                    $sum_s_money += 0;
                    $ss_id[] = $soo['id'];  //挂卖记录id
                    $sell_id[] = $soo['sid'];
                    $sell_user_id[] = $soo['s_user_id'];
                } elseif ($s_money > $b_money) {
                    break;
                }
                //
                if ($sum_s_money == $b_money) break;
            }

            //若没有足够的挂卖，匹配成功
            if ($sum_s_money < $b_money || $sum_s_money > $b_money) {
                unset($ss_id);
            } else {
                //匹配成功，更新买卖双方信息
                //买方 @is_pay 是否匹配成功 @pdt 匹配时间 @s_id 匹配的挂卖id @s_user_id 匹配的挂卖会员编号
                $s_id = implode(",", $sell_id);
                $s_userid = implode(",", $sell_user_id);
                $match_id = implode(",", $ss_id); //匹配记录ID
                $count_match = count($ss_id);      //匹配到的条数

                $cash->execute("update __TABLE__ set is_pay=1,pdt=" . $nowtime . ",sid='" . $s_id . "',s_user_id='" . $s_userid . "',match_id='" . $match_id . "',match_num=" . $count_match . " where id=" . $boo['id']);

                //卖方 @is_pay 是否匹配成功 @pdt 匹配时间 @b_id 匹配的求购id @b_user_id 匹配的求购会员编号
                $map['id'] = array('in', $ss_id);
                $data['is_pay'] = 1;
                $data['pdt'] = $nowtime;
                $data['bid'] = $boo['bid'];
                $data['b_user_id'] = $boo['b_user_id'];
                $data['match_id'] = $boo['id']; //匹配记录ID
                $cash->where($map)->save($data);
                //匹配成功给买卖双方发短信
                $b_tel = tel($boo['uid']);  //买方电话
                $spID = $fee_rs['s4'];            //请根据自己的账户修改
                $password = $fee_rs['str12'];    //请根据自己的账户修改
                $accessCode = $fee_rs['str4'];        //

                $content = '您好，您的订单匹配成功,请及时登录查看并处理';
//此处二次注释yixiyun
                //$this->TXTmsg($accessCode,$spID,$password,$b_tel,$content);
                //   $this->send_sms_new($content, $b_tel);


                //卖方
                $map1 = array();
                $map1['id'] = array('in', $ss_id);

                $sell_pei = $cash->where($map1)->select();
                foreach ($sell_pei as $soo) {
                    $s_tel = tel($soo['uid']);     //卖方电话

                    $content = '您好，您的订单匹配成功,请及时登录查看并处理';
                    //此处二次注释yixiyun
                    //$this->TXTmsg($accessCode,$spID,$password,$s_tel,$content);
                    //  $this->send_sms_new($content, $s_tel);
                }
                unset($sell_pei);
            }

            //记录已经匹配的卖方ID
            foreach ($ss_id as $voo) {
                $selled_id[] = $voo;
            }
            unset($ss_id, $sell_id, $sell_user_id);
        }
        unset($selled_id);

    }


    //取消求购记录

    private function unlockTable()
    {
        if ($this->lockTableFile != NULL) {
            flock($this->lockTableFile, LOCK_UN);
            $this->lockTableFile = NULL;
        }
    }

    //生成会员编号

    public function manaul_match()
    {

        if ($_SESSION['UrlPTPass'] == 'MyssBaiGuoJS') {

            $fee_rs = M('fee')->field('s4,str12,str4')->find();
            $spID = $fee_rs['s4'];            //请根据自己的账户修改
            $password = $fee_rs['str12'];    //请根据自己的账户修改
            $accessCode = $fee_rs['str4'];        //


            $inCode = $_POST['inCode'];
            $outCode = $_POST['outCode'];
            if (count($outCode) != count(array_unique($outCode))) {
                $this->error("有重复订单!");
                exit;
            }


            $cash = M('cash');
            $zm = 0;
            $fck = M('fck');
            $in_rs = $cash->where("type=0 and is_pay=0 and is_cancel=0 and x1='" . $inCode . "'")->find();

            $whlocks['id'] = $in_rs['uid'];
            $whlocks['is_lock'] = 1;
            $locs = $fck->where($whlocks)->find();
            if ($locs) {
                $this->error("账号存在但无效，可能被锁定");
            }


//$cash->where('id='.$in_rs['id'])->delete();

            foreach ($outCode as $yixiwei) {
                $out_rs[] = $cash->where("type=1 and is_pay=0 and is_cancel=0 and x1='" . $yixiwei . "'")->find();
                if (empty($out_rs[0])) {
                    $this->error("该订单已匹配或不存在！");
                    exit;
                }
            }


            if (empty($inCode)) {
                $this->error("请输进场订单编号！");
                exit;
            }
            if (empty($in_rs)) {
                $this->error("该进场订单已匹配或不存在！");
                exit;
            }
            for ($yi = 0; $yi < count($out_rs); $yi++) {
                $zm = $zm + $out_rs[$yi]['money'];
                if ($yi == 0) {
                    $yiid = $out_rs[$yi]['uid'];
                    $syiid = $out_rs[$yi]['s_user_id'];
                    $myiid = $out_rs[$yi]['id'];

                    $whlock['id'] = $out_rs[$yi]['uid'];
                    $whlock['is_lock'] = 1;
                    $loc = $fck->where($whlock)->find();
                    if ($loc) {
                        $this->error("账号存在但无效，可能被锁定");
                    }

                } else {
                    $yiid .= ',' . $out_rs[$yi]['uid'];
                    $syiid .= ',' . $out_rs[$yi]['s_user_id'];
                    $myiid .= ',' . $out_rs[$yi]['id'];
                }
            }


            $num = count($out_rs);

            if ($in_rs['money'] != $zm) {
                $this->error("金额不匹配，请重新选择！");
                exit;
            }
            for ($yi = 0; $yi < count($out_rs); $yi++) {
                if ($in_rs['uid'] == $out_rs[$yi]['uid']) {
                    echo("同一会员的订单不能进行匹配！");
                    exit;
                }
            }


            $nowtime = time();
            if ($in_rs) {
                $resuelt = $cash->execute("update __TABLE__ set is_pay=1,pdt=" . $nowtime . ",sid='" . $yiid . "',s_user_id='" . $syiid . "',match_id='" . $myiid . "',match_num=" . $num . " where id=" . $in_rs['id']);
            }

            $isSendInCode = false;
            for ($yi = 0; $yi < count($out_rs); $yi++) {
                if ($out_rs[$yi]) {
                    $resuelt2 = $cash->execute("update __TABLE__ set is_pay=1,pdt=" . $nowtime . ",bid='" . $in_rs['uid'] . "',b_user_id='" . $in_rs['b_user_id'] . "',match_id='" . $in_rs['id'] . "' where id=" . $out_rs[$yi]['id']);

                    $user_idj = $out_rs[$yi]['user_id'];
                    $s_telj['user_id'] = $out_rs[$yi]['user_id'];    //mai方电话
                    $tels = $fck->where($s_telj)->getField('user_tel');
                    $content = '您好，您的订单[' . $out_rs[$yi]["x1"] . ']匹配成功,请及时登录查看并处理';
                    //$this->TXTmsg($accessCode,$spID,$password,$tels,$content);
                    $this->send_sms_new($content, $tels);
                }

                if(!$isSendInCode){
                    $s_tels['user_id'] = $in_rs['user_id'];    //卖方电话
                    $tel = $fck->where($s_tels)->getField('user_tel');
                    $contentIn = '您好，您的订单[' . $inCode . ']匹配成功,请及时登录查看并处理';
                    //$this->TXTmsg($accessCode,$spID,$password,$tel,$contentss);
                    $this->send_sms_new($contentIn, $tel);
                    $isSendInCode = true;
                }
                if ($resuelt && $resuelt2) {


                } else {
                    $this->error("错误");
                    exit;
                }
            }


            $bUrl = __APP__ . '/YouZi/adminclearing';
            $this->_box(1, '匹配成功！', $bUrl, 1);
        }
    }

    public function confirm_pay()
    {
        $t_id = $_POST['t_id']; //订单ID
        $fee = M('fee');
        $cash = M('cash');
        $fck = D('Fck');
        $where['id'] = $t_id;
        $nowtime = time();
        $b_rs = $cash->where($where)->find();

        //打款间隔超过时间动态余额清0或期间动态奖金清0
        $nowt = time();
        $fee4 = M('fee');
        $fee_rs4 = $fee4->field('*')->find();

        if ($fee_rs4['i47']) {
            $ge = $nowt - 3600 * 24 * $fee_rs4['i47'];

            $mm = M('cash')->where("id=" . $_SESSION[C('USER_AUTH_KEY')] . "and is_get=1 and is_cancel=0 and bdt<" . $ge)->order('bdt DESC')->limit(1)->find();

            if ($mm) {

                $tt = M('chistory')->where("did=" . $_SESSION[C('USER_AUTH_KEY')] . "and time<" . $ge . "and action_type=1")->field("sum(money) as a")->find();
                $whe['id'] = $_SESSION[C('USER_AUTH_KEY')];
                $mdo = $fck->where($whe)->field('*')->find();

                //$dats['agent_use']= $mdo['agent_use']-$tt['a'];
                $dats['agent_use'] = 0;

                $fck->where($whe)->save($dats);

            }
        }


        //7天结束

        $subMatchIds = [];
        if ($b_rs) {

            /*多对多匹配start*/
            /*$subMatchIds = $cash->where(["parent_id" => $b_rs["id"]])->field("x1,bz,match_id,sid,bid")->select();
            if($subMatchIds){
                foreach($subMatchIds as $subInOrder){
                    if($subInOrder['match_id']){
                        $b_rs['match_id'] = $b_rs["match_id"] . "," . $subInOrder['match_id'];
                    }
                }
            }*/
            /*多对多匹配end*/
            /*for($i = 0; $i < 10; $i++) {
                if ($i == 0) {
                    $subMatchIds = $cash->where(["bz" => $b_rs["x1"]])->field("x1,bz,match_id,sid,bid")->find();
                    if ($subMatchIds) {
                        if (!empty($b_rs["match_id"])) {
                            $b_rs['match_id'] = $b_rs["match_id"] . "," . $subMatchIds['match_id'];
                        }
                    }
                }else{
                    if(empty($subMatchIds)){
                        continue;
                    }
                    $subMatchIds = $cash->where(["bz" => $subMatchIds["x1"]])->field("x1,bz,match_id,sid,bid")->find();
                    if ($subMatchIds) {
                        if (!empty($b_rs["match_id"])) {
                            $b_rs['match_id'] = $b_rs["match_id"] . "," . $subMatchIds['match_id'];
                        }
                    }
                }
            }*/
            /*多对多匹配end*/
            //将match_id里对应的订单，收款状态IS_GET改为1；
            $match_id = explode(",", $b_rs['match_id']);
            $map['id'] = array('in', $match_id);
            $data = array();
            $data['is_get'] = 1;
            $resuelt = $cash->where($map)->save($data);
            if ($resuelt) {
                $cash->where($where)->save($data);  //买家状态改为已支付
                //已支付之后，开始计算利息
                // //计算奖金
                // $fck->getusjj_new($t_id,$b_rs['uid'],$nowtime);
                //给卖方发送短信
                $sell_pei = $cash->where($map)->select();
                $fee_rs = M('fee')->field('s4,str12,str4')->find();
                $spID = $fee_rs['s4'];            //请根据自己的账户修改
                $password = $fee_rs['str12'];    //请根据自己的账户修改
                $accessCode = $fee_rs['str4'];        //
                foreach ($sell_pei as $soo) {
                    $s_tel = tel($soo['uid']);     //卖方电话

                    $content = '您好，您的订单匹配成功,请及时登录查看并处理';
                    //$this->TXTmsg($accessCode,$spID,$password,$s_tel,$content);
                    $this->send_sms_new($content, $s_tel);
                }
                unset($sell_pei);
                //$this->redirect('/Public/main');
                echo "<script> alert('确认成功！对方确认收款并冻结期结束后，本期本息即可提现。'); location.href = document.referrer; </script>";
            } else {
                $this->error("操作失败");
                exit;
            }
        }
    }

    public function confirm_get()
    {
        //$l_time=strtotime(date("Y-m-d H:i:00"));

        $t_id = $_REQUEST['p_id']; //订单ID
        $url = $_GET['url']; //订单ID
        $is_ts = $_REQUEST['is_ts'];

        $fee = M('fee');
        $cash = M('cash');
        $fck = D('Fck');
        $nowtime = time();
        $where['id'] = $t_id;
        $where['is_done'] = 0;
        $data = array();
        $data['is_get'] = 1;

        if ($_REQUEST['is_ts'] == '1') {
            $data['is_ts'] = 1;

            $data['is_done'] = 0;
            //$data['okdt']=$nowtime;


        } else {
            $data['is_ts'] = '2';

            $data['is_done'] = 1;
            $data['okdt'] = $nowtime;
        }


        $s_rs = $cash->where($where)->find();
        if ($s_rs) {
            $cash->where($where)->save($data);  //卖家状态改为确认收款
            //将match_id里对应的订单，收款状态finish_num+1
            $match_id = $s_rs['match_id'];
            $map['id'] = $match_id;
            $resuelt = $cash->where($map)->find();
            // dump($match_id);exit;
            if ($resuelt) {
                //已经确认收款的条数
                $finish_num = $resuelt['finish_num'];
                //需要确认的条数
                $match_num = $resuelt['match_num'];
                //更新买家记录
                if ($_REQUEST['is_ts'] != '1') {
                    $cash->execute("update __TABLE__ set finish_num=finish_num+1  where id=" . $resuelt['id']);
                    //确认完成
                    if ($finish_num + 1 == $match_num) {

                        //更新买家状态

                        $cash->execute("update __TABLE__ set is_ts=2,is_done=1,is_get=1,okdt={$nowtime} where id=" . $resuelt['id']);
                        //更新买家领导奖代数级别(买币最高的一个)
                        $fck->execute('update __TABLE__ set get_level=' . $resuelt['money_key'] . " where get_level<" . $resuelt['money_key'] . " and id=" . $resuelt['uid']);
//jia 100
                        $jtj = $fck->where('id=' . $resuelt['uid'])->getfield('agent_cash');
//$jtj+=100;

                        $fck->execute('update __TABLE__ set agent_cash=' . $jtj . ' where id=' . $resuelt['uid']);
                        $fck->execute('update __TABLE__ set yuan_gupiao=1,agent_cash=' . $jtj . ' where id=' . $resuelt['uid']);

                        // dump($fck);exit;
                        //扣除买家对于币种
                        //对应的所有卖家记录
                        $finishBuy = $cash->where("is_cancel=0 and is_pay=1 and id in (0," . $resuelt['match_id'] . ",0)")->select();
                        // dump($finishBuy);exit;
                        //计算奖金
                        $fck->getusjj_new($resuelt['id'], $resuelt['uid'], $nowtime);
                        foreach ($finishBuy as $koo) {
                            $money_type = $koo['money_type'];  //挂卖币种类型
                            $use_mm = $koo['money'];
                            $uid = $koo['uid'];
                            $bid = $koo['bid'];
                            if ($money_type == 1) {
                                //货币流向
                                $fck->addCashhistory($uid, -$use_mm, 23, "接受帮助", 1, $koo["id"]);
                                //货币流向
                                $fck->addCashhistory($bid, $use_mm, 23, "本金", 1, $koo["match_id"]);
                                //
                                $fck->execute("update __TABLE__ set agent_cash=agent_cash-" . $use_mm . " where id=" . $uid);
                                $fck->execute("update __TABLE__ set agent_cash=agent_cash+" . $use_mm . " where id=" . $bid);

                            } elseif ($money_type == 2) {
                                //货币流向
                                $fck->addCashhistory($uid, -$use_mm, 23, "接受帮助", 2, $koo["id"]);
                                //货币流向
                                $fck->addCashhistory($bid, $use_mm, 23, "本金", 1, $koo["match_id"]);
                                $fck->execute("update __TABLE__ set agent_use=agent_use-" . $use_mm . " where id=" . $uid);
                                $fck->execute("update __TABLE__ set agent_cash=agent_cash+" . $use_mm . " where id=" . $bid);

                            }

                            //已经扣除金币的记录，x2记为1
                            $cash->execute("update __TABLE__ set is_buy=1 where id=" . $koo['id']);
                        }
                    }
                }
                if ($url == 1) {
                    // $this->redirect('YouZi/adminClearing');
                    //$bUrl = __APP__.'/YouZi/adminClearing';
                    //$this->_box(1,'已完成确认！',$bUrl,1);
                    echo "<script> alert('确认成功！'); location.href = document.referrer; </script>";
                    exit;
                } else {
                    //$this->redirect('/Public/main');
                    echo "<script> alert('确认成功！'); location.href = document.referrer; </script>";
                }

            } else {
                $this->error("操作失败");
                exit;
            }
        } else {

            $this->error("已完成确认");
            exit;
        }
    }

    public function Delect()
    {
        $fck = M('fck');
        $cash = M('cash');
        $t_id = $_REQUEST['del_id'];
        $url_type = $_REQUEST['url_type'];
        $cash = M('cash');
        $orderTime = $cash->where("id=" . intval($t_id))->field("rdt, type")->find();
        if ((time() - $orderTime["rdt"]) >= 24 * 3600) {
            $this->error("无法取消该记录,排队日期超过一天了");
            exit;
        }

        //找有资格为匹配的记录
        if ($url_type == 1) {
            $where = "is_done=0 and id=" . $t_id;
        } else {
            $where = "is_pay=0 and is_get=0 and is_done=0 and id=" . $t_id;
        }
        //若删除的是业绩挂卖的，扣除掉挂卖上限
        $cash_rs = $cash->where($where)->field("uid,money,money_type")->find();
        if ($cash_rs['money_type'] == 2) {
            //减少挂卖金额
            $fck->execute("update __TABLE__ set xx_money=xx_money-" . $cash_rs['money'] . " where id=" . $cash_rs['uid']);
        }
        $resuelt = $cash->where($where)->delete();
        if ($resuelt) {
            if ($url_type == 1) {
                $this->redirect('YouZi/adminclearing');
            } else {
                $this->redirect('Public/main');
            }

        } else {
            $this->error("无法取消该记录");
            exit;
        }
    }

    public function uploadImg()
    {
        if ($_FILES) {
            $name = $_FILES['filename']['name'];
            switch ($_FILES['filename']['type']) {

                case 'image/jpeg':
                    $ext = 'jpg';
                    break;
                case 'image/gif':
                    $ext = 'gif';
                    break;
                case 'image/png':
                    $ext = 'png';
                    break;
                case 'image/tiff':
                    $ext = 'tif';
                    break;
                default:
                    $ext = '';
                    break;
            }
            if ($ext) {
                $tid = $_GET['tid'];
                $tt = $_GET['t_id'];
                if (empty($tid)) {
                    $this->error("订单不存在或未匹配");
                    exit;
                }
                $n = $tid . "image.$ext";
                $URL = './Public/zh_cn/Uploads/' . $n;
                @chmod($URL, 0777);
                //上传图片
                $a = move_uploaded_file($_FILES['filename']['tmp_name'], $URL);

                $ossUrl = $this->uploadOss($URL, $ext);
                unlink($URL);
                //保存图片路径
                M('cash')->execute("update __TABLE__ set img='" . $ossUrl . "' where id=" . $tid);
                // echo "Uploaded image '$name' as '$URL':<br />";
                // $a="c165/Public/zh_cn/Uploads/bg001.jpg";
                // echo "<img src='$a' />";
                if ($a) {
                    $bUrl = '__APP__/Public/mx/id/' . $tid;
                    $this->_boxx2(1, '图片上传成功', $bUrl, 1);
                    exit;
                } else {
                    $this->error("图片上传失败");
                    exit;
                }

            } else {
                $this->error("暂不支持该图片类型上传");
                exit;
            }
        }
    }
    public function uploadOss($tmp, $ext){
        $accessKeyId = "LTAIJsqEW8dBGNra"; ;
        $accessKeySecret = "JHcytRSsKFNFQjdehOLj0TNCKL6wU8";
        $endpoint = "maimaibao.oss-cn-hongkong.aliyuncs.com";
        $endpoint = "http://oss-cn-qingdao.aliyuncs.com";
        try {
            $ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint, true);
        } catch (OssException $e) {
            print $e->getMessage();
        }
        $bucket = "imagebackend";
        $name = md5(uniqid());
        $object = "order/" . $name . "." . $ext;
        try {
            //$ossClient->putObject($bucket, $object, $tmp);
            $url = $ossClient->uploadFile($bucket, $object, $tmp);
            return $object;
        } catch (OssException $e) {
            print $e->getMessage();
        }
    }
}

?>
