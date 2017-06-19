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
use service\HttpService;
use think\Db;
use PHPExcel_IOFactory;
use PHPExcel;
use think\File;
class Supporter extends BaseController{
    protected $table = 'SystemArea';
    protected $title = '供应商管理';

    public function index(){
        $this->assign('title',$this->title);
        //得到供应商分类
        $logicSupInfo = Model('Supporter','logic');
        $typeInfo = $logicSupInfo->getTypeInfo();
        //dump($typeInfo);
        $this->assign('typeInfo',$typeInfo);
        return view();
    }

    public function showSupporter(){
        $logic = Model('Supporter','logic');
        $list = $logic->getListInfo();
        return $list;
    }

    /**
     * 更新ERP供应商信息到数据库
     */
    public function updataU9Info(){
        $supInfo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/syncSupplier'));//同步供应商
        $prInfo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/syncPr'));//请购单pr
        $prToIo = json_decode(HttpService::curl(getenv('APP_API_HOME').'/u9api/prToInquiry'));//PR生成IO询价单
        return json([
            'supInfo' => $supInfo,
            'prInfo' => $prInfo,
            'prToIo' => $prToIo,
        ]);
    }

    /**
     * 得到供应商信息
     */

    public function getSupList(){
        $logicSupInfo = Model('Supporter','logic');
        $start = input('start');
        $length = input('length');
        $get = input('param.');
        //dump($requestInfo);die;
        $where = [];
        // 应用搜索条件
        foreach (['code', 'name', 'type_name', 'status', 'pay_way_status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $where[$key] = ['like',"%{$get[$key]}%"];
            }
        }
        $list = $logicSupInfo->getListInfo($start,$length,$where);//分页
        $returnArr = [];
        $status = [
            'normal' => '正常',
            'forbid' => '禁用',
            'uncheck' => '待审核',
        ];
        $pay_way_status = [
            '' => '待审核',
            'uncheck' => '待审核',
            'pass' => '不需要审核',
            'refuse' => '拒绝',
        ];
        foreach($list as $k => $v){
            $v['arv_rate'] = $v['arv_rate'] == '' ? '暂无数据' : initPerVal($v['arv_rate']);
            $v['pass_rate'] = $v['pass_rate'] == '' ? '暂无数据' : initPerVal($v['pass_rate']);
            $returnArr[] = [
                'code' => $v['code'],
                'name' => $v['name'],
                'type_name' => $v['type_name'],
                'tech_score' => getTechScore($v['code']),//技术分
                'arv_rate' => $v['arv_rate'],
                'pass_rate' => $v['pass_rate'],
                'quali_score' => getQualiScore($v['code']),//质量分
                'status' => '正常',// FIXME $status[$v['status']],
                'pay_type_status' => $pay_way_status[$v['pay_way_status']],
                'quali' => '<a class="edit" href="javascript:void(0);" data-open="'.url('Supporter/edit',['id'=>$v['id']]).'" >查看</a>',
                'action' => '<a class="edit" href="javascript:void(0);" data-open="'.url('Supporter/edit',['id'=>$v['id']]).'" >编辑</a>',
            ];

        }
        $info = ['draw'=>time(),'recordsTotal'=>$logicSupInfo->getListNum(),'recordsFiltered'=>$logicSupInfo->getListNum(),'data'=>$returnArr];

        return json($info);
    }
    public function del(){

    }

    public function add(){
        return view();
    }
    public function exportExcel(){
        //$path = config('upload_path'); //找到当前脚本所在路径
        //echo $path = dirname(__FILE__);
        //echo die;
        $where = [];
        $get = input('param.');
        // 应用搜索条件
        foreach (['code', 'name', 'type_name', 'status', 'pay_way_status'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $where[$key] = ['like',"%{$get[$key]}%"];
            }
        }
        $logicSupInfo = Model('Supporter','logic');
        $list = $logicSupInfo->getExcelFiledInfo($where);
        $returnArr = [];
        $status = [
            'normal' => '正常',
            'forbid' => '禁用',
            'uncheck' => '待审核',
        ];
        $pay_way_status = [
            '' => '待审核',
            'uncheck' => '待审核',
            'pass' => '不需要审核',
            'refuse' => '拒绝',
        ];
        foreach($list as $k => $v){
            $v['arv_rate'] = $v['arv_rate'] == '' ? '暂无数据' : initPerVal($v['arv_rate']);
            $v['pass_rate'] = $v['pass_rate'] == '' ? '暂无数据' : initPerVal($v['pass_rate']);
            $returnArr[] = [
                'id' => $v['id'],
                'code' => $v['code'],
                'name' => $v['name'],
                'type_name' => $v['type_name'],//主分类名称
                'type_code' => $v['type_code'],//主分类编码
                'state_tax_code' => $v['state_tax_code'],//地税号
                'national_tax_code' => $v['national_tax_code'],//国税号
                'found_date' => atwDate($v['found_date']),//成立日期
                'tax_rate' => initPerVal($v['tax_rate']),//税率
                'mobile' => $v['mobile'],//电话
                'phone' => $v['phone'],
                'email' => $v['email'],
                'fax' => $v['fax'],//传真
                'ctc_name' => $v['ctc_name'],//联系人
                'address' => $v['address'],//地址
                'pay_way' => $v['pay_way'],//付款方式
                'com_name' => $v['com_name'],//企业名称
                'purch_code' => $v['purch_code'],//采购员工号
                'purch_name' => $v['purch_name'],//采购员工姓名
                'purch_type' => $v['purch_type'],//供应商采购属性
                'check_type' => $v['check_type'],//检验类型
                'check_rate' => initPerVal($v['check_rate']),//抽检比例
                //'check_type' => $v['check_type'],//检验类型
            ];
        }
        $list = $returnArr;
        //dump($list);die;
        $path = ROOT_PATH.'public'.DS.'upload'.DS;
        $PHPExcel = new PHPExcel(); //实例化PHPExcel类，类似于在桌面上新建一个Excel表格
        $PHPSheet = $PHPExcel->getActiveSheet(); //获得当前活动sheet的操作对象
        $PHPSheet->setTitle('供应商列表'); //给当前活动sheet设置名称
        //dump($list);die;

        $PHPSheet->setCellValue('A1','供应商ID')->setCellValue('B1','供应商CODE');
        $PHPSheet->setCellValue('C1','供应商名称')->setCellValue('D1','供应商登录名');
        $PHPSheet->setCellValue('E1','供应商密码');
        $PHPSheet->setCellValue('F1','主分类名称');
        $PHPSheet->setCellValue('G1','主分类编码');
        $PHPSheet->setCellValue('H1','地税号');
        $PHPSheet->setCellValue('I1','国税号');
        $PHPSheet->setCellValue('J1','成立日期');
        $PHPSheet->setCellValue('K1','税率');
        $PHPSheet->setCellValue('L1','供应商电话');
        $PHPSheet->setCellValue('M1','供应商手机');
        $PHPSheet->setCellValue('N1','供应商邮箱');
        $PHPSheet->setCellValue('O1','供应商传真');
        $PHPSheet->setCellValue('P1','联系人');
        $PHPSheet->setCellValue('Q1','地址');
        $PHPSheet->setCellValue('R1','付款方式');
        $PHPSheet->setCellValue('S1','企业名称');
        $PHPSheet->setCellValue('T1','采购员工号');
        $PHPSheet->setCellValue('U1','采购员工姓名');
        $PHPSheet->setCellValue('V1','供应商采购属性');
        $PHPSheet->setCellValue('W1','检验类型');
        $PHPSheet->setCellValue('X1','抽检比例');
        $num = 1;
        foreach($list as $k => $v){
            $num = $num+1;
            $PHPSheet->setCellValue('A'.$num,$v['id'])->setCellValue('B'.$num,$v['code'])
                ->setCellValue('C'.$num,$v['name'])->setCellValue('D'.$num,strtolower($v['code']))
                ->setCellValue('E'.$num,'')->setCellValue('F'.$num,$v['type_name'])
                ->setCellValue('G'.$num,$v['type_code'])->setCellValue('H'.$num,$v['state_tax_code'])
                ->setCellValue('I'.$num,$v['national_tax_code'])->setCellValue('J'.$num,$v['found_date'])
                ->setCellValue('K'.$num,$v['tax_rate'])->setCellValue('L'.$num,$v['mobile'])
                ->setCellValue('M'.$num,$v['phone'])->setCellValue('N'.$num,$v['email'])
                ->setCellValue('O'.$num,$v['fax'])->setCellValue('P'.$num,$v['ctc_name'])
                ->setCellValue('Q'.$num,$v['address'])->setCellValue('R'.$num,$v['pay_way'])
                ->setCellValue('S'.$num,$v['com_name'])->setCellValue('T'.$num,$v['purch_code'])
                ->setCellValue('U'.$num,$v['purch_name'])->setCellValue('V'.$num,$v['purch_type'])
                ->setCellValue('W'.$num,$v['check_type'])->setCellValue('X'.$num,$v['check_rate']);
        }
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel,'Excel2007');//按照指定格式生成Excel文件，'Excel2007’表示生成2007版本的xlsx，
        $PHPWriter->save($path.'/supList.xlsx'); //表示在$path路径下面生成supList.xlsx文件
        $file_name = "supList.xlsx";
        $contents = file_get_contents($path.'/supList.xlsx');
        $file_size = filesize($path.'/supList.xlsx');
        header("Content-type: application/octet-stream;charset=utf-8");
        header("Accept-Ranges: bytes");
        header("Accept-Length: $file_size");
        header("Content-Disposition: attachment; filename=".$file_name);
        exit($contents);
    }

    public function uploadexcel(){
        //$file = request()->file('excel');
        //$info = $file->validate(['size'=>102400,'ext'=>'xlsx,xls,csv'])->move(ROOT_PATH . 'public' . DS . 'upload','');
        $path = input('src');
        //$path = 'http://atwwg.oms.atw.com/static/upload/0386c27f2884e94f/b4ab58206b190d3b.xlsx';
        //dump(parse_url($path));die;
        //$path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.'0863affda05d2d00'.DS.'149660d0799f13c5.xlsx';
        //$path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.'0863affda05d2d00'.DS.'0527123222.xlsx';
        if($path){
            $urlInfo = parse_url($path);
            $pathArr = explode('/',$urlInfo['path']);
            //dump($pathArr);die;
            //$path = ROOT_PATH.'public'.DS.'upload'.DS.$info->getFilename();
            $path = ROOT_PATH.'public'.DS.'static'.DS.'upload'.DS.$pathArr[3].DS.$pathArr[4];
            $logicSupInfo = Model('Supporter','logic');
            $logicUserInfo = Model('SystemUser','logic');
            $fileType=PHPExcel_IOFactory::identify($path);//自动获取文件的类型提供给phpexcel用
            $objReader=PHPExcel_IOFactory::createReader($fileType);//获取文件读取操作对象
            $objReader->setLoadSheetsOnly('供应商列表');//只加载指定的sheet
            $objPHPExcel=$objReader->load($path);//加载文件
            $currentSheet= $objPHPExcel->getSheet(0);
            $allColumn= $currentSheet->getHighestColumn();
            $allRow= $currentSheet->getHighestRow();
            for($currentRow =2;$currentRow <= $allRow;$currentRow++)
            {
                $data = [];
                $data['id'] = intval($objPHPExcel->getActiveSheet()->getCell("A".$currentRow)->getValue());//获取A列的值
                $data['sup_code'] = $objPHPExcel->getActiveSheet()->getCell("B".$currentRow)->getValue();//获取B列的值
                $data['sup_name'] = $objPHPExcel->getActiveSheet()->getCell("C".$currentRow)->getValue();//获取C列的值
                $data['user_name'] = $objPHPExcel->getActiveSheet()->getCell("D".$currentRow)->getCalculatedValue();//获取D列的值
                $data['password'] = $objPHPExcel->getActiveSheet()->getCell("E".$currentRow)->getCalculatedValue();//获取E列的值
                //检查sup_id是否存在
                if($logicSupInfo->getSupId($data['id']) == ''){//不存在
                    if(!empty($data['user_name']) && !empty($data['password'])){
                        //salt值
                        $info = [];
                        $info['salt'] = randomStr();
                        $info['user_name'] = $data['user_name'];
                        $info['password'] = $logicUserInfo->generatePwd($data['password'],$info['salt']);
                        $info['create_at'] = time();
                        $sup_id = $logicUserInfo->saveUserInfo($info);
                        if($sup_id){
                            $logicSupInfo->saveSupId($data['id'],['sup_id'=>$sup_id]);
                        }
                    }
                }
            }
            $this->success("更新成功！", '');
            //echo $path;
        }else{
            $this->success("上传失败！", '');
        }
    }

    /*
     * 供应商编辑页面展示
     */
    public function edit(){
        $this->assign('title',$this->title);
        $sup_id = intval(input('param.id'));
        $logicSupInfo = Model('Supporter','logic');
        $sup_info = $logicSupInfo->getOneSupInfo($sup_id);//联合查询得到相关信息
        $sup_info['tech_score'] = getTechScore($sup_info['code']);//技术分
        $sup_info['supply_risk'] = getSupplyRisk($sup_info['code']);//供应风险
        $sup_info['quali_level'] = getQualiLevel($sup_info['code']);//信用等级
        $sup_info['quali_score'] = getQualiScore($sup_info['code']);//资质评分
        //dump($sup_info);
        //echo $busnessArr = ['营业执照','税务登记证','组织代码证','ISO90001','TS认证','PED0','API','CE','SIL','其他'];
        if($sup_info){
            $this->assign('sup_info',$sup_info);
            $supQuali = $logicSupInfo->getSupQuali($sup_info['code']);
            $this->assign('supQuali',$supQuali);
            //dump($supQuali);
        }
        return view();
    }

    /*
     * 更改供应商资质status
     */
    public function changeQualiStatus(){
        $logicSupInfo = Model('Supporter','logic');
        $where = [
            'sup_code' => input('param.sup_code'),
            'code' => input('param.code'),
        ];
        $data = [
            'status' => input('param.status'),
        ];
        $scoreArr = ['iso90001','ts_lic','api_lic','ped_lic'];//加分组
        $result = $logicSupInfo->changeQualiStatus($where,$data);
        if($result !== false){
            if(in_array(input('param.code'),$scoreArr)){
                //更新tech_score
                $logicSupInfo->updateTechScore(['code',input('param.sup_code')]);
            }
            return json(['code'=>2000,'data'=>[],'msg'=>'成功']);
        }else{
            return json(['code'=>4000,'data'=>[],'msg'=>'失败']);
        }
    }

    /*
     * 更改供应商付款审核状态
     */
    public function changePayWayStatus(){
        $logicSupInfo = Model('Supporter','logic');
        $where = [
            'code' => input('param.code'),
        ];
        if(input('param.pay_way_status') == 'pass'){
            $data = [
                'pay_way_status' => input('param.pay_way_status'),
                'pay_way' => input('param.pay_way'),
                'pay_way_change' => ''
            ];
        }else{
            $data = [
                'pay_way_status' => input('param.pay_way_status'),
                //'pay_way' => input('param.pay_way')
            ];
        }
        $result = $logicSupInfo->changeSupplierInfo($where,$data);
        if($result !== false){
            return json(['code'=>2000,'data'=>[],'msg'=>'成功']);
        }else{
            return json(['code'=>4000,'data'=>[],'msg'=>'失败']);
        }
    }

    /*
     *
     */

}