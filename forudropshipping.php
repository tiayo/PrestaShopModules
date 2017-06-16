<?php
/**
 * 2017-2018 Zheng xiang jing.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to it@mg.forudropshipping.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize Forudropshipping for your
 * needs please refer to http://www.forudropshipping.com for more information.
 *
 *  @author Zheng xiang jing <it@mg.forudropshipping.com>
 *  @copyright  2017-2018 Zheng xiang jing
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*
 * 入口文件，加载自动加载文件、数据库链接文件、配置文件
 * */
require_once dirname(__FILE__).'/vendor/autoload.php';
require_once dirname(__FILE__).'/../../config/settings.inc.php';
require_once dirname(__FILE__).'/database/database.php';

if (!defined('_PS_VERSION_')) {
    exit;
}

class Forudropshipping extends Module
{
    public function __construct()
    {
        $this->name = 'forudropshipping';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'zheng xiangjing';
        $this->need_instance = 0;

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('FOUR Dropshipping');
        $this->description = $this->l('FOUR Dropshipping PrestaShop Module');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => '1.7.99.99');
        $this->module_key = '065d2b275f5edc72cb18eacbf0d85e30';
    }

    public function install()
    {
        if (Shop::isFeatureActive()) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }

        //Check whether the ps_tab table already exists
        $orexit = 'SELECT count(*) FROM '._DB_PREFIX_.'tab where class_name="AdminForudropshipping";';
        $class_name_num = Db::getInstance()->getValue($orexit);
        if ($class_name_num == 0) {
            $sql = 'SELECT COUNT(*) FROM '._DB_PREFIX_.'tab where id_parent=10;';
            $totalShop = Db::getInstance()->getValue($sql);
            Db::getInstance()->insert('tab', array(
                'id_parent' => 10,
                'class_name' => 'AdminForudropshipping',
                'module' => 'forudropshipping',
                'position' => $totalShop + 1,
            ));
        }

        //Check whether the ps_tab_lang table already exists
        $id_tab = Db::getInstance()->getValue('SELECT id_tab FROM '._DB_PREFIX_.'tab where class_name="AdminForudropshipping";');
        $orexit = 'SELECT count(*) FROM '._DB_PREFIX_.'tab_lang where id_tab="";';
        $id_tab_num = Db::getInstance()->getValue($orexit);
        if ($id_tab_num == 0) {
            Db::getInstance()->insert('tab_lang', array(
                'id_tab' => $id_tab,
                'id_lang' => '1',
                'name' => 'FORU Dropshipping',
            ));
            Db::getInstance()->insert('tab_lang', array(
                'id_tab' => $id_tab,
                'id_lang' => '2',
                'name' => 'FORU Dropshipping',
            ));
        }
        copy(dirname(__FILE__).'/src/Api/Entrance_forudropshipping.php', _PS_ROOT_DIR_.'/forudropshipping.php');

        return parent::install() &&
            $this->registerHook('displayAdminOrder') &&
            $this->registerHook('updateOrderStatus');
    }

    public function uninstall()
    {
        if (!parent::uninstall()) {
            return false;
        }
        Db::getInstance()->delete('tab_lang', "name = 'FORU Dropshipping'");
        unlink(_PS_ROOT_DIR_.'/forudropshipping.php');

        return true;
    }

    public function hookUpdateOrderStatus($params)
    {
        if (($params['newOrderStatus']->id == Configuration::get('PS_OS_WS_PAYMENT')) ||
            ($params['newOrderStatus']->id == Configuration::get('PS_OS_PAYMENT'))
        ) {
            $monitor = new \Foru\Api\Monitor();
            $monitor->add($params['id_order']);
        }

        if (($params['newOrderStatus']->id == Configuration::get('PS_OS_REFUND')) ||
            ($params['newOrderStatus']->id == Configuration::get('PS_OS_CANCELED'))
        ) {
            $monitor = new \Foru\Api\Monitor();
            $monitor->delete($params['id_order']);
        }
    }
}
