<?php
/*
 * Bizuno dashboard - Daily tip
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-10-30
 * @filesource /lib/controller/module/bizuno/dashboards/daily_tip/daily_tip.php
 */

namespace bizuno;

define('DASHBOARD_DAILY_TIP_VERSION','3.0');

class daily_tip
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'daily_tip';
    public $category = 'bizuno';
    public $noSettings= true;
    public $noCollapse= true;

    function __construct() 
    {
        $this->security= 1;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render()
    {
        global $io;
        $resp = $io->cURLGet("https://www.bizuno.com","p=bizuno/portal/getTip",'get');
        msgDebug("\nReceived back from cURL: ".print_r($resp, true));
        $tip  = json_decode($resp, true);
        $html = '<div>';
        $html.= '  <div id="'.$this->code.'_attr" style="display:none"><form id="'.$this->code.'Form" action=""></form></div>';
        $html.= '  <div style="float:left">'.html5('', ['icon'=>'tip']).'</div><div>'.($tip['tip'] ? $tip['tip'] : lang('no_results')).'</div>';
        $html.= '</div><div style="min-height:4px;"> </div>'."\n";
        return $html;
    }
}
