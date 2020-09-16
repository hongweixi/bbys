<?php
namespace app\admin\controller;

use think\facade\Request;
use think\facade\Lang;
use think\facade\Hook;
use app\common\model\Link as LinkModel;
use app\common\model\SiteConfig;
use app\admin\controller\Admin;
use app\common\validate\LinkValidate;

class Link extends Admin
{
    // 分类配置标识
    protected $category = 'link_category';
    protected $targets=array("_blank"=>"新标签页打开","_self"=>"本窗口打开");

    public function index()
    {
        $links=db('links')->order(array("listorder"=>"ASC"))->select();
        return $this->fetch('index', compact('links'));
    }

    public function create()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $data = input('post.');
            if (db('links')->insert($data)!==false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        return $this->fetch('create', ['targets'=>$this->targets]);
    }

    public function edit()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $data = input('post.');
            $id = $data['link_id'];
            unset($data['link_id']);
            if (db('links')->where('link_id', $id)->update($data)!==false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $id=input("get.id",0,'intval');
        $link=db('links')->where(array('link_id'=>$id))->find();
        $data = array_merge($link, ['targets'=>$this->targets]);

        return $this->fetch('edit', $data);
    }

    // 友情链接排序
    public function listorders() {
        $ids = $_POST['listorders'];
        $pk = db('links')->getPk(); //获取主键名称
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            db('links')->where($pk, $key)->update($data);
        }
        return $this->response(200, Lang::get('Success'));
    }

    public function remove()
    {
        $id = Request::param('id');
        if (db('links')->delete($id)!==false) {
            return $this->response(200, Lang::get('Success'));
        } else {
            return $this->response(201, Lang::get('Fail'));
        }
    }

}