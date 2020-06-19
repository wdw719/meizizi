<?php

namespace app\api\controller\goods;

use function AlibabaCloud\Client\envNotEmpty;
use app\models\goods\Merchant;
use app\models\goods\SystemAdmin;
use app\models\store\StoreProduct;
use app\Request;
use crmeb\services\UtilService;

class TeamController
{
      /**
       * 我的团队
       */
      public function myTeam(Request $request){
          list($token)  = UtilService::getMore([['token']] , $request , true);
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api('0','token已失效，请重新登陆');
          $group = $group->myTeam($rep['uid']);
          return $group;
      }

    /**
     * 团队角色
     */

      public function chiefTeam(Request $request){
          list($token)  = UtilService::getMore([['token']] , $request , true);
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api('0','token已失效，请重新登陆');
          $ref = $group->chiefTeam($rep['uid']);
           if(!empty($ref->id)){
               $chief =  $group ->teamNum($ref->id,$ref->position);

               $atr = $ref->toArray();
               $data= ['info'=>$atr,'data'=>$chief];
               if(empty($chief)){
                  return api(200,'成功查询数据',$atr);
               }
               return api(200,'查询成功',$data);
           }
          return api(0,'系统错误');
      }

      /**
       * 我的商家
       */

      public function myStore(Request $request){
          list($token)  = UtilService::getMore([['token']] , $request , true);
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api('0','token已失效，请重新登陆');
          $mer = new Merchant();
          $store_info =  $mer->catStore($rep['uid']);
          api('200','查询成功',$store_info);
      }

      /**
       * 团队成员
       */

      public function teamNum(Request $request){
          list($token)  = UtilService::getMore([['token']] , $request , true);
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          $teamNum = $group->teaNum($rep['uid']);
          if($rep['status'] == 0)
              return api('0','token已失效，请重新登陆');
          return api('200','查询成功',$teamNum);
      }

      /**
       * 任命
       */

      public function appointTeam(Request $request){
          list($token ,$position)  = UtilService::getMore([['token'],['position']] , $request , true);
          if(empty($position))
              return api('0','该职位不存在');
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api('0','token已失效，请重新登陆');
          $appo = $group->appoInt($rep['uid'],$position);
          return api('200','任命成功');
      }

    /**
     * 更改区域
     */

     public function updArea(Request $request){
         list($token ,$manag_area)  = UtilService::getMore([['token'],['manag_area']] , $request , true);
        if(empty($manag_area))
            return     api('0','该省不存在');
         $group = new SystemAdmin();
         $rep = $group -> userToken($token);
         if($rep['status'] == 0)
             return api('0','token已失效，请重新登陆');
         $province = $group->provinceTeam($rep['uid'],$manag_area);
         return api('200','更改成功');
     }

    /**
     * 店铺信息
     */

      public function shopInfo(Request $request){
          list($token ,$mid)  = UtilService::getMore([['token'],['mid']] , $request , true);
          if(empty($mid))
              return api('0','该商铺不存在');
          $group = new SystemAdmin();
          $mer = new Merchant();
          $store = new StoreProduct();
          $rep = $group -> userToken($token);
          $shop = $mer->shopsInfo($rep['uid'],$mid);
          if(empty($shop->id))
              api('0','商户不存在');
           $ste = $store->storeCat($shop->id);

      }
}