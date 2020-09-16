<?php
namespace app\index\controller;

use app\index\controller\Base;
use think\facade\Config;

class Index extends Base
{
    public function index()
    {
        $data = $this->getChamberInfo();
        //滚动新闻:取商会动态修改时间前3条
        $scrollPosts = sp_sql_posts('cid:26;field:object_id,term_id,post_title;limit:0,3;order:post_modified DESC');

//        $this->assign("data", $data);
//        $this->assign("scrollPosts", $scrollPosts);
//        $this->display(":index");

        return $this->fetch('default/index/index', compact('data','scrollPosts'));
    }

    // 搜索
    public function search()
    {
        $keyword = I('request.keyword/s','');

        if (empty($keyword)) {
            $this -> error("关键词不能为空！请重新输入！");
        }

        $this->assign("keyword", $keyword);
        $this->display(":search");
    }

    // 列表
    public function newList()
    {
        $termId = I('get.id', 0, 'intval');
        $tag = 'field:object_id,term_id,post_modified,post_title,post_content;order:post_date desc,post_modified DESC;';
        $pageSet = 20;
        $list = sp_sql_posts_paged_bycatid($termId, $tag, $pageSet);

        $category = $this->getCategoryInfo($termId);

        $this->assign("list", $list);
        $this->assign("category", $category);
        $this->display(":list");
    }

    // 新闻详情
    public function details()
    {
        //新闻id
        $articleId = I('get.id', 0, 'intval');

        //分类id
        $termId = I('get.cid', 0, 'intval');

        //新闻详情
        $news = sp_sql_post($articleId);

        if (empty($news)) {
            header('HTTP/1.1 404 Not Found');
            header('Status:404 Not Found');
            if(sp_template_file_exists(MODULE_NAME."/404")){
                $this->display(":404");
            }
            return;
        }

        $tag = 'cid:'. $termId . 'field:object_id,term_id,post_title;limit:0,5;order:post_modified DESC;where:object_id != '.$articleId;

        //相关文章
        $relatedPosts = sp_sql_posts($tag);

        //上一篇
        $lastPosts = $this->getPosts($articleId, $termId, 'desc');

        //下一篇
        $nextPosts = $this->getPosts($articleId, $termId, 'asc');

        $category = $this->getCategoryInfo($termId);

        $details  = [
            'title' => $news['post_title'],
            'content' => $news['post_content'],
            'thumb' => empty(json_decode($news['smeta'], true)['thumb']) ?  "" : sp_get_asset_upload_path(json_decode($news['smeta'], true)['thumb']),
            'date' => $news['post_modified'],
            'category' => $category,
            'related_posts' => $relatedPosts,
            'last_posts' => $lastPosts,
            'next_posts' => $nextPosts,
            'cid' => $termId,
        ];
        $this->assign("details", $details);
        $this->display(":details");
    }

    //获取对应分类信息
    public function getCategoryInfo($termId)
    {
        $term = sp_get_term($termId);

        //一级分类
        $data['one_category'] = $term['name'];
        if ($term['parent'] != 0) {
            //二级分类
            $data['one_category'] = sp_get_term($term['parent'])['name'];
            $data['two_category'] = $term['name'];
        }

        return $data;
    }

    //获取上一篇/下一篇文章
    public function getPosts($articleId, $termId, $order)
    {
        $map['term_relationships.term_id'] = array('in', array($termId));
        $map['term_relationships.status'] = array('eq', 1);
        $map['posts.post_status'] = array('eq', 1);

        if ($order == 'desc') {
            $map['id'] = array('lt', $articleId);
        } else {
            $map['id'] = array('gt', $articleId);
        }

        $posts = db("TermRelationships")
            ->alias("term_relationships")
            ->join('__POSTS__ as posts on term_relationships.object_id = posts.id')
            ->field('posts.id,posts.post_title')
            ->where($map)
            ->order(array('posts.id' => $order))
            ->limit(1)
            ->find();

        return $posts;
    }

    //获取商会介绍文章列表
    public function chamberList()
    {
        $list = $this->getChamberInfo();
        $category = $this->getCategoryInfo(1);
        $this->assign("list", $list);
        $this->assign("category", $category);
        $this->display(":chamberlist");
    }

    //获取商会介绍对应文章
    public function getChamberInfo()
    {

        //商会介绍
        $terms = db("Terms")->where("status=1 and parent=1")->order("listorder asc")->select();

        $data = [];
        foreach ($terms as $key => $value) {
            $data[$key]['name'] = $value['name'];
            $data[$key]['id'] = $this->getNews($value['term_id'])['id'];
            $data[$key]['term_id'] = $value['term_id'];
            $data[$key]['post_modified'] = $this->getNews($value['term_id'])['post_modified'];
        }
        return $data;
    }

    public function getNews($termId)
    {

        $map['term_relationships.term_id'] = array($termId);
        $map['term_relationships.status'] = array('eq', 1);
        $map['posts.post_status'] = array('eq', 1);

        $posts = db("TermRelationships")
            ->alias("term_relationships")
            ->join('__POSTS__ posts', 'term_relationships.object_id = posts.id')
//            ->join('__POSTS__ as posts on term_relationships.object_id = posts.id')
            ->field('posts.id,posts.post_title,posts.post_content,posts.post_modified')
            ->where($map)
            ->find();

        return $posts;
    }
}
