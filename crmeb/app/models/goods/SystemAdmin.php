<?php

namespace app\models\goods;

use app\models\user\UserToken;
use crmeb\basic\BaseModel;

class SystemAdmin extends BaseModel
{
    /**
     * 根据手机号判断该用户是否注册
     */
    public function phoneIsRegister($phone){
        return self::where('phone' , $phone)->count();
    }

    /**
     * 根据用户名判断是否有该用户
    */
    public function usernameIsRegister($name){
        return self::where('account' , $name) -> count();
    }

    /**
     * 检查密码是否安全
     * @parm $password
     */
    function check_pass_security($password){
        if(preg_match('/(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}/',$password))
            return true;
        return false;
    }

    /**
     * 生成随机不重复推荐码
     */
    public function setString(){
        while (true) {
            $str = rand(10000 , 999999);
            $user_reco_count= self::where('reco_code' , $str) -> count();
            if ($user_reco_count == 0)
                break;
        }
        return $str;
    }

    /**
     * 根据token判断用户信息
     */
    public function userToken($token){
        $user_token_info = UserToken::where('token' , $token) -> find();
        if(strtotime($user_token_info['expires_time']) < time()){
            return array('status' => 0 , 'msg'=>'token不存在或者已过期');
        }
        return array('status'=>1 , 'uid'=>$user_token_info['uid']);
    }

    /**
     * 注册
     */
    public function register($head_img , $nickname , $birthday , $sex , $phone , $password, $reco , $ip){
        $reco_id = self::where('reco_code', $reco) -> value('id');
        $user_data['account'] = $phone;
        $user_data['pwd'] = md5($password);
        $user_data['nickname'] = $nickname;
        $user_data['phone'] = $phone;
        $user_data['birthday'] = $birthday?$birthday:'0000-00-00 00:00:00';
        $user_data['avatar'] = $head_img;
        $user_data['add_time'] = time();
        $user_data['add_ip'] = $ip;
        $user_data['last_ip'] = $ip;
        $user_data['spread_uid'] = $reco_id;
        $user_data['spread_time'] = time();
        $user_data['reco_code'] = $this  -> setString();
        $user_data['sex'] = $sex;
        return self::save($user_data);
    }


    /**
     * 登陆
     */
    public function login($username , $password , $ip){
        $user_info = self::where('account',$username) -> find();
        if (!$user_info) {
            return array('status' => 0 , 'msg' => '账号不存在!');
        }
        if (md5($password) != $user_info['pwd']) {
            return array('status' => 0, 'msg' => '账号或密码错误!');
        }
        if ($user_info['status'] == 0) {
            return array('status' => 0, 'msg' => '账号异常已被锁定！！！');
        }
        $user_token = new UserToken();
        $user = array('id' => $user_info['id'],
            'nickname' => $user_info['nickname'],
            'avatar' => $user_info['avatar'] ? $user_info['head_pic'] : '',
            'phone' => $user_info['phone'],
            'alipay_name' => $user_info['alipay_name']
            );
        $user['token'] = md5(mt_rand(1, 999999999) . time() . uniqid());
        $sel  = UserToken::where(['uid'=>$user_info['id']])->find();
        if($sel){
            UserToken::update([
                'token'          =>   $user['token'],
                'expires_time'  =>   date('Y-m-d H:i:s', time()+ 7 * 24 * 3600),
                'login_ip'      =>    $ip,
                'update_time'  =>     date('Y-m-d H:i:s', time())
            ],['id'=>$sel['id']]);
            self::where('id' , $user_info['id']) -> save(['last_time'=>time() , 'last_ip' => $ip]);
            return array('status' => 1, 'msg' => '登陆成功', 'data' => $user);
        }else{
            $user_token -> uid    = $user_info['id'];
            $user_token -> token = $user['token'];
            $user_token -> expires_time = date('Y-m-d H:i:s', time()+ 7 * 24 * 3600);
            $user_token -> login_ip = $ip;
            $creates = $user_token -> save();
            $user_uid  = $user_token->uid;
            self::where('id' , $user_info['id']) -> save(['last_time'=>time() , 'last_ip' => $ip]);
            return array('status' => 1, 'msg' => '登陆成功', 'data' => $user);
        }
    }

    /**
     * 修改用户资料
     */
    public function editUserData($avatar , $birthday , $sex , $uid){
        $user_data['avatar'] = $avatar;
        $user_data['birthday'] = $birthday;
        $user_data['sex'] = $sex;
        $rep = self::where('id' , $uid) -> save($user_data);
        return $rep;
    }

    /**
     * 用户资料
     */
    public function userInfo($uid){
        return SystemAdmin::where('id' , $uid) -> field('id , avatar , birthday , sex,pwd')  -> find() -> toArray();
    }

    /**
     * 检查用户是否有设置密码
     */
    public function checkPassword($uid){
        return self::where('id' , $uid) -> value('pwd');
    }


    /**
     * 设置密码
     */
    public function setUpPassword($password , $uid){
        return self::where('id' , $uid) -> update(['pwd'=>md5($password)]);
    }

    /**
     * 修改密码
     */
    public function editPassword($password , $new_password , $uid){
        $user_info = $this -> userInfo($uid);
        if(md5($password) != $user_info['pwd'])
            return false;
        return $this -> setUpPassword($new_password , $uid);
    }

    /**
     * 我的团队
     */
    public function myTeam($uid){
         $info = self::where(['id'=>$uid])->field(['id'])->select()->toArray();
         if(empty($info)){

         }
    }
}