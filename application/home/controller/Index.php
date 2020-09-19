<?php
namespace app\home\controller;

use app\index\controller\Base;
use think\facade\Config;
use think\facade\Request;

class Index extends Base
{
    public function index()
    {
//        var_dump(1);
//        var_dump($_SERVER);
//        exit;

//        $data = $this->getChamberInfo();
        //滚动新闻:取商会动态修改时间前3条
//        $scrollPosts = sp_sql_posts('cid:26;field:object_id,term_id,post_title;limit:0,3;order:post_modified DESC');

//        $this->assign("data", $data);
//        $this->assign("scrollPosts", $scrollPosts);
//        $this->display(":index");

        return $this->fetch('default/index/index', compact('data','scrollPosts'));
    }

    public function product($p = 0)
    {
        if($p){

            $nav_left = [];
            $rs_data = [];

            $terms_model= db("nav");
            $where[] = ['parentid', '=', 0];
            $where[] = ['status', '=', 1];
            $where[] = ['id', '=', $p];
            $page = $terms_model->field(['id','label'])->where($where)->limit(1)->find();
            if(!empty($page)){
                $nav_left = $this->getNavLeft( $page['id'] );
                $rs_data = $this->getData( $nav_left );
            }

        }else{
            $nav_left = [];
            $rs_data = [];
            if( ($page = $this->getPage()) ){
                $nav_left = $this->getNavLeft( $page['id'] );
                $rs_data = $this->getData( $nav_left );
            }
        }


        return $this->fetch('default/index/product', compact('nav_left','rs_data','page'));
    }

    public function support($p = 0, $detail = 0)
    {
        $terms_model= db("nav");
        $where[] = ['parentid', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['id', '=', $p];
        $page = $terms_model->field(['id','label'])->where($where)->limit(1)->find();

        if($detail != 0){
            $info = $this->getPostInfo($detail);
            return $this->fetch('default/index/supportDetail', compact('info','page'));
        }

        if(!empty($page)){
            $rs_data = $this->getData( [['id'=>$page['id']]] );
        }

        return $this->fetch('default/index/support', compact('rs_data','page'));
    }

    public function news($detail = 0)
    {


//        $page = $terms_model->field(['id','label'])->where($where)->limit(1)->find();


        $terms_model= db("nav");
        $where[] = ['parentid', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['id', '=', 72];
        $page = $terms_model->field(['id','label'])->where($where)->limit(1)->find();


        if($detail != 0){
            $info = $this->getPostInfo($detail);
            return $this->fetch('default/index/newsDetail', compact('info','page'));
        }
        $terms_model= db("posts");
        $rs_data = $terms_model->where(['post_type'=>1])->where(['post_status'=>1])->order('post_date desc')->select();



        /*SELECT
DATE_FORMAT( detect_time, "%Y-%m-%d" ) AS time,
COUNT(id) AS total
FROM detect_video_task_result_real
GROUP BY DATE_FORMAT( detect_time, "%Y-%m-%d" )*/


        return $this->fetch('default/index/news', compact('rs_data', 'page'));
    }

    public function message()
    {
        $terms_model= db("nav");
        $where[] = ['parentid', '=', 0];
        $where[] = ['status', '=', 0];
        $where[] = ['id', '=', 71];
        $page = $terms_model->field(['id','label'])->where($where)->limit(1)->find();
        return $this->fetch('default/index/message',compact('page'));
    }

    function getPostInfo($id){
        return db("posts")->where(['id' => $id])->find();
    }

    function getPage(){
        $url = \request()->url();
        $url = substr($url, 0, -5);
        $terms_model= db("nav");
        $where[] = ['parentid', '=', 0];
        $where[] = ['status', '=', 1];
        $where[] = ['href', '=', $url];
        $terms = $terms_model->field(['id','label'])->where($where)->limit(1)->find();
        if(!empty($terms)){
            return $terms;
        }
        return 0;
    }

    function getNavLeft($page_id){
        $terms_model= db("nav");
        $where[] = ['parentid', '=', $page_id];
        $where[] = ['status', '=', 1];
        $terms = $terms_model->where($where)->select();
        return $terms;
    }

    private function getData($nav_left)
    {
        $model= db("posts");
        $ids = [];
        foreach($nav_left as $v){
            array_push($ids, $v['id']);
        }
        $lists = $model->where(['menu_id' => $ids])->select();
        foreach($lists as &$v1){
            if($v1['smeta'] != ''){
                $smeta = json_decode($v1['smeta']);
                $v1['smeta'] = $smeta->thumb;
            }
        }
        return $lists;
    }
}
