<?php
namespace Jar\Services;

use Closure;
use Jar\Utils\Qrcode;
use Jar\Utils\Utils;
use Jar\Facades\Role;
use Jar\Facades\Ju;
use Jar\Exception\TransException;
class JarService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model= D('Jar/Jar');
    }

    public function add(Array $data){
        $item = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/Jar/Index/getJar/'.$data['jar_sn'];
        $data['jar_qrcode'] = Qrcode::wrtieFile($item,UPLOAD_PATH.'Qrcode/'.date('Y-m'));
        $data['jar_image'] =isset($data['jar_image'])?$data['jar_image']:'Public/upload/xcx/jar_back.png';
        $data['jar_thumb_img'] =isset($data['jar_thumb_img'])?$data['jar_thumb_img']:'Public/upload/xcx/jar_thumb.png';
        $data['jar_residue'] =$data['jar_capacity'];
        return parent::add($data);
    }

    /**
     * 新增酒罐,及关联信息
     * @param array $data
     * @return bool
     */
    public function addJar(Array $data){
        try{
            $this->startTrans();
//            $data['jar_status'] =C('Jar.HALF');
            $data['jar_temp_phone'] =$data['jar_temp_phone'];
            $data['jar_status'] =C('Jar.SALED');
            $data['user_name'] =$data['jar_temp_name'];
            if($id = $this->add($data)){
                $this->emit('Jaradd',array_merge($data,['id'=>$id]));
            }
            $this->commit();
            return $id;
        }catch(TransException $e){
            $this->rollback();
            self::$errors = $e->getMessage();
            return false;
        }
    }

    /**
     * 编辑酒坛信息,以及关联信息
     */
    public function editJar(Array $data){
        try{
            $this->startTrans();
            if (!$data['id']){
                self::$errors = '主键id不存在';
                return false;
            }
            $this->update(['id'=>$data['id']],$data);
            if (self::$errors){
                $this->rollback();
                return false;
            }
            $this->emit('JareditJuAfterJar',$data);
            $this->commit();
            return true;
        }catch(TransException $e){
            $this->rollback();
            self::$errors = $e->getMessage();
            return false;
        }
    }

    /**
     * 获取列表
     * @param array $map
     * @param Closure|null $cb
     * @return mixed
     */
    public function getList(Array $map, Closure $cb = null)
    {
        $datas = parent::getList($map, $cb);
        foreach ($datas as $k=>$datum){
            $datas[$k]['jar_image']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$datum['jar_image'];
            $datas[$k]['jar_thumb_img']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$datum['jar_thumb_img'];
        }
        return $datas;
    }

    /**
     * 后台操作绑定用户与酒坛 (废弃)
     * @param Int jar_id 酒坛id
     * @param String moblie 手机号码
     * @param String name 姓名
     */
    public function buyBySeller(Integer $jar_id,String $moblie,$name){
        if (Role::isManager()){
            if (Utils::is_moblie($moblie)){
                $jarData = $this->get($jar_id);
                if ($jarData['jar_status']==C('Jar.NULL')){
                    $map['id']=$jar_id;
                    try {
                        $this->startTrans();
                        $res = $this->update($map,['jar_temp_phone'=>$moblie,'jar_status'=>C('Jar.HALF'),'jar_temp_name'=>trim($name)]);
                        if ($res){
                            $this->model->emit("JarbuyBySeller",['id'=>$jar_id,'mobile'=>$moblie,'name'=>$name]);
                        }
                        $this->commit();
                        return $res;
                    } catch (TransException $e) {
                        $this->rollback();
                        self::$errors=$e->getMessage();
                        return false;
                    }
                }else{
                    self::$errors=L('_JAR_READY_BIND_');
                    return false;
                }
            }else{
                self::$errors=L('_USER_INVALID_PHONE_');
            }
        }else{
            self::$errors=L('_USER_INVALID_PRIV_');
            return false;
        }
    }

    /**
     * 用户确认绑定关系 (废弃)
     * @param Int jar_id 酒坛id
     */
    public function confirmByUser($jar_id){
        $jar_data = $this->get($jar_id);
        if (empty($jar_data['jar_temp_phone'])||($jar_data['jar_temp_phone']!=Role::getmobile())||($jar_data['jar_temp_name']!=Role::getrealname())){
            self::$errors=L('_JAR_NOT_BELONFTO_');
        }else{
            if ($jar_data['jar_status']!=C('Jar.HALF')){
                self::$errors=L('_JAR_NOT_ON_SALE_');
                return false;
            }
            $map['id']=$jar_id;
            try{
                $this->startTrans();
                $res = $this->update($map,['jar_status'=>C('Jar.SALED')]);
                if ($res){
                    $this->model->emit("JarconfirmByUser",$jar_data);
                }
                $this->commit();
            }catch(TransException $e){
                $this->rollback();
                self::$errors = $e->getMessage();
                return false;
            }
            return $res;
        }
    }

    /**
     * 根据串号获取酒罐的详细信息,包括关联的用户信息
     * @param $sn
     * @return mixed
     */
    public function getJarDetailBySn($sn){
        $jarData = $this->getList(['jar_sn'=>$sn]);
        if ($jarData){
            $jar_id=$jarData[0]['id'];
            $data = Ju::getList(['ju.ju_jar_id'=>$jar_id],function ($model){
                $model->alias('ju')->join('ty_users u ON ju.ju_u_id = u.user_id')->order('ju.id desc')->limit(1);
            });
            if ($data){
                $data[0]['head_pic']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$data[0]['head_pic'];
            }
            $jarData[0]['child']=$data[0];
        }
        return $jarData[0];
    }

    /**
     * 根据串号获取酒罐的详细信息,包括关联的用户信息
     * @param $sn
     * @return mixed
     */
    public function getJarDetailById($id){
        if (intval($id)){
            $jarData = $this->get($id);
            if ($jarData){
                $data = Ju::getList(['ju.ju_jar_id'=>$id,"ju_status"=>C('Ju.NORMAL')],function ($model){
                    $model->alias('ju')->join('ty_users u ON ju.ju_u_id = u.user_id')->order('ju.id desc')->limit(1);
                });
                if ($data){
                    $data[0]['head_pic']=$_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$data[0]['head_pic'];
                }
                $jarData['child']=$data[0];
            }
            return $jarData;
        }
        return [];
    }

    /**
     * 根据领酒券还原酒坛数据
     * @param array $ticket
     */
    public function backTicket(Array $ticket){
        if ($ticket){
            if ($data = $this->get($ticket['ticket_jar_id'])){
                if (($ticket['ticket_status'] == C('Ticket.NOTUSED')) || ($ticket['ticket_status'] == C('Ticket.ASKFOR'))){
                    if ($data['jar_freeze']>=$ticket['ticket_capacity']){
                        $saveData['jar_freeze']= $data['jar_freeze'] - $ticket['ticket_capacity'];
                    }else{
                        self::$errors = L('_TICKET_INVALID_CACPCITY_');
                        return false;
                    }
                }elseif(($ticket['ticket_status'] == C('Ticket.BACKING'))||($ticket['ticket_status'] == C('Ticket.INVALID'))||($ticket['ticket_status'] == C('Ticket.USED'))){
                    self::$errors = L('_TICKET_INVALID_STATUS_');
                    return false;
                }
                $saveData['jar_residue']= $data['jar_residue'] + $ticket['ticket_capacity'];
                if ($this->update(['id'=>$ticket['ticket_jar_id']],$saveData)){
                    $this->emit('JarsetSysBackLog',$ticket);
                    return true;
                }
            }
        }
    }

    /**
     * 根据领酒券还原酒坛数据(用户操作)
     * @param array $ticket
     */
    public function backTicketByUser($ticket){
        if ($ticket){
            if ($data = $this->get($ticket['ticket_jar_id'])){
                if (($ticket['ticket_status'] == C('Ticket.NOTUSED')) || ($ticket['ticket_status'] == C('Ticket.ASKFOR'))){
                    if ($data['jar_freeze']>=$ticket['ticket_capacity']){
                        $saveData['jar_freeze']= $data['jar_freeze'] - $ticket['ticket_capacity'];
                    }else{
                        self::$errors = L('_TICKET_INVALID_CACPCITY_');
                        return false;
                    }
                }elseif(($ticket['ticket_status'] == C('Ticket.BACKING'))||($ticket['ticket_status'] == C('Ticket.INVALID'))||($ticket['ticket_status'] == C('Ticket.USED'))){
                    self::$errors = L('_TICKET_INVALID_STATUS_');
                    return false;
                }
                $saveData['jar_residue']= $data['jar_residue'] + $ticket['ticket_capacity'];
                if ($this->update(['id'=>$ticket['ticket_jar_id']],$saveData)){
                    $this->emit('JarsetUserBackLog',$ticket);
                    return true;
                }
            }
        }
        self::$errors = L('_TICKET_INVALID_ID_');
    }


    /**
     * 上传图片
     * @param File img 图片
     */
    public function upload(){
        $info = Utils::upload('Jarimg');
        if ($info){
            return $info;
        }else{
            self::$errors = Utils::$errors;
            return false;
        }
    }

    public function test(){
        echo 'service-test';
    }
}
?>