<?php
namespace Jar\Observers;

use Jar\Exception\TransException;
use Jar\Facades\JarDistribut;
use Jar\Facades\Role;
use Jar\Facades\Users;
use Jar\Facades\Jaraction;
class JarDistributObserver{

    //分销产生后记录
    public static function add($param){
        $data['user_id'] = $param['distribut_u_id'];
        $data['user_money'] = 0;
        $data['frozen_money'] = 0;
        $data['pay_points'] = 0;
        $data['distribut_money'] = $param['distribut_capacity'];
        $data['change_time'] = time();
        $data['desc'] = '用户'.$param['distribut_jar_u_id'].'购买封坛酒:'.$param['distribut_jar_name'].',获得提成';
        if (!M('account_log')->add($data)){
            throw new TransException('提层记录失败');
        }
    }

    /**
     * 生成领酒券,更新酒罐
     * @param $param
     */
    public static function updateJarAfterGetTicket($param){
        $data['distribut_residue'] = $param['distribut_residue'];
        $data['distribut_freeze'] = $param['distribut_freeze'];
        $data['distribut_status'] = $param['distribut_status'];
        if (!$data['jar_residue']){
            $data['distribut_status'] = C('Distribut.INVALID');
        }
        if(!JarDistribut::update(['id'=>$param['id']],$data)){
            throw new TransException(Jar::getError());
        }else{
            JarDistribut::emit('JarDistributsetTickeLog',$param);
        }
    }

    /**
     * 生成日志
     * @param $param
     * @throws TransException
     */
    public static function setTickeLog($param){
        $data['jaraction_u_id']=$param['distribut_u_id'];
        $data['jaraction_ju_id']=0;
        $data['jaraction_act_id']= Role::getuser_id();
        $data['jaraction_jar_id']=$param['distribut_jar_id'];
        $data['jaraction_action']='get';
        $data['jaraction_comment']='用户:'.Role::getrealname().'生成领酒券,消耗提成:'.$param['distribut_jar_name'].' '.$param['num'].'斤';
        $res = Jaraction::add($data);
        if (!$res){
            throw new TransException(Jaraction::getError());
        }
    }

    /**
     * 系统判定领酒券失效日志
     */
    public static function setSysBackLog($param){
        $data['jaraction_u_id']=$param['ticket_act_id'];
        $data['jaraction_ju_id']=0;
        $data['jaraction_act_id']= -1;
        $data['jaraction_jar_id']=$param['ticket_jar_id'];
        $data['jaraction_action']='timeout';
        $data['jaraction_comment']='领酒券超时未领取,返还提成存酒量'.$param['ticket_capacity'];
        $res = Jaraction::add($data);
        if (!$res){
            throw new TransException(Jaraction::getError());
        }
    }

    /**
     * 用户取消领酒券日志
     */
    public static function setUserBackLog($param){
        $data['jaraction_u_id']=$param['ticket_act_id'];
        $data['jaraction_ju_id']=0;
        $data['jaraction_act_id']= Role::getuser_id();
        $data['jaraction_jar_id']=$param['ticket_jar_id'];
        $data['jaraction_action']='getback';
        $data['jaraction_comment']='用户:'.Users::get($param['ticket_act_id'])['realname'].'取消领酒券,返还存酒量'.$param['ticket_capacity'];
        $res = Jaraction::add($data);
        if (!$res){
            throw new TransException(Jaraction::getError());
        }
    }
}
?>