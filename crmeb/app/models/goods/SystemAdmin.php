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
        $user = array('uid' => $user_info['id'],
            'nickname' => $user_info['nickname'],
            'avatar' => $user_info['avatar'] ? $user_info['head_pic'] : '',
            'phone' => $user_info['phone'],
            'alipay_name' => $user_info['alipay_name'],
            'birthday' => $user_info['birthday'],
            'sex' => $user_info['sex']
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
        $user_data['birthday'] = date('Y-m-d H:i:s' , $birthday);
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
         $info = self::where(['sid'=>$uid])
             ->field(['id','sid','real_name','position','phone','head_img'])
             ->select()->toArray();
         if(empty($info)){
            $data = ['total'=>0,'chief'=>0,'area'=>0,'salesman'=>0,'teacher'=>0];
          return  api(200,'未查到数据',['data'=>$data,'info'=>$info]);
         }
         $total = count($info); //总人数
         $chief = self::where(['sid'=>$uid,'position'=>1])->select()->toArray();
         $area = self::where(['sid'=>$uid,'position'=>2])->select()->toArray();
         $salesman = self::where(['sid'=>$uid,'position'=>3])->select()->toArray();
         $teacher = self::where(['sid'=>$uid,'position'=>4])->select()->toArray();

        $data = ['total'=>$total,'chief'=>count($chief),'area'=>count($area),'salesman'=>count($salesman),'teacher'=>count($teacher)];
        return  api(200,'查询成功',['data'=>$data,'info'=>$info]);
    }

    /**
     * 团队
     */

    public function chiefTeam($uid){
        $info = self::where(['id'=>$uid])
            ->field(['id','gid','real_name','position','phone','count_money','manag_area','head_img'])
            ->find();
        if(empty($info)){
              return $info;
        }
        return $info;
    }

    /**
     * 角色
     */

    public function teamNum($uid,$position)
    {
           $mod =new SystemAdmin();
            if($position === 1){
                $info = $mod->where('gid','=',$uid) ->field(['id','gid','real_name','position','phone','head_img'])->select()->toArray();
            }else if($position ===2){
                $info = $mod->where('gid','=',$uid) ->field(['id','gid','real_name','position','phone','head_img'])->select()->toArray();
            }else if($position === 3){
                $info = $mod->where('gid','=',$uid) ->field(['id','gid','real_name','position','phone','head_img'])->select()->toArray();
            }else if($position ===4){
                $info = $mod->where('gid','=',$uid) ->field(['id','gid','real_name','position','phone','head_img'])->select()->toArray();
            }else if($position === 5){
                $info = $mod->where('gid','=',$uid) ->field(['id','gid','real_name','position','phone','head_img','store_name'])->select()->toArray();
            }else{
                $info = self::where(['sid'=>$uid])
                    ->field(['id','gid','real_name','position','phone'])
                    ->select()->toArray();
            }
        if(empty($info)){
            return $info;
        }
        return $info;
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
        return self::where('reco_code' , $reco) -> value('id');
    }

    /**
     * 手机号注册
     */
    public function phoneRegister($phone  , $reco_uid , $ip){
        $user_data['account'] = $phone;
        $user_data['phone'] = $phone;
        $user_data['add_time'] = time();
        $user_data['add_ip'] = $ip;
        $user_data['last_time'] = time();
        $user_data['last_ip'] = $ip;
        $user_data['spread_uid'] = $reco_uid;
        $user_data['spread_time'] = time();
        $user_data['reco_code'] = $this -> setString();
        $user_reco_count = self::where('id' , $reco_uid) -> value('spread_count');
        self::where('id' , $reco_uid) -> save(['spread_count' => $user_reco_count + 1 ]);
        return self::save($user_data);
    }

    public function phoneGetUser($phone){
        return self::where('phone' , $phone) -> value('id');
    }

    /**
     * 手机号登陆
     */
    public function phoneLogin($phone , $ip){
        $user_info = self::where('phone',$phone) -> find() -> toArray();
        if (!$user_info) {
            return array('status' => 0 , 'msg' => '账号不存在!');
        }
        if ($user_info['status'] == 0) {
            return array('status' => 0, 'msg' => '账号异常已被锁定！！！');
        }
        $user = array('uid' => $user_info['id'],
            'nickname' => $user_info['nickname'],
            'avatar' => $user_info['avatar'] ? $user_info['avatar'] : '',
            'phone' => $user_info['phone'],
            'birthday' => $user_info['birthday'],
            'alipay_name' => $user_info['alipay_name'],
            'sex' => $user_info['sex']
        );
        $user['token'] = md5(mt_rand(1, 999999999) . time() . uniqid());
        $user_token = new UserToken();
        $user_token -> uid = $user_info['id'];
        $user_token -> token = $user['token'];
        $user_token -> expires_time = date('Y-m-d H:i:s', time()+ 7 * 24 * 3600);
        $user_token -> login_ip = $ip;
        $user_token -> save();
        self::where('id' , $user_info['id']) -> save(['last_time'=>time() , 'last_ip' => $ip]);
        return array('status' => 1, 'msg' => '登陆成功', 'data' => $user);
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
        return self::where('id' , $uid) -> save(['alipay_name' => '']);
    }

    /**
     * 用户余额
     */
    public function userMoney($uid){
        return self::where('id' , $uid) -> value('now_money');
    }

    /**
     * 返回用户信息
    */
    public function userReturn($uid){
        $user_info = self::where('id' , $uid) -> find() -> toArray();
         return array('uid' => $uid,
            'nickname' => $user_info['nickname'],
            'avatar' => $user_info['avatar'] ? $user_info['avatar'] : '',
            'phone' => $user_info['phone'],
            'birthday' => $user_info['birthday'],
             'alipay_name' => $user_info['alipay_name'],
            'sex' => $user_info['sex']
        );
    }
}