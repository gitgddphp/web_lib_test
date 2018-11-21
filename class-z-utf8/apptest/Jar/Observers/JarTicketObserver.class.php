<?php
namespace Jar\Observers;

use Jar\Facades\Jar;
use Jar\Facades\JarDistribut;
use Jar\Facades\JarTicketAction;
use Jar\Facades\Order;
use Jar\Facades\Role;
use Jar\Facades\JarTicket;
use Jar\Exception\TransException;
use Jar\Facades\Users;

class JarTicketObserver{
    //生成领酒券日志
    public static function setticket($param){
        if ($param['id']){
            $data['ticket_id']=$param['id'];
            $data['ticket_action']='create';
            $data['ticket_comment']=Role::getrealname().'新增一张领酒券:'.$param['ticket_name'];
            if(!JarTicketAction::add($data)){
                throw new TransException(JarTicketAction::getError());
            }
        }
    }
    public static function useTicket($param){
        //日志记录
        if(!JarTicket::update(['id'=>$param['id']],['ticket_order_id'=>$param['order_id'],'ticket_status'=>C('Ticket.ASKFOR'),'ticket_use_id'=>Role::getuser_id(),'ticket_use_name'=>Role::getrealname(),'ticket_use_time'=>date('Y-m-d H:i:s')])){   //更新状态
            throw new TransException(JarTicketAction::getError());
        }
   //     D('Jar')->where(['id'=>$param['ticket_jar_id']])->setDec('jar_freeze',$param['ticket_capacity']); //发货时减冻结量
        $data['ticket_id'] = $param['id'];
        $data['ticket_act_id'] = Role::getuser_id();
        $data['ticket_action'] = '取酒';
        $data['ticket_act_name'] = Role::getrealname();
        $data['ticket_date'] = date('Y-m-d H:i:s');
        $data['ticket_comment'] = '用户:'.$data['ticket_act_name'].'取酒';
        if (!JarTicketAction::add($data)){ //订单操作记录
            throw new TransException(JarTicketAction::getError());
        }
    }

    /**
     * 使用分销领酒券
     * @param $param
     * @throws TransException
     */
    public static function useDistributTicket($param){
        if (!JarDistribut::setDec(['id'=>$param['ticket_distribut_id']],['distribut_freeze',$param['ticket_capacity']])){
            throw new TransException(JarDistribut::getError());
        }else{
            $data['ticket_id'] = $param['id'];
            $data['ticket_act_id'] = Role::getuser_id();
            $data['ticket_action'] = '取酒';
            $data['ticket_act_name'] = Role::getrealname();
            $data['ticket_date'] = date('Y-m-d H:i:s');
            $data['ticket_comment'] = '用户:'.$data['ticket_act_name'].'取酒';
            if (!JarTicketAction::add($data)){ //订单操作记录
                throw new TransException(JarTicketAction::getError());
            }else{
                JarTicket::emit('JarTicketcreateDistributOrder',$param);
            }
        }
    }

    /**
     * 使用分销领酒券后创建订单
     * @param $param
     * @throws TransException
     */
    public static function createDistributOrder($param){
        if (!Order::addOrder($param,$param['addr_id'],$param['package'])){
            throw new TransException(Order::getError());
        }
    }

    /**
     * 设置优惠券无效
     */
    public static function setInvalid($param){
        if ($param){
            foreach ($param as $item){
                if ($item['ticket_jar_id']){
                    Jar::backTicket($item);
                }elseif($item['ticket_distribut_id']){
                    JarDistribut::backTicket($item);
                }
                $data['ticket_id'] = $param['id'];
                $data['ticket_act_id'] = 0;
                $data['ticket_action'] = '超时返还';
                $data['ticket_act_name'] = '';
                $data['ticket_date'] = date('Y-m-d H:i:s');
                $data['ticket_comment'] = '领酒券超时 失效,返还存酒量';
                JarTicketAction::add($data);
            }
        }
    }

    /**
     * 用户取消优惠券
     */
    public static function setInvalidByUser($param){
        if ($param){
            if ($param['ticket_jar_id']){
                if (!Jar::backTicketByUser($param)){
                    throw new TransException(Jar::getError());
                }
            }elseif($param['ticket_distribut_id']){
                if (!JarDistribut::backTicketByUser($param)){
                    throw new TransException(JarDistribut::getError());
                }
            }else{
                return;
            }
            $username = Users::get($param['ticket_act_id'])['realname'];
            $data['ticket_id'] = $param['id'];
            $data['ticket_act_id'] = $param['ticket_act_id'];
            $data['ticket_action'] = '用户取消';
            $data['ticket_act_name'] = $username;
            $data['ticket_date'] = date('Y-m-d H:i:s');
            $data['ticket_comment'] = '用户:'.$username.'取消领酒券,返还存酒量'.$param['ticket_capacity'];
            if (!JarTicketAction::add($data)){
                throw new TransException(JarTicketAction::getError());
            }
        }
    }

    /**
     * 已支付包装费领酒券领取日志
     * @param $param
     * @throws TransException
     */
    public function usePaidTicketLog($param){
        $data['ticket_id'] = $param['id'];
        $data['ticket_act_id'] = Role::getuser_id();
        $data['ticket_action'] = 'get';
        $data['ticket_act_name'] = Role::getrealname();
        $data['ticket_date'] = date('Y-m-d H:i:s');
        $data['ticket_comment'] = '用户:'. Role::getrealname() ." 使用已支付的领酒券领酒(".$param['ticket_name'].")";
        if(!JarTicketAction::add($data)){
            throw new TransException(JarTicketAction::getError());
        }
    }

    /**
     * 重置领酒券收货人地址日志
     */
    public function resetConsigneeLog($param){
        $data['ticket_id'] = $param['id'];
        $data['ticket_act_id'] = Role::getuser_id();
        $data['ticket_action'] = 'get';
        $data['ticket_act_name'] = Role::getrealname();
        $data['ticket_date'] = date('Y-m-d H:i:s');
        $data['ticket_comment'] = '用户:'. Role::getrealname() ." 修改赠酒券".$param['ticket_name']."收货地址";
        if(!JarTicketAction::add($data)){
            throw new TransException(JarTicketAction::getError());
        }
    }
}
?>