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
     function getPrList($start,$length){
        $list = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where('a.status','<>','close')->limit("$start,$length")->select();
//        echo $this->getLastSql();
        if($list){
            $list = collection($list)->toArray();
        }
        //dump($list);
        return $list;
     }

    /*
     * 得到列表数量
     */
     function getListNum(){
         $count = prModel::alias('a')->field('a.*,b.desc,b.pur_attr')->join('item b','a.item_code=b.code','LEFT')->where('a.status','<>','close')->count('pr_code');
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
}