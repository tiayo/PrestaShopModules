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

use Foru\Model\Orders;
use Foru\Model\OrderStateLang;

class All
{
    protected $orders;
    protected $order_state_lang;
    protected $handle;

    public function __construct()
    {
        $this->orders = new Orders();
        $this->order_state_lang = new OrderStateLang();
        $this->handle = new Handle();
    }

    public function all()
    {
        $result = $this->orders
            ->get()
            ->toArray();

        $array = array();
        $data = array();
        foreach ($result as $row) {
            $template = $this->order_state_lang
                ->select('template')
                ->where('id_order_state', $row['current_state'])
                ->first()
                ->toArray();
            if ($template['template'] == 'payment') {
                $array[] = $row;
            }
        }
        foreach ($array as $array_row) {
            $data[] = $this->handle->handle($array_row['id_order'], $array_row);
        }

        return empty($data) ? false : $data;
    }
}
