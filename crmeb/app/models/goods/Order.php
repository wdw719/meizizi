<?php

namespace app\models\goods;

use app\admin\model\store\StoreProductAttrValue;
use app\models\store\StoreProduct;
use crmeb\basic\BaseModel;

class Order extends BaseModel{

    /**
     * 生成订单
    */
    public function order($uid , $goods_id , $spe_id  , $spe_name ,$number , $server , $money , $postage){
        //判断商品是否存在
        $goods_info = $this -> isGoods($goods_id);
        if(empty($goods_info)){
            return array('status' => 0, 'msg' => '商品不存在');
        }
        //判断库存
        $pro = new StoreProductAttrValue();
        if($spe_id){
            $product_value_info = $pro -> where('product_id' , $spe_id) -> where('suk' , $spe_name) -> find() -> toArray();
            if($number > $product_value_info['stock']){
                return array('status' => 0, 'msg' => '商品库存不足');
            }
        }else{
            if($number > $goods_info['stock']){
                return array('status' => 0, 'msg' => '商品库存不足');
            }
        }
        //判断价格是否正确
        $new_money = $goods_info['postage'] + $goods_info['price'] * $number;
        if($money != $new_money){
            return array('status' => 0, 'msg' => '商品价格错误');
        }
        self::startTrans();
        try {
            //生成主订单
            $order_sn = $this -> get_order_sn();
            $or_data['server'] = $server;
            $or_data['order_status'] = 1;
            $or_data['pay_status'] = 1;
            $or_data['add_time'] = date('Y-m-d H:i:s' , time());
            $or_data['order_sn'] = $order_sn;
            $or_data['master_order_sn'] = $order_sn;
            $or_data['user_id'] = $uid;
            $or_data['price'] = $new_money;
            $order_rep = self::save($or_data);
            //生成订单详情
            $or_detail['goods_id'] = $goods_id;
            $or_detail['order_id'] = $order_rep;
            $or_detail['master_order_sn'] = $order_sn;
            $or_detail['spec_id'] = $spe_id;
            $or_detail['order_sn'] = $this -> get_order_sn();
            $or_detail['address_id'] = 0;
            $or_detail['user_id'] = $uid;
            $or_detail['store_id'] = 1;
            $or_detail['price'] = $new_money;
            $or_detail['number'] = $number;
            $or_detail['type'] = 1;
            $or_detail['goods_name'] = $goods_info['store_name'];
            $or_detail['spe_name'] = $spe_name;
            $or_detail['pay_time'] = time();
            $order_goods = new OrderGoods();
            $or_deta_rep = $order_goods -> save($or_detail);
            //减少商品库存
            if($spe_id){    //有规格
                $paoduct_value['stock'] = $product_value_info['stock'] - $number;
                $pro_rep = $pro -> where('product_id' , $spe_id) -> where('suk' , $spe_name) -> save($paoduct_value);
            }else{  //无规格
                $goods = new StoreProduct();
                $pro_rep = $goods -> where('id' , $goods_id) -> save(['stock' =>$goods_info['stock'] - $number]);
            }
            self::commitTrans();


            return array('status' => 1, 'data' => "11111111");
        } catch (\Exception $e) {
            self::rollbackTrans();
            return self::setErrorInfo($e->getMessage());
        }
    }

    public function isGoods($goods_id){
        return StoreProduct::where(['id'=>$goods_id]) -> find() -> toArray();
    }

    public function get_order_sn(){
        while (true) {
            $order_sn = date('YmdHis') . rand(1000, 9999); // 订单编号
            $condition['master_order_sn'] = $order_sn;
            //$order_sn_count = self::where($condition)->count();
            $order_sn_count = 0 ;
            if ($order_sn_count == 0)
                break;
        }
        return $order_sn;
    }

    public function info($master_order_sn){
        return self::where('master_order_sn' , $master_order_sn) -> find();
    }
}
