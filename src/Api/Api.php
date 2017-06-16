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
use Requests;

class Api
{
    protected $configuration;
    protected $value;
    protected $create_token;

    public function __construct()
    {
        $this->configuration = new Configuration();
        $this->create_token = 'https://192.168.10.236:8888/api/password-grant/';
    }

    public function view()
    {
        $this->value = $this->configuration
            ->select('value')
            ->where('name', 'api_key')
            ->first();

        if (empty($this->value['value'])) {
            return null;
        }

        return $this->value['value'];
    }

    public function updateKey()
    {
        //获取key
        try{
            $option_value = $this->createToken();
        } catch (\Exception $e) {
            return false;
        }

        //查询是否存在
        $count = $this->value = $this->configuration
            ->where('name', 'api_key')
            ->count();

        if ($count >= 1) {
            $this->configuration
                ->where('name', 'api_key')
                ->update(
                    array(
                        'value' => $option_value ?: null,
                        'date_upd' => date('Y-m-d H:i:s'),
                    )
                );
        } else {
            $this->configuration->create(
                array(
                    'value' => $option_value ?: null,
                    'name' => 'api_key',
                    'date_add' => date('Y-m-d H:i:s'),
                    'date_upd' => date('Y-m-d H:i:s'),
                )
            );
        }


        return true;
    }

    public function saveKey()
    {
        $this->configuration->create(
            array(
                'value' => 'Entry token',
                'name' => 'api_key',
                'date_add' => date('Y-m-d H:i:s'),
                'date_upd' => date('Y-m-d H:i:s'),
            )
        );
    }

    /**
     * 请求创建店铺
     *
     * @return string
     */
    public function createToken()
    {
        $username = \Tools::getValue('username');
        $password = \Tools::getValue('password');

        $result = Requests::post(
            $this->create_token,
            array(
                'Accept' => 'application/json',
                'Authorization' => "Basic " . base64_encode($username . ':' . $password),
            ),
            array(
                'auth' => array($username, $password),
                'name' => $this->configuration->select('value')->where('name', 'PS_SHOP_NAME')->first()['value'],
                'site' => $this->format_url($this->configuration->select('value')->where('name', 'PS_SHOP_DOMAIN')->first()['value'], false),
                'platform' => 'PrestaShop',
            ),
            array(
                'verify' => false,
            )
        );

        //状态码非200抛错
        if ($result->status_code !=200) {
            throw new \Exception('error');
        }

        return json_decode($result->body, true)['token'];
    }


    /**
     *	规范化 URL
     *	判断是否使用 HTTPS 链接，当是 HTTPS 访问时候自动添加
     *	自动添加链接前面的 http://
     *	$slash 是判断是否要后面添加斜杠
     */
    public function format_url($url, $slash)
    {

        if (substr($url,0,4) != 'http' && substr($url,0,5) != 'https') {
            @$if_https = $_SERVER['HTTPS'];	//这样就不会有错误提示
            if ($if_https) {	//如果是使用 https 访问的话就添加 https
                $url='https://'.$url;
            } else {
                $url='http://'.$url;
            }
        }
        if ($slash) {
            $url = rtrim($url,'/').'/';
        }
        return $url;
    }
}
