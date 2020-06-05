<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

/**
 * 用户搜索记录
*/

class UserSearch extends BaseModel{

    /**
     * 添加用户搜索条件
    */
    public function userAddSearch($value , $uid){
        $info = self::where('uid' , $uid) -> where('value' , $value) -> find();
        if($info){
            self::where('id' , $info['id']) -> save(array('number'=>$info['number'] + 1));
        }else{
            self::save(array('uid'=>$uid , 'value'=>$value));
        }
    }

    /**
     * 用户清空搜索历史
    */
    public function userDelSearch($uid){
        self::where('uid' , $uid) -> delete();
    }

    /**
     * 用户搜索历史
    */
    public function search($uid){
        return self::where('uid' , $uid) -> limit(4)  -> order('number' , 'desc')-> select() -> toArray();
    }
}
