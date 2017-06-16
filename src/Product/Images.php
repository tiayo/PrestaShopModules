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

use Foru\Model\Image;
use Foru\Model\ImageLang;
use Foru\Model\ImageShop;
use Foru\Model\ImageType;
use Requests;

class Images
{
    protected $image;
    protected $image_lang;
    protected $image_shop;
    protected $image_type;

    public function __construct()
    {
        $this->image = new Image();
        $this->image_lang = new ImageLang();
        $this->image_shop = new ImageShop();
        $this->image_type = new ImageType();
    }

    public function images($product_image, $product_id, $schedule = 0, $average = 0)
    {
        $responses = $this->load($product_image, $schedule, $average);  //load product picture
        foreach ($product_image as $key => $image) {
            $count = $this->image
                ->where('id_product', $product_id)
                ->count();

            $id_image = $this->image
                ->create(array(
                    'id_product' => $product_id,
                    'position' => ($count + 1),
                    'cover' => $count == 0 ? 1 : null,
                ))->toArray()['id_image'];

            $tmp = explode('.', $image);
            $add_on = '.'.end($tmp);
            $filename = $id_image.$add_on;
            /*
             * Structured storage path
             * */
            $str = null;
            if (\ToolsCore::strlen($id_image) != 1) {
                $id_image_split = str_split($id_image, 1);
                foreach ($id_image_split as $row) {
                    $str .= '/'.$row;
                }
                $str = $str.'/';
            } else {
                $str = '/'.$id_image.'/';
            }
            $path = dirname(dirname(__FILE__)).'/../../../img/p'.$str;
            if (!file_exists($path)) {
                mkdir($path, 0755, true);
            }
            /*
             * write remote images
             * */
            $images_dir = $path.$filename;
            $fp = fopen($images_dir, 'wb');
            fwrite($fp, $responses[$key]->body);
            fclose($fp);
            /*
             * crop picture
             * */
            $crop_image = new CropImage();
            $image_type = $this->image_type
                ->select('name', 'width', 'height')
                ->where('products', 1)
                ->get()->toArray();

            foreach ($image_type as $image_type_item) {
                $crop_image->crop($path, $images_dir, $image_type_item['width'], $image_type_item['height'], $id_image, $image_type_item['name'], $add_on);
            }
            $this->database($product_id, $id_image);
        }
    }

    public function load($product_image, $schedule, $average)
    {
        $requests = array();
        foreach ($product_image as $key => $image) {
            $requests[$key] = array('url' => $image);
        }
        //Image download timeout
        $options = array(
            'timeout' => 30,
            'connect_timeout' => 30,
        );
        //Write progress
        $this->schedule($schedule + floor($average), 'download_images');
        //Download images
        $responses = Requests::request_multiple($requests, $options);

        return $responses;
    }

    public function database($product_id, $id_image)
    {
        for ($i = 1; $i <= 2; ++$i) {
            $this->image_lang
                ->create(array(
                    'id_image' => $id_image,
                    'id_lang' => $i,
                ));
        }

        $this->image_shop
            ->create(array(
                'id_product' => $product_id,
                'id_image' => $id_image,
                'id_shop' => 1,
                'cover' => $this->image->select('cover')->where('id_image', $id_image)->first()->toArray()['cover'],
            ));
    }

    public function schedule($schedule, $prompt)
    {
        $record = fopen(dirname(__FILE__).'/record.txt', 'w');
        fwrite($record, $schedule.'-'.$prompt);
        fclose($record);
    }
}
