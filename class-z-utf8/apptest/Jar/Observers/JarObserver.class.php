<?php
namespace Jar\Observers;

use Jar\Facades\Jar;
use Jar\Facades\Jaraction;
use Jar\Facades\JarDistribut;
use Jar\Facades\Ju;
use Jar\Facades\Role;
use Jar\Facades\JarTicket;
use Jar\Facades\Users;
use Jar\Exception\TransException;
class JarObserver{
    //管理员添加酒罐后
    public static function add($param){
        $data['ju_u_id']=$param['user_id'];
        $data['ju_u_name']=$param['user_name'];
        $data['ju_timelimit']=$param['ju_timelimit'];
        $data['ju_rate']=0.001;
        $data['ju_jar_id']=$param['id'];
        $res = Ju::add($data);
        if ($res){
            Ju::emit('JuaddJar',array_merge($data,['id'=>$res]));
        }else{
            throw new TransException(Ju::getError());
        }
    }

    /**
     * 添加酒罐后分销逻辑
     * @param $param
     */
    public static function distribut($param){
        if(!JarDistribut::distribut($param)){
            throw new TransException(Jar::getError());
        }
    }
    /**
     * 后台编辑酒坛后编辑关联关系
     * @param $param
     */
    public static function editJuAfterJar($param){
        $juData = Ju::find(['ju_jar_id'=>$param['id']],function($model){
            $model->order('id desc');
        });
        if (isset($param['user_id'])&&intval($param['user_id'])){
            if (($juData['ju_status']!=C('Ju.NORMAL'))&&($juData['ju_status']!=C('Ju.USERBACK'))){ //可以变更用户,新增关联关系
                $data['user_id'] = $param['user_id'];
                $data['user_name'] = $param['jar_temp_name'];
                $data['ju_timelimit']=$param['ju_timelimit'];
                $data['id']=$param['id'];
                Jar::emit("Jaradd",$data);
                Jar::update(['id'=>$param['id'],['jar_status'=>C('Jar.SALED')]]);
            }else{
                throw new TransException(L('_JU_CANNOT_CHANGE_'));
            }
        }else{
            if (isset($param['ju_timelimit'])&&($juData['ju_timelimit']!=$param['ju_timelimit'])&&($juData['ju_timelimit']==1)){
                $param['ju_deadline'] = date('Y-m-d H:i:s',strtotime($juData['ju_date'].' +'.$param['ju_timelimit'].' year'));
            }
            Ju::update(['ju_jar_id'=>$param['id'],'ju_status'=>C('Ju.NORMAL')],$param);
            if (Ju::getError()){
                throw new TransException(Ju::getError());
            }
        }
    }

    public static function setLogs($param){
        if (!Jaraction::add()){
            throw new TransException(Jaraction::getError());
        }
    }

    /**
     * 管理员绑定酒坛与电话号码之后的记录
     * @param $param
     */
    public static function buyBySeller($param){
        $data['jaraction_u_id']=0;
        $data['jaraction_ju_id']=0;
        $data['jaraction_act_id']=Role::getuser_id();
        $data['jaraction_jar_id']=$param['id'];
        $data['jaraction_action']='buy';
        $data['jaraction_comment']='管理员:'.Role::getuser_name().' 绑定该酒坛到电话号码:'.$param['mobile'].'用户名:'.$param['name'];
        $res = Jaraction::add($data);
        if (!$res){
            throw new TransException(Jaraction::getError());
        }
    }

    /**
     * 用户确认酒坛绑定
     * @param $param
     */
    public static function confirmByUser($param){
        $data['ju_u_id']=Role::getuser_id();
        $data['ju_u_name']=Role::getrealname()?Role::getrealname():Role::getnickname();
        $data['ju_jar_id']=$param['id'];
        $res = Ju::add($data);
        if ($res){
            Ju::emit('Juconfirm',array_merge($data,['id'=>$res]));
        }else{
            throw new TransException(Ju::getError());
        }
    }

    /**
     * 生成领酒券,更新酒罐
     * @param $param
     */
    public static function updateJarAfterGetTicket($param){
        $data['jar_residue'] = $param['jar_residue'];
        $data['jar_freeze'] = $param['jar_freeze'];
        $data['jar_status'] = $param['jar_status'];
        if(!Jar::update(['id'=>$param['id']],$data)){
            throw new TransException(Jar::getError());
        }else{
            Jar::emit('JarsetTickeLog',$param);
        }
    }




    /**
     * 用户生成领酒券日志
     * @param $param
     * @throws TransException
     */
    public static function setTickeLog($param){
        $data['jaraction_u_id']=$param['ju_u_id'];
        $data['jaraction_ju_id']=$param['ju_id'];
        $data['jaraction_act_id']= Role::getuser_id();
        $data['jaraction_jar_id']=$param['id'];
        $data['jaraction_action']='get';
        $data['jaraction_comment']='用户:'.Role::getrealname().'生成领酒券,消耗'.$param['num'].$param['jar_unit'];
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
        $data['jaraction_ju_id']=Ju::getCurrentJuId($param['ticket_jar_id'],$param['ticket_act_id']);
        $data['jaraction_act_id']= -1;
        $data['jaraction_jar_id']=$param['ticket_jar_id'];
        $data['jaraction_action']='timeout';
        $data['jaraction_comment']='领酒券超时未领取,返还存酒量'.$param['ticket_capacity'];
        $res = Jaraction::add($data);
        if (!$res){
//            throw new TransException(Jaraction::getError());
        }
    }

    /**
     * 系统判定领酒券失效日志
     */
    public static function setUserBackLog($param){
        $data['jaraction_u_id']=$param['ticket_act_id'];
        $data['jaraction_ju_id']=Ju::getCurrentJuId($param['ticket_jar_id'],$param['ticket_act_id']);
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