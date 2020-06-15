<?php

namespace app\models\goods;

use crmeb\basic\BaseModel;

class News extends BaseModel{

    /**
     * 获取用户消息列表
    */
    public function newsList($uid , $page , $limit){
        return self::where('is_del' , 'not in' , [$uid]) -> field('id , title , content , type , add_time')
            -> page($page , $limit) -> order('add_time desc')-> select() -> toArray();
    }

    /**
     * 删除单条消息
    */
    public function delNews($uid , $id){
        $is_del = self::where('id' , $id) -> where('is_del' , 'not in' , [$uid]) -> value('is_del');
        if(!$is_del){
            $data['is_del'] = $uid;
        }else{
            $data['is_del'] = $is_del.','.$uid;
        }
        return self::where('id' , $id) -> save($data);
    }
}
