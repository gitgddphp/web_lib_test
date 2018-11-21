<?php
namespace Jar\Observers;

use Jar\Facades\Goods;
use Jar\Facades\JarTicket;
use Jar\Facades\OrderGoods;
use Jar\Exception\TransException;

class OrderObserver{

    public static function addordergoods($param){
        $jarInfo = JarTicket::getTicketJarInfo($param['ticket_id']);
        $orderInfos[] = [       //酒信息
            'goods_id'=>$jarInfo['id'],
            'ticket_id'=>$jarInfo['id'],
            'goods_name'=>$jarInfo['jar_name'],
            'order_id'=>$param['id'],
            'goods_sn'=>$jarInfo['jar_sn'],
            'goods_num'=>$param['num'],//取多少酒
            'deleted'=>0,//取多少酒
            'market_price'=>0,
            'shop_price'=>0,
            'cost_price'=>0,
        ];
        if (!empty($param['package'])){     //包装信息
            $goods_id_num = array_column($param['package'],'num','id');
            $map['goods_id']=array('in',array_keys($goods_id_num));
            $datas = Goods::getList($map);
            foreach ($datas as $data){
                $temp = [
                    'goods_id'=>$data['goods_id'],
                    'ticket_id'=>0,
                    'goods_name'=>$data['goods_name'],
                    'order_id'=>$param['id'],
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
    }
}
?>