<?php

namespace app\api\controller\goods;

use app\models\goods\SystemAdmin;
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
}