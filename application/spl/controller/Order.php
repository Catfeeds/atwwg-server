<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/11
 * Time: 14:35
 */

namespace app\spl\controller;

class Order extends Base{
    protected $title = '采购订单';

    public function getOrderList(){
        $orderLogic = model('Order', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $where = ['sup_code' => $sup_code];
        $data = input('param.');
        $tag = input('tag');
        //        var_dump( $data);
        // 应用搜索条件
        if(!empty($data)){
            foreach(['status'] as $key){
                if(isset($data[$key]) && $data[$key] !== ''){
                    if($key == 'status' && $data[$key] == 'all'){
                        continue;
                    }
                    $where[$key] = $data[$key];
                }
            }
            if(!empty($data['contract_begintime']) && !empty($data['contract_endtime'])){
                $where['contract_time'] = array(
                    'between',
                    array(strtotime($data['contract_begintime']), strtotime($data['contract_endtime']))
                );
            }elseif(!empty($data['contract_begintime'])){
                $where['contract_time'] = array('egt', strtotime($data['contract_begintime']));
            }elseif(!empty($data['contract_endtime'])){
                $where['contract_time'] = array('elt', strtotime($data['contract_endtime']));
            }
        }
        $list = $orderLogic->getPolist($where,$tag);

        $returnInfo = [];
        $status = [
            '' => '',
            'init' => '待签订',
            'sup_cancel' => '已取消',
            'sup_sure' => '合同待上传',
            'upload_contract' => '合同待审核',
            'contract_pass' => '合同审核通过',
            'contract_refuse' => '合同审核拒绝',
            'executing' => '执行中',
            'finish' => '结束',
        ];
        // var_dump($list);
        foreach($list as $k => $v){
            $returnInfo[$k]['checked'] = $v['id'];
            $exec_desc = '';
            if(!empty($itemInfo = $orderLogic->getPoItemInfo($v['id']))){
                foreach($itemInfo as $vv){
                    $vv['arv_goods_num'] = $vv['arv_goods_num'] == '' ? 0 : $vv['arv_goods_num'];
                    $vv['pro_goods_num'] = $vv['pro_goods_num'] == '' ? 0 : $vv['pro_goods_num'];
                    $exec_desc .= '物料名称：'.$vv['item_name'].'; '.'已送货数量：'.$vv['arv_goods_num'].'; 未送货数量：'.$vv['pro_goods_num'].'<br>';
                }
                $returnInfo[$k]['exec_desc'] = $exec_desc;
            }else{
                $returnInfo[$k]['exec_desc'] = '';
            }
            $returnInfo[$k]['order_code'] = $v['order_code'];
            //$returnInfo[$k]['pr_code'] = $v['pr_code'];
            //  $returnInfo[$k]['pr_date'] = date('Y-m-d',$offerLogic->getPrDate($v['pr_code']));
            $returnInfo[$k]['create_at'] = date('Y-m-d', $v['create_at']);
            $returnInfo[$k]['status'] = $status[$v['status']];
            $returnInfo[$k]['contract_time'] = empty($v['contract_time']) ? '--' : date('Y-m-d', $v['contract_time']);
            $returnInfo[$k]['id'] = $v['id'];
        }
        //dump($returnInfo);
        $info = ['draw' => time(), 'data' => $returnInfo, 'extData' => [],];
        return json($info);
    }

    public function index(){
        $orderStatus = array(
            'init' => '待签订',
            'sup_cancel' => '供应商取消',
            'sup_edit' => '供应商修改',
            'atw_sure' => '安特威确定',
            'sup_sure' => '待上传合同',
            'upload_contract' => '已经上传合同',
            'contract_pass' => '合同审核通过',
            'contract_refuse' => '合同审核拒绝',
            'executing' => '执行中',
            'finish' => '结束'
        );
        $this->assign('orderstatus', $orderStatus);
        $this->assign('title', $this->title);
        return view();
    }

    public function detail(){
        $pr_id = input('id');
        //$pr_code = '1111222';
        $offerLogic = model('Order', 'logic');
        $piList = $offerLogic->getOrderDetailInfo($pr_id);

        foreach($piList as $key => &$item){
            $result = $offerLogic->getOrderRecordInfo($item['id']);
            $piList[$key]['times'] = (!empty($result) ? count($result) : 0);
            $item['sup_update_date_str'] = empty($item['sup_update_date']) ? '' : date('Y-m-d', $item['sup_update_date']);
        }
        $codeInfo = $offerLogic->getOrderListOneInfo($pr_id);
        $contractable = in_array($codeInfo[0]['status'], array('sup_sure', 'upload_contract')) ? '1' : '0';
        $cancelable = in_array($codeInfo[0]['status'], array('init', 'atw_sure')) ? '1' : '0';
        $confirmorderable = in_array($codeInfo[0]['status'], array('init', 'atw_sure')) ? '1' : '0';
        $confirmable = !in_array($codeInfo[0]['status'], ['sup_cance', 'finish']) ? '1' : '0';
        $statusButton = array(
            'contractable' => $contractable,
            'cancelable' => $cancelable,
            'confirmable' => $confirmable,
            'confirmorderable' => $confirmorderable
        );
        $imgInfos = explode(',', $codeInfo[0]['contract']);
        $imgInfos = array_filter($imgInfos);
        $this->assign('statusButton', $statusButton);
        $this->assign('imgInfos', $imgInfos);
        // var_dump($detail);
        $this->assign('list', $piList);
        if(empty($codeInfo[0]['order_code'])){
            $codeInfo[0]['order_code'] = '--';
        }
        if(empty($codeInfo[0]['contract_time'])){
            $codeInfo[0]['contract_time'] = '--';
        }else{
            $codeInfo[0]['contract_time'] = atwDate($codeInfo[0]['contract_time']);
        }
        $this->assign('codeInfo', $codeInfo[0]);
        return view();
    }

    public function cancel(){
        $id = input('id');
        $offerLogic = model('Order', 'logic');
        $detail = $offerLogic->updateStatus($id, 'sup_cancel');
        if($detail){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    public function orderconfirm(){
        $id = input('id');
        $offerLogic = model('Order', 'logic');
        $detail = $offerLogic->updateStatus($id, 'sup_sure');
        if($detail){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 4000, 'msg' => '更新失败', 'data' => []]);
        }
    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 供应商修改交期
     * @return \think\response\Json
     */
    public function updateSupconfirmdate(){
        $id = input('id');
        $supconfirmdate = strtotime(input('supconfirmdate'));
        $orderLogic = model('Order', 'logic');
        $poRecLogic = model('PoRecord', 'logic');
        $pi = model('PoItem', 'logic')->find($id);
        //var_dump($detailInfo);
        if(empty($pi)){
            return json(['code' => 4004, 'msg' => '获取消息失败', 'data' => []]);
        }
        $times = $poRecLogic->countByPiId($id);
        if($times > 3){
            returnJson(4010, '修改次数已经超过三次');
        }

        $u9Ret = $orderLogic->updateU9Supconfirmdate($pi, $supconfirmdate);
        if($u9Ret['code'] != 2000){
            return returnJson($u9Ret);
        }

        //        if(empty($u9Ret['result']['IsSuccess'])){
        //            return returnJson(6000);
        //        }

        $data = [
            'pi_id' => $id,
            'create_at' => time(),
            'update_at' => time(),
            'promise_date' => $pi['sup_confirm_date'],
            'seq' => $times + 1
        ];
        if(!$poRecLogic->data($data)->save()){
            return json(['code' => 5000, 'msg' => '保存po_record 失败', 'data' => []]);
        }
        //记录修改次数
        $sup_code = session('spl_user')['sup_code'];
        model('SupplierInfo', 'logic')->where('code', $sup_code)->setInc('readjust_count');
        $detail = $orderLogic->updateSupconfirmdate($id, $supconfirmdate);
        //$detailPo = $orderLogic->updateStatus($pi['po_id'], 'sup_edit');
        if($detail){
            return json(['code' => 2000, 'msg' => '成功', 'data' => []]);
        }else{
            return json(['code' => 5000, 'msg' => '更新失败', 'data' => []]);
        }
    }


    public function add(){
        if(request()->isPost()){
            $data = input('param.');
            $id = $data['id'];
            $contract = input('contract');
            $src = empty($contract) ? $data['src'] : $contract.','.$data['src'];
            $offerLogic = model('Order', 'logic');
            $result = $offerLogic->updatecontract($id, $src, 'upload_contract');
            $result !== false ? $this->success('恭喜，保存成功哦！', '') : $this->error('保存失败，请稍候再试！');
        }else{
            $id = input('id');
            $contract = input('contract');
            $this->assign('id', $id);
            $this->assign('contract', $contract);
            return view();
        }

    }

    /**
     * Author: WILL<314112362@qq.com>
     * Time: ${DAY}
     * Describe: 下载合同模版
     */
    public function downContract(){
        $id = input('id');
        $poLogic = model('Order', 'logic');
        $sup_code = session('spl_user')['sup_code'];
        $po = $poLogic->where('sup_code', $sup_code)->where('id', $id)->find();
        if(empty($po)){
            $this->error('无效的id='.$id, '');
        }

        return $poLogic->downContract($po);
    }
}