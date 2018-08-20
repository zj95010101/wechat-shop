<?php

namespace app\api\model;

use think\Model;

class Product extends BaseModel
{
    protected $autoWriteTimestamp = 'datetime';
    protected $hidden = [
        'delete_time', 'main_img_id', 'pivot', 'from', 'category_id',
        'create_time', 'update_time'];

//     * 关联商品图片模型
    public function imgs()
    {
        return $this->hasMany('ProductImage', 'product_id', 'id');
    }

    //    * 关联商品属性模型
    public function properties()
    {
        return $this->hasMany('ProductProperty', 'product_id', 'id');
    }
    //关联分类模型
    public function category(){
        return $this->belongsTo('Category','category_id','id');
    }

    //获取器，给url加域名
    public function getMainImgUrlAttr($value, $data)
    {
        return $this->prefixImgUrl($value, $data);
    }

//     * 获取商品详情
    public static function getProductDetail($id)
    {
        $product=self::with(['imgs'=>function($query){
            $query->with(['imgUrl'])->order('order','asc');
        }])->with(['properties'])->find($id);
        return $product;
    }
//     * 获取某分类下商品
    public static function getProductsByCategoryID($categoryID, $paginate, $size, $page)
    {
        $query = self::
        where('category_id', '=', $categoryID);
        if (!$paginate) {
            return $query->select();
        } else {
            return $query->paginate(
                $size, true, ['page' => $page]);
        }
    }


// 获取指定数量的最近商品
    public static function getMostRecent($count)
    {
        $products = self::limit($count)
            ->order('create_time desc')
            ->select();
        return $products;
    }

}
