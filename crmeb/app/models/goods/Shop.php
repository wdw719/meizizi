<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

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
        $list = self::where('')->field('id , long_number , lati_number') -> select() -> toArray();

        var_dump($list);exit;
    }


}
