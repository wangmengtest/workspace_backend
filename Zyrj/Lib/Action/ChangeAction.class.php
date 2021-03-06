﻿<?php

class ChangeAction extends CommonAction
{
    public function _initialize()
    {
        parent::_initialize();
        header("Content-Type:text/html; charset=utf-8");
        $this->_inject_check(1);//调用过滤函数
        $this->_checkUser();
        $this->_Config_name();
    }

    //二级密码验证
    public function cody()
    {
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
        $fck = M('cody');
        $list = $fck->where("c_id=$UrlID")->getField('c_id');
        if (!empty($list)) {
            $this->assign('vo', $list);

            $v_title = $this->theme_title_value();
            $this->distheme('../Public/cody', $v_title[1]);

// 			$this->display('../Public/cody');
            exit;
        } else {
            $this->error(xstr('second_password_error'));
            exit;
        }
    }

    //二级验证后调转页面
    public function codys()
    {
        $Urlsz = $_POST['Urlsz'];
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
            $list = $fck->where($where)->field('id')->find();
            if ($list == false) {
                $this->error(xstr('second_password_error'));
                exit();
            }
            $_SESSION['user_pwd2'] = 1;
        } else {
            $Urlsz = $_GET['Urlsz'];
        }
        switch ($Urlsz) {
            case 1:
                $_SESSION['DLTZURL02'] = 'changedata';
                $bUrl = __URL__ . '/changedata';//修改资料
                $this->_boxx($bUrl);
                break;
            case 2:
                $_SESSION['DLTZURL01'] = 'changepassword';
                $bUrl = __URL__ . '/changepassword';//修改密码
                $this->_boxx($bUrl);
                break;
            case 3:
                $_SESSION['DLTZURL01'] = 'pprofile';
                $bUrl = __URL__ . '/pprofile';//修改密码
                $this->_boxx($bUrl);
                break;
            case 4:
                $_SESSION['DLTZURL01'] = 'FrontCode';
                $bUrl = __URL__ . '/FrontCode';//修改密码
                $this->_boxx($bUrl);
                break;
            default;
                $this->error(xstr('second_password_error'));
                break;
        }
    }

    //前台入场券查询
    public function FrontCode()
    {
        if ($_SESSION['DLTZURL01'] == 'FrontCode') {
            //=====================分页开始==============================================
            $fck = M('fck');
            $card = M('card');
            $UserID = trim($_POST['UserID']);
            $map = array();
            if (!empty($UserID)) {
                import("@.ORG.KuoZhan");  //导入扩展类
                $KuoZhan = new KuoZhan();
                if ($KuoZhan->is_utf8($UserID) == false) {
                    $UserID = iconv('GB2312', 'UTF-8', $UserID);
                }
                unset($KuoZhan);
                $where['user_id'] = array('like', "%" . $UserID . "%");
                $useMenberId = $fck->where($where)->field('id')->find();
                // $map['bid']=$useMenberId['id'];
                $map['card_no'] = $UserID;
                $UserID = urlencode($UserID);
            }
            $this->assign('UserID', $UserID);
            $id = $_SESSION[C('USER_AUTH_KEY')];
            $fck_rs = $fck->where('id=' . $id)->find();
            $myuserid = $fck_rs['user_id'];
            $map['buser_id'] = $myuserid;
            import("@.ORG.ZQPage");  //导入分页类
            $count = $card->where($map)->count();//总页数
            $listrows = 20;//每页显示的记录数
            $page_where = "buser_id=" . $myuserid . "";//分页条件
            $Page = new ZQPage($count, $listrows, 1, 0, 3, $page_where);
            //===============(总页数,每页显示记录数,css样式 0-9)
            $show = $Page->show();//分页变量
            $this->assign('page', $show);//分页变量输出到模板

            $list = $card->order('is_use ASC,c_time DESC,id DESC')->page($Page->getPage() . ',' . $listrows)->where($map)->select();

            $this->assign('list', $list);//数据输出到模板

            $map['buser_id'] = $myuserid;
            $map['is_use'] = 0;
            $count = $card->where($map)->count();//总页数
            $this->assign('card_num', $count);
            //=================================================
            $this->display('FrontCode', "我的入场券");
        } else {
            $this->error(xstr('operation_error'), '/index.php?s=/Change/cody/c_id/4');
            exit;
        }
    }

    //赠送门票
    public function Zensong()
    {
        //=====================分页开始==============================================
        $fck = M('fck');
        //	$card=M('orderlist');
        $UserID = trim($_POST['UserID']);
        $map = array();
        if (!empty($UserID)) {
            import("@.ORG.KuoZhan");  //导入扩展类
            $KuoZhan = new KuoZhan();
            if ($KuoZhan->is_utf8($UserID) == false) {
                $UserID = iconv('GB2312', 'UTF-8', $UserID);
            }
            unset($KuoZhan);
            $where['user_id'] = array('like', "%" . $UserID . "%");
            $useMenberId = $fck->where($where)->field('id')->find();
            // $map['bid']=$useMenberId['id'];
            $map['card_no'] = $UserID;
            $UserID = urlencode($UserID);
        }
        $this->assign('UserID', $UserID);
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck_rs = $fck->where('id=' . $id)->find();
        /*
        unset($map);
           $myuserid=$id;
           $map['userid']=$id;
           $map['ordstatus']=1;
           import ( "@.ORG.ZQPage" );  //导入分页类
           $count = M('orderlist')->where($map)->count();//总页数
           $listrows = 20;//每页显示的记录数
           $page_where ="userid=".$myuserid."";//分页条件
           $Page = new ZQPage($count, $listrows, 1, 0, 3, $page_where);
           //===============(总页数,每页显示记录数,css样式 0-9)
           $show = $Page->show();//分页变量
           $this->assign('page',$show);//分页变量输出到模板

        $list = M('orderlist')->order('id DESC')->page($Page->getPage().','.$listrows)->where($map)->select();

           $this->assign('list',$list);//数据输出到模板
         */

        unset($map);
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $map['is_use'] = 0;
        $count = M('card')->where($map)->count();//总页数
        $this->assign('card_num', $count);
        //=================================================
        $this->display('Zensong');
        exit;
    }

    //前台入场券查询
    public function Orderlist()
    {
        //=====================分页开始==============================================
        $fck = M('fck');
        $card = M('orderlist');
        $UserID = trim($_POST['UserID']);
        $map = array();
        if (!empty($UserID)) {
            import("@.ORG.KuoZhan");  //导入扩展类
            $KuoZhan = new KuoZhan();
            if ($KuoZhan->is_utf8($UserID) == false) {
                $UserID = iconv('GB2312', 'UTF-8', $UserID);
            }
            unset($KuoZhan);
            $where['user_id'] = array('like', "%" . $UserID . "%");
            $useMenberId = $fck->where($where)->field('id')->find();
            // $map['bid']=$useMenberId['id'];
            $map['card_no'] = $UserID;
            $UserID = urlencode($UserID);
        }
        $this->assign('UserID', $UserID);
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck_rs = $fck->where('id=' . $id)->find();
        unset($map);
        $myuserid = $id;
        $map['userid'] = $id;
        $map['ordstatus'] = 1;
        import("@.ORG.ZQPage");  //导入分页类
        $count = M('orderlist')->where($map)->count();//总页数
        $listrows = 20;//每页显示的记录数
        $page_where = "userid=" . $myuserid . "";//分页条件
        $Page = new ZQPage($count, $listrows, 1, 0, 3, $page_where);
        //===============(总页数,每页显示记录数,css样式 0-9)
        $show = $Page->show();//分页变量
        $this->assign('page', $show);//分页变量输出到模板

        $list = M('orderlist')->order('id DESC')->page($Page->getPage() . ',' . $listrows)->where($map)->select();

        $this->assign('list', $list);//数据输出到模板

        unset($map);
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $map['is_use'] = 0;
        $count = M('card')->where($map)->count();//总页数
        $this->assign('card_num', $count);
        //=================================================
        $this->display('Orderlist');
        exit;
    }

    //门票对账单
    public function Orderlistall()
    {
        //=====================分页开始==============================================
        $fck = M('fck');
        $card = M('orderlist');
        $UserID = trim($_POST['UserID']);
        $map = array();
        if (!empty($UserID)) {
            import("@.ORG.KuoZhan");  //导入扩展类
            $KuoZhan = new KuoZhan();
            if ($KuoZhan->is_utf8($UserID) == false) {
                $UserID = iconv('GB2312', 'UTF-8', $UserID);
            }
            unset($KuoZhan);
            $where['user_id'] = array('like', "%" . $UserID . "%");
            $useMenberId = $fck->where($where)->field('id')->find();
            // $map['bid']=$useMenberId['id'];
            $map['card_no'] = $UserID;
            $UserID = urlencode($UserID);
        }
        $this->assign('UserID', $UserID);
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck_rs = $fck->where('id=' . $id)->find();
        unset($map);
        $myuserid = $id;
        $map['userid'] = $id;
        $map['ordstatus'] = array('gt', 0);
        import("@.ORG.ZQPage");  //导入分页类
        $count = M('orderlist')->where($map)->count();//总页数
        $listrows = 20;//每页显示的记录数
        $page_where = "userid=" . $myuserid . "";//分页条件
        $Page = new ZQPage($count, $listrows, 1, 0, 3, $page_where);
        //===============(总页数,每页显示记录数,css样式 0-9)
        $show = $Page->show();//分页变量
        $this->assign('page', $show);//分页变量输出到模板

        $list = M('orderlist')->order('id DESC')->page($Page->getPage() . ',' . $listrows)->where($map)->select();

        $this->assign('list', $list);//数据输出到模板

        unset($map);
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $map['is_use'] = 0;
        $count = M('card')->where($map)->count();//总页数
        $this->assign('card_num', $count);
        //=================================================
        $this->display('Orderlistall');
        exit;
    }

    public function s()
    {
        //=====================分页开始==============================================
        $fck = M('fck');
        $card = M('orderlist');
        $UserID = trim($_POST['UserID']);
        $map = array();
        if (!empty($UserID)) {
            import("@.ORG.KuoZhan");  //导入扩展类
            $KuoZhan = new KuoZhan();
            if ($KuoZhan->is_utf8($UserID) == false) {
                $UserID = iconv('GB2312', 'UTF-8', $UserID);
            }
            unset($KuoZhan);
            $where['user_id'] = array('like', "%" . $UserID . "%");
            $useMenberId = $fck->where($where)->field('id')->find();
            // $map['bid']=$useMenberId['id'];
            $map['card_no'] = $UserID;
            $UserID = urlencode($UserID);
        }
        $this->assign('UserID', $UserID);
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck_rs = $fck->where('id=' . $id)->find();
        unset($map);
        $myuserid = $id;
        $map['userid'] = $id;
        $map['ordstatus'] = 1;
        import("@.ORG.ZQPage");  //导入分页类
        $count = M('orderlist')->where($map)->count();//总页数
        $listrows = 20;//每页显示的记录数
        $page_where = "userid=" . $myuserid . "";//分页条件
        $Page = new ZQPage($count, $listrows, 1, 0, 3, $page_where);
        //===============(总页数,每页显示记录数,css样式 0-9)
        $show = $Page->show();//分页变量
        $this->assign('page', $show);//分页变量输出到模板

        $list = M('orderlist')->order('id DESC')->page($Page->getPage() . ',' . $listrows)->where($map)->select();

        $this->assign('list', $list);//数据输出到模板

        unset($map);
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $map['is_use'] = 0;
        $count = M('card')->where($map)->count();//总页数
        $this->assign('card_num', $count);
        //=================================================
        $this->display('s');
        exit;
    }

    //前台入场券查询
    public function FrontCodeSave()
    {
        if ($_SESSION['DLTZURL01'] == 'FrontCode') {
            //=====================分页开始==============================================
            $bz_id = trim($_POST['bz_id']);
            $bz = trim($_POST['bz']);
            $card = M('card');
            $map['id'] = $bz_id;
            $data['bz'] = $bz;
            $result = $card->where($map)->save($data);
            // dump($result);exit;
            if ($result) {
                $bUrl = __URL__ . '/FrontCode';
                $this->_box(1, xstr('hint041'), $bUrl, 1);
                exit;
            } else {
                $this->error(xstr('operation_error'));
                exit;
            }
        } else {
            $this->error(xstr('operation_error'));
            exit;
        }
    }


    public function FrontCodesong()
    {
        //if ($_SESSION['DLTZURL01'] == 'FrontCode'){


        //=====================分页开始==============================================

        $card = M('card');
        $pst = trim($_POST['cars']);
        $cards['card_no'] = $pst;
        $psp = trim($_POST['Userto']);
        $userto['user_id'] = $psp;
        $usermy['id'] = $_SESSION[C('USER_AUTH_KEY')];
        $fck = M('fck');

        $fck_rs = $fck->where($userto)->find();
        $my_rs = $fck->where($usermy)->find();

        if (!$fck_rs) {
            echo $fck_rs['user_id'];
            $this->error("转赠对象不存在！");
            exit;
        }
        /*
        $cr=$card->where($cards)->find();
        if(!$cr)
        {

            echo $cards['card_no'];
            $this->error("诚信券不存在！");
                    exit;
        }
         */
        $num = I("num", 1);
        $map['is_use'] = 0;
        $map['buser_id'] = $_SESSION['loginUseracc'];
        $list = M('card')->where($map)->field('id')->limit($num)->order('id desc')->select();
        $arr = array();
        foreach ($list as $v) {
            $arr[] = $v['id'];
        }
        $where['id'] = array('in', $arr);
        $data['buser_id'] = $psp;

        $res = $card->where($where)->save($data);


        if ($res) {

            //转账门票记录，写入orderlist表，一方增加，一方减少。
            $data_p = array();
            $data_p['userid'] = $fck_rs['id'];
            $data_p['ordstatus'] = 8; //转账增加
            $data_p['num'] = $num;
            $data_p['payment_notify_time'] = date("Y-m-d H:i:s", time());
            $data_p['payment_type'] = '会员' . $my_rs['user_id'] . '转账门票增加';
            $rs_p = M('orderlist')->add($data_p);


            $data_s = array();
            $data_s['userid'] = $my_rs['id'];
            $data_s['ordstatus'] = 10; //转账减少
            $data_s['num'] = $num;
            $data_s['payment_notify_time'] = date("Y-m-d H:i:s", time());
            $data_s['payment_type'] = '向会员' . $fck_rs['user_id'] . '转账门票';
            $rs_s = M('orderlist')->add($data_s);

            //$_SESSION[C('USER_AUTH_KEY')]

            $bUrl = __URL__ . '/FrontCode';

            //$this->_box(1,'ok!',$bUrl,1);
            $this->success('赠送成功!');
            exit;
        } else {

            $this->error('fail!!');
            exit;
        }

        //}
    }

    /* ---------------显示用户修改资料界面---------------- */
    public function changedata()
    {
        if ($_SESSION['DLTZURL02'] == 'changedata') {
            $fck = M('fck');
            $id = $_SESSION[C('USER_AUTH_KEY')];
            //输出登录用户资料记录
            $vo = $fck->getById($id);  //该登录会员记录
            if (empty($vo['us_img'])) {
                $vo['us_img'] = "__PUBLIC__/Images/mctxico.jpg";
            }
            $this->assign('vo', $vo);
            unset($vo);

            //输出银行
            $b_bank = $fck->where('id=' . $id)->field("bank_name")->find();
            $this->assign('b_bank', $b_bank);

            $fee = M('fee');
            $fee_s = $fee->field('s2,s9,i4,str29,str99,s11')->find();
            $wentilist = explode('|', $fee_s['str99']);
            $this->assign('wentilist', $wentilist);
            $bank = explode('|', $fee_s['str29']);
            $this->assign('bank', $bank);
            $this->assign('rechRatio', $fee_s['s11']);

            unset($bank, $b_bank);

            $v_title = $this->theme_title_value();
            $this->distheme('changedata', $v_title[22]);

        } else {
            $this->error("操作失败,请先输入2级密码", '/index.php?s=/Change/cody/c_id/1');
            exit;
        }
    }


    /* --------------- 修改保存会员信息 ---------------- */
    public function changedataSave()
    {
        if (!$this->isCanUpdateInfo()) {
            $this->error("请稍后提交修改");
        }
        if ($_POST['ID'] != $_SESSION[C('USER_AUTH_KEY')]) {
            $this->error(xstr('operation_error'));
            exit;
        }

        if ($_SESSION['DLTZURL02'] == 'changedata') {
            $fck = M('fck');

            $myw = array();
            $myw['id'] = $_SESSION[C('USER_AUTH_KEY')];
            $mrs = $fck->where($myw)->field('id,wenti_dan')->find();
            if (!$mrs) {
                $this->error(xstr('parameter_error_2'));
                exit;
            } else {
                $mydaan = $mrs['wenti_dan'];
            }

//			$huida = trim($_POST['wenti_dan']);
//			if(empty($huida)){
//				$this->error('请输入底部的密保答案！');
//				exit;
//			}
//			if($huida!=$mydaan){
//				$this->error('密保答案验证不正确！');
//				exit;
//			}

            $data = array();
            $data['nickname'] = $_POST['NickName'];        //会员昵称
            $data['bank_name'] = $_POST['BankName'];        //银行名称
            $data['bank_card'] = $_POST['BankCard'];        //银行卡号
            $data['user_name'] = $_POST['UserName'];        //开户姓名

            $data['bank_province'] = $_POST['BankProvince'];    //省份
            $data['bank_city'] = $_POST['BankCity'];        //城市
            $data['bank_address'] = $_POST['BankAddress'];     //开户地址
            $data['user_code'] = $_POST['UserCode'];        //身份证号码
            $data['user_address'] = $_POST['UserAddress'];     //联系地址
            $data['email'] = $_POST['UserEmail'];       //电子邮箱
            $data['user_tel'] = $_POST['UserTel'];         //联系电话
            $data['qq'] = $_POST['qq'];         //qq
            $data['chat'] = $_POST['chat'];         //qq
            $data['zhifuPay'] = $_POST['zhifuPay'];         //qq
            $data['weixinWalet'] = $_POST['weixinWalet'];         //qq
            $data['caifuPay'] = $_POST['caifuPay'];         //qq
            /*
            $tRatio = floatval(trim($_POST['subUserRechRatio']));
            $feeArr = M('Fee')->field('s11')->find();
            $maxRatio = floatval($feeArr['s11']);
            if($tRatio > $maxRatio)
            {
                $this->error('下属会员充值手续费不可大于'.$maxRatio.'%');
                exit;
            }
            else if($tRatio < 0)
            {
                $this->error('下属会员充值手续费不能小于0');
                exit;
            }
            $data['rech_ratio']         = $_POST['subUserRechRatio'];
            */
            $usimg = trim($_POST['image']);
            if (!empty($usimg)) {
                $data['us_img'] = $usimg;
            }

            $xg_wenti = trim($_POST['xg_wenti']);
            $xg_wenti_dan = trim($_POST['xg_wenti_dan']);
            if (!empty($xg_wenti)) {
                $data['wenti'] = $xg_wenti;//问题
            }
            if (!empty($xg_wenti_dan) || strlen($xg_wenti_dan) > 0) {
                $data['wenti_dan'] = $xg_wenti_dan;//答案
            }


            $data['id'] = $_SESSION[C('USER_AUTH_KEY')];//要修改资料的AutoId

            $rs = $fck->save($data);
            // dump($_POST['UserEmail']);
            if ($rs) {
                $fee = M('fee');
                $fee_rs = $fee->field('s4,str12,str4')->find();
                $spID = $fee_rs['s4'];            //请根据自己的账户修改
                $password = $fee_rs['str12'];    //请根据自己的账户修改
                $accessCode = $fee_rs['str4'];        //
                $s_tel = $_POST['UserTel'];
                $contentss = '【创客提醒您】您的个人资料被修改，如非亲自操作，请及时联系系统';
                //$this->TXTmsg($accessCode, $spID, $password, $s_tel, $contentss);

                $bUrl = __URL__ . '/changedata';
                $this->_box(1, xstr('hint041'), $bUrl, 1);
            } else {
                $this->error(xstr('operation_error'));
                exit;
            }
        } else {
            $this->error(xstr('operation_error'));
            exit;
        }
    }


    /* ********************** 修改密码 ********************* */
    public function changepassword()
    {
        if ($_SESSION['DLTZURL01'] == 'changepassword') {
            $fck = M('fck');

            $id = $_SESSION[C('USER_AUTH_KEY')];
            //输出登录用户资料记录
            $where = array();
            $where['id'] = array('eq', $id);
            $vo = $fck->where($where)->find();
            $this->assign('vo', $vo);
            unset($vo);

            $v_title = $this->theme_title_value();
            $this->distheme('changepassword', $v_title[21]);
        } else {
            $this->error(xstr('operation_error'));
            exit;
        }
    }


    /* ********************** 修改密码 ********************* */
    public function changepasswordSave()
    {
        if ($_SESSION['DLTZURL01'] == 'changepassword') {
            $fck = M('fck');
            if (md5($_POST['verify']) != $_SESSION['verify']) {
                $this->error(xstr('verify_code_error'));
                exit;
            }

            $myw = array();
            $myw['id'] = $_SESSION[C('USER_AUTH_KEY')];
            $mrs = $fck->where($myw)->field('id,wenti_dan')->find();
            if (!$mrs) {
                $this->error(xstr('parameter_error_2'));
                exit;
            } else {
                $mydaan = $mrs['wenti_dan'];
            }

//			$huida = trim($_POST['wenti_dan']);
//			if(empty($huida)){
//				$this->error('请输入底部的密保答案！');
//				exit;
//			}
//			if($huida!=$mydaan){
//				$this->error('密保答案验证不正确！');
//				exit;
//			}

            $map = array();

            //检测密码级别及获取旧密码
            if ($_POST['type'] == 1) {
                $map['Password'] = pwdHash($_POST['oldpassword']);
            } elseif ($_POST['type'] == 2) {
                $map['passopen'] = pwdHash($_POST['oldpassword']);
            } elseif ($_POST['type'] == 3) {
                $map['passopentwo'] = pwdHash($_POST['oldpassword']);
            } else {
                $this->error(xstr('hint042'));
                exit;
            }

            //检查两次密码是否相等
            if ($_POST['password'] != $_POST['repassword']) {
                $this->error(xstr('hint043'));
                exit;
            }

            if (isset($_POST['account'])) {
                $map['user_id'] = $_POST['account'];
            } elseif (isset($_SESSION[C('USER_AUTH_KEY')])) {
                $map['id'] = $_SESSION[C('USER_AUTH_KEY')];
            }

            //检查用户
            $result = $fck->where($map)->field('id,user_tel')->find();
            if (!$result) {
                $this->error(xstr('hint044'));
            } else {
                echo $result['id'];
                //修改密码
                $pwds = pwdHash($_POST['password']);
                if ($_POST['type'] == 1) {
                    $fck->where($map)->setField('pwd1', $_POST['password']);  //一级密码不加密
                    $fck->where($map)->setField('password', $pwds);           //一级密码加密
                } elseif ($_POST['type'] == 2) {
                    $fck->where($map)->setField('pwd2', $_POST['password']);  //二级密码不加密
                    $fck->where($map)->setField('passopen', $pwds);           //二级密码加密
                } elseif ($_POST['type'] == 3) {
                    $fck->where($map)->setField('pwd3', $_POST['password']);  //三级密码不加密
                    $fck->where($map)->setField('passopentwo', $pwds);          //三级密码加密
                }
                //9260729
                //$fck->save();
                //生成认证条件
                $mapp = array();
                // 支持使用绑定帐号登录
                $mapp['id'] = $_SESSION[C('USER_AUTH_KEY')];
                $mapp['user_id'] = $_SESSION['loginUseracc'];
                import('@.ORG.RBAC');
                $authInfoo = RBAC::authenticate($mapp);
                if (false === $authInfoo) {
                    $this->LinkOut();
                    $this->error(xstr('account_is_noexists'));
                    exit;
                } else {
                    //更新session
                    $_SESSION['login_sf_list_u'] = md5($authInfoo['user_id'] . 'wodetp_new_1012!@#' . $authInfoo['password'] . $_SERVER['HTTP_USER_AGENT']);
                }

                $fee = M('fee');
                $fee_rs = $fee->field('s4,str12,str4')->find();
                $spID = $fee_rs['s4'];            //请根据自己的账户修改
                $password = $fee_rs['str12'];    //请根据自己的账户修改
                $accessCode = $fee_rs['str4'];        //
                $s_tel = $result['user_tel'];
                $contentss = '【创客提醒您】您的个人密码被修改，如非亲自操作，请及时联系系统';
                echo $s_tel;
                $this->TXTmsg($accessCode, $spID, $password, $s_tel, $contentss);


                $bUrl = __URL__ . '/changepassword';
                $this->_box(1, xstr('hint045'), $bUrl, 1);
                exit;
            }
        } else {
            $this->error(xstr('operation_error'));
            exit;
        }
    }


    public function adminshenqing()
    {

        $shenqing = M('shenqing');
        $id = $_SESSION[C('USER_AUTH_KEY')];

        //=====================分页开始==============================================

        import("@.ORG.ZQPage");  //导入分页类

        $count = $shenqing->count();//总页数

        $listrows = 50;//每页显示的记录数

//$page_where = 'UserID=' . $UserID .'&S_Date='. $sDate .'&E_Date='. $eDate . '&tp=' . $ss_type ;//分页条件

        $Page = new ZQPage($count, $listrows, 1, 0, 3);

        //===============(总页数,每页显示记录数,css样式 0-9)

        $show = $Page->show();//分页变量


        $this->assign('page', $show);//分页变量输出到模板

        $list = $shenqing->where("re_path like '%," . $id . ",%'")->page($Page->getPage() . ',' . $listrows)->order('is_done asc')->select();


        $this->assign('list', $list);
        $this->distheme('adminshenqing', '申请处理');


    }


    public function adminshenqingsave()
    {

        $shenqing = M('shenqing');

        $fcks = M('fck');

        $uid = $_GET['uid'];

        $jibie = $_GET['jibie'];

        $id = $_GET['id'];

        $tg['is_done'] = 1;


        switch ($jibie) {

            case 1:

                $shengji['jingli'] = 1;
                $shengji['zongjian'] = 0;
                $shengji['dongshi'] = 0;
                $shengji['h4'] = 0;
                $shengji['h5'] = 0;
                $shengji['h6'] = 0;

                break;

            case 2:

                $shengji['jingli'] = 0;
                $shengji['zongjian'] = 1;
                $shengji['dongshi'] = 0;
                $shengji['h4'] = 0;
                $shengji['h5'] = 0;
                $shengji['h6'] = 0;

                break;

            case 3:

                $shengji['jingli'] = 0;
                $shengji['zongjian'] = 0;
                $shengji['dongshi'] = 1;
                $shengji['h4'] = 0;
                $shengji['h5'] = 0;
                $shengji['h6'] = 0;

                break;

            case 4:

                $shengji['jingli'] = 0;
                $shengji['zongjian'] = 0;
                $shengji['dongshi'] = 0;
                $shengji['h4'] = 1;
                $shengji['h5'] = 0;
                $shengji['h6'] = 0;

                break;

            case 5:

                $shengji['jingli'] = 0;
                $shengji['zongjian'] = 0;
                $shengji['dongshi'] = 0;
                $shengji['h4'] = 0;
                $shengji['h5'] = 1;
                $shengji['h6'] = 0;

                break;

            case 6:

                $shengji['jingli'] = 0;
                $shengji['zongjian'] = 0;
                $shengji['dongshi'] = 0;
                $shengji['h4'] = 0;
                $shengji['h5'] = 0;
                $shengji['h6'] = 1;

                break;

            default:

                $shengji['jingli'] = 0;
                $shengji['zongjian'] = 0;
                $shengji['dongshi'] = 0;
                $shengji['h4'] = 0;
                $shengji['h5'] = 0;
                $shengji['h6'] = 0;

                break;
        }

        //var_dump ($shengji);


        $tgm = $shenqing->where(array('id' => $id))->save($tg);

        $sj = $fcks->where(array('user_id' => $uid))->save($shengji);


        if (false !== $tgm && false !== $sj) {

            $this->success('审核通过！', '__URL__/adminshenqing/');
        } else {
            echo $tgm . '<br>';
            echo $sj;

            $this->error('!!!', '__URL__/adminshenqing');
        }


    }

    public function pprofile()
    {
        //列表过滤器，生成查询Map对象
        $id = $_SESSION[C('USER_AUTH_KEY')];
        $fck = M('fck');
        //会员
        $u_all = $fck->where('id=' . $id)->field('*')->find();
        $lev = $u_all['u_level'] - 1;

        $fee = M('fee');
        $fee_rs = $fee->field('s4,s10,a_money,b_money')->find();
        $s4 = explode('|', $fee_rs['s4']);
        $Level = explode('|', $fee_rs['s10']);
        $a_money = $fee_rs['a_money'];
        $b_money = $fee_rs['b_money'];
        $all_money = $a_money + $b_money;
        $all_money = number_format($all_money, 2);
        $this->assign('all_money', $all_money);

        $this->assign('mycg', $s4[$lev]);//会员级别
        $this->assign('u_level', $Level[$lev]);//会员级别
        $this->assign('rs', $u_all);

        $gglv = "";
        $this->_getlevelConfirm($gglv);
        $this->assign('gglv', $gglv);

        $v_title = $this->theme_title_value();
        $this->distheme('pprofile', $v_title[23]);
    }


    /* **********************shenqing ********************* */
    public function shenqing()
    {

        $fck = M('fck');

        $id = $_SESSION[C('USER_AUTH_KEY')];
        //输出登录用户资料记录
        $where = array();
        $where['id'] = array('eq', $id);
        $vo = $fck->where($where)->find();
        $this->assign('vo', $vo);
        unset($vo);

        //$v_title = $this->theme_title_value();
        $this->distheme('shenqing', '申请升级');

    }

    /* **********************shenqingsave ********************* */
    public function shenqingsave()
    {
        $sq = M('shenqing');
        $where['uid'] = $_SESSION['loginUseracc'];
        $where['is_done'] = 0;
        $yn = $sq->where($where)->find();
        if ($yn) {
            $this->error('有申请在审核状态，请勿重复提交', "__URL__/shenqing/");
        }

        $data['shijian'] = time();
        $data['jibie'] = $_POST["jibie"];
        $data['is_done'] = 0;
        $data['uid'] = $_SESSION['loginUseracc'];
        $result = $sq->add($data);
        if ($result) {
            $this->success('请等待审核', "__URL__/shenqing/");
        } else {
            $this->error('!', "__URL__/shenqing/");
        }
    }

    /* 上传图片 */
    public function uploadImg()
    {
        import('@.ORG.UploadFile');
        $fileName = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s") . rand(1, 100);
        $upload = new UploadFile();                        // 实例化上传类
        $upload->maxSize = 1 * 1024 * 1024;                    //设置上传图片的大小
        $upload->allowExts = array('jpg', 'png', 'gif');    //设置上传图片的后缀
        $upload->uploadReplace = true;                    //同名则替换
        $upload->saveRule = 'temp';                    //设置上传头像命名规则(临时图片),修改了UploadFile上 传类
//		$upload->saveRule = $fileName;
        //完整的头像路径
        $path = './Public/Uploads/';
        $upload->savePath = $path;
        if (!$upload->upload()) {                        // 上传错误提示错误信息
            $this->ajaxReturn('', $upload->getErrorMsg(), 0, 'json');
        } else {                                            // 上传成功 获取上传文件信息
            $info = $upload->getUploadFileInfo();
            $temp_size = getimagesize($path . 'temp.jpg');
            if ($temp_size[0] < 100 || $temp_size[1] < 100) {//判断宽和高是否符合头像要求
                $this->ajaxReturn(0, xstr('hint046'), 0, 'json');
            }
            $this->ajaxReturn(__ROOT__ . '/Public/Uploads/' . $user_path . 'temp.jpg', $info, 1, 'json');
        }
    }

    //裁剪并保存图像
    public function cropImg()
    {
        //图片裁剪数据
//		$params = $this->_post();				//裁剪参数
        $params = $_POST;                        //裁剪参数
//		dump($_POST);
        if (!isset($params) && empty($params)) {
            return;
        }
        //随时间生成文件名
        $randPath = date("Y") . date("m") . date("d") . date("H") . date("i") . date("s") . rand(1, 100);
        //头像目录地址
        $path = './Public/Uploads/';
        //要保存的图片
        $real_path = $path . $randPath . '.jpg';
        //临时图片地址
        $pic_path = $path . 'temp.jpg';
        import('@.ORG.ThinkImage.ThinkImage');
        $Think_img = new ThinkImage(THINKIMAGE_GD);
        //裁剪原图
        $Think_img->open($pic_path)->crop($params['w'], $params['h'], $params['x'], $params['y'])->save($real_path);
        //生成缩略图
//		$Think_img->open($real_path)->thumb(220,150, 1)->save($path.'avatar_220_150.jpg');
//		$Think_img->open($real_path)->thumb(60,60, 1)->save($path.'avatar_60.jpg');
//		$Think_img->open($real_path)->thumb(30,30, 1)->save($path.'avatar_30.jpg');
        $out_realpath = str_replace("./", "/", __ROOT__ . $real_path);
        echo "<script>window.parent.form1.imageShow.src='" . $out_realpath . "';</script>";
        $real_path = (str_replace('./Public/', '__PUBLIC__/', $real_path));
        echo "<script>window.parent.form1.image.value='" . $real_path . "';</script>";
        $this->success(xstr('hint047'));
    }

}

?>
