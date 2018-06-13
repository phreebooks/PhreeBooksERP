<?php
/*
 * View for user profile settings
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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2017-01-14
 * @filesource /lib/view/module/bizuno/tabProfileBizuno.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset><legend>".lang('general')."</legend>".
    html5('title', $viewData['fields']['title'])."<br />".
    html5('email', $viewData['fields']['email'])."
</fieldset>
<fieldset><legend>".'Google Interface'."</legend>".
    html5('gmail', $viewData['fields']['gmail'])."<br />".
    html5('gzone', $viewData['fields']['gzone'])."
</fieldset>
<fieldset><legend>".lang('password_lost')."</legend>".
    html5('password',        $viewData['fields']['password'])."<br />".
    html5('password_new',    $viewData['fields']['password_new'])."<br />".
    html5('password_confirm',$viewData['fields']['password_confirm'])."
</fieldset>
<fieldset><legend>".lang('profile')."</legend>".
    html5('theme', $viewData['fields']['theme']) ."<br />".
    html5('colors',$viewData['fields']['colors'])."<br />".
    html5('menu',  $viewData['fields']['menu'])  ."<br />".
    html5('cols',  $viewData['fields']['cols'])."
</fieldset>\n";
