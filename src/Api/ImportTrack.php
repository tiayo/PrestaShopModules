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

use Foru\Tracking\Handle;

class ImportTrack
{
    public function __construct()
    {
        set_time_limit(0);
        ignore_user_abort(true);
    }

    public function handle()
    {
        $array = array();
        $data = array();
        Verification::verification();
        $value = json_decode(\ToolsCore::file_get_contents('php://input'), true);
        if (empty($value) || !is_array($value)) {
            $this->response('data must be a array()!');
        }

        //delete order number suffix
        foreach ($value as $item) {
            $data[] = $item;
        }
        //send to handle
        $handle = new Handle();
        try {
            $handle->database($data);
        } catch (\Exception $e) {
            $this->response($e->getMessage(), $e->getCode());
        }

        $this->response('success!', 200);
    }

    public function response($info, $code = 403)
    {
        http_response_code($code);
        echo $info;
        exit();
    }
}
