<?php
/*
 * Bizuno dsahboard - Quick start with list of suggestions on getting going form new installs
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-02-24
 * @filesource /lib/controller/module/bizuno/dashboards/quick_start/quick_start.php
 */

namespace bizuno;

class quick_start
{
    public $moduleID   = 'bizuno';
    public $methodDir  = 'dashboards';
    public $code       = 'quick_start';
    public $category   = 'general';
    public $noSettings = true;
    public $noCollapse = true;

    function __construct()
    {
        $this->security= getUserCache('security', 'profile', 0);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render(&$layout=[])
    {
        $layout = array_merge_recursive($layout, ['divs'=>['body'=>['order'=>50,'type'=>'html','html'=>$this->lang['msg_welcome']]]]);
    }
}
