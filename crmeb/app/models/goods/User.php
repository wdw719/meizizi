<?php

namespace app\models\goods;

use app\models\user\UserToken;
use crmeb\basic\BaseModel;

class User extends BaseModel{

    /**
     * 根据手机号判断该用户是否注册
    */
    public function phoneIsRegister($phone){
       return self::where('phone' , $phone)->count();
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
        $user_token_info = UserToken::where('token' , $token) -> find() -> toArray();
        if(strtotime($user_token_info['expires_time']) < time()){
            return array('status' => 0 , 'msg'=>'token不存在或者已过期');
        }
        return array('status'=>1 , 'uid'=>$user_token_info['uid']);
    }

    /**
     * 注册
    */
    public function register($head_img , $nickname , $birthday , $sex , $phone , $password, $reco , $ip){
        $reco_id = self::where('reco_code', $reco) -> value('uid');
        $user = new User();
        $user -> account = $phone;
        $user -> pwd = md5($password);
        $user -> nickname = $nickname;
        $user -> phone = $phone;
        $user -> birthday = $birthday;
        $user -> avatar = $head_img;
        $user -> add_time = time();
        $user -> add_ip = $ip;
        $user -> last_ip = $ip;
        $user -> spread_uid = $reco_id;
        $user -> spread_time = time();
        $user -> reco_code = $this  -> setString();
        $user -> sex = $sex;
        return $user -> save();
    }


    /**
     * 登陆
    */
    public function login($username , $password , $ip){
        $user_info = self::where('account',$username) -> find() -> toArray();
        if (!$user_info) {
            return array('status' => 0 , 'msg' => '账号不存在!');
        }
        if (md5($password) != $user_info['pwd']) {
            return array('status' => 0, 'msg' => '账号或密码错误!');
        }
        if ($user_info['status'] == 0) {
            return array('status' => 0, 'msg' => '账号异常已被锁定！！！');
        }
        $user = array('uid' => $user_info['uid'],
            'nickname' => $user_info['nickname'],
            'avatar' => $user_info['avatar'] ? $user_info['head_img'] : '',
            'phone' => $user_info['phone']);
        $user['token'] = md5(mt_rand(1, 999999999) . time() . uniqid());
        $user_token = new UserToken();
        $user_token -> uid = $user_info['uid'];
        $user_token -> token = $user['token'];
        $user_token -> expires_time = date('Y-m-d H:i:s', time()+ 7 * 24 * 3600);
        $user_token -> login_ip = $ip;
        $user_token -> save();
        self::where('uid' , $user_info['uid']) -> save(['last_time'=>time() , 'last_ip' => $ip]);
        return array('status' => 1, 'msg' => '登陆成功', 'data' => $user);
    }

    /**
     * 修改用户资料
    */
    public function editUserData($avatar , $birthday , $sex , $uid){
        $user_data['avatar'] = $avatar;
        $user_data['birthday'] = $birthday;
        $user_data['sex'] = $sex;
        $rep = self::where('uid' , $uid) -> save($user_data);
        return $rep;
    }

    /**
     * 用户资料
    */
    public function userInfo($uid){
        return User::where('uid' , $uid) -> field('uid , avatar , birthday , sex')  -> find() -> toArray();
    }

    /**
     * 检查用户是否有设置密码
    */
    public function checkPassword($uid){
        return self::where('uid' , $uid) -> value('pwd');
    }


    /**
     * 设置密码
    */
    public function setUpPassword($password , $uid){
        return self::where('uid' , $uid) -> save(['pwd'=>md5($password)]);
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
     * 获取用户推荐码
     */
    public function getUserReco($uid){
        return self::where('id' , $uid) -> value('reco_code');
    }

    /**
     * 根据推荐码获取推荐用户
     */
    public function getUserId($reco){
        return self::where('reco_code' , $reco) -> value('uid');
    }

    /**
     * 绑定支付宝
     */
    public function alipayName($uid , $number){
        return self::where('id' , $uid) -> save(['alipay_name' => $number]);
    }

    /**
     * 取消绑定支付宝
     */
    public function delAlipayName($uid){
        return self::where('uid' , $uid) -> save(['alipay_name' => '']);
    }

    /**
     * 用户余额
     */
    public function userMoney($uid){
        return self::where('uid' , $uid) -> value('now_money');
    }
}