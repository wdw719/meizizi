<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;
use think\db\Query;

class Shop extends BaseModel{

    /**
     *店铺入驻
    */
    public function add($reco_id , $uid ,$name , $phone , $address , $long_number , $lati_number , $front_img , $business_img){
        $data['reco_id'] = $reco_id;
        $data['uid'] = $uid;
        $data['name'] = $name;
        $data['phone'] = $phone;
        $data['address'] = $address;
        $data['long_number'] = $long_number;
        $data['lati_number'] = $lati_number;
        $data['front_img'] = $front_img;
        $data['business_img'] = $business_img;
        return self::save($data);
    }

    public function  nearbyShop($token , $long_number , $lati_number , $page , $limit){
        $list = self::where('') -> alias('a')
            ->field('a.id , a.long_number , a.lati_number , a.phone , a.address , a.wx_name , a.logo , a.company  ,
            (st_distance (point (a.long_number,a.lati_number),point('.$long_number.','.$lati_number.'))* 111195) AS distance')
            ->page($page , $limit)  ->order('distance asc')-> select() -> toArray();
        return $list?array_values($list):array();

    }

    public function info($id){
        return self::where('uid' , $id) -> find() -> toArray();
    }

    public function updateMsg($data , $id){
        return self::where('id' , $id) -> save($data);
    }
}
