<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/20
 * Time: 1:34
 */

namespace app\api\model;


use think\Model;

class ProductImage extends BaseModel
{
    protected $hidden = ['img_id', 'delete_time', 'product_id'];
    //关联图片总表(虽然是一对多，单product_id与img_id存在了ProductImage表里)
    public function imgUrl()
    {
        return $this->belongsTo('Image', 'img_id', 'id');
    }
}