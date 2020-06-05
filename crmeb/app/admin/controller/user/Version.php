<?php

namespace app\admin\controller\user;

use app\admin\controller\AuthController;
use app\admin\model\ump\StoreCoupon as CouponModel;
use crmeb\services\FormBuilder as Form;
use crmeb\services\JsonService as Json;
use crmeb\services\UtilService as Util;
use think\facade\Route as Url;

class Version extends AuthController{

    /**
     *版本列表
    */
    public function index(){
        $version = new \app\models\goods\Version();
        $list = $version -> index();
        $this -> assign('list' , $list['data']);
        $this -> assign('total' , $list['total_count']);
        $this -> assign('page' , $list['page']);
        return $this->fetch('user/version/index');
    }


    /**
     * 添加版本
    */
    public function addVersion(){
        $f = array();
        $f[] = Form::radio('status','版本类型',1)->options([['label'=>'安卓','value'=>1],['label'=>'IOS','value'=>2]]);
        $f[] = Form::radio('type','更新类型',1)->options([['label'=>'提示升级','value'=>1],['label'=>'强制升级','value'=>2]]);
        $f[] = Form::input('content','更新提示');
        $f[] = Form::input('url','更新链接');
        $f[] = Form::input('version','版本号');
        $form = Form::make_post_form('添加版本信息',$f,Url::buildUrl('save'));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存
    */
    public function save(){
        $data = Util::postMore([
            'status',
            'type',
            'content',
            'url',
            'version',
        ]);
        if(!$data['content']) return Json::fail('请输入更新版本内容');
        if(!$data['url']) return Json::fail('请输入版本下载地址');
        if(!$data['version']) return Json::fail('请输入版本号');
        $data['create_time'] = date('Y-m-d H:i:s' , time());
        $data['update_time'] = date('Y-m-d H:i:s' , time());
        $version = new \app\models\goods\Version();
        $version -> versionSave($data);
        return Json::successful('添加版本成功!');
    }

    /**
     * 更新版本信息
    */
    public function edit($id){
        $version = new \app\models\goods\Version();
        $info = $version -> info($id);
        if(!$info) return Json::fail('版本信息错误!');
        $f = array();
        $f[] = Form::radio('status','版本类型',$info->getData('status'))->options([['label'=>'安卓','value'=>1],['label'=>'IOS','value'=>2]]);
        $f[] = Form::radio('type','更新类型',$info->getData('type'))->options([['label'=>'提示升级','value'=>1],['label'=>'强制升级','value'=>2]]);
        $f[] = Form::input('content','提示内容',$info->getData('content'));
        $f[] = Form::input('url','提示内容',$info->getData('url'));
        $f[] = Form::input('version','提示内容',$info->getData('version'));
        $form = Form::make_post_form('更新版本信息',$f,Url::buildUrl('update',array('id'=>$id)));
        $this->assign(compact('form'));
        return $this->fetch('public/form-builder');
    }

    /**
     * 保存版本信息
    */
    public function update($id)
    {
        $data = Util::postMore([
            'status',
            'type',
            'content',
            'url',
            'version'
        ]);
        if(!$data['content']) return Json::fail('请输入更新版本内容');
        if(!$data['url']) return Json::fail('请输入版本下载地址');
        if(!$data['version']) return Json::fail('请输入版本号');
        $data['update_time'] = date('Y-m-d H:i:s' , time());
        $version = new \app\models\goods\Version();
        $version -> updateVersion($data , $id);
        return Json::successful('修改成功!');
    }
}