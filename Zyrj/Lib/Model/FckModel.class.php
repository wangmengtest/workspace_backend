<?php
class FckModel extends CommonModel {
	//数据库名称

	public function xiangJiao($Pid=0,$DanShu=1){
        //========================================== 往上统计单数
        $where = array();
        $where['id'] = $Pid;
        $field = 'treeplace,father_id';
        $vo = $this ->where($where)->field($field)->find();
        if ($vo){
            $Fid = $vo['father_id'];
            $TPe = $vo['treeplace'];
            $table = $this->tablePrefix.'fck';
            if ($TPe == 0 && $Fid > 0){
                $this->execute("update ". $table ." Set `l`=l+$DanShu, `benqi_l`=benqi_l+$DanShu where `id`=".$Fid);
            }elseif($TPe == 1 && $Fid > 0){
                $this->execute("update ". $table ." Set `r`=r+$DanShu, `benqi_r`=benqi_r+$DanShu where `id`=".$Fid);
            }elseif($TPe == 2 && $Fid > 0){
                $this->execute("update ". $table ." Set `lr`=lr+$DanShu, `benqi_lr`=benqi_lr+$DanShu where `id`=".$Fid);
            }
            if ($Fid > 0) $this->xiangJiao($Fid,$DanShu);
        }
        unset($where,$field,$vo);
    }

    public function xiangJiaoJ($Pid=0,$DanShu=1){
        //========================================== 往上统计业绩
        $where = array();
        $where['id'] = $Pid;
        $field = 'treeplace,father_id';
        $vo = $this ->where($where)->field($field)->find();
        if ($vo){
            $Fid = $vo['father_id'];
            $TPe = $vo['treeplace'];
            $table = $this->tablePrefix.'fck';
            // if ( $Fid > 0){
                $this->execute("update ". $table ." Set `g_level_a`=g_level_a+$DanShu where `id`=".$Fid);
            // }
            if ($Fid > 0) $this->xiangJiaoJ ($Fid,$DanShu);
        }
        unset($where,$field,$vo);
    }

        //加入货币流向
    public function addCashhistory($ID=0,$money=0,$bnum=0,$bz="",$type=0, $cashId = 0){
        $chistory=M('chistory');
        //找最近的当前的货币总额
        $fck_rs=$this->where("is_pay>0 and id=".$ID)->field('agent_cash,agent_use')->find();
        if($type==1){
            $old_money=$fck_rs['agent_cash'];
        }elseif($type==2){
            $old_money=$fck_rs['agent_use'];
        }

        $new_money=$old_money+$money;

        $data=array();
        $nowtime=time();
        $data['code']='M';
        $data['cash_id'] = $cashId;
        $data['time']=$nowtime;
        $data['content']=$bz;
        $data['old_money']=$old_money;
        $data['money']=$money;
        $data['new_money']=$new_money;
        $data['action_type']=$bnum;
        $data['did']=$ID;
        $data['type']=$type;   //1为马夫洛币，2为业绩钱包
        $chistory->add($data);
    }

    public function addencAdd($ID=0,$inUserID=0,$money=0,$name=null,$UID=0,$time=0,$acttime=0,$bz=""){
        //添加 到数据表
        if ($UID > 0) {
            $where = array();
            $where['id'] = $UID;
            $frs = $this->where($where)->field('nickname')->find();
            $name_two = $name;
            $name = $frs['nickname'] . ' 开通会员 ' . $inUserID ;
            $inUserID = $frs['nickname'];
        }else{
            $name_two = $name;
        }

        $data = array();
        $history = M ('history');

        $data['user_id']		= $inUserID;
        $data['uid']			= $ID;
        $data['action_type']	= $name;
        if($time >0){
        	$data['pdt']		= $time;
        }else{
        	$data['pdt']		= time();
        }
        $data['epoints']		= $money;
        if(!empty($bz)){
        	$data['bz']			= $bz;
        }else{
        	$data['bz']			= $name;
        }
        $data['did']			= $acttime;
        $data['type']			= 1;
        $data['allp']			= 0;
        if($acttime>0){
        	$data['act_pdt']	= $acttime;
        }
        $result = $history ->add($data);
        unset($data,$history);
    }

    public function huikuiAdd($ID=0,$tz=0,$zk,$money=0,$nowdate=null){
        //添加 到数据表

        $data                   = array();
        $huikui                = M ('huikui');
        $data['uid']            = $ID;
        $data['touzi']    = $tz;
        $data['zhuangkuang']            = $zk;
        $data['hk']        = $money;
        $data['time_hk']             = $nowdate;
        $huikui ->add($data);
        unset($data,$huikui);
    }


    //对碰1：1
    public function touch1to1(&$Encash,$xL=0,$xR=0,&$NumS=0){
        $xL = floor($xL);
        $xR = floor($xR);

        if ($xL > 0 && $xR > 0){
            if ($xL > $xR){
                $NumS = $xR;
                $xL = $xL - $NumS;
                $xR = $xR - $NumS;
                $Encash['0'] = $Encash['0'] + $NumS;
                $Encash['1'] = $Encash['1'] + $NumS;
            }
            if ($xL < $xR){
                $NumS = $xL;
                $xL   = $xL - $NumS;
                $xR   = $xR - $NumS;
                $Encash['0'] = $Encash['0'] + $NumS;
                $Encash['1'] = $Encash['1'] + $NumS;
            }
            if ($xL == $xR){
                $NumS = $xL;
                $xL   = 0;
                $xR   = 0;
                $Encash['0'] = $Encash['0'] + $NumS;
                $Encash['1'] = $Encash['1'] + $NumS;
            }
            $Encash['2'] = $NumS;
        }else{
            $NumS = 0;
            $Encash['0'] = 0;
            $Encash['1'] = 0;
        }
    }

    //计算奖金
    public function getusjj_new($id,$uid,$end_time=0)
    {   
        // $end_time=strtotime(date("2015-7-18"));  //测试
        $fee_rs = M('fee')->find(1);
        $cash=M('cash');
        $rs=$cash->where("id=".$id." and is_get=1 and is_done=1 and is_cancel=0 and is_out=0")->find();
        //求购金额

        $money=$rs['money'];
        //求购时间
        $begin_time=$rs['rdt'];
        $mrs=$this->where('id='.$uid.' AND is_pay>0 AND is_lock=0')->find();

        if($mrs)
        {   
            //静态收入
            $this->B1_fh($mrs['id'],$rs['x1'],$money,$end_time,$begin_time,$rs['id'],$rs['lock_time']);
            //推荐奖
            $this->B2_tjj($mrs['re_path'],$mrs['user_id'],$money);

            //区域奖（给同在一个省的报单中心）
            $this->B3_quyujiang($mrs['shop_id'],$mrs['user_id'],$money);
        }
        unset($mrs);
    }

    //检测是否是互助大使，有会员成为报单中心的时候调用
    public function check_is_aa($repath=0){
        //给上N代
        $where = "id in (0".$repath."0)";
        $lirs = $this->where($where)->field('id,u_level,is_fenh,re_nums')->order('re_level desc')->select();
        foreach ($lirs as $voo) {
            $myid=$voo['id'];
            //找他的直推会员
            $re_menber=$this->where("re_id=".$myid)->field("id")->select();

            $agent_num=0;  //记录有报单中心的线的个数
            foreach ($re_menber as $too) {
                $count_agent=$this->where("is_lock=0 and is_agent=2 and (re_path like '%,".$too['id'].",%' or id=".$too['id'].")")->count();

                if($count_agent>0){
                    $agent_num++;
                }
            }
            
            if($agent_num>=6){
                $this->execute("update __TABLE__ set is_aa=1 where id=".$myid);
            }
            unset($agent_num);
        }
    }

    public function B1_fh($uid,$inUserID=0,$money,$end_time,$begin_time,$kkid,$lock_time=0){
        $fee = M('fee');
        $Cash = D('Cash');
        $fee_rs=$fee->find();
        $yx=0;
        $yyx=0;
		$ca['id']=$kkid;
        $x5=$Cash->where($ca)->getfield('x5');
		if ($x5==0)
		{
		if ($yyx==0)
		{
        $fck_rs=$this->where("is_pay>0 and is_fenh=0 and id=".$uid)->find();
        if($fck_rs){
            //单位时间
            $per_time=$fee_rs['str21']*24*60+$fee_rs['str22']*60+$fee_rs['str23'];
            //封顶时间
            $feng=$fee_rs['str24']*24*60+$fee_rs['str25']*60+$fee_rs['str26'];
            $feng_time=$begin_time+$feng*60;
            
                    //yixi
        $ends=$Cash->where($ca)->getfield('okdt');
        $begs=$Cash->where($ca)->getfield('pdt');
        $yixijiang=$ends-$begs;
        $yixij=$fee_rs['s1'];
                    //yixi
			if($yixijiang<3600*$fee_rs['str45'])
			{$yixij=$fee_rs['s1']+$fee_rs['str2'];}
            if($lock_time==0){
                if($end_time>=$feng_time){
                $end_time=$feng_time;
                $s1=$yixij/100; //单位时间的利息
                $cha_time=($end_time-$begin_time)/($per_time*60);
                $num=floor($cha_time);  //单位时间的倍数
                //更新状态
                $Cash->execute("update __TABLE__ set is_out=1,x5=1 where id=".$kkid);

                }else{
                    $s1=$yixij/100; //单位时间的利息
                    $cha_time=($end_time-$begin_time)/($per_time*60);
                    $num=floor($cha_time);  //单位时间的倍数
                }
            }else{
                $s1=$yixij/100; //单位时间的利息
                $cha_time=($lock_time-$begin_time)/($per_time*60);
                $num=floor($cha_time);  //单位时间的倍数
                //更新状态
                $Cash->execute("update __TABLE__ set is_out=1,x5=1 where id=".$kkid);
            }
            
            // $money=$money+$money*$num*$s1;  //奖金=投资额+投资额*利息*天数
            $money=$money*$num*$s1;

            if($money>0){
                $l_time=$begin_time+$num*($per_time*60);// 本次分红时间
                //货币流向
                //yixi
                
                if($yx==0){
                
		
				
                $this->addCashhistory($uid,$money,1,"利息",1);
				 $Cash->execute("update __TABLE__ set x5=1 where id=".$kkid);
                $yx=1;
                }
                //yixi
                $this->rw_bonus($uid,$inUserID,1,$money);
                //更新时间
                $Cash->execute("update __TABLE__ set ldt={$l_time} where id=".$kkid);
                //互助辅导（利息领导奖！！！！已经取消）
                //$user_rs=$this->where("id=".$uid)->field("re_path,user_id")->find();
                //$this->lingdaojiang($user_rs['re_path'],$user_rs['user_id'],$money);
                //互助大使！！！！已经取消
                //$this->huzhuDS($user_rs['re_path'],$user_rs['user_id'],$money);


            }
        }  
        $yyx=1;
        }
		}
    }   
    //每天的自动分红
    public function B1_fh_perday(){
        $fee = M('fee');
        $cash = M('cash');
        $fee_rs=$fee->find();
        $end_time=time();
        //$l_time=strtotime(date("Y-m-d H:i:00"));
        //找到没发分红的，自动发
        $data=array();
        $data['is_get']=1; //已完成付款
        $data['is_pay']=1; //  已完成匹配
        $data['is_done']=1; //
        $data['is_out']=0;  //分红为出局
        $data['type']=0;  //求购的
        $cash_rs=$cash->where($data)->field('uid,money,ldt,id,rdt,x1,lock_time,is_out')->select();

        foreach ($cash_rs as $key => $voo) {
            # code...
            $uid=$voo['uid'];
            $money=$voo['money'];
            $begin_time=$voo['ldt'];
            $buy_time=$voo['rdt'];   //求购时间
            $lock_time=$voo['lock_time'];   //锁定时间
            $fck_rs=$this->where("is_pay>0 and is_fenh=0 and id=".$uid)->field('user_id')->find();
            // if($voo['id']==2346){
            //     dump($begin_time);exit;
            // }
            if($fck_rs){
//yixi
		$ends=$voo['okdt'];
        $begs=$voo['pdt'];
        $yixijiang=$ends-$begs;
        $yixij=$fee_rs['s1'];
			if($yixijiang<10800)
			{$yixij=$fee_rs['s1']+2;}
//yixi

            //单位时间
            $per_time=$fee_rs['str21']*24*60+$fee_rs['str22']*60+$fee_rs['str23'];
            //封顶时间
            $feng=$fee_rs['str24']*24*60+$fee_rs['str25']*60+$fee_rs['str26'];
            $feng_time=$buy_time+$feng*60;
            // exit;



            if($lock_time==0){
                if($voo['is_out']==0){
                   if($end_time>=$feng_time){
                    $end_time=$feng_time+$per_time-1;
                    $s1=$yixij/100; //单位时间的利息
                    $cha_time=($end_time-$begin_time)/($per_time*60);
                    $num=ceil($cha_time);  //单位时间的倍数 
                    //出局
                    $cash->execute("update __TABLE__ set is_out=1 where id=".$voo['id']);
                    }else{
                        $s1=$yixij/100; //单位时间的利息
                        $cha_time=($end_time-$begin_time)/($per_time*60);
                        $num=floor($cha_time);  //单位时间的倍数 

                    } 
                }            
            }else{
                $s1=$fee_rs['s1']/100; //单位时间的利息
                $cha_time=($lock_time-$begin_time)/($per_time*60);
                $num=floor($cha_time);  //单位时间的倍数
                //更新状态
                $cash->execute("update __TABLE__ set is_out=1 where id=".$voo['id']);
                
            }
            
            $money=$money*$num*$s1;  //奖金=投资额*利息*天数
            

                if($money>0){
                    $l_time=$begin_time+$num*($per_time*60);// 本次分红时间
                    //货币流向
                    $this->addCashhistory($uid,$money,1,"利息",1);
                    //更新奖金
                    $this->rw_bonus($uid,$voo['x1'],1,$money);
                    //更新时间
                    $cash->execute("update __TABLE__ set ldt={$l_time} where id=".$voo['id']);
                    $user_rs=$this->where("id=".$uid)->field("re_path,user_id")->find();
                    //互助辅导（利息领导奖）
                    //$this->lingdaojiang($user_rs['re_path'],$user_rs['user_id'],$money);
                    //互助大使
                    //$this->huzhuDS($user_rs['re_path'],$user_rs['user_id'],$money);
                }
            }
        }  
          
    }   

    //互助辅导
    public function lingdaojiang($repath,$inUserID=0,$money,$pre_type=0){
        $fee = M('fee');
        $fee_rs = $fee->field('str16,str15')->find(1);
        $str16 = explode("|", $fee_rs['str16']);  //拿的代数
        $count=max($str16);
        $str15=$fee_rs['str15']/100;  //比例

        //给上N代 get_level 存放购买金币的最高额
        $where = "id in (0".$repath."0)";
        $lirs = $this->where($where)->field('id,u_level,is_fenh,re_nums,get_level')->order('re_level desc')->limit($count)->select();
        $i = 1;

        foreach($lirs as $lrs){
            $money_count = 0;
            $myid = $lrs['id'];
            $myulv = $lrs['u_level'];
            $re_nums = $lrs['re_nums'];
            $is_fenh = $lrs['is_fenh'];
            $z_id = $lrs['z_id'];
            $get_level = $lrs['get_level'];
            $ssss = $myulv-1;
            $prii = $str15;
            $dai=$str16[$get_level];//代数
            $money_count = bcmul($money,$prii,2);
            if($i>$dai){
                $money_count =0;
            }
            if($money_count>0&&$is_fenh==0){
                if($pre_type==0){
                    //货币流向
                    $this->addCashhistory($myid,$money_count,4,"互助辅导",2);
                    //更新奖金
                    $this->rw_bonus($myid,$inUserID,4,$money_count);
                }else{
                    //更新奖金
                    $this->execute("update __TABLE__ set b3=".$money_count." where id=".$myid);
                }
                
            }
            $i++;
        }
        unset($lirs,$lrs);
        unset($where,$fee,$fee_rs);
    }

    //互助大使
    public function huzhuDS($repath,$inUserID=0,$money,$pre_type=0){
        $fee = M('fee');
        $fee_rs = $fee->field('str14')->find(1);
        $str14=$fee_rs['str14']/100;  //比例

        //给上N代is_aa 是否是互助大使
        $where = "id in (0".$repath."0) and is_aa=1";
        $lirs = $this->where($where)->field('id,u_level,is_fenh,re_nums')->order('re_level desc')->select();
        $i = 0;
        
        foreach($lirs as $lrs){
            $money_count = 0;
            $myid = $lrs['id'];
            $myulv = $lrs['u_level'];
            $re_nums = $lrs['re_nums'];
            $is_fenh = $lrs['is_fenh'];
            $z_id = $lrs['z_id'];
            $ssss = $myulv-1;
            $prii = $str14;
            $money_count = bcmul($money,$prii,2);

            if($money_count>0&&$is_fenh==0){
                if($pre_type==0){
                    //货币流向
                    $this->addCashhistory($myid,$money_count,5,"互助大使",2);
                    //更新奖金
                    $this->rw_bonus($myid,$inUserID,5,$money_count);
                }else{
                    //更新奖金
                    $this->execute("update __TABLE__ set b4=".$money_count." where id=".$myid);
                }
                
            }
            $i++;
        }
        unset($lirs,$lrs);
        unset($where,$fee,$fee_rs);
    }

    public function B2_tjj($repath,$inUserID=0,$money,$pre_type=0){
        $fee = M('fee');
        $fee_rs = $fee->field('s3,str14,str15,str16')->find(1);
        $s3 = explode("|", $fee_rs['s3']);
        $str14 = explode("|", $fee_rs['str14']);
        $str15 = explode("|", $fee_rs['str15']);
        $str16 = explode("|", $fee_rs['str16']);
        $count=count($s3);


	//奖金烧伤 
	$fee4 = M('fee');
        $fee_rs4 = $fee4->field('*')->find();
	if(1 == 2){
		//if ($fee_rs4['i48'])
				$mm= M('fck')->where("id=".$_SESSION[C('USER_AUTH_KEY')])->field('*')->limit(1)->find();
				$xiamoney = M('cash')->where("is_cancel=0 and type=0 and is_done=0 and is_get=0 and uid=".$mm['re_id'])->field("sum(money) as a")->find();
				if ($xiamoney['a']< $money)
				
				{
				$money = $xiamoney['a'];
				}
	}
				
	//烧伤结束 

        //给上N代
        $where = "id in (0".$repath."0)";
        $lirs = $this->where($where)->field('id,u_level,is_fenh,re_nums,jingli,zongjian,dongshi')->order('re_level desc')->limit($count)->select();
        $i = 0;
        
        foreach($lirs as $lrs){
            $moneyAfter = $money;
        	if ($lrs['jingli']==1) 
        	{$blyixi=$str15;}
        	elseif($lrs['zongjian']==1) 
        	{$blyixi=$str16;}
        	elseif($lrs['dongshi']==1) 
        	{$blyixi=$str14;}
        	else
        	{$blyixi=$s3;}
            $money_count = 0;
            $myid = $lrs['id'];
            $myulv = $lrs['u_level'];
            $re_nums = $lrs['re_nums'];
            $is_fenh = $lrs['is_fenh'];
            $z_id = $lrs['z_id'];
            $ssss = $myulv-1;
            $prii = $blyixi[$i]/100;
            if(true){
                //if ($fee_rs4['i48'])烧伤
                //$mm= M('fck')->where("id = " . $myid)->field('*')->limit(1)->find();
                if($this->userIsLockByWhere("id = " . $myid)){
                    continue;
                }
                $xiamoney = M('cash')->where("is_cancel=0 and type=0 and is_done=0 and is_get=0 and uid=".$myid)->field("sum(money) as a")->find();
                if ($xiamoney['a'] <  $money)
                {
                    $moneyAfter = $xiamoney['a'];
                }
            }
            $money_count = bcmul($moneyAfter,$prii,2);
            if($money_count>0&&$is_fenh==0){
                if($pre_type==0){
                    //货币流向
                    $this->addCashhistory($myid,$money_count,2,"推荐奖",2);
                    //更新奖金
                    $this->rw_bonus($myid,$inUserID,2,$money_count);
                }else{
                    //更新奖金
                    $this->execute("update __TABLE__ set b0=b0+".$money_count." where id=".$myid);
                }
            }
            $i++;
        }
        unset($lirs,$lrs);
        unset($where,$fee,$fee_rs);
    }



    public function B3_quyujiang($id,$inUserID=0,$money=0,$pre_type=0){
        $fee = M('fee');
        $fee_rs = $fee->field('s15')->find();
        $s15 = explode("|", $fee_rs['s15']);
       
        $frs = $this->where("id=".$id." and is_pay>0 and is_fenh=0 and is_agent=2")->field('id,u_level')->find();
        if($frs){
            $myid = $frs['id'];
            $ss = $frs['u_level']-1;
            $money_count = $money*$s15[0]/100;

            if($money_count>0){
                if($pre_type==0){
                    //货币流向
                    $this->addCashhistory($myid,$money_count,3,"报单奖",2);
                    //
                    $this->rw_bonus($myid,$inUserID,3,$money_count);
                }else{
                    //更新奖金
                    $this->execute("update __TABLE__ set b1=b1+".$money_count." where id=".$myid);
                }
                
            }
        }
        unset($fee,$fee_rs,$frs,$s14);
    }



        // public function B3_quyujiang($sheng,$inUserID=0,$money=0){
    //     $fee = M('fee');
    //     $fee_rs = $fee->field('s15')->find();
    //     $s15 = explode("|", $fee_rs['s15']);
       
    //     $frs = $this->where("quyu='".$sheng."' and is_pay>0 and is_fenh=0 and is_agent=2")->field('id,u_level')->find();

    //     if($frs){
    //         $myid = $frs['id'];
    //         $ss = $frs['u_level']-1;
    //         $money_count = $money*$s15[0]/100;

    //         if($money_count>0){
    //             //货币流向
    //             $this->addCashhistory($myid,$money_count,3,"区域奖",2);
    //             //
    //             $this->rw_bonus($myid,$inUserID,3,$money_count);
                
    //         }
    //     }
    //     unset($fee,$fee_rs,$frs,$s14);
    // }

    // public function check_islock(){
    //     $id = $_SESSION[C('USER_AUTH_KEY')];
    //     // $id="303";
    //     $nowdate=strtotime(date("Y-m-d"));

    //     $fck=M('fck');
    //     //到期日期小于今天且消费金额不足充值金额，则锁定分红
    //     $rs=$fck->where("id>1 and end_time<=".$nowdate." and g_level_a<cz_epoint")->field('id')->select();
    //     if($rs){
    //         foreach ($rs as $voo) {
    //         $result=$this->execute("update __TABLE__ set is_fenh=1,end_time=0,g_level_a=0,cz_epoint=0,gdt=".$nowdate." where id=".$voo['id']);
    //         }
    //     }

    // }

	//各种扣税
    public function rw_bonus($myid,$inUserID=0,$bnum=0,$money_count=0){
    	//$fee = M('fee');
    	//$fee_rs = $fee->field('*')->find();
        //$st1 = $fee_rs['s12']/100;//税收
        //$max = $fee_rs['s13']*10000;      //奖励值
        // $st2 = $fee_rs['s13']/100;//重复消费
        // $s5 =$fee_rs['s5'];     //1.5倍
		$money_ka = 0;
		$money_kb = 0;
		$money_kc = 0;

        //税收
        // $money_ka = bcmul($money_count,$st1,2);
        // if($money_ka<0)$money_ka = 0;

        // //重复消费 扣除10%作为重复消费，到达3000元进入公排网      
        // $money_kb = bcmul($money_count,$st2,2);
        // if($money_kb<0) $money_kb = 0;

		$last_m = $money_count;//-$money_ka-$money_kb;
	
        $bonus = M('bonus');
    	$bon = $this->_getTimeTableList($myid);

    	$inbb = "b".$bnum;

        if($bnum==1){
            $usqla = "zjj=zjj+{$last_m},agent_cash=agent_cash+{$last_m}";
        }else{
            $usqla = "zjj=zjj+{$last_m},agent_use=agent_use+{$last_m}";
        }
        
		$bsqla = '';//",b5=b5-".$money_ka;
		
		if($bnum==2){
            $usqla .= ',day_feng=day_feng+'.$money_count;
        }
			
		

        $bonus->execute("update __TABLE__ set b0=b0+".$last_m.",".$inbb."=".$inbb."+".$money_count.$bsqla." where id=".$bon['bid']); //加到记录表
        $this->execute("update __TABLE__ set ".$usqla." where id=".$myid);//加到fck
        unset($bonus);

        if($money_count>0){
            $this->addencAdd($myid,$inUserID,$money_count,$bnum,0,0,$bon['did']); 
        }
        // //奖金超过1.6万给上级10%的提成
        
        // $fck_rs=$this->where("is_pay>0 and is_fenh=0 and is_xf=0 and id=".$myid)->field("zjj,re_id,user_id")->find();
        
        // if($fck_rs['zjj']>=$max){
        //     $this->Bonus_b2_jfjl($fck_rs['re_id'],$fck_rs['user_id'],$fee_rs);
        // }
        // if($money_ka>0){
        //     $this->addencAdd($myid,$inUserID,-$money_ka,5,0,0,$bon['did']);
        // }
        // if($money_kb>0){
        //     $this->addencAdd($myid,$inUserID,-$money_kb,9,0,0,$bon['did']);
        // }
        // if($money_kc>0){
        //     $this->addencAdd($myid,$inUserID,-$money_kc,8,0,0,$bon['did']);
        // }
    	unset($fee,$fee_rs,$bonus);
    }
    
  
  

    //清空对碰封顶时间
	public function emptyTime(){
		$nowdate = strtotime(date('Y-m-d'));
		$this->query("UPDATE __TABLE__ SET `xx_money`=0,_times=".$nowdate." WHERE _times !=".$nowdate."");
	}


	public  function _getTimeTableList($uid)
    {
    	$times = M ('times');
    	$bonus = M ('bonus');
    	$boid = 0;
        $nowdate = strtotime(date('Y-m-d'))+3600*24-1;
    	//$nowdate = strtotime(date('Y-m-d H:i'));
    	$settime_two['benqi'] = $nowdate;
    	$settime_two['type']  = 0;
    	$trs = $times->where($settime_two)->find();
    	if (!$trs){
    		$rs3 = $times->where('type=0')->order('id desc')->find();
    		if ($rs3){
    			$data['shangqi']  = $rs3['benqi'];
    			$data['benqi']    = $nowdate;
    			$data['is_count'] = 0;
    			$data['type']     = 0;
    		}else{
    			$data['shangqi']  = strtotime('2010-01-01');
    			$data['benqi']    = $nowdate;
    			$data['is_count'] = 0;
    			$data['type']     = 0;
    		}
    		$shangqi = $data['shangqi'];
    		$benqi   = $data['benqi'];
    		unset($rs3);
    		$boid = $times->add($data);
    		unset($data);
    	}else{
    		$shangqi = $trs['shangqi'];
    		$benqi   = $trs['benqi'];
    		$boid = $trs['id'];
    	}
    	$_SESSION['BONUSDID'] = $boid;
    	$brs = $bonus->where("uid={$uid} AND did={$boid}")->find();
    	if ($brs){
            $mys['bid'] = $brs['id'];
    		$mys['did'] = $brs['did'];
    	}else{
    		$frs = $this->where("id={$uid}")->field('id,user_id')->find();
    		$data = array();
    		$data['did'] = $boid;
    		$data['uid'] = $frs['id'];
    		$data['user_id'] = $frs['user_id'];
    		$data['e_date'] = $benqi;
    		$data['s_date'] = $shangqi;
    		$mys['bid'] = $bonus->add($data);
            $mys['did'] = $boid;
    	}
    	return $mys;
    }
	
	protected function _2Mal($name=0,$wei=0) {
		//格式化数字，保留小数位数
		$map = sprintf('%.'.$wei.'f', (float)$name);
		return $map;
	}

    public function check_bon($num){
        $fee_rs = M('fee')->field('str30')->find(1);
        $str30 = $fee_rs['str30'];

        $ok_a = strpos($str30,','.$num.',');
        if($ok_a===false){
            return 0;
        }else{
            return 1;
        }
    }

    //根据手机号判断用户是否存在
    public function userIsExistByPhone($phone){
        $user = M('fck')->where("user_tel = '" . $phone . "'")->field('id')->find();
        return ($user["id"] > 0) ? true : false;
    }
    //根据user_id判断用户是否锁定
    public function userIsLockByWhere($where){
        $user = M('fck')->where($where)->field('id, is_lock')->find();
        if(empty($user)){
            return true;
        }
        return ($user["is_lock"] == 1) ? true : false;
    }
    //七天未排单直接封号
    public function isInOrder($userId){
        return;
        if(in_array($userId, [1, 4878])){
            return;
        }
        $where = [];
        $where['uid'] = $userId;
        $where['type'] = 0;
        $orderInfo = M('cash')->where($where)->order("id desc")->field('rdt')->find();
        if(empty($orderInfo)){
            M('fck')->where(["id"=>$userId])->save(["is_lock"=>1]);
        }else{
            if((time() - $orderInfo["rdt"]) / 3600*24 >= 7){
                M('fck')->where(["id"=>$userId])->save(["is_lock"=>1]);
            }
        }
    }
}
?>
