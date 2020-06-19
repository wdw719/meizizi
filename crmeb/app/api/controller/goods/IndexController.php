<?php

namespace app\api\controller\goods;

use app\admin\model\order\GoodsBrand;
use app\admin\model\system\SystemAttachment;
use app\models\goods\Footprint;
use app\models\goods\News;
use app\models\goods\PhoneCode;
use app\models\goods\Shop;
use app\models\goods\Sms;
use app\models\goods\SystemAdmin;
use app\models\goods\Version;
use app\models\goods\User;
use app\models\store\StoreProductRelation;
use app\Request;
use crmeb\services\UtilService;
use function Symfony\Component\VarDumper\Tests\Fixtures\bar;

class IndexController{

    /**
     * 普通注册
     */
    public function register(Request $request){
        $ip = $_SERVER['REMOTE_ADDR'];
        list($head_img , $nickname ,$birthday, $sex , $phone , $password , $reco) = UtilService::getMore([['head_img'], ['nickname'],['birthday'],
            ['sex',1],['phone'],['password'],['reco']], $request, true);
        if(!$phone && !$password && !$reco){
            return api(0,'参数缺失');
        }
        if(empty($reco)) return api(0,'推荐码不能为空');
        if(empty($phone) || empty($password)) return api('0','账号或密码不能为空');
        $user = new SystemAdmin();
        $check_pass = $user -> check_pass_security($password);
        if($check_pass == false){
            return api(0,'该密码不安全，请重新输入密码！');
        }
        $count = $user -> phoneIsRegister($phone);
        if($count > 0){
            return api(0,'该电话号码已经注册');
        }
        $user_count = $user -> usernameIsRegister($phone);
        if($user_count > 0){
            return api(0,'该电话号码已经注册');
        }
        $rep = $user -> register($head_img , $nickname , $birthday , $sex , $phone , $password, $reco , $ip);
        return api(200,'注册成功',$rep);
    }

    /**
     * 账号登陆
     */
    public function login(Request $request){
        $ip = $_SERVER['REMOTE_ADDR'];
        list($username , $password) = UtilService::getMore([['username'] , ['password']] , $request , true);
        if(!$username && !$password)
            return api(0,'参数错误');
        if(!$username || !$password)
            return api(0,'账号或密码不能为空');
        $user = new SystemAdmin();
        $info = $user -> login($username , $password , $ip);
        if($info['status'] == 0)
            return api(0,$info['msg']);
        return app('json') -> successful($info['data']);
    }

    /**
     * 修改用户资料
     */
    public function editUserData(Request $request){
        list($token , $avatar , $birthday , $sex)  = UtilService::getMore([['token'] , ['avatar'] , ['birthday'] , ['sex' , 1]] , $request , true);
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return api(0,'token已失效，请重新登陆');
        $user_rep = $user -> editUserData($avatar , $birthday , $sex , $rep['uid']);
        if($user_rep == false){
            return api(0,'修改错误');
        }
        return api(200,'修改成功');
    }

    /**
     * 用户资料
     */
    public function userInfo(Request $request){
        list($token)  = UtilService::getMore([['token']] , $request , true);
        $user = new SystemAdmin();
        if(empty($token)) return api(0,'token不存在');
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return api(0,'token已失效，请重新登陆');
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
        if(empty($password)) return api('0','请输入密码');
        if(empty($token)) return api('0','token不存在');
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return api(0,'token已失效，请重新登陆');
        $check_password = $user -> checkPassword($rep['uid']);
        if($check_password)
            return api(0,'已设置过密码');
        $ser_pwd_info = $user -> setUpPassword($password , $rep['uid']);
        return api(0,'设置成功');
    }

    /**
     * 修改密码
     */
    public function editPassword(Request $request){
        list($token , $password , $new_password) = UtilService::getMore([['token'] , ['password'] , ['new_password']] , $request , true);
        if(!$token && !$password && !$new_password){
            return api(0,'参数缺失');
        }
        if(empty($token)) return api(0,'token不能为空');
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $check_pass = $user -> check_pass_security($new_password);
        if($check_pass == false){
            return app('json')->fail('该密码不安全，请重新输入密码！');
        }
        if(empty($password)) return api(0,'密码不能为空');
        if(empty($new_password)) return api(0,'新密码不能为空');
        $rep = $user -> editPassword($password , $new_password , $rep['uid']);
        return api(200,'修改成功',['new_password'=>$new_password]);
    }

    /**
     * 获取品牌
     */
    public function brandList(){
        $brand = new GoodsBrand();
        $list = $brand -> brandAllList();
        return app('json') -> succsessful();
    }

    /**
     * 店铺或者商品收藏
     */
    public function relation(Request $request){
        list($id , $type , $re_type , $token) = UtilService::getMore([['id'] , ['type' , 1] , ['re_type' , 1] , ['token']] , $request , true);
        if(!$id && !$type && !$re_type){
            return app('json')->fail('参数缺失');
        }
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $store = new StoreProductRelation();
        $rep = $store -> collection($id , $type , $re_type , $rep['uid']);
        if(!$rep) return app('json')->fail('操作失败！');
        else return app('json')->status('SUCCESS', '操作成功!');
    }

    /**
     *商品收藏列表
     */
    public function goodsRelation(Request $request){
        list($token , $page , $limit) = UtilService::getMore([['token'] , ['page' , 1] , ['limit' , 10]] , $request , true);
        $user = new SystemAdmin();
        $rep = $user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $store = new StoreProductRelation();
        $list = $store -> goodsRelation($rep['uid'] , $page , $limit);
        return json_encode( array(
            'status'=>200,
            'msg'=>'',
            'data'=> $list
        ));
    }


    /**
     * 获取手机验证码
     */
    public function getPhoneCode(Request $request){
        list($phone , $type) = UtilService::getMore([['phone'] , ['type']] , $request , true);
        if(!$phone){
            return app('json') -> fail('手机号错误！');
        }
        //调用短信接口
        $sms = new Sms();
        $code = rand(10000 , 99999);
        $sms -> sendSms($phone , $code , $type);
        $phone_code = new PhoneCode();
        $phone_code -> add($phone , $code);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> ''));
    }

    /**
     *判断手机验证码
     */
    public function ifPhoneCode(Request $request){
        list($phone , $code) = UtilService::getMore([['phone'] , ['code']] , $request , true);
        if(!$phone && $code){
            return app('json') -> fail('手机号或者验证码错误！');
        }
        $phone_code = new PhoneCode();
        $info = $phone_code -> info($phone);
        if($code != $info['code']){
            return app('json') -> fail('验证码错误！');
        }
        if(time() > $info['code_time']){
            return app('json') -> fail('验证码已过期！');
        }
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> ''));
    }

    /**
     * 手机号注册
     */
    public function phoneRegister($phone  , $code){
        if(!$phone){
            return app('json') -> fail('手机号错误！');
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $sy_user = new SystemAdmin();
        $count = $sy_user -> phoneIsRegister($phone);
        if($count > 0){
            return app('json')->fail('该电话号码已经注册');
        }
        $reco_uid = $sy_user -> getUserId($code);
        if(!$reco_uid){
            return app('json')->fail('推荐码错误！');
        }
        $rep = $sy_user -> phoneRegister($phone  , $reco_uid , $ip);
        return app('json')->successful($rep);
    }

    /**
     *推荐码
     */
    public function getUserCode(Request $request){
        list($token) = UtilService::getMore([['token']] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $reco = $sy_user -> getUserReco($rep['uid']);
        $user_reco_info = SystemAttachment::getInfo($rep['uid'].'_user_recommend.jpg', 'name');
        if($user_reco_info){
            $reco_dir = $user_reco_info['att_dir'];
        }else{
            $imageInfo = UtilService::getQRCodePath($reco, $rep['uid'].'_user_recommend.jpg');
            SystemAttachment::attachmentAdd($imageInfo['name'], $imageInfo['size'], $imageInfo['type'], $imageInfo['dir'], $imageInfo['thumb_path'], 1, $imageInfo['image_type'], $imageInfo['time'], 2);
            $reco_dir = $imageInfo['dir'];
        }
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> array('reco_code'=>$reco , 'reco_url'=>$reco_dir)));
    }

    /**
     * 上传资料
     */
    public function addShop(Request $request){
        $ip = $_SERVER['REMOTE_ADDR'];
        list($code , $name , $phone , $address , $long_number , $lati_number , $front_img , $business_img) =
            UtilService::getMore([['code'] , ['name'] , ['phone'] , ['address'] , ['long_number'] , ['lati_number'] , ['front_img'] ,
                ['business_img']] , $request , true);
        if(!$code && !$name && !$phone && !$address && !$long_number && !$lati_number && !$front_img && !$business_img){
            return app('json')->fail('参数内容错误！');
        }
        $shop = new Shop();
        $sy_user = new SystemAdmin();
        $reco_uid = $sy_user -> getUserId($code);
        if(!$reco_uid){
            return app('json')->fail('推荐码错误！');
        }
        $sy_user -> phoneRegister($phone  , $reco_uid , $ip);
        $uid = $sy_user -> phoneGetUser($phone);
        $rep = $shop -> add($reco_uid , $uid , $name , $phone , $address , $long_number , $lati_number , $front_img , $business_img);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $rep));
    }

    /**
     * 验证码登陆
     */
    public function phoneLogin($phone){
        if(!$phone){
            return app('json')->fail('手机号错误！');
        }
        $ip = $_SERVER['REMOTE_ADDR'];
        $sy_name = new SystemAdmin();
        $rep = $sy_name -> phoneLogin($phone , $ip);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $rep['data']));
    }

    /**
     * 附近门店
     */
    public function nearbyShop(Request $request){
        list($token , $long_number , $lati_number , $page , $limit) = UtilService::getMore([['token'] , ['long_number'] , ['lati_number'] , ['page' , 1] , ['limit' , 10]] , $request , true);
        if(!$token && !$long_number && !$lati_number){
            return app('json')->fail('参数内容错误！');
        }
        $shop = new Shop();
        $list = $shop -> nearbyShop($token , $long_number , $lati_number , $page , $limit);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $list));
    }

    /**
     * 关注店铺
     */
    public function followShop(Request $request){
        list($token , $page , $limit) = UtilService::getMore([['token'] , ['page' , 1] , ['limit' , 10 ]] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $store = new StoreProductRelation();
        $list = $store -> followShop($rep['uid'] , $page , $limit);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $list));
    }

    /**
     * 足迹
     */
    public function footprint(Request $request){
        list($token , $page , $limit) = UtilService::getMore([['token'] , ['page' , 1] , ['limit' , 10 ]] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $foot = new Footprint();
        $list = $foot -> footprint($rep['uid'] , $page , $limit);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $list));
    }

    /**
     * 删除单条足迹
     */
    public function delOneFoot(Request $request){
        list($token , $id) = UtilService::getMore([['token'] , ['id']] ,$request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $foot = new Footprint();
        $rep = $foot -> delOneFoot($rep['uid'] , $id);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $rep));
    }

    /**
     * 删除全部
     */
    public function delFoot(Request $request){
        list($token) = UtilService::getMore([['token']] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $foot = new Footprint();
        $reps = $foot -> delFoot($rep['uid']);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=> $reps));
    }

    /**
     * 用户消息列表
     */
    public function newList(Request $request){
        list($token , $page , $limit) = UtilService::getMore([['token'] , ['page' , 1] , ['limit' , 10]] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $news = new News();
        $list = $news -> newsList($rep['uid'] , $page , $limit);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=>$list?array_values($list):array()));
    }

    /**
     * 删除消息
     */
    public function delNews(Request $request){
        list($token , $id) = UtilService::getMore([['token'] , ['id']] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $news = new News();
        $reps = $news -> delNews($rep['uid'] , $id);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=>$reps));
    }

    /**
     * 绑定支付宝账号
     */
    public function alipayName(Request $request){
        list($token , $number) = UtilService::getMore([['token'] , ['number']] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $rep = $sy_user -> alipayName($rep['uid'] , $number);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=>$rep));
    }

    /**
     * 删除支付宝账号
     */
    public function delAlipayName(Request $request){
        list($token) = UtilService::getMore([['token']] , $request , true);
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $rep = $sy_user -> delAlipayName($rep['uid']);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=>$rep));
    }

    /**
     * 用户余额
     */
    public function userMoney($token){
        $sy_user = new SystemAdmin();
        $rep = $sy_user -> userToken($token);
        if($rep['status'] == 0)
            return app('json') -> fail('token已失效，请重新登陆');
        $info = $sy_user -> userMoney($rep['uid']);
        return json_encode( array('status'=>200, 'msg'=>'', 'data'=>$info));
    }

}