<?php

namespace app\api\model;

use think\Model;

class Banner extends BaseModel
{
//    protected $hidden=['delete_time'];
    public function items()
    {
        return $this->hasMany('BannerItem','banner_id','id');
    }
    //

    /**
     * @param $id int banner所在位置
     * @return Banner
     */
    public static function getBannerById($id)
    {

//        $banner = self::with(['items','items.img'])
//            ->find($id);
         $banner = self::relation('items,items.img')
             ->find($id);
        return $banner;
    }
}
