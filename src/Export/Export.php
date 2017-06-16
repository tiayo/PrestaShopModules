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

class Export
{
    public function export($data, $filename = null)
    {
        $str = "Order Number,SKU,Product Name,Quantity,First Name,Last Name,Address,City,State,Zip Code,Country,Phone Number,Email\n";
        foreach ($data as $data_row) {
            if (empty($data_row)) {
                continue;
            }
            if (is_array($data_row) || is_object($data_row)) {
                foreach ($data_row as $key => $row) {
                    $str .= '"'.$row['order_number'].'"'.','.
                        '"'.$row['sku'].'"'.','.
                        '"'.$row['product_name'].'"'.','.
                        '"'.$row['quantity'].'"'.','.
                        '"'.$row['first_name'].'"'.','.
                        '"'.$row['last_name'].'"'.','.
                        '"'.$row['street_1'].'"'.','.
                        '"'.$row['city'].'"'.','.
                        '"'.$row['state'].'"'.','.
                        '"'.$row['zip'].'"'.','.
                        '"'.$row['country'].'"'.','.
                        '"'.$row['phone'].'"'.','.
                        '"'.$row['email'].'"'.','
                        ."\r\n";
                }
            }
        }
        $filename = $filename == null ? date('Ymd').'.csv' : $filename.'.csv';
        $this->exportCsv($filename, $str);
    }

    public function exportCsv($filename, $str)
    {
        header('Content-type:text/csv');
        header('Content-Disposition:attachment;filename='.$filename);
        header('Cache-Control:must-revalidate,post-check=0,pre-check=0');
        header('Expires:0');
        header('Pragma:public');
        echo $str;
        exit();
    }
}
