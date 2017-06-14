<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/24
 * Time: 9:57
 */
namespace app\admin\logic;

use app\common\model\U9Pr as prModel;

class RequireOrder extends BaseLogic{

    /*
     * 得到请购单信息
     */
     function getPrList($start,$length,$where){
         if(!empty($where)){
             if(key_exists('status',$where)){
                 $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where($where)->limit("$start,$length")->order('update_at desc')->select();
             }else{
                 //->where('a.status','<>','close')
                 $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where($where)->limit("$start,$length")->order('update_at desc')->select();
             }

         }else{
             //->where('a.status','<>','close')
             $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->limit("$start,$length")->order('update_at desc')->select();
         }

//        echo $this->getLastSql();
        if($list){
            $list = collection($list)->toArray();
        }
        //dump($list);
        return $list;
     }

     /*
      *无分页得到所有信息
      */
     function getAllListInfo($where){
         if(!empty($where)){
             if(key_exists('status',$where)){
                 $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where($where)->select();
             }else{
                 //->where('a.status','<>','close')
                 $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where($where)->select();
             }

         }else{
             //->where('a.status','<>','close')
             $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->select();
         }

         if($list){
             $list = collection($list)->toArray();
         }
         return $list;
     }

    /*
     * 得到列表数量
     */
     function getListNum($where){
         if(!empty($where)){
             if(key_exists('status',$where)){
                 $count = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where($where)->count();
             }else{
                 //->where('a.status','<>','close')
                 $count = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where($where)->count();
             }

         }else{
             //->where('a.status','<>','close')
             $count = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->count();
         }
         return $count;
     }

     /*
      *根据itemcode得到供应商物料交叉表信息
      */
     function getSupList($item_code){
        $list = model('U9SupItem')->alias('a')->field('a.*,b.status')->join('supplier_info b','a.sup_code=b.code','LEFT')->where('item_code',$item_code)->select();
         if($list){
             $list = collection($list)->toArray();
         }
         //dump($list);
         return $list;
     }

     /*
      * 保存唯一指定供应商到表
      */
     function updateByPrCode($where,$data){
         return model('U9Pr')->where($where)->update($data);
     }
     /*
      * 获得pro_no项目号
      */
     function getProNo($where){
         return model('U9Pr')->where($where)->value('pro_no');
     }
     /*
      * 得到请购日期
      */
     function getPrDate($where){
         return model('U9Pr')->where($where)->value('pr_date');
     }

    /*
    * 得到请购日期
    */
    function getPrStatus($where){
        return model('U9Pr')->where($where)->value('status');
    }

    /*
     * 通过条件得到请购单数量
     */
    function getNumByWhere($where){
        return prModel::where($where)->count();
    }
}