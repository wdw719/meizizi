<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class Pay extends  BaseModel{

    public function alipay($order_sn){
        if (!$order_sn) {
            return array('status' => 0, 'msg' => '订单编号错误');
        }
        $order = new Order();
        $order_info = $order -> info($order_sn);
        if (!$order_info) {
            return array('status' => 0, 'msg' => '订单不存在');
        }
        if ($order_info['pay_status'] == 2) {
            return array('status' => 0, 'msg' => '订单已支付');
        }
        $amount = $order_info['price'];
        $data['order_sn'] = $order_sn;
        $amount = 0.01; //测试一分钱支付
        $data['amount'] = $amount;
        $data['detail'] = '美孜孜商品购买';
        $data['type'] = 'order';
        $data['subject'] = '美孜孜商品测试购买';
        $data['order_id'] = $order_info['id'];
        $result = $this -> pay_alipay($data);
        return array('msg' => '获取成功', 'status' => 1, 'data' => array($result));
    }

    function pay_alipay($data){
        //$aop = new AopClient();
    }
}
