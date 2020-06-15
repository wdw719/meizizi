<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class OrderGoods extends BaseModel{

    /**
     * 订单列表
    */
    public function orderList($type , $page , $limit , $uid){
        if($type != 10)
            $condition['A.type'] = $type;
        $condition['A.user_id'] = $uid;
        $list = self::where($condition) ->alias('A')->join('StoreProduct B','A.goods_id = B.id')
            ->field('A.id , A.goods_id  , A.price , A.number , A.type , A.goods_name , A.spe_name , A.pay_time , B.image , B.store_info')
            -> page($page , $limit) -> order('A.id desc') -> select() -> toArray();
        if($list){
            foreach ($list as $k => $value){
                if($value['type'] == 1){
                    $time = $value['pay_time'] + 3600 * 24 - time();
                    if($time > 0){
                        $list[$k]['pay_time'] = $time;
                    }else{
                        $list[$k]['pay_time'] = 0;
                    }
                }
            }
        }
        return $list;
    }


    /**
     * 订单详情
    */
    public function orderInfo($order_id , $uid){
        $order_info = self::where('A.id' , $order_id) -> where('A.user_id' , $uid) ->alias('A')->join('StoreProduct B','A.goods_id = B.id')
            ->field('A.id , A.goods_id  , A.price , A.number , A.type , A.goods_name , A.spe_name  , A.master_order_sn , A.pay_time , B.image , B.store_info')-> find() -> toArray();
        $order_info['add_time'] = Order::where('master_order_sn' , $order_info['master_order_sn']) -> value('add_time');
        if($order_info['type'] == 1){
            $time = $order_info['pay_time'] + 3600 * 24 - time();
            if($time > 0 ){
                $order_info['pay_time'] = $time * 1000;
            }else{
                $order_info['pay_time'] = 0;
            }
        }
        return $order_info;
    }
}
