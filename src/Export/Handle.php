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

namespace Foru\Export;

use Foru\Model\Address;
use Foru\Model\CountryLang;
use Foru\Model\Customer;
use Foru\Model\OrderDetail;
use Foru\Model\Product;
use Foru\Model\ProductAttribute;
use Foru\Model\State;

class Handle
{
    protected $order_detail;
    protected $customer;
    protected $address;
    protected $country;
    protected $state;
    protected $product;
    protected $product_attribute;

    public function __construct()
    {
        $this->order_detail = new OrderDetail();
        $this->customer = new Customer();
        $this->address = new Address();
        $this->country = new CountryLang();
        $this->state = new State();
        $this->product = new Product();
        $this->product_attribute = new ProductAttribute();
    }

    public function handle($id_order, $array_row)
    {
        $line_items = $this->order_detail
            ->select('product_name', 'product_quantity', 'product_id', 'product_attribute_id')
            ->where('id_order', $id_order)
            ->get()
            ->toArray();

        $customer = $this->customer
            ->select('firstname', 'lastname', 'email')
            ->where('id_customer', $array_row['id_customer'])
            ->first()
            ->toArray();

        $address = $this->address
            ->select('id_country', 'id_state', 'address1', 'address2', 'firstname', 'lastname', 'city', 'phone', 'phone_mobile', 'postcode')
            ->where('id_customer', $array_row['id_customer'])
            ->where('id_address', $array_row['id_address_delivery'])
            ->first();

        $country = $this->country
            ->select('name')
            ->where('id_country', $address['id_country'])
            ->first()
            ->toArray();

        $state = $this->state
            ->select('name')
            ->where('id_state', $address['id_state'])
            ->first()
            ->toArray();
        $line_items_result = null;
        $num = 1;

        foreach ($line_items as $row) {
            //获取产品sku
            $sku = $this->product_attribute
                ->select('reference')
                ->where('id_product_attribute', $row['product_attribute_id'])
                ->first()['reference'];

            //当产品没有属性时
            if (empty($sku)) {
                $sku = $this->product
                    ->select('reference')
                    ->where('id_product', $row['product_id'])
                    ->first()['reference'];
            }

            //判断是否为本工厂商品
            $sku = explode('_', $sku);
            if ($sku[0] != 'FORU') {
                continue;
            }

            //返回数据
            $line_items_result[] = array(
                'order_number' => $id_order,
                'sku' => $sku[1],
                'product_name' => $row['product_name'],
                'quantity' => $row['product_quantity'],
                'first_name' => $address['firstname'],
                'last_name' => $address['lastname'],
                'street_1' => $address['address1']."\r".$address['address2'],
                'city' => $address['city'],
                'state' => $state['name'],
                'zip' => $address['postcode'],
                'country' => $country['name'],
                'phone' => $address['phone_mobile'],
                'email' => $customer['email'],
            );

            ++$num;
        }

        return $line_items_result;
    }
}
