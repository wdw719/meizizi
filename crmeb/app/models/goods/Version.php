<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class Version extends BaseModel{

    /**
     * 更新版本信息
    */
    public function edition($type){
        return self::where('status' , $type) -> order('create_time' , 'desc') -> find() -> toArray();
    }

    /**
     * 版本列表
    */
    public function index(){
        $list =  self::where('') -> select() -> toArray();
        $total_count = self::where('') -> count();
        return array('data'=>$list , 'total_count' => $total_count , 'page' => 1);
    }

    /**
     * 添加版本信息
    */
    public function versionSave($data){
        return self::save($data);
    }

    /**
     * 获取版本信息
    */
    public function info($id){
        return self::where('id' , $id) -> find();
    }

    public function updateVersion($data , $id){
        return self::where('id' , $id) -> save($data);
    }
}