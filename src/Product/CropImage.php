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

class CropImage
{
    public function crop($path, $image, $xx, $yy, $filename, $name, $type)
    {
        $imgstream = \ToolsCore::file_get_contents($image);
        $im = imagecreatefromstring($imgstream);
        $x = imagesx($im); //获取图片的宽
        $y = imagesy($im); //获取图片的高

        if ($x > $y) {
            //图片宽大于高
            $sx = abs(($y - $x) / 2);
            $sy = 0;
            $thumbw = $y;
            $thumbh = $y;
        } else {
            //图片高大于等于宽
            $sy = abs(($x - $y) / 2.5);
            $sx = 0;
            $thumbw = $x;
            $thumbh = $x;
        }
        if (function_exists('imagecreatetruecolor')) {
            $dim = imagecreatetruecolor($yy, $xx); // 创建目标图gd2
        } else {
            $dim = imagecreate($yy, $xx); // 创建目标图gd1
        }
        imageCopyreSampled($dim, $im, 0, 0, $sx, $sy, $yy, $xx, $thumbw, $thumbh);
// 保存
        imagejpeg($dim, $path.$filename.'-'.$name.$type);
        imagedestroy($dim);
    }
}
