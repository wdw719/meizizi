<?php

namespace app\models\goods;

use app\admin\model\store\StoreProductAttrValue;
use app\models\store\StoreProduct;
use crmeb\basic\BaseModel;
use think\facade\Env;

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
            $rep = $this -> alipay($or_data);
            return array('status' => 1, 'data' => $rep);
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

    public function alipay($order)
    {
        var_dump($order);exit;
        $aop = new \AopClient();

        $aop->appId = '2021001167664675';
        $aop->charset = 'utf-8';
        $aop->signType = 'RSA2';
        $aop->alipayrsaPublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAmYZNRxZTO32hU/bV9VKbfpaIK5gKW/nYjSiwwS1XiOdlbrjcQaW99p0W
        lyueB59OjfoB/OBKHQlnLaromDHmvGjad/GbjnPiXHWaIlo8sr8gmzwLDzKpA0skETtCJ+Hd75iy+x4vqw/0K+iTqL5V5B/UFPMCro0IDUE60MBIdHvx3AWW1W9ws302f
        sxwgBwpzZpCP6mXIcnHFHjgh4HiyRJYp3xR1LZ+oA/Z8PVj7JMPUAVdxxYF0/hM6CJorDXcfawucsGkBsHbJXv3e9DIBwJcP2C0nu2E1aifau8xqIEkzemkR6NDanm6/o9
        EUtnMKUInwiXWP+nfKZe5ktMxrQIDAQAB';
        $aop->rsaPrivateKey = 'MIIEpAIBAAKCAQEAmYZNRxZTO32hU/bV9VKbfpaIK5gKW/nYjSiwwS1XiOdlbrjcQaW99p0WlyueB59OjfoB/OBKHQlnLaromDHmvGjad/G
        bjnPiXHWaIlo8sr8gmzwLDzKpA0skETtCJ+Hd75iy+x4vqw/0K+iTqL5V5B/UFPMCro0IDUE60MBIdHvx3AWW1W9ws302fsxwgBwpzZpCP6mXIcnHFHjgh4HiyRJYp3xR
        1LZ+oA/Z8PVj7JMPUAVdxxYF0/hM6CJorDXcfawucsGkBsHbJXv3e9DIBwJcP2C0nu2E1aifau8xqIEkzemkR6NDanm6/o9EUtnMKUInwiXWP+nfKZe5ktMxrQIDAQABAoI
        BAFn7iZe80hPwUnpwFn+vZ0wO1skWWBwImJBD5TWUadXoKl93IkGn72t4LyFzgzBcgyJcAoZAv6+9LyhpH+L0fJS5sDLU/aPE8EvY8fpogWMS+v2Bd8n0m3M+u2kOHnxZukc
        QbPcafj35H9gMfV9OD/GhZiDRPiUorCt4rAxmZxE+Gbq4H2sbYRpVndQCy9MdstBkGxro/z7bL5+QIaClxDUD8h6YhZa91klwhBwE5hUACVUMIKxYHnR2lcq+IxwMgM/2k5TV
        ydLd9hCe5BW0Bl8vLC9k6R0wyzplpnebYUqLCeTAvbutTcuD+H6RoMODWmTm+EDjUz5Tt+0B4rMwywECgYEA4ysC5HAyTbTgcl30V22rb4kFDpjxcGcsUoGKPNSBXSDpuw5nfa
        +EHc5Z0Ar/vNfLQxnCGkw7AO4EaAR8QvxhUKxNZbPNivYpVkxRuHTRgxTCGuzUxiQ+tFuUd53xitFTy/vZ6dKHdqKDDKIurr3n6geNg4L3XL0cZhBiqPV/UP0CgYEArQKE9j79
        +1DIKjE1S+xjhNSm04cg53g/CqsxYk0XXNMNB65j8wwWpYyYEHq01Q3coQSrI8Da5NMLhmc8KwEB6bSVVIT9OVf8/O1JI3VqTVKBAzEAzF6E2fIrLrk5hTYomU72/997VWZNZo
        2TDZA5vdJBNAr17nM25ElrjnYd2nECgYB/4Ip0RaRLkfJ27uTJAndrBdrO6NGg0LNmjn1e2NEpt4lbPzSKz+6zSKHONyLXDzLgxvM5Eoh0cYgRddTtcFznqNa41YpzGzcR2Ux3
        ZWs7Osg5l+/+yhByPstIuqRp3IQrY867jUOsSLc0uWdF/qk6WJ4U1fihP+NooPio2+mbkQKBgQCjy/HULRlKuVV41LPP2NLzrFzxgUq+utJ8qE2N8sy+njYE4q9QKU67l5tUZs
        gTuhb6/y+EHw9eewy7R7voPwDvoX+L0IjppIspbwHCp2RoJkdsnRVTZ91Bdow5pTV3ECpp0x/4aj4bQUrgYAMsYTK5q3j9666g/cWnZneFHgDvkQKBgQDSMNXtvSh7Fi8Rh7uj
        BaGWEBZnSkOYad33Xa0485vaxJt2jCyXkn/JYk+d/UzlXM8eZWHLHnx7QPFxL3U3oxOiBgrb10Mesmv+T6ehTcsphg1Vty+xW5fyjn2PVe/qixx4N5liuxKvoTGdvoQ3Ay6gf3
        KCsSlIOLn7FF0gSZhsng==';
        $biz_content = json_encode([
            'out_trade_no' => $order['order_sn'],
            'product_code' => 'QUICK_MSECURITY_PAY',
            'total_amount' => 0.01,
            'subject' => '美孜孜',
            'timeout_express' => '美孜孜商品名称',
            'passback_params' => 'order'
        ]);
        $payRequest = new \AlipayTradeAppPayRequest();
        $payRequest->setNotifyUrl('www.baidu.com');
        $payRequest->setBizContent($biz_content);
        $response = $aop->sdkExecute($payRequest);
        return $response;
    }
}
