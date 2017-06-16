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

namespace Foru\Product;

use Foru\Model\Attribute;
use Foru\Model\AttributeGroup;
use Foru\Model\AttributeGroupLang;
use Foru\Model\AttributeGroupShop;
use Foru\Model\AttributeLang;
use Foru\Model\AttributeShop;
use Foru\Model\CategoryProduct;
use Foru\Model\Product;
use Foru\Model\ProductAttribute;
use Foru\Model\ProductAttributeCombination;
use Foru\Model\ProductAttributeShop;
use Foru\Model\ProductLang;
use Foru\Model\ProductShop;
use Foru\Model\StockAvailable;

class Handle
{
    protected $product;
    protected $product_lang;
    protected $product_shop;
    protected $product_attribute;
    protected $attribute;
    protected $attribute_lang;
    protected $attribute_group;
    protected $attribute_group_lang;
    protected $product_attribute_combination;
    protected $product_attribute_shop;
    protected $category_product;
    protected $stock_available;
    protected $attribute_shop;
    protected $attribute_group_shop;

    public function __construct()
    {
        $this->product = new Product();
        $this->product_lang = new ProductLang();
        $this->product_shop = new ProductShop();
        $this->product_attribute = new ProductAttribute();
        $this->attribute = new Attribute();
        $this->attribute_lang = new AttributeLang();
        $this->attribute_group = new AttributeGroup();
        $this->attribute_group_lang = new AttributeGroupLang();
        $this->product_attribute_combination = new ProductAttributeCombination();
        $this->product_attribute_shop = new ProductAttributeShop();
        $this->category_product = new CategoryProduct();
        $this->stock_available = new StockAvailable();
        $this->attribute_shop = new AttributeShop();
        $this->attribute_group_shop = new AttributeGroupShop();
    }

    public function get($category)
    {
        $value = array();
        if ($_FILES['product']['type'] != 'text/csv') {
            throw new \Exception('Must be CSV format!');
        }
        $filename = empty($_FILES['product']['tmp_name']) ? null : $_FILES['product']['tmp_name'];
        //获取临时文件名
        if ($filename != null) {
            $file = fopen($filename, 'r'); //打开临时文件
            while (!feof($file)) {
                $value[] = fgetcsv($file); //按行读取数据
            }
            fclose($file); //关闭文件
            $product = $array = array();
            foreach ($value as $key => $row) {
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
            $this->handle($array, $category);

            return true;
        }
        throw new \Exception('File upload error');
    }

    /**
     * @param $data array()
     * @param $category array()
     */
    public function handle($data, $category)
    {
        $count_data = count($data);
        foreach ($data as $key_data => $row) {
            //Write progress
            $update_row = array();
            $record = fopen(dirname(__FILE__).'/record.txt', 'w');
            $schedule = floor(($key_data / $count_data) * 100);
            fwrite($record, $schedule.'-Loading a new product');
            fclose($record);
            $average = floor((1 / $count_data) * 100);

            //handle data
            $sku = $price = $weight = $type = array();
            $product_name = htmlspecialchars($row[0][1], ENT_QUOTES);
            $product_describe = strip_tags($row[0][2], '<div><ul><li><p><h1><h2><img><ol>');
            $product_image = preg_split("/(\r\n|\n|\r)/", $row[0][3]);
            $update_row[] = $row;
            unset($row[0]);
            $if_continue = 0;
            foreach ($row as $key => $item) {
                if ($item[0] != 'Variant') {
                    unset($row[$key]);
                    continue;
                }

                /*handle product already exists start*/
                $all_sku = $this->product_attribute
                    ->select('id_product', 'reference')
                    ->where('default_on', 1)
                    ->where('reference', '<>', '')
                    ->where('reference', '<>', null)
                    ->get()
                    ->toArray();

                foreach ($all_sku as $all_sku_row) {
                    $all_sku_ex = explode('-', explode('_', $all_sku_row['reference'])[1]);
                    $if_sku = explode('-', $item[1]);
                    if ($all_sku_ex[0] == $if_sku[0] && $all_sku_ex[1] == $if_sku[1]) {
                        $update_product = new UpdateProduct();
                        $update_product->update($update_row, $all_sku_row, $category);
                        $if_continue = 1;
                        break 2;
                    }
                }
                /*handle product already exists end*/

                $sku[] = $item[1];
                $price[] = $item[2];
                $weight[] = $item[3];
                $type[] = explode(';', $item[4]);
            }
            if ($if_continue == 1) {
                continue;
            }

            //Insert basic information
            $product_id = $this->product
                ->create(array(
                    'price' => $price[0],
                    'reference' => 'FORU_'.$sku[0],
                    'id_category_default' => $category[0],
                    'weight' => $weight[0],
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                    'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
                    'id_tax_rules_group' => 1,
                    'active' => 1,
                    'redirect_type' => '404',
                ))->toArray()['id_product'];

            //insert product images
            $get_image = new Images();
            $get_image->images($product_image, $product_id, $schedule, $average);

            // insert product detailed infomation
            foreach ($category as $category_row) {
                $this->category_product
                    ->create(array(
                        'id_category' => $category_row,
                        'id_product' => $product_id,
                    ));
            }

            for ($i = 1; $i < 3; ++$i) {
                $this->product_lang
                    ->create(array(
                        'id_product' => $product_id,
                        'id_lang' => $i,
                        'description' => $product_describe,
                        'description_short' => $product_name,
                        'name' => $product_name,
                        'link_rewrite' => str_replace(' ', '-', \ToolsCore::strtolower($product_name)),
                        'available_now' => 'In stock',
                    ));
            }

            $this->product_shop
                ->create(array(
                    'id_product' => $product_id,
                    'id_shop' => 1,
                    'id_category_default' => $category[0],
                    'id_tax_rules_group' => 1,
                    'price' => $price[0],
                    'active' => 1,
                    'redirect_type' => '404',
                    'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
                    'indexed' => 1,
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                ));
            $count = count($row);
            for ($i = 0; $i < $count; ++$i) {
                $id_product_attribute = $this->product_attribute
                    ->create(array(
                        'id_product' => $product_id,
                        'reference' => 'FORU_'.$sku[$i],
                        'price' => $price[$i] - $price[0],
                        'quantity' => 100,
                        'weight' => $weight[$i] - $weight[0],
                        'default_on' => ($i == 0) ? 1 : null,
                        'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
                    ))->toArray()['id_product_attribute'];

                $this->product_attribute_shop
                    ->create(array(
                        'id_product' => $product_id,
                        'id_product_attribute' => $id_product_attribute,
                        'id_shop' => 1,
                        'price' => $price[$i] - $price[0],
                        'weight' => $weight[$i] - $weight[0],
                        'default_on' => ($i == 0) ? 1 : null,
                        'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
                    ));
                $this->stock_available
                    ->create(array(
                        'id_product' => $product_id,
                        'id_product_attribute' => $id_product_attribute,
                        'id_shop' => 1,
                        'id_shop_group' => 0,
                        'quantity' => 99999,
                        'out_of_stock' => 2,
                    ));

                $this->insertType($type[$i], $id_product_attribute);
            }

            $this->stock_available
                ->create(array(
                    'id_product' => $product_id,
                    'id_product_attribute' => 0,
                    'id_shop' => 1,
                    'id_shop_group' => 0,
                    'quantity' => 199998,
                    'out_of_stock' => 2,
                ));
        }
    }

    public function insertType($data, $id_product_attribute)
    {
        foreach ($data as $row) {
            if (empty($row)) {
                continue;
            }

            $id_attribute = null; //每次查询开始初始化
            $array = explode(':', $row);
            $title = ucwords(\ToolsCore::strtolower($array[0]));
            $value = $array[1];
            $id_attribute_array = $this->attribute_lang
                ->select('id_attribute')
                ->where('name', $value)
                ->where('id_lang', '1')
                ->get();

            if (!empty($id_attribute_array)) {
                $id_attribute_array = $id_attribute_array->toArray();
                foreach ($id_attribute_array as $attribute_row) {
                    $id_attribute_group = $this->attribute
                        ->select('id_attribute_group')
                        ->where('id_attribute', $attribute_row['id_attribute'])
                        ->first()['id_attribute_group'];
                    $id_attribute_group_name = $this->attribute_group_lang
                        ->select('name')
                        ->where('id_attribute_group', $id_attribute_group)
                        ->where('id_lang', 1)
                        ->first()['name'];
                    if ($id_attribute_group_name == $title) {
                        $id_attribute = $attribute_row['id_attribute'];
                        break;
                    } else {
                        $id_attribute = null;
                    }
                }
            }

            if (empty($id_attribute)) {
                $id_attribute_group = $this->attribute_group_lang
                    ->select('id_attribute_group')
                    ->where('name', $title)
                    ->orwhere('public_name', $title)
                    ->first()['id_attribute_group'];

                if (empty($id_attribute_group)) {
                    $id_attribute_group = $this->attribute_group
                        ->create(array(
                            'position' => ($this->attribute_group->count()) + 1,
                        ))->toArray()['id_attribute_group'];
                    for ($i = 1; $i < 3; ++$i) {
                        $this->attribute_group_lang
                            ->create(array(
                                'id_attribute_group' => $id_attribute_group,
                                'id_lang' => $i,
                                'name' => $title,
                                'public_name' => $title,
                            ))->toArray();
                    }
                    $this->attribute_group_shop
                        ->create(array(
                            'id_attribute_group' => $id_attribute_group,
                            'id_shop' => 1,
                        ));
                }

                $id_attribute = $this->attribute
                    ->create(array(
                        'id_attribute_group' => $id_attribute_group,
                        'position' => ($this->attribute->count()) + 1,
                    ))->toArray()['id_attribute'];

                $this->attribute_shop
                    ->create(array(
                        'id_attribute' => $id_attribute,
                        'id_shop' => 1,
                    ));

                for ($i = 1; $i < 3; ++$i) {
                    $this->attribute_lang
                        ->create(array(
                            'id_attribute' => $id_attribute,
                            'id_lang' => $i,
                            'name' => $value,
                        ));
                }
            }

            $this->product_attribute_combination
                ->updateORcreate(array(
                    'id_attribute' => $id_attribute,
                    'id_product_attribute' => $id_product_attribute,
                ));
        }
    }
}
