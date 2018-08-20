<?php

namespace app\api\controller\v1;

use app\api\model\Theme as ThemeModel;
use app\api\validate\Id;
use app\api\validate\Ids;
use app\api\validate\PagingParameter;
use app\api\validate\ThemeProduct;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ThemeException;
use think\Controller;
use app\api\model\ThemeProduct as modelThemeProduct;
use Think\Db;
use think\Exception;

/**
 * 主题推荐,主题指首页里多个聚合在一起的商品
 * 注意同专题区分
 * 常规的REST服务在创建成功后，需要在Response的
 * header里附加成功创建资源的URL，但这通常在内部开发中
 * 并不常用，所以本项目不采用这种方式
 */
class Theme extends Controller
{
    /**
     * @param   theme?ids=id1,id2,id3.
     *       对于传递多个数组的id可以选用post传递、
     *       多个id+分隔符或者将多个id序列化成json并在query中传递
     */
    //获取主题列表
    public function getSimpleList($ids = '')
    {
        $validate = new Ids();
        $validate->goCheck();
        $ids = explode(',', $ids);
        ThemeModel::with(['topicImg','headImg']);
        $result = ThemeModel::all($ids);
//        $result = ThemeModel::getThemeList($ids);
        if (empty($result)) {
            throw new ThemeException();
        }
        return $result;
    }

    /**
     * @url  api/:version/theme/:id   get
     * @return @返回主题详情页所需数据头图地址+主题下商品信息
     */
    public function getComplexOne($id)
    {
        (new Id())->goCheck();
        $theme = ThemeModel::getThemeWithProducts($id);
        if(!$theme){
            throw new ThemeException();
        }
        return $theme->hidden(['products.summary'])->toArray();
    }

    //向中间表添加数据
    public function addThemeProduct($t_id, $p_id)
    {
        (new ThemeProduct())->goCheck();
        ThemeModel::addThemeProduct($t_id, $p_id);
        return api(0,'success',[],201);
    }

    //将中间表数据删除
    public function deleteThemeProduct($t_id, $p_id)
    {
        (new ThemeProduct())->goCheck();
        $themeID = (int)$t_id;
        $productID = (int)$p_id;
        ThemeModel::deleteThemeProduct($themeID, $productID);
        return api(0,'success',[],204);
    }
    //主题分页查询(cms)
    public function getPage($page=1,$size=5){
        (new PagingParameter())->goCheck();
        $data=ThemeModel::with(['topicImg','headImg'])->paginate($size, false, ['page' => $page]);
        return api(0,'success',$data);
    }
    //主题添加
    public function addTheme($name='',$description=''){
        $arr['name']=$name;
        $arr['description']=$description;
        model('Theme')->save($arr);
        return api(0,'success');
    }
    //删除主题
    public function delTheme($id){
        ThemeModel::where(['id'=>['in',$id]])->delete($id);
        return api(0,'success');
    }
    //为主题关联商品
    public function relevance($themeId,$productIds){
        //将有关联的商品id连为数组
        if(strpos($productIds,',')){
            $productIds=explode(',',$productIds);
        }
        $theme=ThemeModel::find($themeId);;
        Db::startTrans();
        try{

            //删除原有关联
            modelThemeProduct::where(['theme_id'=>$themeId])->delete();
            //关联
            $theme->products()->saveAll($productIds);
            Db::commit();
        }catch (\Exception $e){
            Db::rollback();
            return $e->getMessage();
        }
        return api();
    }
}
