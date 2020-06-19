<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class Merchant extends BaseModel
{
    /**
     *  查看店铺信息
     */
     public function catStore($uid){
         return Merchant::where(['uid'=>$uid])->field(['id','uid','stores_name','stocks','store_phone','areas','s_stocks'])->select();
     }

    /**
     *  店铺信息
     */

    public function shopsInfo($uid,$mid){
         return self::where(['uid'=>$uid,'id'=>$mid])->find();
    }


}