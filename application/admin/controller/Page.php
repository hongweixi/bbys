<?php
namespace app\admin\controller;

use think\facade\Request;
use think\facade\Lang;
use think\facade\Hook;
use think\facade\Config;
use app\common\model\DocumentContent;
use app\common\model\DocumentCategory;
use app\common\model\DocumentContentExtra;
use app\admin\controller\Admin;
use app\common\validate\DocumentContentValidate;
use app\common\model\AuthRole;
use app\common\model\Tags;
use think\Session;

class Page extends Admin
{

    protected $taxonomys=array("article"=>"文章","picture"=>"图片");

    public function index()
    {
        $term_id=input('request.term',0,'intval');
        if(!empty($term_id)) {
            $where[] = ['b.term_id', '=', $term_id];
        }
        $start_time=input('request.start_time');
        if(!empty($start_time)){
            $where[]= ['post_date', '>=', $start_time];
        }

        $end_time=input('request.end_time');
        if(!empty($end_time)){
            $where[]= ['post_date', '<=', $end_time];
        }

        $keyword=input('request.keyword');
        if(!empty($keyword)){
            $where[]= ['post_title', 'like', "%$keyword%"];
        }

        $query = [
            'term' => $term_id,
            'keyword'     => $keyword,
            'start_time'     => $start_time,
            'end_time'     => $end_time,
        ];

        $where[]= ['post_type', '=', 2];
        $where[]= ['post_status', '<>', 3];

        $obj = db('posts')
            ->alias("a")
            ->join("__USERS__ c", "a.post_author = c.id")
            ->where($where)
//            ->limit($page->firstRow , $page->listRows)
            ->order("a.post_date DESC");
        if(empty($term_id)){
            $obj->field('a.*,c.user_login,c.user_nicename');
        }else{
            $obj->field('a.*,c.user_login,c.user_nicename,b.listorder,b.tid');
            $obj->join("term_relationships b", "a.id = b.object_id");
        }

        $list = $obj->paginate(20, false, [
            'type'     => 'bootstrap',
            'var_page' => 'page',
            'query'    => $query,
        ]);

        $data = [
            'search'   => $query,
//            'category' => $category,
            'list'     => $list,
            'page'     => $list->render(),
            'option'   => Config::get('site.document_option'),
        ];

        return $this->fetch('index', $data);
    }

    public function create()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            $page=input("post.post");

            $page['post_date']=date("Y-m-d H:i:s",time());
            $page['post_author']=get_current_admin_id();
            $page['smeta']=json_encode($_POST['smeta']);
            $page['post_type']=2;
            $page['post_content']=htmlspecialchars_decode($page['post_content']);
            $result=db('posts')->insert($page);
            if ($result) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }
        $result = db('nav')->order(array("listorder" => "ASC"))->select();
        import('Tree');
        $tree = new \Tree();
        $tree->icon = array('&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $parentid= input("get.parentid",0,'intval');
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . url("Menu/add", array("parentid" => $r['id'], "menuid" => input("get.menuid"))) . '">添加子菜单</a> | <a href="' . url("Menu/edit", array("id" => $r['id'], "menuid" => input("get.menuid"))) . '">修改</a> | <a class="js-ajax-delete" href="' . url("Menu/delete", array("id" => $r['id'], "menuid" => input("get.menuid"))) . '">删除</a> ';
            $r['status'] = $r['status'] ? "显示" : "隐藏";
            $r['selected'] = $r['id']==$parentid?"selected":"";
            $array[] = $r;
        }

        $tree->init($array);
        $str="<option value='\$id' \$selected>\$spacer\$label</option>";
        $nav_trees = $tree->get_tree(0, $str);
        $this->assign("nav_trees", $nav_trees);
        return $this->fetch('create');
    }


    public function edit()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {

            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);

            unset($_POST['post']['post_author']);
            $page=input("post.post");
            $page['smeta']=json_encode($_POST['smeta']);
            $page['post_content']=htmlspecialchars_decode($page['post_content']);
            $result=db('posts')->update($page);
            if ($result !== false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $terms_obj = db("Terms");
        $term_id = input("get.term",0,'intval');
        $id= input("get.id",0,'intval');
        $term=$terms_obj->where(array('term_id'=>$term_id))->find();
        $post=db('posts')->where(array('id'=>$id))->find();

        $result = db('nav')->order(array("listorder" => "ASC"))->select();
        import('Tree');
        $tree = new \Tree();
        $tree->icon = array('&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        $parentid =  $post['menu_id'];
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . url("Menu/add", array("parentid" => $r['id'], "menuid" => input("get.menuid"))) . '">添加子菜单</a> | <a href="' . url("Menu/edit", array("id" => $r['id'], "menuid" => input("get.menuid"))) . '">修改</a> | <a class="js-ajax-delete" href="' . url("Menu/delete", array("id" => $r['id'], "menuid" => input("get.menuid"))) . '">删除</a> ';
            $r['status'] = $r['status'] ? "显示" : "隐藏";
            $r['selected'] = $r['id']==$parentid?"selected":"";
            $array[] = $r;
        }

        $tree->init($array);
        $str="<option value='\$id' \$selected>\$spacer\$label</option>";
        $nav_trees = $tree->get_tree(0, $str);
        $this->assign("nav_trees", $nav_trees);


        $smeta = json_decode($post['smeta'],true);
        return $this->fetch('edit', compact('post','smeta','term'));
    }

    public function handle()
    {
        $request = Request::instance()->param();

        $contentObj = new DocumentContent;
        switch ($request['type']) {
            case 'delete':
                foreach ($request['ids'] as $v) {
                    $result = $contentObj->remove($this->site_id, $v);
                }

                if ($result !== false) {
                    return $this->response(200, Lang::get('Success'));
                }
                break;
            case 'approval':
                foreach ($request['ids'] as $v) {
                    $result = $contentObj
                        ->where('id', $v)
                        ->setField('status', 1);
                }

                if ($result !== false) {
                    return $this->response(200, Lang::get('Success'));
                }
                break;
            case 'progress':
                foreach ($request['ids'] as $v) {
                    $result = $contentObj
                        ->where('id', $v)
                        ->setField('status', 0);
                }

                if ($result !== false) {
                    return $this->response(200, Lang::get('Success'));
                }
                break;
        }

        return $this->response(201, Lang::get('Fail'));
    }

    public function createInput()
    {
        $request = Request::param();

        // 查询model_id
        $cateObj = new DocumentCategory;
        $model_id = $cateObj->where('id', $request['cid'])->value('model_id');

        $inputObj = new DocumentContentExtra();

        // extra 不为空则设置默认值
        if (!empty($request['extra'])) {
            $inputObj->setInputDefaultData($request['extra']);
        }

        return $inputObj->buildInput($this->site_id, $model_id);
    }

    public function remove()
    {
        if(isset($_POST['id'])){
            $id = input("id",0,'intval');
            if (db('posts')->where(array('id'=>$id))->update(array('post_status'=>3)) !==false) {
			    return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        if(isset($_POST['ids'])){
            $ids = input('post.ids/a');

            if (db('posts')->where(array('id'=>array('in',$ids)))->update(array('post_status'=>3))!==false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }
    }
}