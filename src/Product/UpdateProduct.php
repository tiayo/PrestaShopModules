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
use Foru\Model\Image;
use Foru\Model\ImageShop;
use Foru\Model\Product;
use Foru\Model\ProductAttribute;
use Foru\Model\ProductAttributeCombination;
use Foru\Model\ProductAttributeShop;
use Foru\Model\ProductLang;
use Foru\Model\ProductShop;
use Foru\Model\StockAvailable;

class UpdateProduct
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
    protected $image;
    protected $image_shop;
    protected $is_default_product;

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
        $this->image = new Image();
        $this->image_shop = new ImageShop();
    }

    public function update($data, $all_sku, $category)
    {
        //处理是否有需要删除
        $this->handleDelete($data, $all_sku);

        foreach ($data as $key_data => $row) {
            /**
             * handle data.
             */
            $sku = $price = $weight = $type = $id_product_attribute = array();
            $product_name = htmlspecialchars($row[0][1], ENT_QUOTES);
            $product_describe = strip_tags($row[0][2], '<div><ul><li><p><h1><h2><img><ol>');
            if (!empty($row[0][3])) {
                $product_image = preg_split("/(\r\n|\n|\r)/", $row[0][3]);
            }

            $id_product = $all_sku['id_product']; //get id_product
            //get product original info
            $original_product = $this->product
                ->select('id_category_default', 'price', 'reference', 'weight')
                ->where('id_product', $id_product)
                ->first();

            $original_price = $original_product['price'];
            $original_weight = $original_product['weight'];
            $original_reference = $original_product['reference'];

            //判断是否有默认商品
            $default_exists = $this->product_attribute
                ->select('id_product_attribute')
                ->where('id_product', $id_product)
                ->where('default_on', 1)
                ->first()['id_product_attribute'];

            unset($row[0]);
            foreach ($row as $key => $item) {
                if ($item[0] != 'Variant' || empty($item[0]) || !isset($item[0])) {
                    unset($row[$key]);
                    continue;
                }

                //判断是否是默认商品
                $default_product = $this->product_attribute
                    ->select('default_on')
                    ->where('reference', 'FORU_'.$item[1])
                    ->first()['default_on'];

                $this->is_default_product = 0;
                if ($default_product == 1 || $default_product === null) {
                    //设置为默认商品
                    if (empty($default_exists)) {
                        $id_product_attribute = $this->product_attribute
                            ->select('id_product_attribute')
                            ->where('reference', 'FORU_'.$item[1])
                            ->first()['id_product_attribute'];
                        $this->product_attribute
                            ->where('id_product_attribute', $id_product_attribute)
                            ->update(array('default_on' => 1));
                        $this->product_attribute_shop
                            ->where('id_product_attribute', $id_product_attribute)
                            ->update(array('default_on' => 1));
                    }
                    //设置默认商品属性
                    $this->is_default_product = 1;
                }
                $sku[] = $item[1];
                $price[] = $item[2];
                $weight[] = $item[3];
                $type[] = explode(';', $item[4]);
            }
            //Modify all price changes
            $diff_original_price = $price[0] - $original_price;
            if ($this->is_default_product == 1) {
                $this->refreshAttribute($id_product, $diff_original_price); //Default product Operation
            }
            //update basic information
            $this->product
             ->where('id_product', $id_product)
             ->update(array(
                'price' => $this->is_default_product == 1 ? $price[0] : $original_price,
                'reference' => $this->is_default_product == 1 ? 'FORU_'.$sku[0] : $original_reference,
                'id_category_default' => $category[0],
                'weight' => $weight[0],
                'date_upd' => date('Y-m-d H:i:s'),
                'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
             ));

            //update product images
            if (isset($product_image)) {
                $this->image
                    ->where('id_product', $id_product)
                    ->delete();
                $this->image_shop
                    ->where('id_product', $id_product)
                    ->delete();
                unlink(dirname(dirname(__FILE__)).'/../../../img/tmp/product_mini_'.$id_product.'_1.jpg');
                $get_image = new Images();
                $get_image->images($product_image, $id_product);
            }

            //update product detailed infomation

            $this->category_product
                ->where('id_product', $id_product)
                ->delete();

            foreach ($category as $category_row) {
                $this->category_product
                    ->create(array(
                        'id_category' => $category_row,
                        'id_product' => $id_product,
                    ));
            }

            for ($i = 1; $i < 3; ++$i) {
                $this->product_lang
                    ->where('id_product', $id_product)
                    ->where('id_lang', $i)
                    ->update(array(
                        'description' => $product_describe,
                        'description_short' => $product_name,
                        'name' => $product_name,
                        'link_rewrite' => str_replace(' ', '-', \ToolsCore::strtolower($product_name)),
                        'available_now' => 'In stock',
                    ));
            }

            $this->product_shop
                ->where('id_product', $id_product)
                ->update(array(
                    'price' => $this->is_default_product == 1 ? $price[0] : $original_price,
                    'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
                    'date_upd' => date('Y-m-d H:i:s'),
                    'id_category_default' => $category[0],
                ));

            $count = count($row);
            for ($i = 0; $i < $count; ++$i) {
                $id_product_attribute = $this->product_attribute
                    ->select('id_product_attribute')
                    ->where('reference', 'FORU_'.$sku[$i])
                    ->first()['id_product_attribute'];
                if (empty($id_product_attribute)) {
                    $this->creatAttribute($id_product, $sku, $original_price, $price, $original_weight, $weight, $type, $i);
                    continue;
                }
                $this->updateAttribute($id_product_attribute, $original_price, $price, $original_weight, $weight, $type, $i);
            }
        }
    }

    public function creatAttribute($id_product, $sku, $original_price, $price, $original_weight, $weight, $type, $i)
    {
        $id_product_attribute = $this->product_attribute
            ->create(array(
                'id_product' => $id_product,
                'reference' => 'FORU_'.$sku[$i],
                'price' => $price[$i] - ($this->is_default_product == 1 ? $price[0] : $original_price),
                'quantity' => 100,
                'weight' => $weight[$i] - ($this->is_default_product == 1 ? $weight[0] : $original_weight),
                'default_on' => null,
                'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
            ))->toArray()['id_product_attribute'];

        $this->product_attribute_shop
            ->create(array(
                'id_product' => $id_product,
                'id_product_attribute' => $id_product_attribute,
                'id_shop' => 1,
                'price' => $price[$i] - ($this->is_default_product == 1 ? $price[0] : $original_price),
                'weight' => $weight[$i] - ($this->is_default_product == 1 ? $weight[0] : $original_weight),
                'default_on' => null,
                'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
            ));
        $this->stock_available
            ->create(array(
                'id_product' => $id_product,
                'id_product_attribute' => $id_product_attribute,
                'id_shop' => 1,
                'id_shop_group' => 0,
                'quantity' => 99999,
                'out_of_stock' => 2,
            ));

        $this->updateType($type[$i], $id_product_attribute);
    }

    public function updateAttribute($id_product_attribute, $original_price, $price, $original_weight, $weight, $type, $i)
    {
        $this->product_attribute
            ->where('id_product_attribute', $id_product_attribute)
            ->update(array(
                'price' => $price[$i] - ($this->is_default_product == 1 ? $price[0] : $original_price),
                'weight' => $weight[$i] - ($this->is_default_product == 1 ? $weight[0] : $original_weight),
                'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
            ));

        $this->product_attribute_shop
            ->where('id_product_attribute', $id_product_attribute)
            ->update(array(
                'price' => $price[$i] - ($this->is_default_product == 1 ? $price[0] : $original_price),
                'weight' => $weight[$i] - ($this->is_default_product == 1 ? $weight[0] : $original_weight),
                'available_date' => date('Y-m-d H:i:s', strtotime('+10 year')),
            ));
        $this->updateType($type[$i], $id_product_attribute);
    }

    /*
     *
     * */
    public function refreshAttribute($id_product, $diff_original_price)
    {
        $all_id_product_attribute = $this->product_attribute
            ->select('id_product_attribute', 'price')
            ->where('id_product', $id_product)
            ->get()
            ->toArray();

        foreach ($all_id_product_attribute as $item) {
            $this->product_attribute
                ->where('id_product_attribute', $item['id_product_attribute'])
                ->update(array(
                    'price' => $item['price'] - $diff_original_price,
                ));
            $this->product_attribute_shop
                ->where('id_product_attribute', $item['id_product_attribute'])
                ->update(array(
                    'price' => $item['price'] - $diff_original_price,
                ));
        }
    }

    public function updateType($data, $id_product_attribute)
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

    public function handleDelete($data, $all_sku)
    {
        $exists = 0;
        $id_product = $all_sku['id_product']; //get id_product
        $database_sku = $this->product_attribute
            ->select('reference')
            ->where('id_product', $id_product)
            ->get();

        foreach ($database_sku as $item) {
            //检查sku是否存在，不存在删除
            foreach ($data[0] as $value) {
                if ($value[0] != 'Variant') {
                    continue;
                }
                if ('FORU_'.$value[1] == $item['reference']) {
                    $exists = 1;
                    break;
                }
                $exists = 0;
            }
            //不存在，删除属性
            if ($exists == 0) {
                //查询分类id
                $id_product_attribute = $this->product_attribute
                    ->select('id_product_attribute')
                    ->where('reference', $item['reference'])
                    ->first()['id_product_attribute'];

                //删除分类
                $this->product_attribute
                    ->where('id_product_attribute', $id_product_attribute)
                    ->delete();

                $this->product_attribute_shop
                    ->where('id_product_attribute', $id_product_attribute)
                    ->delete();
            }
        }
    }
}
