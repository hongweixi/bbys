<?php
namespace app\admin\controller;

use think\facade\Request;
use think\facade\Lang;
use app\common\model\DocumentCategory;
use app\common\model\DocumentContent;
use app\common\model\DocumentModel;
use app\admin\controller\Admin;
use app\common\validate\DocumentCategoryValidate;
use app\common\model\Site;

class Category extends Admin
{
    public function index()
    {
        $cid=input('request.cid',0,'intval');
        if(empty($cid)){
            $navcat=db('nav_cat')->find();
            $cid=$navcat['navcid'];
        }
        $result = db('nav')->where(array('cid'=>$cid))->order(array("listorder" => "ASC"))->select();
        import('Tree');
        $tree = new \Tree();
        $tree->icon = array('&nbsp;&nbsp;&nbsp;│ ', '&nbsp;&nbsp;&nbsp;├─ ', '&nbsp;&nbsp;&nbsp;└─ ');
        $tree->nbsp = '&nbsp;&nbsp;&nbsp;';
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . url("admin/category/create", array("parentid" => $r['id'],"cid"=>$r['cid'])) . '">'.lang('ADD_SUB_NAV').'</a> 
| <a href="' . url("admin/category/edit", array("id" => $r['id'],"parentid"=>$r['parentid'],"cid"=>$r['cid'])) . '">'.lang('Edit').'</a> 
| <a onclick="remove('.$r['id'].')" href="#">'.lang('Delete').'</a> ';
            $r['status'] = $r['status'] ? lang('DISPLAY') : lang('HIDDEN');
            $array[] = $r;
        }

        $tree->init($array);
        $str = "<tr>
				<td><input name='listorders[\$id]' type='text' size='3' value='\$listorder' class='input input-order'></td>
				<td>\$id</td>
				<td >\$spacer\$label</td>
			    <td>\$status</td>
				<td>\$str_manage</td>
			</tr>";
        $categorys = $tree->get_tree(0, $str);
        $navcats = db('nav_cat')->select();
        $navcid = $cid;
        return $this->fetch('index', compact('navcats','categorys','navcid'));
    }

    public function create()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {


            $data=input("post.");
            if(isset($data['external_href'])){
                $data['href']=$data['external_href'];
                unset($data['external_href']);
            }else{
                $data['href']=base64_decode($data['href']);
                unset($data['nav']);
            }

            $result=db('nav')->insertGetId($data);
            if ($result!==false) {
                $parentid=input('post.parentid',0,'intval');
                if(empty($parentid)){
                    $data['path']="0-$result";
                }else{
                    $parent=db('nav')->where(array('id'=>$parentid))->find();
                    $data['path']=$parent['path']."-$result";
                }
                $data['id']=$result;
                db('nav')->update($data);
                cache("site_nav_".intval($data['cid']),null);
                cache("site_nav_main",null);
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $cid=input('request.cid',0,'intval');
        $result = db('nav')->where(array('cid'=>$cid))->order(array("listorder" => "ASC"))->select();
        import('Tree');
        $tree = new \Tree();
        $tree->icon = array('&nbsp;│ ', '&nbsp;├─ ', '&nbsp;└─ ');
        $tree->nbsp = '&nbsp;';
        $parentid=input("get.parentid",0,'intval');
        $array = [];
        foreach ($result as $r) {
            $r['str_manage'] = '<a href="' . url("Menu/add", array("parentid" => $r['id'], "menuid" => input("get.menuid"))) . '">添加子菜单</a> | <a href="' . url("Menu/edit", array("id" => $r['id'], "menuid" => input("get.menuid"))) . '">修改</a> | <a class="js-ajax-delete" href="' . url("Menu/delete", array("id" => $r['id'], "menuid" => input("get.menuid"))) . '">删除</a> ';
            $r['status'] = $r['status'] ? "显示" : "隐藏";
            $r['selected'] = $r['id']==$parentid?"selected":"";
            $array[] = $r;
        }

        $tree->init($array);
        $str="<option value='\$id' \$selected>\$spacer\$label</option>";
        $nav_trees = $tree->get_tree(0, $str);

        $navcats=db('nav_cat')->select();

        $navs= $this->_select();

        foreach ($navs as $key=>$navdata){
            $tree->init($navdata['items']);
            $tpl="<option value='\$rule' >\$spacer\$label</option>";
            $navs_html = $tree->get_tree(0, $tpl);
            $navs[$key]['html']=$navs_html;
        }
        $navcid = $cid;
        return $this->fetch('create', compact('navcid','navs','navcats','nav_trees'));
    }

    /**
     * 获取所有应用可用的导航菜单
     */
    private function _select(){
        $navcatname="文章分类";
        $term_obj= db("terms");

        $where=array();
        $where['status'] = array('eq',1);
        $terms=$term_obj->field('term_id,name,parent')->where($where)->order('term_id')->select();
        $datas=$terms;
        $navrule = array(
            "id"=>'term_id',
            "action" => "Home/Index/newList",
            "param" => array(
                "id" => "term_id"
            ),
            "label" => "name",
            "parentid"=>'parent'
        );
        $navs[]= sp_get_nav4admin($navcatname,$datas,$navrule) ;


        $navcatname="页面";

        $where=array();
        $where['post_status'] = array('eq',1);
        $where['post_type'] = array('eq',2);

        $posts_model= db("posts");

        $datas=$posts_model->where($where)->select();
        $navrule=array(
            'id'=>'id',
            "action"=>"Home/Index/details",
            "param"=>array(
                "id"=>"id"
            ),
            "label"=>"post_title",
            'parentid'=>0
        );
        $navs[]= sp_get_nav4admin($navcatname,$datas,$navrule);

        return $navs;
    }

    public function edit()
    {
        // 处理AJAX提交数据
        if (Request::isAjax()) {
            $parentid=empty($_POST['parentid'])?"0":$_POST['parentid'];
            if(empty($parentid)){
                $_POST['path']="0-".$_POST['id'];
            }else{
                $parent=db('nav')->where("id=$parentid")->find();

                $_POST['path']=$parent['path']."-".$_POST['id'];
            }

            $data=input("post.");

            if(isset($data['external_href'])){
                $data['href']=$data['external_href'] . '/cid/'.$data['id'];
                unset($data['external_href']);
            }else{
                $data['href']=base64_decode($data['href']);
                unset($data['nav']);
            }
            if (db('nav')->where('id', $_POST['id'])->update($data) !== false) {
                cache("site_nav_".intval($data['cid']),null);
                cache("site_nav_main",null);
                return $this->response(200, Lang::get('Success'));
            } else {
                return $this->response(201, Lang::get('Fail'));
            }
        }

        $cid=input('request.cid',0,'intval');;
        $id=input("get.id",0,'intval');
        $result = db('nav')->where("cid=$cid and id!=$id")->order(array("listorder" => "ASC"))->select();
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


        $cats=db('nav_cat')->select();
        $this->assign("navcats",$cats);

        $nav=db('nav')->where(array('id'=>$id))->find();
        $nav['hrefold']=$nav['href'];
        $nav['href'] = base64_encode($nav['href']);

        $this->assign($nav);

        $navs= $this->_select();

        foreach ($navs as $key=>$navdata){
            $tree->init($navdata['items']);
            $tpl="<option value='\$rule' >\$spacer\$label</option>";
            $navs_html = $tree->get_tree(0, $tpl);
            $navs[$key]['html']=$navs_html;
        }
        $navcid = $cid;

        return $this->fetch('edit', compact('nav_trees','nav','navcid','navs'));
    }

    public function remove()
    {
        $id = input("post.id",0,'intval');
        $count = db('nav')->where(array("parentid" => $id))->count();
        if ($count > 0) {
            return $this->response(201, "该菜单下还有子菜单，无法删除！");
        }

        if (db('nav')->delete($id)!==false) {
            return $this->response(200, Lang::get('Success'));
        } else {
            return $this->response(201, Lang::get('Fail'));
        }
    }

    public function listorders(){
        $ids = $_POST['listorders'];
        $pk = db('nav')->getPk(); //获取主键名称
        foreach ($ids as $key => $r) {
            $data['listorder'] = $r;
            db('nav')->where($pk, $key)->update($data);
        }
        return $this->response(200, Lang::get('Success'));
    }


}












