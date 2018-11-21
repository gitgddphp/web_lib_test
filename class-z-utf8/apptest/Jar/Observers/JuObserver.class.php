<?php
namespace Jar\Observers;

use Jar\Facades\Jar;
use Jar\Facades\Jaraction;
use Jar\Facades\Role;
use Jar\Exception\TransException;

class JuObserver{
    //用户关联酒罐后
//    public static function add($param){
//        //修改酒坛状态
//        if (!Jar::update(['jar_id'=>$param['ju_jar_id']],['jar_status'=>1,'jar_id'=>$param['ju_jar_id']])){
//            prs(Jar::getError());
//        }
//        //添加用户操作记录
//        $data['jaraction_u_id']=$param['ju_u_id'];
//        $data['jaraction_jar_id']=$param['ju_jar_id'];
//        $data['jaraction_action']='buy';
//        $data['jaraction_comment']='购买封坛酒';
//        if(!Jaraction::add($data)){
//            prs(Jaraction::getError());
//        }
//    }

    /**
     * 用户确认关联封坛酒
     * @param $param
     */
    public static function confirm($param){
        //添加用户操作记录
        $data['jaraction_u_id']=$param['ju_u_id'];
        $data['jaraction_ju_id']=$param['id'];
        $data['jaraction_jar_id']=$param['ju_jar_id'];
        $data['jaraction_action']='confirm';
        $data['jaraction_comment']='用户:'.Role::getuser_name().'确认绑定酒罐';
        if(!Jaraction::add($data)){
            throw new TransException(Jaraction::getError());
        }
    }

    /**
     * 添加酒罐日志
     */
    public static function addJar($param){
        //添加用户操作记录
        $data['jaraction_u_id']=$param['ju_u_id'];
        $data['jaraction_ju_id']=$param['id'];
        $data['jaraction_jar_id']=$param['ju_jar_id'];
        $data['jaraction_action']='start';
        $data['jaraction_comment']='管理员:'.Role::getuser_name().'添加酒罐到:'.$param['ju_u_name'];
        if(!Jaraction::add($data)){
            throw new TransException(Jaraction::getError());
        }else{
            Jar::emit('Jardistribut',$param);       //分销
        }
    }

}
?>