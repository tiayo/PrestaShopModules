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

use Foru\Api\Verification;
use Foru\Model\Categorylang;

class Category
{
    protected $category;
    protected $category_lang;
    protected $token;

    public function __construct()
    {
        $this->category = new \Foru\Model\Category();
        $this->category_lang = new Categorylang();
    }

    /**
     * 验证token.
     *
     * @return array()
     */
    public function verification()
    {
        Verification::verification();

        return $this->get('api');
    }

    /*
     *Read data from database
     * */
    public function get($option = null)
    {
        $category = array();
        $all_category = $this->category
            ->select('id_category', 'id_parent', 'level_depth')
            ->where('active', 1)
            ->get()
            ->toArray();
        foreach ($all_category as $row) {
            $name = $this->category_lang
                ->select('name')
                ->where('id_category', $row['id_category'])
                ->first()['name'];
            $row['name'] = $name;
            $category[] = $row;
        }

        if ($option == 'api') {
            return $this->api($category);
        }

        return $this->tree($category);
    }

    /*
     * Create array tree
     * */
    public function tree($items)
    {
        $childs = array();

        foreach ($items as &$item) {
            $childs[$item['id_parent']][] = &$item;
        }

        unset($item);

        foreach ($items as &$item) {
            if (isset($childs[$item['id_category']])) {
                $item['childs'] = $childs[$item['id_category']];
            }
        }

        return $this->procHtml($childs[0]);
    }

    /*
     * Generate html
     * */
    public function procHtml($tree)
    {
        $html = '';
        foreach ($tree as $t) {
            $t['childs'] = isset($t['childs']) ? $t['childs'] : null; //No report index does not exist
            if ($t['childs'] == '') {
                $html .= "<li><option value='{$t['id_category']}'>{$this->dash($t['level_depth'])}{$t['name']}</option></li>";
            } else {
                $html .= "<li><option value='{$t['id_category']}'>{$this->dash($t['level_depth'])}{$t['name']}</option>";
                $html .= $this->procHtml($t['childs']);
                $html = $html.'</li>';
            }
        }

        return $html ? '<ul>'.$html.'</ul>' : $html;
    }

    public function dash($key)
    {
        $str = null;
        for ($i = 2; $i < $key; ++$i) {
            $str .= '—';
        }

        return $str;
    }

    public function api($categary)
    {
        $result = null;
        foreach ($categary as $key => $item) {
            $result[$key]['id'] = $item['id_category'];
            $result[$key]['pid'] = $item['id_parent'];
            $result[$key]['name'] = $item['name'];
        }

        return $result;
    }
}
