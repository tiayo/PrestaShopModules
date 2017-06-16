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

use Foru\Export\Handle;
use Foru\Model\Configuration;
use Foru\Model\Orders;
use Requests;

class Monitor
{
    protected $orders;
    protected $token;
    protected $configuration;
    const URL = 'https://www.forudropshipping.com/api/orders/';

    public function __construct()
    {
        $this->orders = new Orders();
        $this->configuration = new Configuration();
        $this->token = $this->configuration
            ->select('value')
            ->where('name', 'api_key')
            ->first()['value'];
    }

    public function add($order_id)
    {
        $result = $this->orders
            ->where('id_order', $order_id)
            ->first();

        //get data
        $handle = new Handle();
        $data = $handle->handle($order_id, $result);

        if (empty($data)) {
            return;
        }

        //send api
        Requests::post(
            self::URL,
            array(
                'Accept' => 'application/json',
                'Token' => $this->token,
            ),
            array(
                'orders' => $data,
            ),
            array(
                'verify' => false,
            )
        );
    }

    public function delete($order_id)
    {
        Requests::delete(
            self::URL.$order_id,
            array(
                    'Accept' => 'application/json',
                    'Token' => $this->token,
                ),
            array(
                'verify' => false,
                )
        );
    }
}
