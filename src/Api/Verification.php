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

use Foru\Model\Configuration;

class Verification
{
    public static function verification()
    {
        $configuration = new Configuration();
        $response = new ImportProduct();
        $token = $configuration
            ->select('value')
            ->where('name', 'api_key')
            ->first()['value'];

        $get_token = $_SERVER['HTTP_TOKEN'];

        if ($token != $get_token) {
            $response->response('Token error!');
        }
    }
}
