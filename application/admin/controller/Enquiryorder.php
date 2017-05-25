<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/9
 * Time: 9:33
 */
namespace app\admin\controller;

use controller\BasicAdmin;
use service\LogService;
use service\DataService;

use think\Db;

class Enquiryorder extends BaseController{
    protected $table = 'Io';
    protected $title = '询价单管理';

    public function index(){
        $statusArr=[

        ];
        $this->assign('title',$this->title);
        $this->assign('statusArr',$statusArr);
        return view();
    }

    public function getInquiryList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $logicIoInfo = Model('Io','logic');
        $list = $logicIoInfo->getIoList($start,$length);

        $returnArr = [];
        //状态init=未报价  quoted=已报价  winbid=中标 giveupbid=弃标  close=已关闭
        $status = [
            'init' => '未报价',
            'quoted' => '已报价',
            'winbid' => '中标',
            'giveupbid' => '弃标',
            'close' => '已关闭',
        ];

        foreach($list as $k => $v){
            //得到全部的询价单 by pr_code item_code
            $where = [
                'pr_code' => $v['pr_code'],
                'item_code' => $v['item_code'],
            ];
            $allIo = $logicIoInfo->getIoCountByWhere($where);
            //得到已报价的询价单by pr_code item_code status
            $where = [
                'pr_code' => $v['pr_code'],
                'item_code' => $v['item_code'],
                'status' => 'quoted',//已报价
            ];
            $quotedIo = $logicIoInfo->getIoCountByWhere($where);
            $returnArr[] = [
                'io_code' => $v['io_code'],//询价单号
                'pr_code' => $v['pr_code'],//请购单号
                'item_code' => $v['item_code'],//料号
                'desc' => $v['desc'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => date('Y-m-d',$v['req_date']),//交期
                'quote_date' =>  date('Y-m-d',$v['create_at']),//询价日期
                'quote_endtime' =>  date('Y-m-d',$v['quote_endtime']),//报价截止日期
                'price_status' => $quotedIo.'/'.$allIo,//报价状态
                'status' => $status[$v['status']],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => '<a class="" href="javascript:void(0);" data-open="/enquiryorder/particulars/io_code/'.$v['io_code'].'">详情</a>',//详情
            ];

        }

        $info = ['draw'=>time(),'recordsTotal'=>$logicIoInfo->getListNum(),'recordsFiltered'=>$logicIoInfo->getListNum(),'data'=>$returnArr];

        return json($info);
    }


    public function del(){

    }

    public function add(){

    }

    public function particulars(){
        return view();
    }

}