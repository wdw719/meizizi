<?php

namespace app\models\goods;

use app\models\article\ArticleCategory;
use crmeb\basic\BaseModel;
use crmeb\traits\ModelTrait;
use think\Db;

class Article extends BaseModel{


    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'article';

    use ModelTrait;

    /**
     * 帮助中心
    */
    public function help(int $page , int $limit , $content){
        $category_id = ArticleCategory::where('title','帮助中心')->value('id');
        $condition['cid'] = $category_id;
        $condition['status'] = 1;
        if($content){
            $list = self::where($condition)->where('title','like','%'.$content.'%') -> field('id , title') -> page($page , $limit) -> select();
            $total_count = self::where($condition) -> where('title','like','%'.$content.'%') -> count();
        }else{
            $list = self::where($condition) -> field('id , title') -> page($page , $limit) -> select();
            $total_count = self::where($condition) -> count();
        }
        $page_count = ceil($total_count / $limit);
        return array('list'=>$list, 'total_count' => $total_count , 'page_count' => $page_count);
    }

    /**
     * 帮助中心详情
    */
    public function helpInfo(int $id){
        $info =  self::where('id' , $id) -> where('status' , 1) -> field('id , title , add_time , author')  -> find() -> toArray();
        $info['content'] = ArticleContent::where('nid' , $id) -> value('content');
        $info['add_time'] = date('Y-m-d H:i:s' , $info['add_time']);
        return $info;
    }
}