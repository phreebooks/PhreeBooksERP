<?php
/*
 * View for Contacts -> Settings -> Tools tab
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
 * @version    2.x Last Update: 2017-07-28
 * @filesource /lib/view/module/contacts/tabSettingsTools.php
 */

namespace bizuno;

$output['body'] .= "
<fieldset><legend>".$data['lang']['close_j9_title']."</legend>
    <p>".$data['lang']['close_j9_desc']."</p>
    <p>".$data['lang']['close_j9_label'].' '.html5('dateJ9Close', $data['fields']['dateJ9Close']).html5('btnJ9Close', $data['fields']['btnJ9Close']).'</p>
</fieldset>
<fieldset><legend>'.$data['lang']['sync_attach_title']."</legend>
    <p>".$data['lang']['sync_attach_desc']."</p>
    <p>".html5('btnSyncAttach', $data['fields']['btnSyncAttach']).'</p>
</fieldset>';
