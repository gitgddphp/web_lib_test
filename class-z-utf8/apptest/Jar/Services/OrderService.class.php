<?php
namespace Jar\Services;

use Jar\Exception\TransException;
use Jar\Facades\JarTicket;
use Jar\Facades\Role;
use Jar\Facades\UserAddress;
use Jar\Facades\Jar;
use Jar\Facades\OrderGoods;
use Jar\Facades\TransPort;
use Jar\Facades\Goods;
use Jar\Facades\JarDistribut;

class OrderService extends Service {

    public function __construct(){
        parent::__construct();
        $this->model = D('Jar/Order');
    }
    /**
     * [add description]
     * @param Array $jarTicketInfo [description]
     * @param int   $addressId     [description]
     * @param Array $package       [description]
     */
    public function add(Array $jarTicketInfo, int $addressId, Array $package, $shipping_code=0)
    {
        if (!$addrInfo = UserAddress::find(['address_id'=>$addressId,'user_id'=>Role::getuser_id()])){     //地址信息
            // self::$errors = '地址未查询到';
            // return false;
            $addrInfo = [
            'zipcode'=>0,'address'=>0,
            'twon'=>0,'district'=>0,
            'city'=>0,'country'=>0,
            'consignee'=>0
            ];
        }

        $jarInfo = Jar::find(['id'=>$jarTicketInfo['ticket_jar_id']]);//$jarTicketInfo['ticket_jar_id']);//酒坛信息
        // $shippingInfo = D('plugin')->where(['status'=>1,'type'=>'shipping']);//物流信息
        // $shippingInfo = $shippingInfo[0];
        $userId = Role::getuser_id();
        //是否开启免邮
        if(!tpCache('basic.post_fee_open')){
            list($shipping_code, $shippingPrice) = TransPort::getTransMoney($jarTicketInfo['ticket_capacity'], $addressId, $shipping_code);
        }else{
            $shippingPrice = 0;
        }

        if($shippingPrice === false){
            // self::$errors = TransPort::getError();
            // return false;
            $shippingPrice = 0;
        }
    //    $shippingPrice = 1;
        $packagePrice = Goods::countMoney($package);
    //    $packagePrice = 1;

        $order = [
            'user_id'=>$userId,
            'consignee'=>$addrInfo['consignee'],
            'country'=>$addrInfo['country'],
            'city'=>$addrInfo['city'],
            'district'=>$addrInfo['district'],
            'twon'=>$addrInfo['twon'],
            'address'=>$addrInfo['address'],
            'zipcode'=>$addrInfo['zipcode'],
            'shipping_code'=>$shipping_code,//$shippingInfo['code'],
            'shipping_name'=>'',//$shippingInfo['name'],
            'deleted'=>0,//$shippingInfo['name'],
            'u_text'=>'',//$shippingInfo['name'],
            'pay_status'=>0,
            'pay_time'=>time(),
            'add_time'=>time(),
            'shipping_status'=>0,
            'goods_price'=>$packagePrice,
            'shipping_price'=>$shippingPrice,
            'order_amount'=>$shippingPrice+$packagePrice,
            'total_amount'=>$shippingPrice+$packagePrice,
            'order_sn'=>date('YmdHis').rand(1000,9999)
        ];
        if (!$orderId = parent::add($order)){   //创建订单
            return false;
        }

        $orderInfos[] = [           //酒信息
        'goods_id'=>$jarInfo['id'],
        'ticket_id'=>$jarTicketInfo['id'],
        'goods_name'=>$jarInfo['jar_name'],
        'order_id'=>$orderId,
        'goods_sn'=>$jarInfo['jar_sn'],
        'goods_num'=>$jarTicketInfo['ticket_capacity'],//取多少酒
        'market_price'=>0,
        'shop_price'=>0,
        'cost_price'=>0,
        'deleted'=>0,//取多少酒
        ];
        if (!empty($package)){  //包装信息
            $goods_id_num = array_column($package,'num','id');
            $map['goods_id']=array('in',array_keys($goods_id_num));
            $datas = Goods::getList($map);
            foreach ($datas as $data){
                $temp = [
                    'goods_id'=>$data['goods_id'],
                    'ticket_id'=>0,
                    'goods_name'=>$data['goods_name'],
                    'order_id'=>$orderId,
                    'goods_sn'=>$data['goods_sn'],
                    'market_price'=>$data['market_price'],
                    'shop_price'=>$data['shop_price'],
                    'cost_price'=>$data['cost_price'],
                    'goods_num'=>$goods_id_num[$data['goods_id']],//取多少酒
                    'deleted'=>0,//取多少酒
                ];
                $orderInfos[] = $temp;
            }
        }
        if(!$ogid = OrderGoods::addAll($orderInfos)){    //插入酒信息
            throw new TransException(OrderGoods::getError());
        }
        return $orderId;
    }

    /**
     * [getOwnList description]
     * @param  Int    $status [状态 0全部,1待支付,2待发货,3待收货,4完成,5退款]
     * @param  Array  $page   [description]
     * @return [type]         [description]
     */
    public function getOwnList(Int $status,Array $page){
        $map = [];
        switch($status){
            case 0:break;
            case 1:$map['o.order_status']=0;$map['o.pay_status']=0;break;
            case 2:$map['o.shipping_status']=0;$map['o.pay_status']=1;
                   $map['o.order_status']=0;break;
            case 3:$map['o.shipping_status']=1;
                   $map['o.order_status']=1;
                   break;
            case 4:$map['o.order_status']=2;break;
            case 5:$map['o.order_status']=['in',[6,7]];break;//申请退款
        }

        $sql_c = D('jar_ticket')->alias('jt')
            ->join(C('db_prefix').'order as o on(o.order_id=jt.ticket_order_id)','LEFT')
            ->where(
                array_merge($map,[
                    'jt.ticket_order_id'=>['neq',0],
                    'jt.ticket_act_id'=>Role::getuser_id(),
                    'o.user_id'=>Role::getuser_id(),
                    ])
                )
            ->field('o.order_id')
            ->buildSql();//创建者的订单

        $sql_u = D('jar_ticket')->alias('jt')
            ->join(C('db_prefix').'order as o on(o.order_id=jt.ticket_order_id)','LEFT')
            ->where(
                array_merge($map,[
                    'jt.ticket_order_id'=>['neq',0],
                    'jt.ticket_use_id'=>Role::getuser_id(),
                    'o.user_id'=>['exp',' = jt.ticket_use_id']
                    ])
                )
            ->field('o.order_id')
            ->buildSql();//使用者的订单   

        $orderList = D()->query("select DISTINCT order_id from (select * from($sql_c union all $sql_u) as otinfo) as otinfo order by order_id desc limit ".$page[0]*$page[1].",".$page[1]);
        // $orderList = $this->getListWithPage($map, $page, function($model){
        //     $model->field('order_id');
        // });
        $ids = [0];
        foreach($orderList as $k=>$v){
            $ids[] = $v['order_id'];
        }

        $list = OrderGoods::getList(['og.order_id'=>['in',$ids]],function($model){
            $model->alias('og')
            ->join(C('db_prefix').'order as o on(o.order_id=og.order_id)','LEFT')
            ->join(C('db_prefix').'jar_ticket as g on(g.id=og.ticket_id)','LEFT')
            ->join(C('db_prefix').'jar as j on(j.id=g.ticket_jar_id)','LEFT')
            ->field('og.order_id,goods_name,og.ticket_id,
                goods_num,o.order_sn,o.order_status,
                o.shipping_status,o.order_amount,j.jar_thumb_img,o.pay_status')
            ->order('og.rec_id desc');
        });//订单商品 列表

        $data = [];
        foreach($list as $v){
            $id = $v['order_id'];
            is_array($data[$id]) ? $data[$id]=Array():false;
            $data[$id][] = $v;
        }
        $tmpData = [];
        while(count($data)){
            $v = array_pop($data)[0];
            $v['jar_thumb_img'] = $_SERVER['REQUEST_SCHEME']."://".$_SERVER['HTTP_HOST'].'/'.$v['jar_thumb_img'];
            $tmpData[] = $v;
        }
        return $tmpData;
    }

    /**
     * 新增订单 (暂时是分销提成的)
     * @param array $jarTicketInfo
     * @param $addressId
     * @param $package [[id=>,num=>],[id=>,num=>]]
     * @return bool
     */
    public function addOrder(Array $jarTicketInfo,$addressId,Array $package){

        $addrInfo = UserAddress::find(['address_id'=>$addressId]);//地址信息
        // $shippingInfo = D('plugin')->where(['status'=>1,'type'=>'shipping']);//物流信息
        // $shippingInfo = $shippingInfo[0];
        $userId = Role::getuser_id();
        $shippingPrice = TransPort::getTransMoney($jarTicketInfo['ticket_capacity'], $addressId);
        $packagePrice = Goods::countMoney($package);
        $order = [
            'user_id'=>$userId,
            'consignee'=>$addrInfo['consignee'],
            'country'=>$addrInfo['country'],
            'city'=>$addrInfo['city'],
            'district'=>$addrInfo['district'],
            'twon'=>$addrInfo['twon'],
            'address'=>$addrInfo['address'],
            'zipcode'=>$addrInfo['zipcode'],
            'shipping_code'=>'',//$shippingInfo['code'],
            'shipping_name'=>'',//$shippingInfo['name'],
            'deleted'=>0,//$shippingInfo['name'],
            'u_text'=>'',//$shippingInfo['name'],
            'pay_status'=>0,
            'pay_time'=>time(),
            'add_time'=>time(),
            'order_status'=>0,
            'shipping_status'=>0,
            'shipping_price'=>$shippingPrice,
            'order_amount'=>$shippingPrice+$packagePrice,
            'total_amount'=>$shippingPrice+$packagePrice,
            'order_sn'=>date('YmdHis').rand(1000,9999)
        ];
        if (!$orderId = parent::add($order)){   //创建订单
            return false;
        }else{
            $this->emit("Orderaddordergoods",array_merge($order,['id'=>$orderId,'ticket_id'=>$jarTicketInfo['id'],'num'=>$jarTicketInfo['ticket_capacity'],'package'=>$package]));
            return true;
        }
    }

    public function getOwnOrder(Int $id){
        return $this->model->alias('o')
        ->join('order_goods og on(og.order_id=o.order_id)','LEFT')
        ->where(['o.order_id'=>$id,'o.user_id'=>Role::getuser_id()])
        ->select();
    }

    /**
     * 使用领酒券修改订单的收货人
     * @param Int $id   订单id
     * @param Int $user_id   订单用户id
     * @param Int $addrId  地址id
     */
    public function setOrderConsignee($id,$user_id,$addrId){
        $map = ['order_id'=>$id,'user_id'=>$user_id];
        if ($data = $this->find($map)){
            if (!$addrId){self::$errors = L('_ADDRESS_INVALID_ID_');return false;}
            if (!$addrInfo = UserAddress::find(['address_id'=>$addrId,'user_id'=>Role::getuser_id()])){     //地址信息
                self::$errors = '地址未查询到';
                return false;
            }else{
                $saveData = array(
                    'consignee'=>$addrInfo['consignee'],
                    'country'=>$addrInfo['country'],
                    'city'=>$addrInfo['city'],
                    'district'=>$addrInfo['district'],
                    'twon'=>$addrInfo['twon'],
                    'address'=>$addrInfo['address'],
                    'zipcode'=>$addrInfo['zipcode']
                );
                return $this->update($map,$saveData);
            }
        }else{
            self::$errors = L('_ORDER_INVALID_ID_');
        }
    }
    /**
     * [refund 自动退单]
     * @return [type] [description]
     */
    public function autoRefund(){
        $overTime = 12*3600;
        $count = D('order')->where('add_time < '.(time()-$overTime).' and pay_status=0')->count();
        if($count) $this->refund();
    }
    /**
     * [refund 退单]
     * @return [type] [description]
     */
    public function refund(){
        $list = D('order')->where('add_time < '.(time()-$overTime).' and pay_status=0')->select();
        D('order')
        ->where('add_time < '.(time()-$overTime).' and pay_status=0')
        ->save(['order_status'=>3]);//取消订单
        foreach($list as $item){
            //取消订单后续操作
            $ids[] = $item['order_id'];
        }
        $ticketList = D('jar_ticket')->where(['ticket_order_id'=>['in',$ids]])->select();
        foreach($ticketList as $ticket){
            $success = D('jar_ticket')
            ->where(['id'=>$ticket['id'], 'ticket_status'=>1])
            ->save(['ticket_status']);
            if($success){ JarDistribut::backTicket($ticket);}
        }
    }
}