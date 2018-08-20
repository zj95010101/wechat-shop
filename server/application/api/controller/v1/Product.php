<?php
/**
 * Created by 朱江.
 * User: 朱江
 * Date: 2017/2/15
 * Time: 1:00
 */

namespace app\api\controller\v1;

use app\api\model\Product as ProductModel;
use app\api\model\Theme;
use app\api\validate\Count;
use app\api\validate\Id;
use app\api\validate\PagingParameter;
use app\lib\exception\ParameterException;
use app\lib\exception\ProductException;
use app\lib\exception\ThemeException;
use think\Controller;
use think\Exception;

class Product extends Controller
{
    protected $beforeActionList = [
        'checkSuperScope' => ['only' => 'createOne,deleteOne']
    ];

    /**
     * 获取某分类下商品信息(分页）
     * @url /product?id=:category_id&page=:page&size=:page_size
     * @param int $id 分类id
     * @param int $page 分页页数（可选)
     * @param int $size 每页数目(可选)
     * @return array of Product
     * @throws ParameterException
     */
    public function getByCategory($id = -1, $page = 1, $size = 30)
    {
        (new Id())->goCheck();
        (new PagingParameter())->goCheck();
        //true标识需要分页
        $pagingProducts = ProductModel::getProductsByCategoryID($id, true, $page, $size);
        if ($pagingProducts->isEmpty()) {
            return [
                'current_page' => $pagingProducts->currentPage(),
                'data' => []
            ];
        }
        $data = $pagingProducts
            ->hidden(['summary'])
            ->toArray();
        // 如果是简洁分页模式，直接序列化$pagingProducts这个Paginator对象会报错
        return [
            'current_page' => $pagingProducts->currentPage(),
            'data' => $data
        ];
    }

    /**
     * 获取某分类下全部商品(不分页）
     * @url /product/all?id=:category_id&paginate=...&size=...   get
     */
    public function getAllInCategory($id,$paginate=false,$size=6,$page=1)
    {
        (new Id())->goCheck();
        $products = ProductModel::getProductsByCategoryID($id, $paginate,$size,$page);
        if ($products->isEmpty()) {
            throw new ProductException();
        }
        $data = $products->hidden(['summary']);
        return $data;
    }

    /**
     * 获取指定数量的最近商品
     */
    public function getRecent($count = 1)
    {
        (new Count())->goCheck();
        $products = ProductModel::getMostRecent($count);
        if ($products->isEmpty()) {
            throw new ProductException();
        }
        $products = $products->hidden(['summary'])->toArray();
        return $products;
    }

//     * 获取商品详情
//     * 如果商品详情信息很多，需要考虑分多个接口分布加载
    public function getOne($id)
    {
        (new Id())->goCheck();
        $product = ProductModel::getProductDetail($id);
        if (!$product) {
            throw new ProductException();
        }
        return $product;
    }
    //添加商品，未完善
    public function create($id)
    {
        (new ProductModel)->save($id);
    }
     //删除商品，未完善
    public function deleteOne($id)
    {
        ProductModel::destroy($id);
    }
    //展示所有商品,并判断有无关联传来的theme
    public function getAll($theme_id=''){
        //查询所有商品
        $productArr=ProductModel::with('category')->select()->toArray();
        if(empty($theme_id)){
            return api(0,'success',$productArr);
        }
        //查询主题关联的所有商品
        $productByTheme=model('Theme')->find($theme_id)->products->toArray();
        $productIdArr=[];
        foreach($productByTheme as $j=>$i){
            $productIdArr[]=$i['id'];
        }
        foreach($productArr as $k=>$v){
            if(in_array($v['id'],$productIdArr)){
                $productArr[$k]['isSelect']=true;
            }else{
                $productArr[$k]['isSelect']=false;
            }
        }
        return api(0,'success',$productArr);
    }
    //修改商品状态
    public function upStatus($id,$status){
        $product=ProductModel::find($id);
        $product->status=$status;
        $product->save();
        return api(0,'success',$product->status,201);
    }
}