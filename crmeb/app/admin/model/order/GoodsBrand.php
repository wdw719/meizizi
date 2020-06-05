<?php

namespace app\admin\model\order;

use crmeb\basic\BaseModel;

/**
 * 商品品牌
*/
class GoodsBrand extends BaseModel{

    /**
     * 品牌列表
    */
    public function brandList(){
        $list =  self::where('') -> select() -> toArray();
        $total_count = self::where('') -> count();
        return array('data'=>$list , 'total_count' => $total_count , 'page' => 1);
    }

    /**
     * 保存品牌
    */
    public function  saveBrand($data){
        return self::save($data);
    }

    /**
     * 品牌详情
    */
    public function info($id){
        return self::where('id' , $id) -> find();
    }

    /**
     * 更新品牌信息
    */
    public function updataGoodsBrand($data , $id){
        return self::where('id' , $id) -> save($data);
    }

    public function brandAllList(){
        return  self::where('') -> select() -> toArray();
    }
}