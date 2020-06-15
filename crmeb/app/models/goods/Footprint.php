<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class Footprint extends BaseModel{

    /**
     * 足迹
    */
    public function footprint($uid , $page , $limit){
        $list = self::where('A.uid' , $uid)-> alias('A') -> join('StoreProduct B' , 'A.goods_id = B.id')
        ->field('A.add_time , B.id , B.image , B.store_name , B.price') -> page($page , $limit) -> order('A.add_time desc') -> select() -> toArray();
        return $list?array_values($list):array();
    }

    /**
     * 添加足迹
    */
    public function addFootPrint($uid , $goods_id){
        $info = self::where(['uid'=>$uid , 'goods_id'=>$goods_id]) -> find();
        if(!$info){
            $data['uid'] = $uid;
            $data['add_time'] = date('Y-m-d H:i:s' , time());
            $data['goods_id'] = $goods_id;
            self::save($data);
        }else{
            self::where('id' , $info['id']) -> save(['add_time' => date('Y-m-d H:i:s' , time())]);
        }
    }

    /**
     * 删除单条
    */
    public function delOneFoot($uid , $id){
        return self::where(['uid' => $uid , 'goods_id' => $id]) -> delete();
    }

    /**
     * 删除全部
    */
    public function delFoot($uid){
        return self::where('uid' , $uid) -> delete();
    }
}