<?php
namespace app\admin\controller;

use think\facade\Request;
use think\facade\Lang;
use think\facade\Hook;
use app\common\model\Slider as SliderModel;
use app\common\model\SiteConfig;
use app\admin\controller\Admin;
use app\common\validate\SliderValidate;

class Slider extends Admin
{
    // 分类配置标识
    protected $category = 'slider_category';

    public function index()
    {
        $cates=array(
            array("cid"=>"0","cat_name"=>"默认分类"),
        );
        $categorys=db('slide_cat')->field("cid,cat_name")->where("cat_status!=0")->select();
        if($categorys){
            $categorys=array_merge($cates,$categorys);
        }else{
            $categorys=$cates;
        }
        $where=array();
        $cid = input('post.cid',0,'intval');
        if(!empty($cid)){
            $where=array('slide_cid'=>$cid);
        }
        $slides=db('slide')->where($where)->order("listorder ASC")->select();
        $slide_cid = $cid;
        return $this->fetch('index', compact('slides','slide_cid','categorys'));
    }

    public function create()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $data = input('post.');
            if (db('slide')->insert($data)!==false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }
        $categorys = db('slide_cat')->field("cid,cat_name")->where("cat_status!=0")->select();
        return $this->fetch('create', ['categorys'=>$categorys]);
    }

    public function edit()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $data = input('post.');
            $id = $data['slide_id'];
            unset($data['slide_id']);
            if (db('slide')->where('slide_id', $id)->update($data)!==false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $categorys=db('slide_cat')->field("cid,cat_name")->where("cat_status!=0")->select();
        $id = input("get.id",0,'intval');
        $slide=db('slide')->where(array('slide_id'=>$id))->find();
        $data = array_merge($slide, ['categorys'=>$categorys]);

        return $this->fetch('edit', $data);
    }

    // 友情链接排序
    public function listorders() {
        $ids = $_POST['listorders'];
        $pk = db('slide')->getPk(); //获取主键名称
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            db('slide')->where($pk, $key)->update($data);
        }
        return $this->response(200, Lang::get('Success'));
    }

    public function remove()
    {
        $id = Request::param('id');
        if (db('slide')->delete($id)!==false) {
            return $this->response(200, Lang::get('Success'));
        } else {
            return $this->response(201, Lang::get('Fail'));
        }
    }
}