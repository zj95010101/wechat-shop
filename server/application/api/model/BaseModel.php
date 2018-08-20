<?php
/**
 * Created by 朱江.
 * Author: 朱江
 * 微信公号：小楼昨夜又秋风
 * 知乎ID: 朱江在夏天
 * Date: 2017/2/19
 * Time: 2:42
 */

namespace app\api\model;


use think\Model;
use traits\model\SoftDelete;

class BaseModel extends Model
{
    // 软删除，设置后在查询时要特别注意whereOr
    // 使用whereOr会将设置了软删除的记录也查询出来
    // 可以对比下SQL语句，看看whereOr的SQL
    use SoftDelete;
    protected $autoWriteTimestamp=true;
    protected $createTime=false;
    protected $hidden = ['delete_time','update_time'];

    //如果是外部链接+上域名
    protected function  prefixImgUrl($value, $data){
        $finalUrl = $value;
        if($data['from'] == 1){
            $finalUrl = config('wx.img_prefix').$value;
        }
        return $finalUrl;
    }
}