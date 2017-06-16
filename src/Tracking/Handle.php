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

namespace Foru\Tracking;

use Foru\Model\Carrier;
use Foru\Model\CarrierLang;
use Foru\Model\OrderCarrier;
use Foru\Model\Orders;

class Handle
{
    protected $orders;
    protected $carrier;
    protected $order_carrier;
    protected $carrier_lang;

    public function __construct()
    {
        $this->orders = new Orders();
        $this->carrier = new Carrier();
        $this->order_carrier = new OrderCarrier();
        $this->carrier_lang = new CarrierLang();
    }

    public function get()
    {
        $filename = empty($filename = $_FILES['tracking']['tmp_name']) ? null : $filename;
        $value = array();
        $data = array();
        //获取临时文件名
        if (!empty($filename)) {
            $file = fopen($filename, 'r'); //打开临时文件
            while (!feof($file)) {
                $value[] = fgetcsv($file); //按行读取数据
            }
            fclose($file); //关闭文件
            $value_key = $value[0];
            $key_num = count($value_key); //元素个数
            unset($value[0]);
            $one = array();
            foreach ($value as $row) {
                //循环构建数组
                for ($i = 0; $i < $key_num; ++$i) {
                    $one[$value_key[$i]] = $row[$i];
                }
                $data[] = array(
                    'order_number' => $one['Order Number'],
                    'tracking_number' => $one['Traking Number'],
                    'tracking_carrier' => $one['Traking Carrier'],
                );
            }

            return $data;
        }
        throw new \Exception('File upload error');
    }

    public function database($data)
    {
        //All delivery methods
        $carrier = $this->carrier
            ->select('id_carrier', 'name')
            ->get()
            ->toArray();
        foreach ($data as $row) {
            //Remove empty items
//            $row['order_number'] = explode('_', $row['order_number'])[0];
            if (!empty($oreder_id = htmlspecialchars($row['order_number'], ENT_QUOTES)) &&
                !empty($tacking_number = htmlspecialchars($row['tracking_number'], ENT_QUOTES)) &&
                !empty($express_type = htmlspecialchars($row['tracking_carrier'], ENT_QUOTES))
            ) {
                if (!is_numeric($oreder_id)) {
                    continue;
                }
                //Deal with express type
                foreach ($carrier as $carrier_row) {
                    if ($express_type == $carrier_row['name']) {
                        $carrier_id = $carrier_row['id_carrier'];
                        break;
                    }
                    $carrier_id = null;
                }

                if ($carrier_id == null) {
                    //New delivery mode
                    $return = $this->carrier
                        ->create(array(
                            'id_reference' => ($this->carrier->count()) + 1,
                            'name' => $express_type,
                            'active' => 1,
                        ));

                    $carrier_id = $return->id_carrier;

                    $this->carrier_lang
                        ->create(array(
                            'id_carrier' => $carrier_id,
                            'id_lang' => 1,
                            'delay' => 'Delivery next day!',
                        ));
                    $this->carrier_lang
                        ->create(array(
                            'id_carrier' => $carrier_id,
                            'id_lang' => 2,
                            'delay' => 'Delivery next day!',
                        ));
                }
                //insert order value
                $this->orders
                    ->where('id_order', $oreder_id)
                    ->update(array(
                        'id_carrier' => $carrier_id,
                        'shipping_number' => $tacking_number,
                    ));
                $this->order_carrier
                    ->where('id_order', $oreder_id)
                    ->update(array(
                        'id_carrier' => $carrier_id,
                        'tracking_number' => $tacking_number,
                    ));
            }
        }
    }
}
