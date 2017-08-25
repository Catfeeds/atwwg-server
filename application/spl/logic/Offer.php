<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */

namespace app\spl\logic;

use app\common\model\Io as IoModel;

class Offer extends BaseLogic{

    protected $table = 'atw_io';
    protected $STATUS_ARR = [
        'init' => '未报价',
        'quoted' => '已报价',
        'winbid' => '中标',
        'losebid' => '未中标',
        'winbid_uncheck' => '待审核',
        'wait' => '待下单',
        'un_tender' => '未投标',
        'close' => '关闭', //废弃
    ];

    //获得报价中心列表
    function getOfferInfo($sup_code, $where = '',$orderby=''){
        if(!empty($where)){
            $list = IoModel::where('sup_code', $sup_code)
                ->where($where)
                ->field("*, '' AS pro_no ,IF(`status`='init','1','0') AS sort ")
                ->order($orderby)
                ->order('sort DESC, update_at DESC')
                ->select();
        }else{
            $list = IoModel::where('sup_code', $sup_code)
                ->field("*, '' AS pro_no ,IF(`status`='init','1','0') AS sort ")
                ->order($orderby)
                ->order('sort DESC, update_at DESC')
                ->select();
        }
        foreach($list as &$pi){
            $pi['pro_no'] = model('PR', 'logic')->where('id', $pi->pr_id)->column('pro_no');
        }
        return $list;
    }

    //更改交期
    function updateData($key, $dataArr){
        $result = model('Io')->where('id', $key)->update($dataArr);
        //echo $this->getLastSql();die;
        return $result;
    }

    //获取报价单条信息
    function getOneById($Id){
        $result = IoModel::where('id', $Id)->find($Id);
        return $result;
    }
    //获取报价单条信息
    function getOneItem($where=[]){
        $result = IoModel::where($where)->find();
        return $result;
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Describe: 如果请购单的 供应商已经全部报完价了，则该状态为 已报价
     * @param $id
     */
    public function updatePrStatusById($id){
        $dbRet = $this->field('pr_id')->where('id', $id)->group('pr_id')->find();
        $count = $this->where('pr_id', $dbRet['pr_id'])->where('status', 'init')->count();
        if($count == 0){
            model('PR', 'logic')->updateStatus(['id' => $dbRet['pr_id']], 'quoted');
        }
        return $count;
    }

    /*
     * 得到待报价的数量
     */
    public function getWaitQuoteNum($where){
        return $this->where($where)->count();
    }
}