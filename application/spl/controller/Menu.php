<?php

// +----------------------------------------------------------------------
// | Think.Admin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Admin
// +----------------------------------------------------------------------

namespace app\spl\controller;

use controller\BasicSpl;
use service\DataService;
use service\ToolsService;
use think\Db;

/**
 * 系统后台管理管理
 * Class Menu
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15
 */
class Menu extends Base {

    /**
     * 绑定操作模型
     * @var string
     */
    protected $table = 'SystemUserMenu';

    /**
     * 菜单列表
     */
    public function index() {
        $this->title = '系统菜单管理';
        $db = Db::name($this->table)->order('sort asc,id asc');
        parent::_list($db, false);
    }

    /**
     * 列表数据处理
     * @param array $data
     */
    protected function _index_data_filter(&$data) {
        foreach ($data as &$vo) {
            ($vo['url'] !== '#') && ($vo['url'] = url($vo['url']));
            $vo['ids'] = join(',', ToolsService::getArrSubIds($data, $vo['id']));
        }
        $data = ToolsService::arr2table($data);
    }

    /**
     * 添加菜单
     */
    public function add() {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            DataService::save($this->table, $data);
            $this->success('菜单保存成功！', '');
            //dump($data);die;
            //$this->error('系统开发中，不要动菜单哦！');
        }
        return $this->_form($this->table, 'form');
    }

    /**
     * 编辑菜单
     */
    public function edit() {
        return $this->add();
    }

    /**
     * 表单数据前缀方法
     * @param array $vo
     */
    protected function _form_filter(&$vo) {
        if ($this->request->isGet()) {
            // 上级菜单处理
            $_menus = Db::name($this->table)->where('status', '1')->order('sort desc,id desc')->select();
            $_menus[] = ['title' => '顶级菜单', 'id' => '0', 'pid' => '-1'];
            $menus = ToolsService::arr2table($_menus);
            foreach ($menus as $key => &$menu) {
                if (substr_count($menu['path'], '-') > 3) {
                    unset($menus[$key]);
                    continue;
                }
                if (isset($vo['pid'])) {
                    $current_path = "-{$vo['pid']}-{$vo['id']}";
                    if ($vo['pid'] !== '' && (stripos("{$menu['path']}-", "{$current_path}-") !== false || $menu['path'] === $current_path)) {
                        unset($menus[$key]);
                    }
                }
            }

            $this->assign('menus', $menus);
        } else {
            $this->error('请不要改菜单构造！');
        }
    }

    /**
     * 删除菜单
     */
    public function del() {
        //$this->error('别再删我菜单了...');
        if (DataService::update($this->table)) {
            $this->success("菜单删除成功！", '');
        }
        $this->error("菜单删除失败，请稍候再试！");
    }

    /**
     * 菜单禁用
     */
    public function forbid() {
        //$this->error('请不要禁用菜单...');
        if (DataService::update($this->table)) {
            $this->success("菜单禁用成功！", '');
        }
        $this->error("菜单禁用失败，请稍候再试！");
    }

    /**
     * 菜单禁用
     */
    public function resume() {
        if (DataService::update($this->table)) {
            $this->success("菜单启用成功！", '');
        }
        $this->error("菜单启用失败，请稍候再试！");
    }

}
