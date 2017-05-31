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
use Qiniu\Auth;
use Qiniu\Processing\PersistentFop;

class Order extends BaseController{
    protected $table = 'SystemPo';
    protected $title = '订单管理';

    public function index(){
        //echo '111111111';die;
        $this->assign('title',$this->title);
        return view();
    }

    public function getOrderList(){
        $poLogic = model('Po','logic');
        $get = input('param.');
        //dump($requestInfo);die;
        $where = [];
        // 应用搜索条件
        foreach (['order_code', 'pr_code'] as $key) {
            if (isset($get[$key]) && $get[$key] !== '') {
                $where[$key] = ['like',"%{$get[$key]}%"];
            }
        }
        $list = $poLogic->getPolist($where);
        $returnInfo = [];
        $status = [
            '' => '',
            'init' => '初始',
            'sup_cancel' => '供应商取消',
            'sup_edit' => '供应商修改',
            'atw_sure' => '安特威确定',
            'sup_sure' => '供应商确定/待上传合同',
            'upload_contract' => '供应商已经上传合同',
            'contract_pass' => '合同审核通过',
            'contract_refuse' => '合同审核拒绝',
            'executing' => '执行中',
            'finish' => '结束',
        ];

        foreach($list as $k => $v){
            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            if(!empty($itemInfo = $poLogic->getPoItemInfo($v['id']))){
                foreach($itemInfo as $vv){
                    $vv['arv_goods_num'] = $vv['arv_goods_num'] == '' ? 0 : $vv['arv_goods_num'];
                    $vv['pro_goods_num'] = $vv['pro_goods_num'] == '' ? 0 : $vv['pro_goods_num'];
                    $exec_desc .= '物料名称：'.$vv['item_name'].'; '.'到货数量：'.$vv['arv_goods_num'].'; 未到货数量：'.$vv['pro_goods_num'].'<br>';
                }
                $returnInfo[$k]['exec_desc'] = $exec_desc;
            }else{
                $returnInfo[$k]['exec_desc'] = '';
            }

            $returnInfo[$k]['order_code'] = $v['order_code'];
            $returnInfo[$k]['pr_code'] = $v['pr_code'];
            $returnInfo[$k]['pr_date'] = date('Y-m-d',$poLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = date('Y-m-d',$v['create_at']);
            $returnInfo[$k]['sup_name'] = $poLogic->getSupName($v['sup_code']);
            $returnInfo[$k]['status'] = $status[$v['status']];
            /*if($status[$v['status']] == 'sup_edit'){//供应商修改-->action
                next($status);
            }*/
            $returnInfo[$k]['detail'] = $v['id'];
        }
        //dump($returnInfo);
        $info = ['draw'=>time(),'data'=>$returnInfo,'extData'=>[],];

        return json($info);
    }
    public function del(){

    }

    public function add(){

    }

    public function mkzip(){
        $accessKey = sysconf('storage_qiniu_access_key');
        $secretKey = sysconf('storage_qiniu_secret_key');
        $bucket = sysconf('storage_qiniu_bucket');
        $host = sysconf('storage_qiniu_domain');
        $key = '1.png';

        $auth = new Auth($accessKey, $secretKey);

    }
    public function detailed(){
        $id = input('get.id');
        //echo $id;
        $poLogic = model('Po','logic');
        $poInfo = $poLogic->getPoInfo($id);
        $poItemInfo = $poLogic->getPoInfo($id);
        return view();
    }
}