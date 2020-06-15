<?php

namespace app\api\controller\goods;

use app\admin\model\store\StoreProductReply;
use app\models\goods\Article;
use app\models\goods\Cart;
use app\models\goods\Footprint;
use app\models\goods\Order;
use app\models\goods\OrderGoods;
use app\models\goods\User;
use app\models\goods\UserSearch;
use app\models\store\StoreProduct;
use app\models\store\StoreProductAttr;
use app\Request;
use crmeb\services\UtilService;
/**
 * 美滋滋项目商城
 */
class GoodsController{

    /**
     * 帮助中心
     */
    public function help(Request $request){
        list($page , $limit ,$content) = UtilService::getMore([['page', 1], ['limit', 10],['content','']], $request, true);
        $article = new Article();
        $data = $article -> help($page , $limit , $content);
        return app('json')->successful($data);
    }

    /**
     * 帮助中心详情
     */
    public function helpInfo(Request $request){
        list($id) = UtilService::getMore(['id'] , $request , true);
        $article = new Article();
        $info = $article -> helpInfo($id);
        return app('json')->successful($info);
    }


    /**
     * 用户添加搜索条件
     */
    public function userAddSearch(Request $request){
        list($value , $uid) = UtilService::getMore([['value'] , ['uid']] , $request , true);
        if($uid){
            $user_search = new UserSearch();
            $user_search -> userAddSearch($value , $uid);
        }
        return app('json')->successful();
    }

    /**
     * 用户清空搜索条件
     */
    public function userDelSearch($uid){
        $user_search = new UserSearch();
        $user_search -> userDelSearch($uid);
        return app('json') -> successful();
    }

    /**
     * 产品列表
     */
    public function goodsList(Request $request){
        list($search_type , $content , $type , $p , $limit) = UtilService::getMore([['search_type' , 0] , ['content'] , ['type' , 1] , ['p' , 1] , ['limit' , 20]] , $request , true);
        $goods = new StoreProduct();
        $data = $goods -> goodsList($search_type , $content , $type , $p , $limit);
        return app('json') -> successful($data);
    }

    /**
     *产品系列
     */
    public function goodsSeries(){

    }

    /**
     * 专区
     */
    public function specialArea(){

    }

    /**
     *商品详情
     */
    public function goodsInfo($id , $uid){
        if (!$id || !($storeInfo = StoreProduct::getValidProduct($id))) return app('json')->fail('商品不存在或已下架');
        $goods = new StoreProduct();
        list($productAttr, $productValue) = StoreProductAttr::getProductAttrDetail($id, $uid, 0);
        $foot = new Footprint();
        $foot -> addFootPrint($uid , $id);
        $data = $goods -> goodsInfo($id);
        $data['productAttr'] = $productAttr;
        $data['productValue'] = $productValue;
        return app('json') -> successful($data);
    }

    /**
     * 商品评价
     */
    public function goodsEva(Request $request){
        list($number , $content , $imgs , $token , $is_ano , $oid , $gid) = UtilService::getMore([['number' , 5] , ['content'] , ['imgs'] , ['token'] , ['is_ano' , 0] , ['oid'] , ['gid']] , $request , true);
        if(!$number && !$content){
            return app('json')->fail('参数缺失');
        }
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $eva = new StoreProductReply();
        $rep = $eva -> goodsEva($number , $content , $imgs , $rep['uid'] , $is_ano , $oid , $gid);
        return app('json') -> successful($rep);
    }


    /**
     * 评价列表
     */
    public function evaList(Request $request){
        list($type , $gid , $page , $limit) = UtilService::getMore([['type' , 1] , ['gid'] ,['page' , 1] , ['limit' , 10]] , $request , true);
        if(!$gid){
            return app('json')->fail('参数缺失');
        }
        $eva = new StoreProductReply();
        $data = $eva -> evaList($type , $gid , $page , $limit);
        return app('json') -> successful($data);
    }

    /**
     * 创建订单
     */
    public function createOrder(Request $request){
        list($order_id , $uid , $goods_id , $spe_id  , $spe_name , $number , $server , $money , $postage) =
            UtilService::getMore([['order_id' , 0 ] , ['uid'] , ['goods_id'] , ['spe_id'] , ['spe_name'] ,  ['number'] , ['server'] , ['money'] , ['postage' , 0]] , $request , true);
        if(!$uid && !$goods_id  && $number && $server && $money){
            return app('json')->fail('参数缺失');
        }
        $order = new Order();
        //
        /* if(!empty($order_id)){
             $order -> delNotPayOrder($order_id);
         }*/
        $rep = $order -> order($uid , $goods_id , $spe_id  , $spe_name , $number , $server , $money , $postage);
        if($rep['status'] == 0){
            return app('json')->fail($rep['msg']);
        }else{
            if($server == 1){
                $res = array(
                    'alipay'=>$rep['data'],
                    'pay_ment'=>''
                );
            }else{
                $res = array(
                    'alipay'=>'',
                    'pay_ment' => $rep['data']
                );
            }
            exit(json_encode(array("status" => 200, "msg" => "ok", "data" => $res)));
        }
    }


    /**
     * 订单列表
     */
    public function orderList(Request $request){
        list($type , $page , $limit , $uid) = UtilService::getMore([['type' , 10] , ['page' , 1] , ['limit' , 10] , ['uid']] , $request  , true);
        $order_goods = new OrderGoods();
        $list = $order_goods -> orderList($type , $page , $limit , $uid);
        exit(json_encode(array("status" => 200, "msg" => "ok", "data" => $list)));
    }

    /**
     * 订单详情
     */
    public function orderInfo(Request $request){
        list($order_id , $uid) = UtilService::getMore([['order_id'] , ['uid']] , $request , true);
        $order_goods = new OrderGoods();
        $info = $order_goods -> orderInfo($order_id , $uid);
        exit(json_encode(array("status" => 200, "msg" => "ok", "data" => $info)));
    }

    /**
     *购物车列表
     */
    public function cartList(Request $request){
        list($token  , $page , $limit) = UtilService::getMore([['token'] , ['page' , 1] , ['limit' , 10]] , $request , true);
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $cart = new Cart();
        $data = $cart -> cartList($rep['uid'] ,  $page , $limit);
        exit(json_encode(array("status" => 200, "msg" => "ok", "data" => $data)));
    }

    /**
     * 添加购物车
     */
    public function addCart(Request $request){
        list($token , $goods_id , $number , $spe_id , $spe_name) = UtilService::getMore([['token'] , ['goods_id'] , ['number'] , ['spe_id'] , ['spe_name']] , $request , true);
        if(!$token && !$goods_id && !$number){
            return app('json')->fail('参数缺失');
        }
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $cart = new Cart();
        $rep = $cart -> addCart($rep['uid'] , $goods_id , $number , $spe_id , $spe_name);
        if($rep['status'] == 0){
            return app('json') -> fail($rep['msg']);
        }
        exit(json_encode(array("status" => 200, "msg" => "ok", "data" => $rep['data'])));
    }

    /**
     * 修改购物车数量
     */
    public function editCart(Request $request){
        list($token , $id , $number) = UtilService::getMore([['token'] , ['id'] , ['number']] , $request , true);
        if(!$token && !$id && !$number){
            return app('json')->fail('参数缺失');
        }
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $cart = new Cart();
        $rep = $cart -> editCart($rep['uid'] , $id , $number);
        if($rep['status'] == 0){
            return app('json') -> fail($rep['msg']);
        }
        exit(json_encode(array("status" => 200, "msg" => "ok", "data" => '')));
    }

    /**
     * 删除购物车
     */
    public function delCart(Request $request){
        list($token , $id) = UtilService::getMore([['token'] , ['id'] ] , $request , true);
        if(!$token && !$id){
            return app('json')->fail('参数缺失');
        }
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $cart = new Cart();
        $rep = $cart -> delCart($id);
        exit(json_encode(array("status" => 200, "msg" => "ok", "data" => $rep)));
    }


}
