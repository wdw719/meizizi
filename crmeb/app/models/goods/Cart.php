<?php

namespace app\models\goods;

use app\admin\model\store\StoreProductAttrValue;
use app\models\store\StoreProduct;
use crmeb\basic\BaseModel;

class Cart extends BaseModel{

    /**
     *购物车列表
    */
    public function cartList($uid , $page , $limit){
        $list = self::where('user_id' , $uid) -> alias('A')-> join('StoreProduct B' , 'A.goods_id = B.id')
            -> field('A.id , A.goods_id , A.goods_name , A.market_price , A.goods_num , A.spe_id , A.spe_name , B.image')
            -> page($page , $limit) -> order('A.id desc') -> select() -> toArray();
        $goods = new StoreProduct();
        $goods_list = $goods -> getHotProductLoading('id , image , store_name  , price , vip_price , postage , stock , is_postage , browse',1 , 4);
        return array('cart_list'=>$list , 'goods_list' => $goods_list);
    }

    /**
     * 添加购物车
    */
    public function addCart($uid , $goods_id , $number , $spe_id , $spe_name){
        $goods = new StoreProduct();
        $goods_info = $goods -> info($goods_id);
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
        $cart_info = self::where('user_id' , $uid) -> where('goods_id' , $goods_id)
            -> where('spe_id' , $spe_id) -> where('spe_name' , $spe_name) -> find();
        if($cart_info){
            $data['goods_num'] = $cart_info['goods_num'] + $number;
            $rep = self::where('id' , $cart_info['id']) -> save($data);
        }else{
            $data['user_id'] = $uid;
            $data['add_time'] = time();
            $data['goods_id'] = $goods_id;
            $data['goods_name'] = $goods_info['store_name'];
            $data['market_price'] = $goods_info['price'];
            $data['goods_price'] = $goods_info['price'];
            $data['goods_num'] = $number;
            $data['spe_id'] = $spe_id;
            $data['spe_name'] = $spe_name;
            $rep = self::save($data);
        }

        if($rep){
            return array('status'=>1 , 'data'=>$rep);
        }else{
            return array('status' => 0, 'msg' => '未知错误！');
        }
    }


    public function editCart($uid , $id , $number){
        $cart_info = self::where('id' , $id) -> where('user_id' , $uid) -> find() -> toArray();
        $pro = new StoreProductAttrValue();
        if($cart_info['spe_id']){
            $product_value_info = $pro -> where('product_id' , $cart_info['spe_id']) -> where('suk' , $cart_info['spe_name']) -> find() -> toArray();
            if($number > $product_value_info['stock']){
                return array('status' => 0, 'msg' => '商品库存不足');
            }
        }else{
            $goods = new StoreProduct();
            $goods_info = $goods -> info($cart_info['goods_id']);
            if($number > $goods_info['stock']){
                return array('status' => 0, 'msg' => '商品库存不足');
            }
        }
        $rep = self::where('id' , $id) -> save(['goods_num'=>$number]);
        if($rep){
            return array('status'=>1 , 'data'=>$rep);
        }else{
            return array('status' => 0, 'msg' => '修改失败！');
        }
    }

    public function delCart($id){
        return self::where('id' , $id) -> delete();
    }
}