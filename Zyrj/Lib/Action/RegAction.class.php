﻿<?php

class RegAction extends CommonAction
{
    function _initialize()
    {
        parent::_initialize();
        $this->_inject_check(0);//调用过滤函数
        $this->_Config_name();
        header("Content-Type:text/html; charset=utf-8");
    }

    /**
     * 会员注册
     * **/
    public function users($Urlsz = 0)
    {
        $this->_checkUser();
        $fck = M('fck');
        $fee = M('fee');
        $RID = (int)$_GET['RID'];
        $FID = (int)$_GET['FID'];
        $TP = (int)$_GET['TPL'];
        if (empty($TPL)) $TPL = 0;
        $TPL = array();
        for ($i = 0; $i < 5; $i++) {
            $TPL[$i] = '';
        }
        if ($FID <= 0) {
            //自动找点（规则：从左到右，从上到下）
            $curLayIDsArr = array();
            $curLayIDsArr[0] = intval($_SESSION[C('USER_AUTH_KEY')]);
            $exitFind = false;
            $nodeNum = 2;
            while (!$exitFind) {
                $nextLayIDsArr = array();
                foreach ($curLayIDsArr as $xid) {
                    $subUserArr = M('Fck')->field('id,treeplace,is_pay')->where(array('father_id' => array('eq', $xid)))->select();
                    if (count($subUserArr) < $nodeNum) {
                        $ePosArr = array();
                        foreach ($subUserArr as $user) {
                            $ePosArr[] = intval($user['treeplace']);
                        }
                        for ($ix = 0; $ix <= $nodeNum; $ix++) {
                            if (!in_array($ix, $ePosArr)) {
                                $FID = $xid;
                                $TP = $ix;
                                $exitFind = true;
                                break;
                            }
                        }
                    } else {
                        foreach ($subUserArr as $user) {
                            if ($user['is_pay'] > 0)
                                $nextLayIDsArr[] = intval($user['id']);
                        }
                    }
                }
                if (count($nextLayIDsArr) == 0)
                    break;
                else
                    $curLayIDsArr = $nextLayIDsArr;
            }
        }
        unset($curLayIDsArr, $nextLayIDsArr);
        $TPL[$TP] = 'selected="selected"';

        //===受理中心
        $zzc = array();
        $where = array();
        $where['id'] = $_SESSION[C('USER_AUTH_KEY')];
        $field = 'user_id,is_agent,agent_cash,shop_name';
        $rs = $fck->where($where)->field($field)->find();
        $money = $rs['agent_cash'];
        $mmuserid = $rs['user_id'];
        if ($rs['is_agent'] >= 2) {
            $zzc[1] = $rs['user_id'];
        } else {
            //$mrs = M('fck')->where('id=1')->field('id')->find();
            $zzc[1] = $rs['shop_name'];
        }
        $this->assign('myid', $_SESSION[C('USER_AUTH_KEY')]);

        //===推荐人
        $where['id'] = $RID;
        $field = 'user_id,is_agent';
        $rs = $fck->where($where)->field($field)->find();
        if ($rs) {
            $zzc[2] = $rs['user_id'];
        } else {
            $zzc[2] = $mmuserid;
        }
        $zzc[2] = $mmuserid;

        //===接点人
        $where['id'] = $FID;
        $field = 'user_id,is_agent';
        $rs = $fck->where($where)->field($field)->find();
        if ($rs) {
            $zzc[3] = $rs['user_id'];
        } else {
            $zzc[3] = '';
        }

        $arr = array();
        $arr['UserID'] = $this->_getUserID();
        $this->assign('flist', $arr);


        $card = M('card');
        //$Code = trim($_POST['zhucema']);  //获取诚信券
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $map['is_use'] = 0;
        //  $c_num=$money/1000;
        $res = M('card')->where($map)->field('id,card_no')->group('id')->select();

        $res_count = count($res);
        $this->assign('res_count', $res_count);

        $pwhere = array();
        $product = M('product');
        $prs = $product->where($pwhere)->select();
        $this->assign('plist', $prs);


        $fee_s = $fee->field('s10,s9,str29,str99,s5,s11')->find();
        $regLevel = explode('|', $fee_s['s10']);
        $regMoney = explode('|', $fee_s['s9']);

        //输出银行
        $bank = explode('|', $fee_s['str29']);

        $wentilist = explode('|', $fee_s['str99']);

        $this->assign('bank', $bank);
        $this->assign('regLevel', $regLevel);
        $this->assign('regMoney', $regMoney);
        $this->assign('wentilist', $wentilist);

        unset($bank, $Level, $$Level);

        $this->assign('TPL', $TPL);
        $this->assign('zzc', $zzc);
        $this->assign('feeArr', $fee_s);

        unset($fck, $TPL, $where, $field, $rs, $data_temp, $temp_rs, $rs);

        $v_title = $this->theme_title_value();
        $this->distheme('users', $v_title[5]);
    }

    /**
     * 注册确认
     * **/
    public function usersConfirm()
    {
        $this->_checkUser();
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck = M('fck');
        $rs = $fck->field('is_pay,agent_cash')->find($id);
        if ($rs['is_pay'] == 0) {
            $this->error(xstr('hint108'));
            exit;
        }
        if (strlen($_POST['UserID']) < 1) {
            $this->error(xstr('hint109'));
            exit;
        }
        $this->assign('UserID', $_POST['UserID']);

        $data = array();  //创建数据对象
        $shopid = trim($_POST['shopid']);  //所属受理中心帐号
        if (empty($shopid)) {
            $this->error(xstr('hint110'));
            exit;
        }
        $smap = array();
        $smap['user_id'] = $shopid;
        $smap['is_agent'] = array('gt', 1);
        $shop_rs = $fck->where($smap)->field('id,user_id')->find();
        if (!$shop_rs) {
            $this->error(xstr('hint111'));
            exit;
        }
        $this->assign('shopid', $shopid);
        unset($smap, $shop_rs, $shopid);

        //检测推荐人
        $RID = trim($_POST['RID']);  //获取推荐会员帐号
        $mapp = array();
        $mapp['user_id'] = $RID;
        $mapp['is_pay'] = array('gt', 0);
        $authInfoo = $fck->where($mapp)->field('id,user_id,re_level,re_path')->find();
        if ($authInfoo) {
            $this->assign('RID', $RID);
            $data['re_id'] = $authInfoo['id'];
        } else {
            $this->error(xstr('hint112'));
            exit;
        }
        unset($authInfoo, $mapp);

//		//检测上节点人
        $FID = trim($_POST['FID']);  //上节点帐号
        $mappp = array();
        $mappp['user_id'] = $FID;
        $authInfoo = $fck->where($mappp)->field('id,p_path,p_level,user_id,is_pay,tp_path')->find();
        if ($authInfoo) {
            $this->assign('FID', $FID);
            $fatherispay = $authInfoo['is_pay'];
            $data['father_id'] = $authInfoo['id'];                        //上节点ID
            $tp_path = $authInfoo['tp_path'];
        } else {
            $this->error(xstr('hint113'));
            exit;
        }
        unset($authInfoo, $mappp);
        $TPL = (int)$_POST['TPL'];
        $where = array();
        $where['father_id'] = $data['father_id'];
        $where['treeplace'] = $TPL;
        $rs = $fck->where($where)->field('id')->find();
        if ($rs) {
            $this->error(xstr('hint114'));
            exit;
        }
        /*
        if($TPL==0){
            $zy_n = "左区";
        }elseif($TPL==1){
            $zy_n = "右区";
        }else{
            $TPL = 0;
            $zy_n = "左区";
        }
        $this->assign('zy_n',$zy_n);
        $this->assign('TPL',$TPL);
        */

        if ($fatherispay == 0)//&&$TPL>0)
        {
            $this->error(xstr('hint115'));
            exit;
        }

//		$renn = $fck->where('re_id='.$data['re_id'].' and is_pay>0')->count();
//		if($renn<1){
//			$tjnn = $renn+1;
//			if($renn==0){
//				$oktp = 0;
//				$errtp = "左区";
//			}
//			$zz_id = $this->pd_left_us($data['re_id'],$oktp);
//			$zz_rs = $fck->where('id='.$zz_id)->field('id,user_id')->find();
//			if($zz_id!=$data['father_id']){
//				$this->error('推荐第'.$tjnn.'人必须放在'.$zz_rs['user_id'].'的'.$errtp.'！');
//				exit;
//			}
//			if($TPL!=$oktp){
//				$this->error('推荐第'.$tjnn.'人必须放在'.$zz_rs['user_id'].'的'.$errtp.'！');
//				exit;
//			}
//		}
        unset($rs, $where, $TPL);

        $fwhere = array();//检测帐号是否存在
        $fwhere['user_id'] = trim($_POST['UserID']);
        $frs = $fck->where($fwhere)->field('id')->find();
        if ($frs) {
            $this->error(xstr('user_uid_is_exists'));
            exit;
        }
        $kk = stripos($fwhere['user_id'], '-');
        if ($kk) {
            $this->error(xstr('hint116'));
            exit;
        }
        unset($fwhere, $frs);

        $errmsg = "";
        if (empty($_POST['wenti_dan'])) {
            $errmsg .= "<li>" . xstr('hint117') . "</li>";
        }
        $this->assign('wenti_dan', $_POST['wenti_dan']);
        if (empty($_POST['BankCard'])) {
            $errmsg .= "<li>" . xstr('hint118') . "</li>";
        }
        $this->assign('BankCard', $_POST['BankCard']);
        $huhu = trim($_POST['UserName']);
        if (empty($huhu)) {
            $errmsg .= "<li>" . xstr('hint119') . "</li>";
        }
        $this->assign('UserName', $_POST['UserName']);
        if (empty($_POST['UserCode'])) {
            $errmsg .= "<li>" . xstr('hint120') . "</li>";
        }
        $this->assign('UserCode', $_POST['UserCode']);
        if (empty($_POST['UserTel'])) {
            $errmsg .= "<li>" . xstr('hint121') . "</li>";
        }
        $this->assign('UserTel', $_POST['UserTel']);
        if (empty($_POST['qq'])) {
            $errmsg .= "<li>" . xstr('hint122') . "</li>";
        }
        $this->assign('qq', $_POST['qq']);
        if (empty($_POST['BankAddress'])) {
            $errmsg .= "<li>" . xstr('hint123') . "</li>";
        }
        $this->assign('BankAddress', $_POST['BankAddress']);

        $usercc = trim($_POST['UserCode']);
        $this->assign('UserCode', $_POST['UserCode']);

        if (strlen($_POST['Password']) < 1 or strlen($_POST['Password']) > 16 or strlen($_POST['PassOpen']) < 1 or strlen($_POST['PassOpen']) > 16) {
            $this->error(xstr('hint124'));
            exit;
        }
        if ($_POST['Password'] != $_POST['rePassword']) {  //一级密码
            $this->error(xstr('hint125'));
            exit;
        }
        if ($_POST['PassOpen'] != $_POST['rePassOpen']) {  //二级密码
            $this->error(xstr('hint126'));
            exit;
        }
        if ($_POST['Password'] == $_POST['PassOpen']) {  //二级密码
            $this->error(xstr('hint127'));
            exit;
        }
        $this->assign('Password', $_POST['Password']);
        $this->assign('PassOpen', $_POST['PassOpen']);

// 		$us_name = $_POST['us_name'];
// 		$us_address = $_POST['us_address'];
// 		$us_tel = $_POST['us_tel'];
// 		if(empty($us_name)){
// 			$errmsg.="<li>".xstr('hint128')."</li>";
// 		}
// 		if(empty($us_address)){
// 			$errmsg.="<li>".xstr('hint129')."</li>";
// 		}
// 		if(empty($us_tel)){
// 			$errmsg.="<li>".xstr('hint130')."</li>";
// 		}
// 		$this->assign('us_name',$_POST['us_name']);
// 		$this->assign('us_address',$_POST['us_address']);
// 		$this->assign('us_tel',$_POST['us_tel']);

        $s_err = "<ul>";
        $e_err = "</ul>";
        if (!empty($errmsg)) {
            $out_err = $s_err . $errmsg . $e_err;
            $this->error($out_err);
            exit;
        }


        $uLevel = $_POST['u_level'];
        $this->assign('u_level', $_POST['u_level']);
        $fee = M('fee')->find();
        $s = $fee['s9'];
        $s10 = explode('|', $fee['s10']);
        $this->assign('uarray', $s10);
        $s9 = explode('|', $fee['s9']);
        $u_money = $s9[$uLevel];

// 		//======产品========
// 		$product = M ('product');
// 		$ydate = time();
// 		$cpid = $_POST['uid'];//所以产品的ID
// 		if (empty($cpid)){
// 			$this->error(xstr('hint131'));
// 			exit;
// 		}
// 		$pro_where = array();
// 		$pro_where['id'] = array ('in',$cpid);
// 		$pro_rs = $product->where($pro_where)->field('id,a_money,name')->select();
// 		$cpmoney = 0;//产品总价
// 		$cparray = array();
// 		$txt = "";
// 		$cpi = 0;
// 		foreach ($pro_rs as $pvo){
// 			$aa = "shu".$pvo['id'];
// 			$cc = (int)$_POST[$aa];
// 			if ($cc >0) {
// 				$cpmoney = $cpmoney + $pvo['a_money'] * $cc;
// 				$txt .= $pvo['id'] .',';
// 				$cparray[$cpi]['id'] = $pvo['id'];
// 				$cparray[$cpi]['a_money'] = $pvo['a_money'];
// 				$cparray[$cpi]['name'] = $pvo['name'];
// 				$cparray[$cpi]['buynub'] = $cc;
// 				$cpi++;
// 			}
// 		}
// 		unset($product,$pro_rs);
// 		$this->assign('cparray',$cparray);//产品
// 		if($cpmoney!=$u_money){
// 			$this->error(xstr('hint132'));
// 			exit;
// 		}
// 		$this->assign('cpmoney',$cpmoney);
// 		//======产品END=====

        $this->assign('BankName', $_POST['BankName']);
        $this->assign('BankProvince', $_POST['BankProvince']);
        $this->assign('BankCity', $_POST['BankCity']);
        $this->assign('BankAddress', $_POST['BankAddress']);

        $this->assign('UserAddress', $_POST['UserAddress']);
        $this->assign('qq', $_POST['qq']);

        $this->display();

    }

    /**
     * 注册处理
     * **/
    public function usersAdd()
    {
        $this->_checkUser();
        $Mcode = I('Mcode');
        if (strcmp($Mcode, $_SESSION['Mcode']) != 0 || empty($Mcode)) {
            $this->error('手机验证密码错误');
        }
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck = D('Fck');  //注册表
        $fee = M('fee')->find();

        $rs = $fck->field('is_pay,agent_cash')->find($id);
        $m = $rs['agent_cash'];
        if ($rs['is_pay'] == 0) {
            $this->error(xstr('hint108'));
            exit;
        }
        if (strlen($_POST['UserID']) < 1) {
            $this->error(xstr('hint109'));
            exit;
        }

        //检测注册人数
        $start_time = strtotime(date('Y-m-d')) - 1;
        $end_time = strtotime(date('Y-m-d')) + 24 * 60 * 60;
        $count_rg = $fck->where("pdt>" . $start_time . " and pdt<" . $end_time)->count();

        if ($count_rg > $fee['str10']) {
            $this->error("系统当前注册人数已超上限，请明日及时注册");
            exit;
        }


        $data = array();  //创建数据对象
        //检测受理中心
        $shopid = trim($_POST['shopid']);  //所属受理中心帐号
        if (empty($shopid)) {
            $this->error(xstr('hint110'));
            exit;
        }
        $smap = array();
        $smap['user_id'] = $shopid;
        $smap['is_agent'] = array('gt', 1);
        $shop_rs = $fck->where($smap)->field('id,user_id')->find();
        if (!$shop_rs) {
            $this->error(xstr('hint111'));
            exit;
        } else {
            $data['shop_id'] = $shop_rs['id'];      //隶属会员中心编号
            $data['shop_name'] = $shopid; //隶属会员中心帐号
        }
        unset($smap, $shop_rs);

        //检测推荐人
        $RID = trim($_POST['RID']);  //获取推荐会员帐号
        $mapp = array();
        $mapp['user_id'] = $RID;
        $mapp['is_pay'] = array('gt', 0);
        $authInfoo = $fck->where($mapp)->field('id,user_id,re_level,re_path')->find();
        if ($authInfoo) {
            $data['re_path'] = $authInfoo['re_path'] . $authInfoo['id'] . ',';  //推荐路径
            $data['re_id'] = $authInfoo['id'];                              //推荐人ID
            $data['re_name'] = $authInfoo['user_id'];                       //推荐人帐号
            $data['re_level'] = $authInfoo['re_level'] + 1;                 //代数(绝对层数)
            $REID = $authInfoo['id'];
        } else {
            $this->error(xstr('hint112'));
            exit;
        }
        unset($authInfoo, $mapp);

        //检测入场券
        $card = M('card');
        $Code = trim($_POST['zhuce']);  //获取入场券
        $mapp = array();
        $mapp['card_no'] = $Code;
        $mapp['is_use'] = array('eq', 0);
        //$mapp['buser_id']	    = array('eq',$shopid);
        $authInfoo = $card->where($mapp)->field('*')->find();
        if (!$authInfoo) {
            //	$this->error("注册码不存在或已被使用");
            //exit;
        }

        unset($authInfoo, $mapp);


        /*

        //注册激活码，消耗20张门票
        $card=M('card');
                //$Code = trim($_POST['zhucema']);  //获取诚信券
              $map['buser_id']=$_SESSION['loginUseracc'];
              $map['is_use']=0;
              $c_num=20;
              $res=M('card')->where($map)->field('id,card_no')->group('id')->order('id asc')->limit($c_num)->select();

              $res_count=count($res);
              if($res_count<$c_num){
                          $this->error('注册激活需要20张门票，您的门票数不足，请先购买',U('/Change/FrontCode'));
              }
              /*
              if(!$res=M('card')->where($map)->field('card_no')->order('id asc')->find()){
                $this->error('您的门票已经使用完，请先购买',U('/Change/FrontCode'));
              }
              */
        /*

       $code_arr=array();

       foreach($res as $k=>$v){

          $code_arr[]=$v['id'];

          $Code=$res[$k]['card_no'];
          $mapp  = array();
          $mapp['card_no']	= $Code;
          $mapp['is_use']	    = array('eq',0);

      //	$mapp['buser_id']	    = array('eq',$buhu);
          $authInfoo = $card->where($mapp)->field('*')->find();
          if (!$authInfoo){
              $this->error("门票不存在或已被使用，或者越权使用");
              exit;
          }
       }
  $card_where['id']=array('in',$code_arr);
              $card_data['b_time']=time();
              $card_data['is_use']=1;
              $card_data['bid']=$fck_rs['id'];
              $card_data['buser_id']=$fck_rs['user_id'];
              $card->where($card_where)->save($card_data);

  */
// 注册 消耗门票结束 

        $fwhere = array();//检测帐号是否存在
        $fwhere['user_id'] = trim($_POST['UserID']);
        $frs = $fck->where($fwhere)->field('id')->find();
        if ($frs) {
            $this->error(xstr('user_uid_is_exists'));
            exit;
        }
        $kk = stripos($fwhere['user_id'], '-');
        if ($kk) {
            $this->error(xstr('hint116'));
            exit;
        }
        unset($fwhere, $frs);

        $errmsg = "";
        // if(empty($_POST['nickname'])){
        // 	$errmsg.="<li>昵称不能为空！</li>";
        // }
        /*
        if(empty($_POST['wenti_dan'])){
            $errmsg.="<li>".xstr('hint117')."</li>";
        }
        if(empty($_POST['BankCard'])){
            $errmsg.="<li>".xstr('hint118')."</li>";
        }*/
        $huhu = trim($_POST['UserName']);
        if (empty($huhu)) {
            //$errmsg.="<li>".xstr('hint119')."</li>";
        }
        // if(empty($_POST['UserCode'])){
        // 	$errmsg.="<li>".xstr('hint120')."</li>";
        // }
        // if(empty($_POST['UserAddress'])){
        // 	$errmsg.="<li>".xstr('hint129')."</li>";
        // }
        if (empty($_POST['UserTel'])) {
            $errmsg .= "<li>" . xstr('hint121') . "</li>";
        }
        if (empty($_POST['qq'])) {
            //$errmsg.="<li>".xstr('hint122')."</li>";
        }
        if (empty($_POST['chat'])) {
            //$errmsg.="<li>请输入您的微信号</li>";
        }
        if (empty($_POST['quyu'])) {
            //$errmsg.="<li>请填写所属区域</li>";
        }

        if (empty($_POST['zhifuPay'])) {
            //$errmsg.="<li>请填写支付宝</li>";
        }


        if (empty($_POST['UserEmail'])) {
            //$errmsg.="<li>".xstr('hint134')."</li>";
        }
        /*
         if(strlen($_POST['UserCode']) != 18){
            $this->error(xstr('hint135'));
            exit;
        }
        if(strlen($_POST['UserTel']) != 11){
            $this->error(xstr('hint136'));
            exit;
        }*/

        $usercc = trim($_POST['UserCode']);

        if (strlen($_POST['Password']) < 1 or strlen($_POST['Password']) > 16 or strlen($_POST['PassOpen']) < 1 or strlen($_POST['PassOpen']) > 16) {// or strlen($_POST['passopentwo']) < 1 or strlen($_POST['passopentwo']) > 16){
            $this->error(xstr('hint124'));
            exit;
        }
        if ($_POST['Password'] == $_POST['PassOpen']) {  //二级密码
            $this->error(xstr('hint127'));
            exit;
        }
        if ($_POST['Password'] != $_POST['rePassword']) {
            $this->error(xstr('hint125'));
            exit;
        }
        if ($_POST['PassOpen'] != $_POST['rePassOpen']) {
            $this->error(xstr('hint126'));
            exit;
        }
        $_POST['passopentwo'] = strval(rand(111111, 999999));    //自动生成随机三级密码
        /*
        if($_POST['passopentwo'] != $_POST['repassopentwo']){
            $this->error(xstr('hint137'));
            exit;
        }*/


        // $us_name = $_POST['us_name'];
        // $us_address = $_POST['us_address'];
        // $us_tel = $_POST['us_tel'];
        // if(empty($us_name)){
        // 	$errmsg.="<li>".xstr('hint128')."</li>";
        // }
        // if(empty($us_address)){
        // 	$errmsg.="<li>".xstr('hint129')."</li>";
        // }
        // if(empty($us_tel)){
        // 	$errmsg.="<li>".xstr('hint130')."</li>";
        // }

        $s_err = "<ul>";
        $e_err = "</ul>";
        if (!empty($errmsg)) {
            $out_err = $s_err . $errmsg . $e_err;
            $this->error($out_err);
            exit;
        }

        //检测手机号

        $count_pho = $fck->where("is_pay>0 and user_tel=" . $_POST['UserTel'])->count();
        if ($count_pho >= $fee['str9']) {
            $this->error("同一个手机号码最多只能注册" . $fee['str9'] . "个账号");
            exit;
        }

        $uLevel = (int)$_POST['u_level'];

        $regMoney = explode('|', $fee['s11']);
        $cpzj = $regMoney[$uLevel];
        $singular = floor($cpzj / $regMoney[0]);//认购单数


        $new_userid = $_POST['UserID'];

        $data['user_id'] = $new_userid;
        $data['bind_account'] = '3333';
        $data['last_login_ip'] = '';                            //最后登录IP
        $data['verify'] = '0';
        $data['status'] = 1;                             //状态(?)
        $data['type_id'] = '0';
        $data['last_login_time'] = time();                        //最后登录时间
        $data['login_count'] = 0;                             //登录次数
        $data['info'] = '信息';
        $data['name'] = '名称';
        $data['password'] = md5(trim($_POST['Password']));  //一级密码加密
        $data['passopen'] = md5(trim($_POST['PassOpen']));  //二级密码加密
        $data['passopentwo'] = md5(trim($_POST['passopentwo']));  //三级密码加密
        $data['pwd1'] = trim($_POST['Password']);       //一级密码不加密
        $data['pwd2'] = trim($_POST['PassOpen']);       //二级密码不加密
        $data['pwd3'] = trim($_POST['passopentwo']);       //三级密码不加密

        $data['wenti'] = ' ';//trim($_POST['wenti']);  //密保问题
        $data['wenti_dan'] = md5(rand(111111, 9999999));//trim($_POST['wenti_dan']);  //密保答案
        $data['nickname'] = $_POST['nickname'];  //昵称
        $data['user_name'] = '';             //姓名
        /*
                $data['bank_name']           = $_POST['BankName'];             //银行名称
                $data['bank_card']           = $_POST['BankCard'];             //帐户卡号
                $data['user_name']           = $_POST['UserName'];             //姓名
                $data['bank_province']       = $_POST['BankProvince'];  //省份
                $data['bank_city']           = $_POST['BankCity'];      //城市
                $data['bank_address']        = $_POST['BankAddress'];          //开户地址
                //$data['user_post']           = $_POST['UserPost']; 		   //
                $data['user_code']           = $_POST['UserCode'];             //身份证号码
                $data['user_address']        = $_POST['UserAddress'];          //联系地址
                */
        $data['email'] = '44@163.com';//$_POST['UserEmail'];            //电子邮箱
        /*
                $data['qq']              	 = $_POST['qq'];            	   //qq
                $data['chat']              	 = $_POST['chat'];            	   //微信号
                */
        $data['user_tel'] = $_POST['UserTel'];              //联系电话
        $data['is_pay'] = 0;                              //是否开通
        $data['rdt'] = time();                         //注册时间
        $data['u_level'] = $uLevel + 1;                      //注册等级
        $data['y_level'] = $uLevel + 1;                      //注册等级
        $data['singular'] = $singular;                      //注册单数
        $data['cpzj'] = $cpzj;                          //注册金额
        $data['wlf_money'] = 0;                          //见点封顶值
        $data['wlf'] = 0;                              //网络费
        $data['end_time'] = 0;                             //到期时间
        $data['quyu'] = $_POST['quyu'];
        //$data['zhifuPay']              =$_POST['zhifuPay'];//支付宝
        //$data['weixinWalet']              =$_POST['weixinWalet'];//微信钱包
        //$data['caifuPay']              =$_POST['caifuPay'];//财付通

        //开通会员

        $data['is_pay'] = 1;
        $data['pdt'] = time();
        $data['open'] = 1;
        $data['get_date'] = time();
        //$data['fanli_time'] = $nowday;//当天没有分红奖


        $result = $fck->add($data);
        // dump($data);exit;

        if ($result) {
            //更新入场券情况
            $card_where['card_no'] = $Code;
            $card_data['b_time'] = time();
            $card_data['is_use'] = 1;
            $card_data['bid'] = $result;
            // $card_data['buser_id']=$new_userid;
            $card->where($card_where)->save($card_data);
            //给推荐人添加推荐人数或单数
            $fck->query("update __TABLE__ set `re_nums`=re_nums+1,re_f4=re_f4+" . $singular . ",re_cpzj=re_cpzj+" . $cpzj . " where `id`=" . $REID);
            //统计新增业绩，用来分红
            $fee = M('fee');
            $fee->query("update __TABLE__ set `a_money`=a_money+" . $cpzj . ",`b_money`=b_money+" . $cpzj);
            $shouru = M('shouru');
            $data = array();
            $data['uid'] = $result;
            $data['user_id'] = $new_userid;
            $data['in_money'] = $cpzj;
            $data['in_time'] = time();
            $data['in_bz'] = "新会员加入";
            $shouru->add($data);
            unset($data);

            $kt_cont = "开通会员";
            $fck->addencAdd(1, $voo['user_id'], 0, 19, 0, 0, 0, $kt_cont);//历史记录


            $fee_rs = $fee->field('s4,str12,str4')->find();
            $spID = $fee_rs['s4'];            //请根据自己的账户修改
            $password = $fee_rs['str12'];    //请根据自己的账户修改
            $accessCode = $fee_rs['str4'];        //
            $s_tel = $_POST['UserTel'];
            $contentss = '【聚诚众联提醒您】您的ID注册成功：' . $new_userid . '.一级密码：' . trim($_POST['Password']) . '.二级密码：' . trim($_POST['PassOpen']);
            //	$this->TXTmsg($accessCode,$spID,$password,$s_tel,$contentss);


            $_SESSION['new_user_reg_id'] = $result;

            echo "<script>window.location='" . __URL__ . "/users_ok/';</script>";
            exit;
        } else {
            $this->error(xstr('hint138'));
            exit;
        }
    }

    /**
     * 注册完成
     * **/
    public function users_ok()
    {
        $this->_checkUser();
        $gourl = __GROUP__ . "/Reg/users/";
        if (!empty($_SESSION['new_user_reg_id'])) {

            $fck = M('fck');
            $fee_rs = M('fee')->find();

            $this->assign('alert_msg', $fee_rs['str28']);
            $this->assign('str26', $fee_rs['str26']);
            $this->assign('regMoneyArr', explode('|', $fee_rs['s9']));

            $myrs = $fck->where('id=' . $_SESSION['new_user_reg_id'])->find();
            $this->assign('myrs', $myrs);

            $this->assign('gourl', $gourl);
            unset($fck, $fee_rs);

            $v_title = $this->theme_title_value();
            $this->distheme('users_ok', $v_title[6]);
        } else {
            echo "<script>window.location='" . $gourl . "';</script>";
            exit;
        }
    }


    public function users_tglj()
    {

        $YU = "http://" . $_SERVER['HTTP_HOST'] . __ROOT__ . "/index.php/Reg/us_reg/rid/" . $_SESSION[C('USER_AUTH_KEY')];;
        $this->assign('YU', $YU);

        $v_title = $this->theme_title_value();
        $this->distheme('users_tglj', $v_title[7]);
    }


    //前台注册
    public function us_reg()
    {
        //$redis = new CacheRedis();
        $fck = M('fck');
        $fee = M('fee');
        $reid = (int)$_GET['rid'];
        $fee_s = $fee->field('s10,s9,str29,str99,s5,s11')->find();
        $regLevel = explode('|', $fee_s['s10']);
        $regMoney = explode('|', $fee_s['s9']);

        //输出银行
        $bank = explode('|', $fee_s['str29']);

        $wentilist = explode('|', $fee_s['str99']);

        $this->assign('bank', $bank);
        $this->assign('regLevel', $regLevel);
        $this->assign('regMoney', $regMoney);
        $this->assign('wentilist', $wentilist);

        $this->assign('bank', $bank);
        $arr = array();
        $arr['UserID'] = $this->_getUserID();
        $this->assign('flist', $arr);

        //检测推荐人
        $where = array();
        $where['id'] = $reid;
        // $where['is_pay'] = array('gt',0);
        $field = 'id,user_id,is_agent,shop_name';
        $rs = $fck->where($where)->field($field)->find();
        if ($rs) {
//			if(empty($rs['us_img'])){
//				$rs['us_img'] = "__PUBLIC__/Images/tirns.jpg";
//			}
//			if($rs['is_agent']==2){
//				$this->assign('shopname',$rs['user_id']);
//			}else{
//				$this->assign('shopname',$rs['shop_name']);
//			}
            $this->assign('rs', $rs);
            $this->assign('reid', $reid);

        }
        $plan = M('plan');
        $prs = $plan->find(4);
        $this->assign('prs', $prs);
        $this->display();
    }

    //前台注册处理
    public function us_regAC()
    {
        $Mcode = I('Mcode');
        if (strcmp($Mcode, $_SESSION['Mcode']) != 0 || empty($Mcode)) {
            $this->error('手机验证密码错误');
        }
        if($_POST['UserTel'] != session("reg_phone")){
            $this->error('请输入接收验证码的手机号');
        }
        $yixi = $_POST['RID'];

        $fck = M('fck');  //注册表

        if (strlen($_POST['UserID']) < 1) {
            $this->error(xstr('hint109'));
            exit;
        }

        $data = array();  //创建数据对象
        //检测受理中心
        //$shopid = $_POST['shopid'];  //所属受理中心帐号


        $maqp['user_id'] = $yixi;
        $maqp['is_agent'] = array('gt', 0);
        $shopyn = $fck->where($maqp)->find();
        //echo $shopyn.'<br>';
        if ($shopyn) {
            $shopids = $yixi;
            $shopid = $yixi;

//echo $shopid .'123<br>';

        } else {


            $sap['user_id'] = $yixi;
            $shopid = $fck->where($sap)->getfield('shop_name');
            $shopids = $fck->where($sap)->getfield('shop_name');
            //$authInfoo = $fck->where($mapp)->field('id,user_id,re_level,re_path')->find();
            //echo $yixi . '<br>';
            //echo $shopid .'<br>';

            //echo $shopids;

        }
        //die;

        if (empty($shopid)) {
            $this->error(xstr('hint110'));
            exit;
        }
        $smap = array();
        $smap['user_id'] = $shopid;
        $smap['is_agent'] = array('gt', 1);
        $shop_rs = $fck->where($smap)->field('id,user_id')->find();
        if (!$shop_rs) {
            $this->error(xstr('hint111'));
            exit;
        } else {
            $data['shop_id'] = $shop_rs['id'];      //隶属会员中心编号
            $data['shop_name'] = $shop_rs['user_id']; //隶属会员中心帐号
        }
        unset($smap, $shop_rs, $shopid);

        //检测推荐人
        $RID = trim($_POST['RID']);  //获取推荐会员帐号
        $mapp = array();
        $mapp['user_id'] = $RID;
        // $mapp['is_pay']	    = array('gt',0);
        $authInfoo = $fck->where($mapp)->field('id,user_id,re_level,re_path')->find();
        if ($authInfoo) {
            $data['re_path'] = $authInfoo['re_path'] . $authInfoo['id'] . ',';  //推荐路径
            $data['re_id'] = $authInfoo['id'];                              //推荐人ID
            $data['re_name'] = $authInfoo['user_id'];                       //推荐人帐号
            $data['re_level'] = $authInfoo['re_level'] + 1;                 //代数(绝对层数)
        } else {
            $this->error(xstr('hint112'));
            exit;
        }
        unset($authInfoo, $mapp);

        //检测入场券
        $card = M('card');
        $Code = trim($_POST['zhuce']);  //获取入场券
        $mapp = array();
        $mapp['card_no'] = $Code;
        $mapp['is_use'] = array('eq', 0);
        $mapp['buser_id'] = array('eq', $shopids);
        $authInfoo = $card->where($mapp)->field('*')->find();
        if (!$authInfoo) {
            //$this->error("入场券不存在或已被使用");
            //exit;
        }


        unset($rs, $where, $TPL);
        $fees = M('fee')->find();

        $count_pho = $fck->where("is_pay>0 and user_tel=" . $_POST['UserTel'])->count();
        if ($count_pho >= $fees['str9']) {
            $this->error("同一个手机号码最多只能注册" . $fees['str9'] . "个账号");
            exit;
        }

        $fwhere = array();//检测帐号是否存在
        $fwhere['user_id'] = trim($_POST['UserID']);
        $frs = $fck->where($fwhere)->field('id')->find();
        if ($frs) {
            $this->error(xstr('user_uid_is_exists'));
            exit;
        }
        $kk = stripos($fwhere['user_id'], '-');
        if ($kk) {
            $this->error(xstr('hint116'));
            exit;
        }
        unset($fwhere, $frs);

        $errmsg = "";
//		if(empty($_POST['wenti_dan'])){
//			$errmsg.="<li>".xstr('hint117')."</li>";
//		}
        /*
            if(empty($_POST['BankCard'])){
                    $errmsg.="<li>".xstr('hint118')."</li>";
                }
                $huhu=trim($_POST['UserName']);
                if(empty($huhu)){
                    $errmsg.="<li>".xstr('hint119')."</li>";
                }
        */
        // if(empty($_POST['UserCode'])){
        // 	$errmsg.="<li>".xstr('hint120')."</li>";
        // }
        // if(empty($_POST['UserAddress'])){
        // 	$errmsg.="<li>".xstr('hint129')."</li>";
        // }
        if (empty($_POST['UserTel'])) {
            $errmsg .= "<li>" . xstr('hint121') . "</li>";
        }
        /*
                if(empty($_POST['qq'])){
                    $errmsg.="<li>".xstr('hint122')."</li>";
                }
                if(empty($_POST['chat'])){
                    $errmsg.="<li>请填写微信号</li>";
                }
        */
        if (empty($_POST['UserEmail'])) {
            //$errmsg.="<li>请填写电子邮箱</li>";
        }
// $_POST['Password'];
//die;

        if (strlen($_POST['Password']) < 1 or strlen($_POST['Password']) > 16 or strlen($_POST['PassOpen']) < 1 or strlen($_POST['PassOpen']) > 16) {
            $this->error(xstr('hint124'));
            exit;
        }
        if ($_POST['Password'] == $_POST['PassOpen']) {  //二级密码
            $this->error(xstr('hint127'));
            exit;
        }
        if ($_POST['Password'] != $_POST['rePassword']) {
            $this->error(xstr('hint125'));
            exit;
        }
        if ($_POST['PassOpen'] != $_POST['rePassOpen']) {
            $this->error(xstr('hint126'));
            exit;
        }
        if ($_POST['passopentwo'] != $_POST['repassopentwo']) {
            //$this->error(xstr('hint137'));
            //	exit;
        }


        $s_err = "<ul>";
        $e_err = "</ul>";
        if (!empty($errmsg)) {
            $out_err = $s_err . $errmsg . $e_err;
            $this->error($out_err);
            exit;
        }


        $uLevel = $_POST['u_level'];
        $fee = M('fee')->find();
        $s = $fee['s9'];
        $s2 = explode('|', $fee['s2']);
        $s9 = explode('|', $fee['s9']);
        $s15 = explode('|', $fee['s15']);
        $s10 = explode('|', $fee['s10']);

        $F4 = 1;//认购单数
        $cpzj = $s2[$uLevel];
        // $ul     = $s9[$uLevel];
        $gp = $s15[$uLevel];
        $Jfeng = $s10[$uLevel];//见点封顶值

        $Money = explode('|', C('Member_Money'));  //注册金额数组

        $new_userid = $_POST['UserID'];

        $data['user_id'] = $new_userid;
        $data['bind_account'] = '3333';
        $data['last_login_ip'] = '';                            //最后登录IP
        $data['verify'] = '0';
        $data['status'] = 1;                             //状态(?)
        $data['type_id'] = '0';
        $data['last_login_time'] = time();                        //最后登录时间
        $data['login_count'] = 0;                             //登录次数
        $data['info'] = '信息';
        $data['name'] = '名称';
        $data['password'] = md5(trim($_POST['Password']));  //一级密码加密
        $data['passopen'] = md5(trim($_POST['PassOpen']));  //二级密码加密
        $data['passopentwo'] = md5(trim($_POST['passopentwo']));  //三级密码加密
        $data['pwd1'] = trim($_POST['Password']);       //一级密码不加密
        $data['pwd2'] = trim($_POST['PassOpen']);       //二级密码不加密
        $data['pwd3'] = trim($_POST['passopentwo']);       //三级密码不加密
        $data['email'] = '44@163.com';//$_POST['UserEmail'];            //电子邮箱
        $data['user_name'] = '';             //姓名
        //$data['zhifuPay']            = trim($_POST['UserEmail']);          //电子邮箱

//		$data['wenti']				= trim($_POST['wenti']);  //密保问题
//		$data['wenti_dan']			= trim($_POST['wenti_dan']);  //密保答案
        /*
                $data['bank_name']           = $_POST['BankName'];             //银行名称
                $data['bank_card']           = $_POST['BankCard'];             //帐户卡号
                $data['bank_province']       = $_POST['BankProvince'];  //省份
                $data['bank_city']           = $_POST['BankCity'];      //城市
                $data['bank_address']        = $_POST['BankAddress'];          //开户地址
                */
        //$data['user_post']           = $_POST['UserPost']; 		   //
        $data['nickname'] = $_POST['nickname'];//$_POST['nickname'];  //昵称
        $data['user_code'] = $_POST['UserCode'];             //身份证号码
        /*
        $data['user_address']        = $_POST['UserAddress'];          //联系地址
        */
        /*
        $data['qq']              	 = $_POST['qq'];            	   //qq
        $data['chat']              	 = $_POST['chat'];            	   //chat
        */
        $data['user_tel'] = $_POST['UserTel'];              //联系电话
        $data['is_pay'] = 0;                              //是否开通
        $data['rdt'] = time();                         //注册时间
        // $data['pdt']                 = time();
        $data['u_level'] = 1;                      //注册等级
        $data['cpzj'] = $cpzj;                          //注册金额
        $data['wlf'] = 0;                              //网络费
        //见点封顶值
        $data['wlf_money'] = $Jfeng;
        //
        $data['quyu'] = $_POST['quyu'];
        //开通会员

        $data['is_pay'] = 1;
        $data['pdt'] = time();
        $data['open'] = 1;
        $data['get_date'] = time();
        $result = $fck->add($data);
        unset($data, $fck);
        if ($result) {
            session('reg_phone',null);
            session('Mcode',null);
            //更新入场券情况
            $card_where['card_no'] = $Code;
            $card_data['b_time'] = time();
            $card_data['is_use'] = 1;
            $card_data['bid'] = $result;
            // $card_data['buser_id']=$new_userid;
            //$card->where($card_where)->save($card_data);

            echo "<script>";
            echo 'alert("' . xstr('hint140') . $new_userid . ' ！");';
            echo "window.location='" . __APP__ . "/Public/login/';";
            echo "</script>";
            exit;
        } else {
            $this->error(xstr('hint138'));
            exit;
        }
    }

    //生成会员编号
    private function _getUserID()
    {
        $fck = M('fck');
//		$fee = M('fee');
//		$fee_rs = $fee->field('us_num')->find(1);
//		$us_num = $fee_rs['us_num'];
//		$first_n = 800000000;
//		$mynn = $first_n+$us_num;

        $mynn = 'M' . rand(10000000, 99999999);

//		if($us_num<10){
//			$mynn = "00000".$us_num;
//		}elseif($us_num<100){
//			$mynn = "0000".$us_num;
//		}elseif($us_num<1000){
//			$mynn = "000".$us_num;
//		}elseif($us_num<10000){
//			$mynn = "00".$us_num;
//		}elseif($us_num<100000){
//			$mynn = "0".$us_num;
//		}else{
//			$mynn = $us_num;
//		}
        $fwhere['user_id'] = $mynn;
        $frss = $fck->where($fwhere)->field('id')->find();
        if ($frss) {
            return $this->_getUserID();
        } else {
            unset($fck, $fee);
            return $mynn;
        }
    }

    //判断最左区
    public function pd_left_us($uid, &$tp)
    {
        $fck = M('fck');
        $c_l = $fck->where('father_id=' . $uid . ' and treeplace=' . $tp . '')->field('id')->find();
        if ($c_l) {
            $n_id = $c_l['id'];
            $tp = 0;
            $ren_id = $this->pd_left_us($n_id, $tp);
        } else {
            $ren_id = $uid;
        }
        unset($fck, $c_l);
        return $ren_id;
    }

    //
    public function find_agent()
    {
        $fck = M('fck');
        $where = "is_agent=2 and is_pay>0";
        $s_echo = '<table width="100%" border="0" cellpadding="0" cellspacing="0" class="tab1"><tr><td>';
        $e_echo = '</td></tr></table>';
        $m_echo = "";
        $c_l = $fck->where($where)->field('user_id,user_name,shop_a')->select();
        foreach ($c_l as $ll) {
            $m_echo .= "<li><b>" . $ll['user_id'] . "</b>(" . $ll['user_name'] . ")<br>" . $ll['shop_a'] . "</li>";
        }
        unset($fck, $c_l);
        echo $s_echo . $m_echo . $e_echo;
    }


    // 找回密码1
    public function find_pw()
    {
        $_SESSION['us_openemail'] = "";
        $this->display('find_pw');
    }

    // 找回密码2
    public function find_pw_s()
    {
        if (empty($_SESSION['us_openemail'])) {
            if (empty($_POST['us_name']) && empty($_POST['us_email'])) {
                $_SESSION = array();
                $this->display('../Public/LinkOut');
                return;
            }
            $ptname = $_POST['us_name'];
            $us_email = $_POST['us_email'];
            $fck = M('fck');
            $rs = $fck->where("user_id='" . $ptname . "'")->field('id,user_tel,user_id,user_name,pwd1,pwd2')->find();
            if ($rs == false) {
                $errarry['err'] = '<font color=red>' . xstr('hint141') . '</font>';
                $this->assign('errarry', $errarry);
                $this->display('find_pw');
            } else {
                if ($us_email <> $rs['user_tel']) {
                    $errarry['err'] = '<font color=red>手机号码不正确，请重新输入</font>';
                    $this->assign('errarry', $errarry);
                    $this->display('find_pw');
                } else {

                    $passarr = array();
                    $passarr[0] = $rs['pwd1'];
                    $passarr[1] = $rs['pwd2'];

                    ////给卖方发送短信
                    $tel_send = $us_email;
                    $fee_rs = M('fee')->field('s4,str12,str4')->find();
                    $spID = $fee_rs['s4'];            //请根据自己的账户修改
                    $password = $fee_rs['str12'];    //请根据自己的账户修改
                    $accessCode = $fee_rs['str4'];        //请根据自己的账户修改

                    $content = '【大富翁提醒您】' . $rs['user_id'] . '密码找回为一级' . $rs['pwd1'] . '，二级' . $rs['pwd2'] . '，请及时登录修改并牢记。';
                    $s_tel = $tel_send;      //

                    $this->TXTmsg($accessCode, $spID, $password, $s_tel, $content);

                    $phone = $us_email;
                    // $code=getRandChar();
                    $ip = get_real_ip();
                    $map['phone'] = $phone;
                    // $sql="select * from "
                    $sign = "【" . C('sms_sign') . "】";
                    $uid = C('sms_uid');
                    $key = C('sms_key');
                    $content = $rs['user_id'] . '密码找回为一级' . $rs['pwd1'] . '，二级' . $rs['pwd2'] . '，请及时登录修改并牢记。' . $sign;
                    $url = "http://utf8.sms.webchinese.cn/?Uid=$uid&Key=$key&smsMob=$phone&smsText=$content";

                    //echo $url;
                    //die();
                    $res = file_get_contents($url);
                    //$_SESSION['Mcode']=$code;
                    /*
                          if($res==1){
                             $data['phone']=$phone;
                             $data['code']=$code;
                             $data['ip']=$ip;
                             $data['create_date']=date('Y-m-d H:i:s',time());
                             M("phone")->add($data);
                             echo 1;
                          //   $this->ajaxReturn(array('success'=>1,'info'=>'发送成功'));
                          }else{
                             echo 0;
                             //$this->ajaxReturn(array('success'=>0,'info'=>'发送失败'));
                          }
                     */

                    // $title = '感谢您使用密码找回';

                    // $body="<table width=\"100%\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\" style=\"font-size:12px; line-height:24px;\">";
                    // $body=$body."<tr>";
                    // $body=$body."<td height=\"30\">尊敬的客户:".$rs['user_name']."</td>";
                    // $body=$body."</tr>";
                    // $body=$body."<tr>";
                    // $body=$body."<td height=\"30\">你的会员编号:".$rs['user_id']."</td>";
                    // $body=$body."</tr>";
                    // $body=$body."<tr>";
                    // $body=$body."<td height=\"30\">一级密码为:".$rs['pwd1']."</td>";
                    // $body=$body."</tr>";
                    // $body=$body."<tr>";
                    // $body=$body."<td height=\"30\">二级密码为:".$rs['pwd2']."</td>";
                    // $body=$body."</tr>";
                    // $body=$body."此邮件由系统发出，请勿直接回复。<br>";
                    // $body=$body."</td></tr>";
                    // $body=$body."<tr>";
                    // $body=$body."<td height=\"30\" align=\"right\">".date("Y-m-d H:i:s")."</td>";
                    // $body=$body."</tr>";
                    // $body=$body."</table>";

                    // $this->send_email($us_email,$title,$body);

                    $_SESSION['us_openemail'] = $us_email;
                    $this->find_pw_e($us_email);
                }
            }
        } else {
            $us_email = $_SESSION['us_openemail'];
            $this->find_pw_e($us_email);
        }
    }

    // 找回密码3
    public function find_pw_e($us_email)
    {
        $this->assign('myask', $us_email);
        $this->display('find_pw_s');
    }

    public function send_email($useremail, $title = '', $body = '')
    {

        require_once "stemp/class.phpmailer.php";
        require_once "stemp/class.smtp.php";

        $arra = array();

        $mail = new PHPMailer();
        $mail->IsSMTP();                  // send via SMTP
        $mail->Host = "smtp.163.com";   // SMTP servers
        $mail->SMTPAuth = true;           // turn on SMTP authentication
        $mail->Username = "yuyangtaoyecn";     // SMTP username     注意：普通邮件认证不需要加 @域名
        $mail->Password = "yuyangtaoyecn666";          // SMTP password
        $mail->From = "yuyangtaoyecn@163.com";        // 发件人邮箱
        $mail->FromName = "商务会员管理系统";    // 发件人
        $mail->CharSet = "utf-8";              // 这里指定字符集！
        $mail->Encoding = "base64";
        $mail->AddAddress("" . $useremail . "", "" . $useremail . "");    // 收件人邮箱和姓名
        //$mail->AddAddress("119515301@qq.com","text");    // 收件人邮箱和姓名
        $mail->AddReplyTo("" . $useremail . "", "163.com");
        $mail->IsHTML(true);    // send as HTML
        $mail->Subject = $title; // 邮件主题
        $mail->Body = "" . $body . "";// 邮件内容
        $mail->AltBody = "text/html";
//		$mail->Send();

        if (!$mail->Send()) {
            echo "Message could not be sent. <p>";
            echo "Mailer Error: " . $mail->ErrorInfo;
            exit;
        }
        //echo "Message has been sent";
    }

}

?>
