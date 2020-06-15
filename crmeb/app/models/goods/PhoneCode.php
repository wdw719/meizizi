<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class PhoneCode extends BaseModel{

    public function add($phone , $code){
        $phone_data['phone'] = $phone;
        $phone_data['code'] = $code;
        $phone_data['code_time'] = time()+ 1800;
        self::save($phone_data);
    }

    public function info($phone){
        return self::where('phone' , $phone) -> order('id desc') -> find() -> toArray();
    }
}
