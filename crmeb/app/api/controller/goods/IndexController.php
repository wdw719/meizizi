<?php

namespace app\api\controller\goods;

use app\admin\model\order\GoodsBrand;
use app\models\goods\SystemAdmin;
use app\models\goods\Version;
use app\models\goods\User;
use app\Request;
use crmeb\services\UtilService;

class IndexController{

    /**
     * 普通注册
     */
    public function register(Request $request){
        $ip = $_SERVER['REMOTE_ADDR'];
        list($head_img , $nickname ,$birthday, $sex , $phone , $password , $reco) = UtilService::getMore([['head_img'], ['nickname'],['birthday'],
            ['sex',1],['phone'],['password'],['reco']], $request, true);
        if(!$phone && !$password && !$reco){
            return app('json')->fail('参数缺失');
        }
        if(empty($reco)) return api('0','推荐码不能为空');
        if(empty($phone) || empty($password)) return api('0','账号或密码不能为空');
        $user = new SystemAdmin();
        $check_pass = $user -> check_pass_security($password);
        if($check_pass == false){
            return app('json')->fail('该密码不安全，请重新输入密码！');
        }
        $count = $user -> phoneIsRegister($phone);
        if($count > 0){
            return app('json')->fail('该电话号码已经注册');
        }
        $rep = $user -> register($head_img , $nickname , $birthday , $sex , $phone , $password, $reco , $ip);
        return app('json')->successful($rep);
    }

    /**
     * 账号登陆
     */
    public function login(Request $request){
        $ip = $_SERVER['REMOTE_ADDR'];
        list($username , $password) = UtilService::getMore([['username'] , ['password']] , $request , true);
        if(!$username && !$password)
            return app('json')->fail('参数缺失');
        $user = new SystemAdmin();
        $info = $user -> login($username , $password , $ip);
        if($info['status'] == 0)
            return app('json')->fail($info['msg']);
        return app('json') -> successful($info['data']);
    }

    /**
     * 修改用户资料
     */
    public function editUserData(Request $request){
        list($token , $avatar , $birthday , $sex)  = UtilService::getMore([['token'] , ['avatar'] , ['birthday'] , ['sex' , 1]] , $request , true);
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $user_rep = $user -> editUserData($avatar , $birthday , $sex , $rep['uid']);
        return app('json') -> successful($user_rep);
    }

    /**
     * 用户资料
     */
    public function userInfo(Request $request){
        list($token)  = UtilService::getMore([['token']] , $request , true);
        $user = new User();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $info = $user -> userInfo($rep['uid']);
        return app('json') -> successful($info);

    }

    /**
     * 版本更新
     */
    public function edition(Request $request){
        list($type) = UtilService::getMore([['type' , 1]] , $request , true);
        $version = new Version();
        $info = $version -> edition($type);
        return app('json') -> successful($info);
    }

    /**
     * 设置密码
     */
    public function setUpPassword(Request $request){
        list($token , $password) = UtilService::getMore([['token'] , ['password']] , $request , true);
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $check_password = $user -> checkPassword($rep['uid']);
        if($check_password)
            return app('json') -> fail('已经设置过密码');
        $ser_pwd_info = $user -> setUpPassword($password , $rep['uid']);
        return app('json') -> successful($ser_pwd_info);
    }

    /**
     * 修改密码
     */
    public function editPassword(Request $request){
        list($token , $password , $new_password) = UtilService::getMore([['token'] , ['password'] , ['new_password']] , $request , true);
        if(!$token && !$password && !$new_password){
            return app('json')->fail('参数缺失');
        }
       if(empty($token)) return api('0','token不能为空');
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $check_pass = $user -> check_pass_security($new_password);
        if($check_pass == false){
            return app('json')->fail('该密码不安全，请重新输入密码！');
        }
        if(empty($password)) return api('0','密码不能为空');
        if(empty($new_password)) return api('0','新密码不能为空');
        $rep = $user -> editPassword($password , $new_password , $rep['uid']);
        if($rep==false)
            return app('json') -> fail('原密码错误，请重新输入！');
      return api('200','修改成功',['new_password'=>$new_password]);
    }

    /**
     * 获取品牌
     */
    public function brandList(){
        $brand = new GoodsBrand();
        $list = $brand -> brandAllList();
        return app('json') -> succsessful();
    }
}