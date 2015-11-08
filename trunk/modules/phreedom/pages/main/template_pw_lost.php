<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |

// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /modules/phreedom/pages/pw_lost/template_main.php
//
echo html_form('pw_lost', FILENAME_DEFAULT, 'action=SendLostPassWord') . chr(10);
if(is_object($messageStack)) echo $messageStack->output();
?>
<div style="margin-left:25%;margin-right:25%;margin-top:50px;">
<table class="ui-widget" style="border-collapse:collapse;width:100%">
 <thead class="ui-widget-header">
   <tr height="70"><th colspan="2" align="right"><img src="modules/phreedom/images/phreesoft_logo.png" alt="Phreedom Business Toolkit" height="50" /></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr><td colspan="2"><h2><?php echo TEXT_RESEND_PASSWORD; ?></h2></td></tr>
  <tr>
   <td nowrap="nowrap">&nbsp;&nbsp;<?php echo TEXT_EMAIL_ADDRESS . ': '; ?></td>
   <td><?php echo html_input_field('admin_email', $basis->cInfo->admin_email, 'size="60"'); ?></td>
  </tr>
  <tr>
   <td nowrap="nowrap">&nbsp;&nbsp;<?php echo sprintf(TEXT_SELECT_ARGS, TEXT_COMPANY); ?></td>
   <td><?php echo html_pull_down_menu('company', $basis->user->companies, $basis->cInfo->company_index, '', true); ?></td>
  </tr>
  <tr><td colspan="2" align="right"><?php echo html_submit_field('submit', TEXT_RESEND_PASSWORD) . '&nbsp;&nbsp;'; ?></td></tr>
 </tbody>
</table>
</div>
</form>
