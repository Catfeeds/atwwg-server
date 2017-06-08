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
use PHPExcel_IOFactory;
use PHPExcel;

class Requireorder extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '请购单管理';

    public function index(){
        //echo '111111111';die;
        $this->assign('title',$this->title);
        return view();
    }

    public function getPrList(){
        $start = input('start') == '' ? 0 : input('start');
        $length = input('length') == '' ? 10 : input('length');
        $whereInfo = ['pr_code','pr_date','item_code','item_name','pro_no','pur_attr','status','is_appoint_sup','check_status'];//继续筛选操作
        $get = input('param.');
        if(isset($get['pr_date']) && $get['pr_date'] !== ''){
            $get['pr_date'] = strtotime($get['pr_date']);
        }
        $where = [];
        // 应用搜索条件
        foreach ($whereInfo as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $where[$key] = ['like',"%{$get[$key]}%"];
            }
        }
        $logicPrInfo = Model('RequireOrder','logic');
        $list = $logicPrInfo->getPrList($start,$length,$where);
        $returnArr = [];
        //dump($list);die;
        //init=初始,hang=挂起,inquiry=询价中,quoted = 供应商全部报价完毕,flow = 流标,winbid=中标,order=已下单,close = 关闭
        $status = [
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'winbid' => '中标',
            'order' => '已下单',
            'close' => '关闭',
        ];

        $checkStatus = [
            '' => '未审批',
            'agree' => '已同意',
            'refuse' => '已拒绝',
        ];

        $inquiry_way = [
            '' => '自动询价',
            'assign' => '指定供应商',
            'exclusive' => '独家采购',
            'compete' => '充分竞争'
        ];
        //dump($list);die;
        foreach($list as $k => $v){
            //status为init状态可以指定
            $is_appoint_sup = $v['is_appoint_sup'];//先获取是否指定
            if($v['status'] == 'init' || $v['status'] == 'hang'){//订单挂起状态 且订单为初始状态
                if($v['status'] == 'init'){

                    $v['check_status'] = '';
                    if($v['appoint_sup_code'] == 1){
                        //选择供应商
                        $inquiry = '<a class="select_sell" href="javascript:void(0);" onclick="bomb_box(event,\''.$v['pr_code'].'\',\''.$v['item_code'].'\',\''.$v['id'].'\');" data-url="'
                            .url('requireorder/selectSup',array('pr_code'=>$v['pr_code'],'item_code'=>$v['item_code'])).'">选择供应商</a>';
                    }else{
                        $inquiry = $inquiry_way[$v['inquiry_way']];
                    }
                }else{
                    if($is_appoint_sup == 1){//为指定状态 --->挂起状态下的指定
                        if(!empty($v['appoint_sup_code'])){//指定状态下有供应商的名称
                            $inquiry = $v['appoint_sup_name'];
                            if($v['check_status'] == ''){//判断主管审核
                                //auth();
                                $v['check_status'] =  '<a href="javascript:;" onclick="checkStatus(\'agree\','.$v['id'].');">通过</a>
                                &nbsp;&nbsp;<a href="javascript:;" onclick="checkStatus(\'refuse\','.$v['id'].');">拒绝</a>';
                            }else{
                                if(key_exists($v['check_status'],$checkStatus)){
                                    $v['check_status'] = $checkStatus[$v['check_status']];
                                }else{
                                    $v['check_status'] = $v['check_status'];
                                }
                            }
                        }else{//非指定状态下无供应商名称---->选择供应商 && 无审核状态
                            $v['check_status'] = '';
                            $inquiry = '<a class="select_sell" href="javascript:void(0);" 
                            onclick="bomb_box(event,\''.$v['pr_code'].'\',\''.$v['item_code'].'\',\''.$v['id'].'\');" data-url="'
                                .url('requireorder/selectSup',array('pr_code'=>$v['pr_code'],'item_code'=>$v['item_code'])).'">选择供应商</a>';
                        }

                    }else{//挂起 &&  非指定状态
                        $inquiry = $inquiry_way[$v['inquiry_way']];
                        $v['check_status'] = '';
                    }
                }
                //init 和 hang 只要 appoint_sup_code 为可以选择供应商都可以指定
                if($is_appoint_sup == 1){
                    $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_id="'.$v['id'].'" data-pr_code="'.
                    $v['pr_code'].'" data-item_code="'.$v['item_code'].'" checked class="ver_top" checked value="1">指定';//有指定的时候
                }else{
                    $v['is_appoint_sup'] = '<input style="margin-right: 15px;" type="checkbox" data-pr_id="'.$v['id'].'" data-pr_code="'.
                        $v['pr_code'].'" data-item_code="'.$v['item_code'].'" class="ver_top" value="0">指定';//没有指定的时候
                }
            }else{//订单非挂起 和  非指定
                $v['is_appoint_sup'] = '';
                $v['check_status'] = '';
                if(key_exists($v['inquiry_way'],$inquiry_way)){
                    $inquiry = $inquiry_way[$v['inquiry_way']];
                }else{
                    //$v['inquiry_way'] = $inquiry_way[$v['inquiry_way']];
                    //$v['inquiry_way'] = '其他';
                    $inquiry = $v['inquiry_way'];
                }
            }

            $returnArr[] = [
                'pr_code' => $v['pr_code'],//请购单号
                'pr_date' => date('Y-m-d',$v['pr_date']),//请购日期
                'item_code' => $v['item_code'],//料号
                'desc' => $v['item_name'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => date('Y-m-d',$v['req_date']),//交期
                'status' => key_exists($v['status'],$status) ? $status[$v['status']] : $v['status'],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => $v['pur_attr'],//物料采购属性
                'is_appoint_sup' => $v['is_appoint_sup'],//是否指定供应商
                'inquiry_way' => $inquiry,//询价方式
                'check_status' => $v['check_status'],//主管审批
            ];

        }

        $info = ['draw'=>time(),'recordsTotal'=>$logicPrInfo->getListNum($where),'recordsFiltered'=>$logicPrInfo->getListNum($where),'data'=>$returnArr];

        return json($info);
    }

    /*
     * 选择物料所对应的多个供应商
     */
    public function showSelectSup(){
        $data=input('param.');
        $result = $this->validate($data,'Enquiry');
        if($result !== true){
            return json(['code'=>4000,'msg'=>$result,'data'=>[]]);
        }
        $logicPrInfo = Model('RequireOrder','logic');
        $list = $logicPrInfo->getSupList($data['item_code']);
        foreach($list as $k=>$v){
            $list[$k]['pr_code'] = $data['pr_code'];
        }
        return json($list);
    }

    /*
     * 更改询价状态
     */
    public function changeInquiType(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');

        if($data['is_appoint_sup'] == 0){
            $dataArr = [
                'status' => 'init',
                'inquiry_way' => '',
                'is_appoint_sup' => 0,
                'appoint_sup_code' => '',
                'appoint_sup_name' => '',
                'check_status' => '',
            ];

        }else if($data['is_appoint_sup'] == 1){
            $dataArr = [
                'status' => 'hang',
                'inquiry_way' => 'assign',
                'is_appoint_sup' => 1,
            ];
        }else{
            return json(['code'=>4000,'msg'=>'请传入合法的is_appoint_sup参数值','data'=>[]]);
        }
        $where = [
            'pr_code' => $data['pr_code'],
            'item_code' => $data['item_code'],
        ];
        $result = $logicPrInfo->updateByPrCode($where,$dataArr);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'更改指定供应商状态失败','data'=>[]]);
        }
        return json(['code'=>2000,'msg'=>'成功','data'=>[]]);
    }

    /*
     * 保存到pr表
     */
    public function savePr(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');
        $dataArr = [
            'is_appoint_sup' => $data['is_appoint_sup'],
            'appoint_sup_code' => $data['appoint_sup_code'],
            'appoint_sup_name' => $data['appoint_sup_name'],
        ];
        $where = [
            'pr_code' => $data['pr_code'],
            'item_code' => $data['item_code'],
        ];
        $result = $logicPrInfo->updateByPrCode($where,$dataArr);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'指定供应商状态失败','data'=>[]]);
        }
        //重新计算价格

        return json(['code'=>2000,'msg'=>'成功','data'=>['sup_name' => $data['appoint_sup_name']]]);
        //is_appoint_sup 1 //appoint_sup_code //appoint_sup_name
    }

    /*
     * 审批指定供应商状态
     */
    public function checkStatus(){
        $data=input('param.');
        $logicPrInfo = Model('RequireOrder','logic');
        $dataArr = [
            'check_status' => $data['check_status'],
        ];
        $where = [
            'id' => $data['id'],
        ];
        $checkStatus = [
            '' => '未审批',
            'agree' => '已同意',
            'refuse' => '已拒绝',
        ];
        $result = $logicPrInfo->updateByPrCode($where,$dataArr);
        if (false === $result) {
            return json(['code'=>4000,'msg'=>'失败','data'=>[]]);
        }else{
            $status = $checkStatus[$data['check_status']];
            return json(['code'=>2000,'msg'=>'成功','data'=>['check_status'=>$status]]);
        }
    }

    /*
     * 导出excel
     */
    function exportExcel(){
        $whereInfo = ['pr_code','pr_date','item_code','item_name','pro_no','pur_attr','status','is_appoint_sup','check_status'];//继续筛选操作
        $get = input('param.');
        if(isset($get['pr_date']) && $get['pr_date'] !== ''){
            $get['pr_date'] = strtotime($get['pr_date']);
        }
        $where = [];
        // 应用搜索条件
        foreach ($whereInfo as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $where[$key] = ['like',"%{$get[$key]}%"];
            }
        }
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        $prLogic = Model('RequireOrder','logic');
        $list = $prLogic->getAllListInfo($where);
        $status = [
            'init' => '待询价',
            'hang' => '挂起',
            'inquiry' => '询价中',
            'quoted' => '待评标',
            'flow' => '流标',
            'winbid' => '中标',
            'order' => '已下单',
            'close' => '关闭',
        ];

        $checkStatus = [
            '' => '未审批',
            'agree' => '已同意',
            'refuse' => '已拒绝',
        ];

        $inquiry_way = [
            '' => '自动询价',
            'assign' => '指定供应商',
            'exclusive' => '独家采购',
            'compete' => '充分竞争'
        ];
        foreach($list as $k => $v){
            $returnArr[] = [
                'pr_code' => $v['pr_code'],//请购单号
                'pr_date' => date('Y-m-d',$v['pr_date']),//请购日期
                'item_code' => $v['item_code'],//料号
                'desc' => $v['item_name'],//物料描述
                'pro_no' => $v['pro_no'],//项目号
                'tc_uom' => $v['tc_uom'],//交易单位
                'tc_num' => $v['tc_num'],//交易数量
                'price_uom' => $v['price_uom'],//计价单位
                'price_num' => $v['price_num'],//计价数量
                'req_date' => date('Y-m-d',$v['req_date']),//交期
                'status' => key_exists($v['status'],$status) ? $status[$v['status']] : $v['status'],//状态 init=初始 hang=挂起 inquiry=询价中 close = 关闭
                'pur_attr' => $v['pur_attr'],//物料采购属性
                'is_appoint_sup' => $v['is_appoint_sup'] == 1 ? '是' : '否',//是否指定供应商   1是 0否
                'inquiry_way' => key_exists($v['inquiry_way'],$inquiry_way) ? $inquiry_way[$v['inquiry_way']] : $v['inquiry_way'],//询价方式
                'check_status' => key_exists($v['check_status'],$checkStatus) ? $checkStatus[$v['check_status']] : $v['check_status'],//主管审批
            ];

        }
        $list = $returnArr;
        //dump($list);die;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('请购单列表'); //给当前活动sheet设置名称
        $PHPSheet->setCellValue('A1','请购单号');
        $PHPSheet->setCellValue('B1','请购日期');
        $PHPSheet->setCellValue('C1','料号');
        $PHPSheet->setCellValue('D1','物料描述');
        $PHPSheet->setCellValue('E1','项目号');
        $PHPSheet->setCellValue('F1','交易单位');
        $PHPSheet->setCellValue('G1','交易单位数量');
        $PHPSheet->setCellValue('H1','计价单位');
        $PHPSheet->setCellValue('I1','计价单位数量');
        $PHPSheet->setCellValue('J1','交期');
        $PHPSheet->setCellValue('K1','状态');
        $PHPSheet->setCellValue('L1','物料采购属性');
        $PHPSheet->setCellValue('M1','是否指定供应商');
        $PHPSheet->setCellValue('N1','询价方式');
        $PHPSheet->setCellValue('O1','主管审批');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num+1;
            $PHPSheet->setCellValue('A'.$num,$v['pr_code'])->setCellValue('B'.$num,$v['pr_date'])
                ->setCellValue('C'.$num,$v['item_code'])->setCellValue('D'.$num,$v['desc'])
                ->setCellValue('E'.$num,$v['pro_no'])->setCellValue('F'.$num,$v['tc_uom'])
                ->setCellValue('G'.$num,$v['tc_num'])
                ->setCellValue('H'.$num,$v['price_uom'])
                ->setCellValue('I'.$num,$v['price_num'])
                ->setCellValue('J'.$num,$v['req_date'])
                ->setCellValue('K'.$num,$v['status'])
                ->setCellValue('L'.$num,$v['pur_attr'])
                ->setCellValue('M'.$num,$v['is_appoint_sup'])
                ->setCellValue('N'.$num,$v['inquiry_way'])
                ->setCellValue('O'.$num,$v['check_status']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/prList.xlsx'); //表示在$path路径下面生成prList.xlsx文件
        $file_name = "prList.xlsx";
        $contents = file_get_contents($path.'/prList.xlsx');
        $file_size = filesize($path.'/prList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

}