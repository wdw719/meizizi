<?php

namespace app\admin\model\user;

use crmeb\basic\BaseModel;

class News extends BaseModel{

    /**
     * 后台消息列表
    */
    public function msgList(){
        $list =  self::where('type' , 1) -> select() -> toArray();
        $total_count = self::where('') -> count();
        return array('data'=>$list , 'total_count' => $total_count , 'page' => 1);
    }

    public function saveMsg($data){
        $data['type'] = 1;
        $data['add_time'] = date('Y-m-d H:i:s' , time());
        return self::save($data);
    }

    public function info($id){
        return self::where('id' , $id) -> find() -> toArray();
    }

    public function updateMsg($data , $id){
        return self::where('id' , $id) -> save($data);
    }

    public function delMsg($id){
        return self::where('id' , $id) -> delete();
    }


}
