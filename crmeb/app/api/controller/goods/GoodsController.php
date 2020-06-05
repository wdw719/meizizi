<?php

namespace app\api\controller\goods;

use app\admin\model\store\StoreProductReply;
use app\models\goods\Article;
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


}

