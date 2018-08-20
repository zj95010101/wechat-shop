<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/19
 * Time: 11:28
 */

namespace app\api\controller\v1;


use app\api\controller\v1\Base;
use app\api\model\Category as CategoryModel;
use app\api\validate\Id;
use app\lib\exception\CategoryException;
use app\lib\exception\MissException;
use think\Controller;

class Category extends Base
{
    /**
     * 获取全部类目列表，但不包含类目下的商品
     * @url /category/all
     * @return array of Categories
     * @throws MissException
     */
    public function getAllCategories()
    {
        $categories = CategoryModel::with(['img'])->select();
        if(empty($categories)){
           throw new CategoryException([
               'msg' => '还没有任何类目',
               'errorCode' => 50000
           ]);
        }
        return $categories;
    }


    /**
     * 此接口主要是为了返回分类下面的products，需要做关联
     * 相对直接查products，这是一种不好的接口设计
     */
    public function getCategory($id)
    {
    }
}