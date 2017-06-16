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

require_once dirname(__FILE__).'/classes/Tools.php';
require_once dirname(__FILE__).'/modules/forudropshipping/vendor/autoload.php';
require_once dirname(__FILE__).'/config/settings.inc.php';
require_once dirname(__FILE__).'/modules/forudropshipping/database/database.php';

$action = ToolsCore::getValue('action');

switch ($action) {
    case 'forudropshipping-push-products':
        api_product();
        break;
    case 'forudropshipping-push-tracks':
        api_track();
        break;
    case 'forudropshipping-categories':
        api_category();
        break;
}

function api_product()
{
    $import = new Foru\Api\ImportProduct();
    $import->handle();
}

function api_track()
{
    $import = new Foru\Api\ImportTrack();
    $import->handle();
}

function api_category()
{
    $categorys = new \Foru\Product\Category();
    echo json_encode($categorys->verification());
}
