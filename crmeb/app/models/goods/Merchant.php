<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class Merchant extends BaseModel
{
    /**
     * 团队管理-我的商家
     */
     public function catStore($uid){
         return Merchant::where(['uid'=>$uid])->field(['id','uid','stores_name','stocks','store_phone','areas','s_stocks'])->select();
     }

    /**
     * 讲师-我的商家
     */
    public function catTeacher($uid){
        return Merchant::where(['uid'=>$uid])->field(['id','uid','stores_name','store_phone','areas'])->select();
    }


    /**
     *  店铺信息
     */

    public function shopsInfo($uid,$mid){
         return self::where(['uid'=>$uid,'id'=>$mid])->find();
    }


}