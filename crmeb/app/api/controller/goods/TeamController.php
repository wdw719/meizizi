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
              return api(0,'token已失效，请重新登陆');
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
               return api(200,'查询成功',$data);
           }
          return api(0,'系统错误');
      }

      /**
       * 团队管理-我的商家
       */

      public function myStore(Request $request){
          list($token)  = UtilService::getMore([['token']] , $request , true);
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api(0,'token已失效，请重新登陆');
          $mer = new Merchant();
          $store_info =  $mer->catStore($rep['uid']);
        return  api(200,'查询成功',$store_info);
      }

      /**
       * 团队成员
       */

      public function teamNum(Request $request){
          list($token)  = UtilService::getMore([['token']] , $request , true);
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api(0,'token已失效，请重新登陆');
          $teamNum = $group->teaNum($rep['uid']);
          if($rep['status'] == 0)
              return api(0,'token已失效，请重新登陆');
          return api(200,'查询成功',$teamNum);
      }

      /**
       * 任命
       */

      public function appointTeam(Request $request){
          list($token ,$position)  = UtilService::getMore([['token'],['position']] , $request , true);
          if(empty($position))
              return api(0,'该职位不存在');
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api(0,'token已失效，请重新登陆');
          $appo = $group->appoInt($rep['uid'],$position);
          return api(200,'任命成功');
      }

    /**
     * 更改省区域
     */

     public function updArea(Request $request){
         list($token ,$manag_area,$u_id)  = UtilService::getMore([['token'],['manag_area'],['u_id']] , $request , true);
        if(empty($manag_area) || empty($u_id) || empty($token))
            return     api(0,'缺少参数');
         $group = new SystemAdmin();
         $rep = $group -> userToken($token);
         if($rep['status'] == 0)
             return api(0,'token已失效，请重新登陆');
         $reu = $group->isArea($u_id,$rep['uid']);
         $rej = $group -> isPositon($rep['uid']);
         if(empty($reu) || empty($rej)){
             return api(0,'您没有权限修改');
         }
         $province = $group->provinceTeam($u_id,$manag_area);
         return api(200,'更改成功');
     }


    /**
     * 更改市区域
     */

      public function updCity(Request $request){
          list($token ,$manag_area,$u_id)  = UtilService::getMore([['token'],['manag_area'],['u_id']] , $request , true);
          if(empty($manag_area) || empty($u_id) || empty($token))
              return     api(0,'缺少参数');
          $group = new SystemAdmin();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api(0,'token已失效，请重新登陆');
          $reu = $group->isCity($u_id,$rep['uid']);
          $rej = $group->isCityp($rep['uid']);
          if(empty($reu) || empty($rej)){
              return api(0,'您没有权限修改');
          }
          $province = $group->provinceCity($u_id,$manag_area);
          return api(200,'更改成功');
      }

    /**
     * 店铺信息 -讲师
     */

      public function shopInfo(Request $request){
          list($token ,$mid)  = UtilService::getMore([['token'],['mid']] , $request , true);
          if(empty($mid))
              return api(0,'商铺不存在');
          $group = new SystemAdmin();
          $mer = new Merchant();
          $store = new StoreProduct();
          $rep = $group -> userToken($token);
          if($rep['status'] == 0)
              return api(0,'token已失效，请重新登陆');
          $shop = $group->mer($rep['uid']);
          if(empty($shop->id))
             return api(0,'您还不是商户');
          $pow = $group->pwd($rep['uid']);
             if(empty($pow))
                 return api(0,'角色有误');
           $ste = $store->storeCat($mid);
           return api(200,'查询成功',$ste);
      }

    /**
     * 我的商家-讲师
     */

     public function myTeacher(Request $request){
         list($token ,)  = UtilService::getMore([['token']] , $request , true);
         $group = new SystemAdmin();
         $rep = $group -> userToken($token);
         if($rep['status'] == 0)
             return api(0,'token已失效，请重新登陆');
         $pow = $group->pwd($rep['uid']);
         if(empty($pow))
             return api(0,'角色有误');
         $mer = new Merchant();
         $store_info =  $mer->catTeacher($rep['uid']);
         return api(0,'查询成功',$store_info);
     }
}