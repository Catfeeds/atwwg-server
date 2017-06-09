<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/5/25
 * Time: 13:42
 */
namespace app\spl\logic;

use think\Model;

class PR extends Model{
    protected $table = 'atw_u9_pr';

    protected $STATUS_ARR = [
        'init' => '初始',
        'hang' => '挂起',
        'inquiry' => '询价中',
        'flow' => '流标',
        'close' => '关闭',
    ];
    /**
     * Author: WILL<314112362@qq.com>
     * Describe:
     * @param $pr      array  请购单
     * @param $status  string 状态值
     */
    public function updateStatus($pr, $status){
        $this->where(['id' => $pr['id']])->update(['status' => $status]);
    }
}