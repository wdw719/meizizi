<?php

namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService as Json;
use crmeb\services\UtilService as Util;
use think\facade\Route as Url;

/**
 * 商品品牌
*/
class GoodsBrand extends AuthController{

    /**
     * 品牌列表
    */
    public function brandList(){
        $brand = new \app\admin\model\order\GoodsBrand();
        $list = $brand -> brandList();
        $this -> assign('list' , $list['data']);
        $this -> assign('total' , $list['total_count']);
        $this -> assign('page' , $list['page']);
        return $this->fetch('user/brand/index');
    }

    /**
     * 添加品牌
    */
    public function addBrand(){
        $f = array();
        $f[] =  Form::number('sort','排序')->min(0)->precision(0)->col(0);
        $f[] = Form::input('brand_name','品牌名称');
        $form = Form::make_post_form('添加品牌',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     *保存品牌
    */
    public function save(){
        $data = Util::postMore([
            'brand_name',
            'sort']);
        if(!$data['brand_name']) return Json::fail('请输入品牌名称!');
        $brand = new \app\admin\model\order\GoodsBrand();
        $brand -> saveBrand($data);
        return Json::successful('添加品牌成功!');
    }

    /**
     * 更新品牌
    */
    public function edit($id){
        $brand = new \app\admin\model\order\GoodsBrand();
        $info = $brand -> info($id);
        if(!$info) return Json::fail('品牌信息错误!');
        $f = array();
        $f[] = Form::input('brand_name','品牌名称',$info->getData('brand_name'));
        $f[] = Form::number('sort','排序',$info->getData('sort'));
        $form = Form::make_post_form('更新品牌信息',$f,Url::buildUrl('update',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 更新品牌信息
    */
    public function update($id)
    {
        $data = Util::postMore([
            'brand_name',
            'sort'
        ]);
        if(!$data['brand_name']) return Json::fail('请输入品牌名称');
        $brand = new \app\admin\model\order\GoodsBrand();
        $brand -> updataGoodsBrand($data , $id);
        return Json::successful('修改成功!');
    }


}
