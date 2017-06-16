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

namespace Foru\Api;

use Foru\Model\Category;
use Foru\Model\CategoryGroup;
use Foru\Model\Categorylang;
use Foru\Model\CategoryProduct;
use Foru\Model\CategoryShop;
use Foru\Product\Handle;

class ImportProduct
{
    protected $configuration;
    protected $token;
    protected $category;
    protected $category_lang;
    protected $category_product;
    protected $category_shop;
    protected $category_group;

    public function __construct()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        $this->category = new Category();
        $this->category_lang = new Categorylang();
        $this->category_product = new CategoryProduct();
        $this->category_gropu = new CategoryGroup();
        $this->category_shop = new CategoryShop();
    }

    public function handle()
    {
        Verification::verification();
        $value = json_decode(\ToolsCore::file_get_contents('php://input'), true);
        if (empty($value) || !is_array($value)) {
            $this->response('Product must be a array()!');
        }
        /*
         * handle data
         */
        $product = $array = array();
        foreach ($value['products'] as $key => $row) {
            if ($row[0] == 'Product') {
                if (!empty($product)) {
                    $array[] = $product;
                    unset($product);
                }
                $product[] = $row;
            } else {
                $product[] = $row;
            }
        }
        $array[] = $product;
        /*
         * add product
         */
        $handle = new Handle();

        $category = $value['categories'];

        try {
            $handle->handle($array, $category);
        } catch (\Exception $e) {
            $this->response($e->getMessage(), $e->getCode());
        }

        $this->response('success!', 200);
    }

    public function category($category_str)
    {
        $category_str_ex = explode(' ', \ToolsCore::strtolower($category_str));
        $category_str_h = $category_str_ex[0];
        if (!empty($category_str_ex[1])) {
            $category_str_h .= '-'.$category_str_ex[1];
        }
        //find category id
        $category_id = $this->category_lang
            ->select('id_category')
            ->where('link_rewrite', $category_str_h)
            ->first()['id_category'];
        if (!empty($category_id)) {
            return $category_id;
        }
        //add new category
        $category_id = $this->category
            ->create(array(
                'id_parent' => 2,
                'level_depth' => 2,
                'active' => 1,
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            ))->toArray()['id_category'];
        $this->category_lang
            ->create(array(
                'id_category' => $category_id,
                'id_shop' => 1,
                'id_lang' => 1,
                'name' => $category_str,
                'link_rewrite' => $category_str_h,
            ));
        for ($i = 1; $i < 4; ++$i) {
            $this->category_gropu
                ->create(array(
                    'id_category' => $category_id,
                    'id_group' => $i,
                ));
        }

        $this->category_shop
            ->create(array(
                'id_category' => $category_id,
                'id_shop' => 1,
                'position' => 0,
            ));

        return $category_id;
    }

    public function response($info, $code = 403)
    {
        http_response_code($code);
        echo $info;
        exit();
    }
}
