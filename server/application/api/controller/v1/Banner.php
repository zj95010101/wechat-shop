<?php
/**
 * Created by 朱江
 * User: 朱江
 * Date: 2017/2/15
 * Time: 13:40
 */

namespace app\api\controller\v1;


use app\api\controller\v1\Base;
use app\api\model\Banner as BannerModel;
use app\api\validate\Id;
use app\lib\exception\MissException;
use think\Db;

/**
 * Banner资源
 */ 
class Banner extends Base
{
    /**
     * 获取Banner信息
     * @url     /banner/:id
     * @http    get
     * @param   int $id banner的id，id值得不同位置
     * @return  array of banner item , code 200
     * @throws  MissException
     */
    public function getBanner($id)
    {
        $validate = (new Id())->goCheck();
        $banner = BannerModel::getBannerById($id);
        if (!$banner ) {
            throw new MissException([
                'msg' => '请求banner不存在',
                'errorCode' => 40000
            ]);
        }
        return $banner;
    }
}