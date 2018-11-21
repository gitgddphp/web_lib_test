<?php
namespace Jar\Services;

use Jar\Exception\TransException;
use Jar\Facades\Jar;
use Jar\Facades\JarDistribut;
use Jar\Facades\JarTicket;
use Jar\Facades\JarTicketAction;
use Jar\Facades\Role;
use Jar\Facades\Users;
use Jar\Utils\Utils;
use Jar\Facades\Ju;
use Jar\Facades\Order;

class JarTicketService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/JarTicket');
    }

    /**
     * ticket_jar_id,ticket_capacity
     * @param array $data
     * @return bool|mixed
     */
    public function add(Array $data)
    {
        $data['ticket_act_id'] = isset($data['ticket_act_id'])?$data['ticket_act_id']:Role::getuser_id();
        $data['ticket_name'] = isset($data['ticket_name'])?$data['ticket_name']:Role::getuser_name().'的赠酒卡';
        $data['ticket_date'] = isset($data['ticket_date'])?$data['ticket_date']:Utils::date("Y-m-d H:i:s");
        $data['ticket_deadline'] = date('Y-m-d H:i:s',strtotime($data['ticket_date'].' +'.tpCache('basic.ticket_time_out').' day'));
        return parent::add($data);
    }

    /**
     * 生成领酒券
     * @param Int id 酒罐id
     * @param Int num   取酒量
     */
    public function getTicket($id,$num){
        $num = Utils::regNum($num);
//        if (Users::checkPass($pass)){
            if($jarData = Ju::getOwnJarByJarId($id)){
                $_num = intval($jarData['jar_residue'])-intval($num);
                if ($_num>=0){
                    $data['ticket_jar_id']=$id;
                    $data['ticket_capacity']=$num;
                    $data['ticket_name'] = Role::getrealname().'分享的领酒券';
                    try{
                        $this->startTrans();
                        if($tid = $this->add($data)){
                            $data = array_merge($data,['id'=>$tid]);
                            $this->emit('JarTicketsetticket',$data);
                            Jar::emit('JarupdateJarAfterGetTicket',array_merge($jarData,['num'=>$num,'id'=>$id,'jar_residue'=>$_num,'jar_freeze'=>$jarData['jar_freeze']+$num,'jar_status'=>C('Jar.GET')]));
                        }else{
                            return false;
                        }
                        $this->commit();
                        return $data;
                    }catch(TransException $e){
                        $this->rollback();
                        self::$errors=$e->getMessage();
                        return false;
                    }
                }else{
                    self::$errors = L('_GETJAR_NOT_ENOUGH_');
                }
            }else{
                self::$errors = L('_JAR_INVALID_ID_');
            }
//        }else{
//            self::$errors = Users::getError();
//        }
    }

    /**
     * 使用领酒券
     * @param Int id 领酒券id
     * @param Int id 收货地址id
     */
    public function useTicket($id, $addressId,$package = array()){
        if($data = $this->getNotUsedTicketById($id)){
            try{
                $this->startTrans();
                if ($id = Order::add($data, $addressId,$package)){
                    $this->emit('JarTicketuseticket',array_merge($data,['order_id'=>$id]));
                    $this->commit();
                    return $id;
                }
                return false;
            }catch(TransException $e){
                $this->rollback();
                self::$errors = $e->getMessage();
                return false;
            }
        }
    }

    /**
     * 使用已支付包装费的领酒券
     * @param Int id 领酒券id
     * @param Int id 收货地址id
     */
    public function usePaidTicket($id, $addressId){
        $data = $this->find(['id'=>$id],function ($model){
            $model->alias('jt')
                ->join('ty_order o ON o.order_id = jt.ticket_order_id','LEFT');
        });
        if($data&&($data['ticket_act_id'] == $data['ticket_use_id'])&&($data['ticket_status']!=C('Ticket.SENDING'))){       //使用者和拥有者同一人,且未发货
            try{
                $this->startTrans();
                if ($this->update(['id'=>$data['id']],['ticket_use_id'=>Role::getuser_id(),'ticket_use_name'=>Role::getrealname(),'ticket_use_time'=>date("Y-m-d H:i:s")])){
                    if (($addressId)&&(!Order::setOrderConsignee($data['order_id'],$data['user_id'],$addressId))){
                        self::$errors = Order::getError();
                    }else{
                        $this->emit("JarTicketusePaidTicketLog",$data);
                        $this->commit();
                        return true;
                    }
                }
                return false;
            }catch(TransException $e){
                $this->rollback();
                self::$errors = $e->getMessage();
                return false;
            }
        }else{
            self::$errors = L('_TICKET_INVALID_ID_');
        }
    }

    /**
     * 使用分销领酒券
     * @param Int id 领酒券id
     * @param Int id 收货地址id
     */
    public function useDistributTicket($id, $addressId,$package = array()){
        if($data = $this->getNotUsedTicketById($id)){
            try{
                $this->startTrans();
                $upData = ['ticket_status'=>C('Ticket.ASKFOR'),'ticket_use_id'=>Role::getuser_id(),'ticket_use_name'=>Role::getrealname(),'ticket_use_time'=>date('Y-m-d H:i:s')];
                if ($this->update(['id'=>$id],$upData)){
                    $this->emit('JarTicketuseDistributTicket',array_merge($data,['addr_id'=>$addressId,'package'=>$package]));
                }
                $this->commit();
                return Order::getLastInsID();
            }catch(TransException $e){
                $this->rollback();
                self::$errors = $e->getMessage();
                return false;
            }
        }
    }

    /**
     * 获取没有使用过的领酒券
     */
    public function getNotUsedTicketById($id){
        if (intval($id)){
            if($data = $this->get($id)){
                if ($data['ticket_status']!=C('Ticket.NOTUSED')){
                    self::$errors = L('_TICKET_USED_ID_');
                    return false;
                }else{
                    return $data;
                }
            }
        }
        self::$errors=L('_TICKET_INVALID_ID_');
        return false;
    }

    /**
     * 获取使用过的领酒券
     */
    public function getUsedTicketById($id){
        if (intval($id)){
            if($data = $this->get($id)){
                if ($data['ticket_status']!=C('Ticket.ASKFOR')){
                    self::$errors = L('_TICKET_USED_ID_');
                    return false;
                }else{
                    return $data;
                }
            }
        }
        self::$errors=L('_TICKET_INVALID_ID_');
        return false;
    }

    /**
     * 获取我发出去的领酒券列表
     */
    public function getOwnProductedTicket(){
        return $this->getList(['ticket_act_id'=>Role::getuser_id()],function($model){
            $model->alias('tc')->join("ty_jar jar ON jar.id = tc.ticket_jar_id");
        });
    }

    /**
     * 获取分销提成的领酒券
     * @param $id
     * @param $num
     */
    public function getDistributTicket($id,$num){
        $num = Utils::regNum($num);
//        if (Users::checkPass($pass)){
            if($jarData = JarDistribut::getOwnDistributById($id)){
                if ($jarData['distribut_status']==C('Distribut.NORMAL')){
                    $_num = intval($jarData['distribut_residue'])-intval($num);
                    if ($_num>= 0){
                        $data['ticket_distribut_id']=$id;
                        $data['ticket_capacity']=$num;
                        $data['ticket_name'] = Role::getrealname().'分享的领酒券';
                        try{
                            $this->startTrans();
                            if($tid = $this->add($data)){
                                $data = array_merge($data,['id'=>$tid]);
                                $this->emit('JarTicketsetticket',$data);
                                JarDistribut::emit('JarDistributupdateJarAfterGetTicket',array_merge($jarData,['num'=>$num,'id'=>$id,'distribut_residue'=>$_num,'distribut_freeze'=>$jarData['distribut_freeze']+$num]));
                            }else{
                                return false;
                            }
                            $this->commit();
                            return $data;
                        }catch(TransException $e){
                            $this->rollback();
                            self::$errors=$e->getMessage();
                            return false;
                        }
                    }else{
                        self::$errors = L('_GETJAR_NOT_ENOUGH_');
                    }
                }else{
                    self::$errors = L('_DISTRIBUT_JAR_NOTUSE_STATUS_');
                }
            }else{
                self::$errors = L('_GETJAR_INVALID_ID_');
            }
//        }else{
//            self::$errors = Users::getError();
//        }
    }

    /**
     * 获取领酒券酒坛信息
     * @param Int $id 领酒券id
     */
    public function getTicketJarInfo($id){
        if($ticketData = $this->get($id)){
            return Jar::find([],function ($model) use ($ticketData){
                if ($id = $ticketData['ticket_jar_id']){
                    $model->where(['id'=>$id]);
                }elseif($id = $ticketData['ticket_distribut_id']){
                    $model->where(['dis.id'=>$id])->alias('jar')
                        ->join('ty_jar_distribut dis ON dis.distribut_jar_id = jar.id');
                }else{
                    self::$errors = L('_TICKET_INVALID_ID_');
                }
            });
        }else{
            self::$errors = L('_TICKET_INVALID_ID_');
        }
    }

    /**
     * 领酒券详情
     * @param Int id 领酒券id
     */
    public function getTicketInfoById($id){
        if($data = $this->get($id)){
            $data['jarData']=$this->getTicketJarInfo($id);
            $data['consignee'] = Order::find(['order_id'=>$data['ticket_order_id']],function($model){
                $model->field('consignee,country,city,district,twon,address,zipcode');
            });
        }else{
            self::$errors = L('_TICKET_INVALID_ID_');
            return false;
        }
        return $data;
    }

    /**
     * 用户取消领酒券
     * @param Int id 领酒券id
     */
    public function setInvalidByUser($id){
        if($data = $this->get($id)){
            try{
                $this->startTrans();
                if($this->update(['id'=>$id],['ticket_status'=>C('Ticket.INVALID')])){
                    $this->emit('JarTicketsetInvalidByUser',$data);
                }
                $this->commit();
                return true;
            }catch(TransException $e){
                self::$errors = $e->getMessage();
                $this->rollback();
                return false;
            }
        }
        self::$errors=L('_TICKET_INVALID_ID_');
    }

    /**
     * 设置领酒券无效 最好是定时任务,不会抛异常,
     */
    public function setInvalidByTimeout(){
        $timeout = tpCache('basic.ticket_time_out');
        $map['ticket_date'] = array('lt',date("Y-m-d H:i:s",strtotime('- '.$timeout.' day')));
        $map['ticket_status'] = C('Ticket.NOTUSED');
        if($datas = $this->getList($map)){
            try{
//                $this->startTrans();
                if($this->update($map,['ticket_status'=>C('Ticket.INVALID')])){
                    $this->emit('JarTicketsetInvalid',$datas);
                }
//                $this->commit();
                return true;
            }catch(Exception $e){
                self::$errors = $e->getMessage();
//                $this->rollback();
                return false;
            }

        }
        return true;
    }

    /**
     * 获取领酒券列表
     */
    public function getTicketList(){
        $map = array();
        return $this->getListWithPage($map,[1,10],function ($model){

        });
    }

    /**
     * 获取某个领酒券的日志
     * @param $id
     */
    public function getTicketLogById($id){
        if ($id = intval($id)){
            return JarTicketAction::getList(['ticket_id'=>$id]);
        }
        return [];
    }

    /**
     * 修改领酒券使用的地址,收货人信息
     * @param $id 领酒券id
     * @param $add_id   地址id
     */
    public function resetOrderConsignee($id,$add_id){
        $data = $this->find(['id'=>$id],function ($model){
            $model->alias('jt')
                ->join('ty_order o ON o.order_id = jt.ticket_order_id','LEFT');
        });
        if($data&&($data['ticket_act_id'] == $data['ticket_use_id'])&&($data['ticket_status']!=C('Ticket.SENDING'))){       //使用者和拥有者同一人,且未发货
            try{
                $this->startTrans();
                if (Order::setOrderConsignee($data['order_id'],$data['user_id'],$add_id)){
                    $this->emit("JarTicketresetConsigneeLog",$data);
                    $this->commit();
                    return true;
                }else{
                    self::$errors = Order::getError();
                }
                return false;
            }catch(TransException $e){
                $this->rollback();
                self::$errors = $e->getMessage();
                return false;
            }
        }else{
            self::$errors = L('_TICKET_INVALID_ID_');
        }
    }
}