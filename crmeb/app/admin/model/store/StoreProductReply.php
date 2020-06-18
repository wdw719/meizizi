<?php
/**
 * @author: xaboy<365615158@qq.com>
 * @day: 2017/11/11
 */

namespace app\admin\model\store;

use crmeb\traits\ModelTrait;
use crmeb\basic\BaseModel;

/**
 * 评论管理 model
 * Class StoreProductReply
 * @package app\admin\model\store
 */
class StoreProductReply extends BaseModel
{
    /**
     * 数据表主键
     * @var string
     */
    protected $pk = 'id';

    /**
     * 模型名称
     * @var string
     */
    protected $name = 'store_product_reply';

    use ModelTrait;

    protected function getPicsAttr($value)
    {
        return json_decode($value,true);
    }
    /*
     * 设置where条件
     * @param array $where
     * @param string $alias
     * @param object $model
     * */
    public static function valiWhere($where,$alias='',$joinAlias='',$model=null)
    {
        $model=is_null($model) ? new self() : $model;
        if($alias){
            $model=$model->alias($alias);
            $alias.='.';
        }
        $joinAlias=$joinAlias ? $joinAlias.'.' : '';
        if(isset($where['title']) && $where['title']!='') $model=$model->where("{$alias}comment",'LIKE',"%$where[title]%");
        if(isset($where['is_reply']) && $where['is_reply']!='') $model= $where['is_reply'] >= 0 ? $model->where("{$alias}is_reply",$where['is_reply']) : $model->where("{$alias}is_reply",'>',0);
        if(isset($where['producr_id']) && $where['producr_id']!=0) $model=$model->where($alias.'product_id',$where['producr_id']);
        if(isset($where['product_name']) && $where['product_name']) $model=$model->where("{$joinAlias}store_name",'LIKE',"%$where[product_name]%");
        return $model->where("{$alias}is_del",0);
    }

    public static function getProductImaesList($where)
    {
        $list=self::valiWhere($where,'a','p')->group('p.id')->join('wechat_user u','u.uid=a.uid','LEFT')->join("store_product p",'a.product_id=p.id','LEFT')->field(['p.id','p.image','p.store_name','p.price'])->page($where['page'],$where['limit'])->select();
        $list=count($list) ? $list->toArray() : [];
        foreach ($list as &$item){
            $item['store_name']=self::getSubstrUTf8($item['store_name'],10,'UTF-8','');
        }

        return $list;
    }

    public static function getProductReplyList($where)
    {
        $data=self::valiWhere($where,'a','p')->join("store_product p",'a.product_id=p.id','left')
            ->join('user u','u.uid=a.uid','left')
            ->order('a.add_time desc,a.is_reply asc')
            ->field('a.*,u.nickname,u.avatar')
            ->page((int)$where['message_page'],(int)$where['limit'])
            ->select();
        $data=count($data) ? $data->toArray() : [];
        foreach ($data as &$item){
            $item['time']=\crmeb\services\UtilService::timeTran($item['add_time']);
        }

        $count=self::valiWhere($where,'a','p')->join('user u','u.uid=a.uid','left')->join("store_product p",'a.product_id=p.id','left')->count();
        return ['list'=>$data,'count'=>$count];
    }
    /**
     * @param $where
     * @return array
     */
    public static function systemPage($where){
        $model = new self;
        if($where['comment'] != '')  $model = $model->where('r.comment','LIKE',"%$where[comment]%");
        if($where['is_reply'] != ''){
            if($where['is_reply'] >= 0){
                $model = $model->where('r.is_reply',$where['is_reply']);
            }else{
                $model = $model->where('r.is_reply','>',0);
            }
        }
        if($where['product_id'])  $model = $model->where('r.product_id',$where['product_id']);
        $model = $model->alias('r')->join('wechat_user u','u.uid=r.uid');
        $model = $model->join('store_product p','p.id=r.product_id');
        $model = $model->where('r.is_del',0);
        $model = $model->field('r.*,u.nickname,u.headimgurl,p.store_name');
        $model = $model->order('r.add_time desc,r.is_reply asc');
        return self::page($model,function($itme){

        },$where);
    }


    /**
     * 评价
    */
    public function goodsEva($number , $content , $imgs , $uid , $is_ano , $oid , $gid){
        $data['uid'] = $uid;
        $data['oid'] = $oid;
        $data['product_id'] = $gid;
        $data['product_score'] = $number;
        $data['comment'] = $content;
        $data['pics'] = $imgs;
        $data['add_time'] = time();
        $data['is_ano'] = $is_ano;
        return self::save($data);
    }

    public function evaList($type , $gid , $page , $limit){
        $ex_count = self::where('is_del' , 0)
            -> where('product_id' , $gid)
            -> where('product_score' , '>' , 3)
            -> count();
        $di_count = self::where('is_del' , 0) -> where('product_id' , $gid) -> where('product_score' , '<' , 3) -> count();
        $total_count = self::where('is_del' , 0) -> where('product_id' , $gid) -> count();
        $in_count = $total_count - $ex_count - $di_count;
        if($type == 1){ //好评
            $where = '>';
        }elseif ($type == 2){   //中评
            $where = '=';
        }else{  //差评
            $where = '<';
        }
        $ev_list = self::where('A.is_del' , 0) -> where('A.product_id' , $gid)-> where('A.product_score' , $where , 3)
            ->alias('A')
            ->join('user B','A.uid = B.uid')
            ->field('A.id , A.product_score , A.comment , A.pics , A.add_time , B.nickname , B.avatar')-> page($page , $limit) -> select() -> toArray();
        return array('eva_list'=>$ev_list , 'ex_count'=>$ex_count ,'in_count'=>$in_count , 'di_count'=>$di_count);
    }
}