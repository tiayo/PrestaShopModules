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

class AdminForudropshippingController extends ModuleAdminController
{
    public function __construct()
    {
        $this->bootstrap = true;
        $this->display = 'view';
        parent::__construct();
        $this->meta_title = $this->l('FORU Dropshipping');
        $this->view();
        $page = Tools::getValue('page') ?: null;

        //delete old record.txt
        $this->file = dirname(__FILE__).'/../../src/Product/record.txt';
        if (file_exists($this->file)) {
            unlink($this->file);
        }

        // Choose the method
        switch ($page) {
            case 'export_all_order':
                $this->exportAllOrder();
                break;
            case 'export_order_by_date':
                $this->exportOrderByDate();
                break;
            case 'import_product':
                $this->importProduct();
                break;
            case 'import_tracking':
                $this->importTracking();
                break;
            case 'api_key':
                $this->apiKey();
                break;
            default:
                return 'Unknown module';
        }
    }

    public function setMedia()
    {
        $this->addJqueryUI('ui.progressbar');
        $this->addJS('http://cdn.bootcss.com/flatpickr/2.4.3/flatpickr.min.js');
        $this->addCSS('http://cdn.bootcss.com/flatpickr/2.4.3/flatpickr.min.css');

        return parent::setMedia();
    }

    public function initToolBarTitle()
    {
        $this->toolbar_title[] = $this->l('FORU Dropshipping');
    }

    public function view($message = null, $status = null)
    {
        $api_key = new Foru\Api\Api();
        $category = new Foru\Product\Category();
        $this->tpl_view_vars = array(
            'links' => $this->context->link->getAdminLink('AdminForudropshipping'),
            'adminCategories' => $this->context->link->getAdminLink('AdminCategories'),
            'category' => $category->get(),
            'api_key' => $api_key->view(),
            'message' => $message,
            'status' => $status,
        );
        $this->base_tpl_view = 'forudropshipping.tpl';

        return parent::renderView();
    }

    /*
     * Start implementing the appropriate method
     * */

    public function exportAllOrder()
    {
        $new_all = new Foru\Export\All();
        $data = $new_all->all();
        if (!$data) {
            return $this->view('There is no qualified order', 2);
        }

        $export = new \Foru\Export\Export();
        $export->export($data, date('Y-m-d H:i'));
    }

    public function exportOrderByDate()
    {
        $new_date = new Foru\Export\Date();

        //date为空报错
        try {
            $data = $new_date->date();
        } catch (Exception $e) {
            return $this->view($e->getMessage(), 2);
        }

        //找不到数据报错
        if (!$data) {
            return $this->view('There is no qualified order', 2);
        }

        $export = new \Foru\Export\Export();
        $export->export($data, date('Y-m-d H:i'));
    }

    public function importTracking()
    {
        $new_tracking = new Foru\Tracking\Handle();

        try {
            $data = $new_tracking->get();
        } catch (\Exception $e) {
            return $this->view($e->getMessage(), 2);
        }

        $new_tracking->database($data);
        return $this->view('Tracking import complete!', 1);
    }

    public function importProduct()
    {
        $category = array(Tools::getValue('category'));
        $product = new Foru\Product\Handle();
        try {
            $product->get($category);
        } catch (Exception $e) {
            return $this->view($e->getMessage(), 2);
        }
        /*
        *insert success
        * */
        $record = fopen($this->file, 'w');
        $schedule = 100;
        fwrite($record, $schedule.'-Import complete');
        fclose($record);
        usleep(3010000);
        return $this->view('Product import complete!', 1);
    }

    public function apiKey()
    {
        $api_key = new Foru\Api\Api();
        if ($api_key->updateKey()) {
            return $this->view('Create token update success!', 1);
        }
        return $this->view('Create token fail.Please check your account username、password or try again!', 2);
    }
}
