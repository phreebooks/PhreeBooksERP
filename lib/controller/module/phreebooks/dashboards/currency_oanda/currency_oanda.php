<?php
/*
 * PhreeBooks dashboard - Currency Converter using XE
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
 * @version    3.x Last Update: 2018-05-11
 * @filesource /lib/controller/module/phreebooks/dashboards/currency_oanda/currency_oanda.php
 */

namespace bizuno;

define('DASHBOARD_CURRENCY_OANDA_VERSION','2.0');

class currency_oanda
{
    public $moduleID = 'phreebooks';
    public $methodDir= 'dashboards';
    public $code     = 'currency_oanda';
    public $category = 'general_ledger';
    public $noSettings= true;
    
    function __construct($settings=[])
    {
        $this->security= getUserCache('security', 'j2_mgr', false, 0);
        $defaults      = ['users'=>'-1','roles'=>'-1'];
        $this->settings= array_replace_recursive($defaults, $settings);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function settingsStructure()
    {
        return [
            'users' => ['label'=>lang('users'), 'position'=>'after','values'=>listUsers(),'attr'=>['type'=>'select','value'=>$this->settings['users'],'size'=>10,'multiple'=>'multiple']],
            'roles' => ['label'=>lang('groups'),'position'=>'after','values'=>listRoles(),'attr'=>['type'=>'select','value'=>$this->settings['roles'],'size'=>10,'multiple'=>'multiple']]];
    }

    public function render()
    {
        $defISO= getUserCache('profile','currency');
        $ISOs  = getModuleCache('phreebooks', 'currency', 'iso', false, []);
        $cVals = [];
        foreach ($ISOs as $code => $iso) {
            if ($defISO == $code) { continue; }
            $cVals[$code] = $iso['title'];
        }
//      $cVals = ['EUR'=>'Euro', 'ZAR'=>'South Africa Rand'];
        $lang  = substr(getUserCache('profile', 'language', false, 'en_US'), 0, 2);
        $action = BIZUNO_AJAX."&p=phreebooks/currency/setExcRate";
        $html  = '<div><div id="oanda_ecc">
<span style="color:#000; text-decoration:none; font-size:9px; float:left;">Currency Converter <a id="oanda_cc_link" style="color:#000; font-size:9px;" href="https://www.oanda.com/currency/converter/">by OANDA</a></span>
<script src="https://www.oanda.com/embedded/converter/get/b2FuZGFlY2N1c2VyLy9kZWZhdWx0/?lang='.$lang.'"></script></div>';
        if (sizeof($cVals)) {
            $xRate = ['attr'=>['value'=>'']];
            $xSel  = ['values'=>viewKeyDropdown($cVals), 'attr'=>['type'=>'select']];
            $btnUpd= ['attr'=>['type'=>'button','value'=>lang('update')],'events'=>['onClick'=>"jq('#oandaForm').submit();"]];
            $js    = "ajaxForm('oandaForm');\n";
            $html .= '<form id="oandaForm" action="'.$action.'">';
            $html .= "<p>".$this->lang['update_desc']."</p>";
            $html .= "<p>1 $defISO = ".html5('excRate', $xRate).' '.html5('excISO', $xSel).'<br />'.html5('', $btnUpd).'</p></div>';
            $html .= htmlJS($js);            
        } else {
            $html .= '<br />'.$this->lang['no_multi_langs'];
        }
        return $html;
    }
}
