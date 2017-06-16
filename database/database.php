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

namespace Conf;

use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule();
/*
 * 多表切换
 * */
$capsule->addConnection(array(
    'driver' => 'mysql',
    'host' => constant('_DB_SERVER_'),
    'database' => constant('_DB_NAME_'),
    'username' => constant('_DB_USER_'),
    'password' => constant('_DB_PASSWD_'),
    'charset' => 'utf8',
    'collation' => 'utf8_general_ci',
    'prefix' => constant('_DB_PREFIX_'),
), 'mysql');
// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule->setEventDispatcher(new Dispatcher(new Container()));
// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();
// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
