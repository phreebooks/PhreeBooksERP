<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
//  Path: /modules/phreedom/pages/admin/template_tab_vendor.php
//

?>
<div title="<?php echo MENU_HEADING_EMAIL;?>" id="tab_email">
<table class="ui-widget" style="border-style:none;width:100%">
 <thead class="ui-widget-header">
  <tr><th colspan="2"><?php echo MENU_HEADING_EMAIL; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo CD_12_01_DESC; ?></td>
    <td><?php echo html_pull_down_menu('email_transport', $sel_transport, $_POST['email_transport'] ? $_POST['email_transport'] : EMAIL_TRANSPORT, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_02_DESC; ?></td>
    <td><?php echo html_pull_down_menu('email_linefeed', $sel_linefeed, $_POST['email_linefeed'] ? $_POST['email_linefeed'] : EMAIL_LINEFEED, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_04_DESC; ?></td>
    <td><?php echo html_pull_down_menu('email_use_html', $sel_yes_no, $_POST['email_use_html'] ? $_POST['email_use_html'] : EMAIL_USE_HTML, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_10_DESC; ?></td>
    <td><?php echo html_input_field('store_owner_email_address', $_POST['store_owner_email_address'] ? $_POST['store_owner_email_address'] : STORE_OWNER_EMAIL_ADDRESS, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_11_DESC; ?></td>
    <td><?php echo html_input_field('email_from', $_POST['email_from'] ? $_POST['email_from'] : EMAIL_FROM, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_15_DESC; ?></td>
    <td><?php echo html_pull_down_menu('admin_extra_email_format', $sel_format, $_POST['admin_extra_email_format'] ? $_POST['admin_extra_email_format'] : ADMIN_EXTRA_EMAIL_FORMAT, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_70_DESC; ?></td>
    <td><?php echo html_input_field('email_smtpauth_mailbox', $_POST['email_smtpauth_mailbox'] ? $_POST['email_smtpauth_mailbox'] : EMAIL_SMTPAUTH_MAILBOX, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_71_DESC; ?></td>
    <td><?php echo html_input_field('email_smtpauth_password', $_POST['email_smtpauth_password'] ? $_POST['email_smtpauth_password'] : EMAIL_SMTPAUTH_PASSWORD, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_72_DESC; ?></td>
    <td><?php echo html_input_field('email_smtpauth_mail_server', $_POST['email_smtpauth_mail_server'] ? $_POST['email_smtpauth_mail_server'] : EMAIL_SMTPAUTH_MAIL_SERVER, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_73_DESC; ?></td>
    <td><?php echo html_input_field('email_smtpauth_mail_server_port', $_POST['email_smtpauth_mail_server_port'] ? $_POST['email_smtpauth_mail_server_port'] : EMAIL_SMTPAUTH_MAIL_SERVER_PORT, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_12_74_DESC; ?></td>
    <td><?php echo html_input_field('currencies_translations', $_POST['currencies_translations'] ? $_POST['currencies_translations'] : CURRENCIES_TRANSLATIONS, ''); ?></td>
  </tr>
 </tbody>
</table>
</div>
