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

class Document extends Admin
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

        $where[]= ['post_type', '=', 1];
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
            'taxonomys'     => $this->_getTree(),
            'option'   => Config::get('site.document_option'),
        ];

        return $this->fetch('index', $data);
    }

    public function create()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {

            if(empty($_POST['term'])){
                return $this->response(201, "请至少选择一个分类！");
            }
            if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
                foreach ($_POST['photos_url'] as $key=>$url){
                    $photourl=sp_asset_relative_url($url);
                    $_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
                }
            }
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);

            $article=input("post.post");
            $article['post_modified']=date("Y-m-d H:i:s",time());
            $article['post_author'] = get_current_admin_id();
            $article['smeta']=json_encode($_POST['smeta']);
            $article['post_content']=htmlspecialchars_decode($article['post_content']);
            $result=db('posts')->insertGetId($article);
            if ($result) {
                foreach ($_POST['term'] as $mterm_id){
                    $rs = db('term_relationships')->insert(array("term_id"=>intval($mterm_id),"object_id"=>$result));
                }
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $terms = db('terms')->order(array("listorder"=>"asc"))->select();
        $term_id = input("get.term",0,'intval');
        $taxonomys = $this->_getTermTree();
        $term=db('terms')->where(array('term_id'=>$term_id))->find();

        return $this->fetch('create', compact('term','terms', 'taxonomys'));
    }

    // 获取文章分类树结构
    private function _getTree($term=array()){
        $term_id=empty($_REQUEST['term'])?0:intval($_REQUEST['term']);
        $result = db('terms')->order(array("listorder"=>"asc"))->select();
        import('Tree');
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . url("AdminTerm/add", array("parent" => $r['term_id'])) . '">添加子类</a> | <a href="' . url("AdminTerm/edit", array("id" => $r['term_id'])) . '">修改</a> | <a class="js-ajax-delete" href="' . url("AdminTerm/delete", array("id" => $r['term_id'])) . '">删除</a> ';
            $r['visit'] = "<a href='#'>访问</a>";
            $r['taxonomys'] = isset($this->taxonomys[$r['taxonomy']]) ? $this->taxonomys[$r['taxonomy']] : '';
            $r['id']=$r['term_id'];
            $r['parentid']=$r['parent'];
            $r['selected']=$term_id==$r['term_id']?"selected":"";
            $array[] = $r;
        }

        $tree->init($array);
        $str="<option value='\$id' \$selected>\$spacer\$name</option>";
        return $tree->get_tree(0, $str);
    }

    // 获取文章分类树结构
    private function _getTermTree($term=array()){
        $result = db('terms')->order(array("listorder"=>"asc"))->select();
        import('Tree');
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . url("AdminTerm/add", array("parent" => $r['term_id'])) . '">添加子类</a> | <a href="' . url("AdminTerm/edit", array("id" => $r['term_id'])) . '">修改</a> | <a class="js-ajax-delete" href="' . url("AdminTerm/delete", array("id" => $r['term_id'])) . '">删除</a> ';
            $r['visit'] = "<a href='#'>访问</a>";

            $r['taxonomys'] = isset($this->taxonomys[$r['taxonomy']]) ? $this->taxonomys[$r['taxonomy']] : '';
            $r['id']=$r['term_id'];
            $r['parentid']=$r['parent'];
            $r['selected']=in_array($r['term_id'], $term)?"selected":"";
            $r['checked'] =in_array($r['term_id'], $term)?"checked":"";
            $array[] = $r;
        }

        $tree->init($array);
        $str="<option value='\$id' \$selected>\$spacer\$name</option>";
        return $tree->get_tree(0, $str);

        $this->assign("taxonomys", $taxonomys);
    }

    public function edit()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            if(empty($_POST['term'])){
                return $this->response(201, "请至少选择一个分类！");
            }
            $post_id=intval($_POST['post']['id']);

            db('term_relationships')
//                ->where(array("object_id"=>$post_id,"term_id"=>array("not in",implode(",", $_POST['term']))))
                ->where("object_id", $post_id)
                ->whereNotIn("term_id", implode(",", $_POST['term']))
                ->delete();
            foreach ($_POST['term'] as $mterm_id){
                $find_term_relationship=db('term_relationships')->where(array("object_id"=>$post_id,"term_id"=>$mterm_id))->count();
                if(empty($find_term_relationship)){
                    db('term_relationships')->insert(array("term_id"=>intval($mterm_id),"object_id"=>$post_id));
                }else{
                    db('term_relationships')->where(array("object_id"=>$post_id,"term_id"=>$mterm_id))->update(array("status"=>1));
                }
            }


            if(!empty($_POST['photos_alt']) && !empty($_POST['photos_url'])){
                foreach ($_POST['photos_url'] as $key=>$url){
                    $photourl=sp_asset_relative_url($url);
                    $_POST['smeta']['photo'][]=array("url"=>$photourl,"alt"=>$_POST['photos_alt'][$key]);
                }
            }
            $_POST['smeta']['thumb'] = sp_asset_relative_url($_POST['smeta']['thumb']);
            unset($_POST['post']['post_author']);
            $article=input("post.post");
            $article['post_modified'] = date("Y-m-d H:i:s",time());
            $article['smeta']=json_encode($_POST['smeta']);
            $article['post_content']=htmlspecialchars_decode($article['post_content']);
            $result= db('posts')->update($article);
            if ($result!==false) {
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $id =  input("get.id",0,'intval');

        $term_relationship = db('term_relationships')
            ->where("object_id", '=', $id)
            ->where("status", '=', 1)
            ->column("term_id");

        $taxonomys = $this->_getTermTree($term_relationship);
        $terms=db('terms')->select();
        $post=db('posts')->where("id=$id")->find();
        $smeta = json_decode($post['smeta'],true);

        return $this->fetch('edit', compact('post','smeta','terms','term','taxonomys'));
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