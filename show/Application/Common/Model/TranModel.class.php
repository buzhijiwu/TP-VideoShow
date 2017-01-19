<?php
/**
 * 事务处理模型
 */
namespace Common\Model;
use Think\Model;

class TranModel{
	
    // 购买vip
    function buy_vip($uid=0,$vip=1,$num=1){	
		$map['vipid'] = array('eq', $vip) ;
		$map['lantype'] = array('eq', getLanguage());
		$vips = M('vipdefinition')->where($map)->find();
		$price = price($uid);
		if($price < $vips['vipprice']*$num){
			return '余额不足';
		}else{
			$tran = new Model(); 
			$tran->startTrans();  
            
            // 1. 插入会员购买纪录
            $d1['userid'] = $uid;
			$d1['vipid'] = $vip;
			$d1['vipname'] = $vips['vipname'];
			$d1['pcsmallvippic'] = $vips['pcsmallvilogo'];
			$d1['spendmoney'] = $vips['vipprice']*$num;
			$d1['effectivetime'] = date('Y-m-d H:i:s');
			$d1['expiretime'] = date('Y-m-d H:i:s',strtotime('+'.$num.' month'));
            $r1 = $tran->table('ws_viprecord')->add($d1);
            // 2. 更新会员信息
			$r2 = $tran->table('ws_member')->where('userid='.$uid)->save(array('isvip'=>$vip,'time'=>time()));
			// 3. 插入交易记录表
			$d3['userid'] = $uid;
			$d3['tradetype'] = 7;
			$d3['vipid'] = $vip;
			$d3['spendamount'] = $vips['vipprice']*$num;
			$d3['tradetime'] = date('Y-m-d H:i:s');
			$d3['status'] = 1;		
			$r3 = $tran->table('ws_spenddetail')->add($d3);
			// 4. 更新用户余额和总消费
            $r4 = $tran->table('ws_balance')->where('userid='.$uid)->setInc('spendmoney',$vips['vipprice']*$num);
			$r5 = $tran->table('ws_balance')->where('userid='.$uid)->setDec('balance',$vips['vipprice']*$num);
	        if($r1&&$r2&&$r3&&$r4&&$r5){
	            $tran->commit();
				return 1;
	        }else{
	            $tran->rollback();
				return '操作失败';
	        }
		}
    }
    
    // 购买座驾
    function buy_car($uid=0,$id=1,$num=1){
    	// 1. 获取座驾信息
    	$car = M('commodity')->where(array('commodityid'=>array('eq', $id)))->find();
    	$price = price($uid);
		$prices = $car['commodityprice']*$num;
		if($price < $prices){
			return '余额不足';
		}else{
			$tran = new Model(); 
			$tran->startTrans();
			
			// 2. 插入座驾购买纪录
			$d1['userid'] = $uid;
			$d1['commodityid'] = $id;
			$d1['commodityname'] = $car['commodityname'];
			$d1['pcbigpic'] = $car['appbigpic'];
			$d1['pcsmallpic'] = $car['appbigpic'];
			$d1['appbigpic'] = $car['appbigpic'];
			$d1['appsmallpic'] = $car['appbigpic'];
			$d1['spendmoney'] = $prices;
			$d1['count'] = $num;
			$d1['effectivetime'] = date('Y-m-d H:i:s');
			$d1['expiretime'] = date('Y-m-d H:i:s',strtotime('+'.$num.' month'));
			$r2 = $tran->table('ws_equipment')->add($d1);
			
			// 3. 插入交易记录表
			$d3['userid'] = $uid;
			$d3['tradetype'] = 2;
			$d3['comid'] = $id;
			$d3['comname'] = $car['commodityname'];
			$d3['comprice'] = $car['commodityprice'];
			$d3['comcount'] = $num;
			$d3['spendamount'] = $prices;
			$d3['tradetime'] = date('Y-m-d H:i:s');
			$d3['status'] = 1;		
			$r3 = $tran->table('ws_spenddetail')->add($d3);
			
			// 4. 更新用户余额和总消费
			$r4 = $tran->table('ws_balance')->where('userid='.$uid)->setInc('spendmoney', $prices);
			$r5 = $tran->table('ws_balance')->where('userid='.$uid)->setDec('balance', $prices);
	        if($r2&&$r3&&$r4&&$r5){
	            $tran->commit();
				return 1;
	        }else{
	            $tran->rollback();
				return '操作失败';
	        }
		}
		
    }
    
    // 购买靓号
    function buy_nicenum($uid=0,$no=1,$num=1){
    	// 1. 获取座驾信息
    	$car = M('nicenumber')->where(array('niceno'=>array('eq', $id)))->find();
    	$price = price($uid);
		$prices = $car['commodityprice']*$num;
		if($price < $prices){
			return '余额不足';
		}else{
			$tran = new Model(); 
			$tran->startTrans();
			
			// 2. 插入座驾购买纪录
			$d1['userid'] = $uid;
			$d1['commodityid'] = $id;
			$d1['commodityname'] = $car['commodityname'];
			$d1['pcbigpic'] = $car['appbigpic'];
			$d1['pcsmallpic'] = $car['appbigpic'];
			$d1['appbigpic'] = $car['appbigpic'];
			$d1['appsmallpic'] = $car['appbigpic'];
			$d1['spendmoney'] = $prices;
			$d1['count'] = $num;
			$d1['effectivetime'] = date('Y-m-d H:i:s');
			$d1['expiretime'] = date('Y-m-d H:i:s',strtotime('+'.$num.' month'));
			$r2 = $tran->table('ws_equipment')->add($d1);
			
			// 3. 插入交易记录表
			$d3['userid'] = $uid;
			$d3['tradetype'] = 2;
			$d3['comid'] = $id;
			$d3['comname'] = $car['commodityname'];
			$d3['comprice'] = $car['commodityprice'];
			$d3['comcount'] = $num;
			$d3['spendamount'] = $prices;
			$d3['tradetime'] = date('Y-m-d H:i:s');
			$d3['status'] = 1;		
			$r3 = $tran->table('ws_spenddetail')->add($d3);
			
			// 4. 更新用户余额和总消费
			$r4 = $tran->table('ws_balance')->where('userid='.$uid)->setInc('spendmoney', $prices);
			$r5 = $tran->table('ws_balance')->where('userid='.$uid)->setDec('balance', $prices);
	        if($r2&&$r3&&$r4&&$r5){
	            $tran->commit();
				return 1;
	        }else{
	            $tran->rollback();
				return '操作失败';
	        }
		}
    	// 1. 插入会员购买纪录 viprecord 
		// 2. 更新会员信息  member 
		// 3. 插入交易记录表 spenddetail 
		// 4. 更新用户余额和总消费  balance
    }
	
	
}