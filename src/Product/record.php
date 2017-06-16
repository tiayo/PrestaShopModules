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

class Record
{
    public function record()
    {
        header('Content-type: application/json');
        if (file_exists(dirname(__FILE__).'/record.txt')) {
            $record = fopen(dirname(__FILE__).'/record.txt', 'r');
            $body = fread($record, '200');
            fclose($record);
            $explode = explode('-', $body);
            $body = array();
            $prompt = array(
                '1' => "Download the product picture,don't close the browser and the network !",
                '2' => 'The progress of the import will be slightly delayed !',
                '3' => 'Speed depends on the network, please be patient for some time !',
                '4' => 'If the product are particularly large, it will take more time !',
                '5' => 'If you see the information there is a switch, it means that the import work is in progress !',
            );
            $body['value'] = $explode[0];
            $body['prompt'] = $explode[1] == 'download_images' ? $prompt[array_rand($prompt, 1)] : $explode[1];

            return json_encode($body);
        }
        $this->newRecord();
    }

    public function newRecord()
    {
        $record = fopen(dirname(__FILE__).'/record.txt', 'w');
        $txt = 0;
        fwrite($record, $txt.'-Loading a new product');
        fclose($record);

        return json_encode($txt);
    }
}

$h = new Record();
echo $h->record();
