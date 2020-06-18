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

      }
}