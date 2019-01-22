<?php
/*
 * Bizuno dashboard - Subscribe 
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
 * @version    3.x Last Update: 2018-09-05
 * @filesource /lib/controller/module/bizuno/dashboards/subscribe/subscribe.php
 */

namespace bizuno;

define('DASHBOARD_SUBSCRIBE_VERSION','1.0');

class subscribe
{
    public $moduleID  = 'bizuno';
    public $methodDir = 'dashboards';
    public $code      = 'subscribe';
    public $noSettings= true;
    public $noCollapse= true;
    public $noClose   = true;

    function __construct()
    {
        $this->security= getUserCache('profile', 'biz_id', false, 0) ? 0 : 1; // only for the portal to log in
        $this->hidden  = true;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render($settings=[])
    {
        $data = ['btnSubscribe'=>['attr'=>['type'=>'button','value'=>$this->lang['bizuno_subscribe']],'styles'=>['cursor'=>'pointer'],
                'events'=>['onClick'=>"window.location='https://www.phreesoft.com/product-category/store-bizuno/'"]]];
        return '<div><!-- subscribe section --><p>'.$this->lang['instructions'].'</p><div style="text-align:center">'.html5('btnSubscribe', $data['btnSubscribe']).'</div><div>&nbsp;</div></div>';
    }
}
