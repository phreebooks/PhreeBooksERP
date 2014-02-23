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
//  Path: /modules/phreedom/pages/admin/template_tab_config.php
//

?>
<div title="<?php echo MENU_HEADING_CONFIG;?>" id="tab_config">
<table class="ui-widget" style="border-style:none;width:100%">
 <thead class="ui-widget-header">
 <tr><th colspan="2"><?php echo MENU_HEADING_CONFIG; ?></th></tr>
 </thead>
 <tbody class="ui-widget-content">
  <tr>
    <td><?php echo CD_00_01_DESC; ?></td>
    <td><?php echo html_input_field('date_format', $_POST['date_format'] ? $_POST['date_format'] : DATE_FORMAT, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_00_02_DESC; ?></td>
    <td><?php echo html_input_field('date_delimiter', $_POST['date_delimiter'] ? $_POST['date_delimiter'] : DATE_DELIMITER, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_00_03_DESC; ?></td>
    <td><?php echo html_input_field('date_time_format', $_POST['date_time_format'] ? $_POST['date_time_format'] : DATE_TIME_FORMAT, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_01_30_DESC; ?></td>
    <td><?php echo html_pull_down_menu('enable_encryption', $sel_yes_no, $_POST['enable_encryption'] ? $_POST['enable_encryption'] : ENABLE_ENCRYPTION, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_07_17_DESC; ?></td>
    <td><?php echo html_input_field('entry_password_min_length', $_POST['entry_password_min_length'] ? $_POST['entry_password_min_length'] : ENTRY_PASSWORD_MIN_LENGTH, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_08_01_DESC; ?></td>
    <td><?php echo html_input_field('max_display_search_results', $_POST['max_display_search_results'] ? $_POST['max_display_search_results'] : MAX_DISPLAY_SEARCH_RESULTS, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_08_10_DESC; ?></td>
    <td><?php echo html_input_field('limit_history_results', $_POST['limit_history_results'] ? $_POST['limit_history_results'] : LIMIT_HISTORY_RESULTS, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_08_05_DESC; ?></td>
    <td><?php echo html_pull_down_menu('hide_success_messages', $sel_yes_no, $_POST['hide_success_messages'] ? $_POST['hide_success_messages'] : HIDE_SUCCESS_MESSAGES, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_15_01_DESC; ?></td>
    <td><?php echo html_input_field('session_timeout_admin', $_POST['session_timeout_admin'] ? $_POST['session_timeout_admin'] : SESSION_TIMEOUT_ADMIN, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_15_05_DESC; ?></td>
    <td><?php echo html_pull_down_menu('session_auto_refresh', $sel_yes_no, $_POST['session_auto_refresh'] ? $_POST['session_auto_refresh'] : SESSION_AUTO_REFRESH, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_09_01_DESC; ?></td>
    <td><?php echo html_pull_down_menu('ie_rw_export_preference', $sel_ie_method, $_POST['ie_rw_export_preference'] ? $_POST['ie_rw_export_preference'] : IE_RW_EXPORT_PREFERENCE, ''); ?></td>
  </tr>

  <tr><th colspan="2" class="ui-widget-header"><?php echo TEXT_OPTIONS; ?></th></tr>
  <tr>
    <td><?php echo CD_01_18_DESC; ?></td>
    <td><?php echo html_pull_down_menu('enable_multi_branch', $sel_yes_no, $_POST['enable_multi_branch'] ? $_POST['enable_multi_branch'] : ENABLE_MULTI_BRANCH, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_01_19_DESC; ?></td>
    <td><?php echo html_pull_down_menu('enable_multi_currency', $sel_yes_no, $_POST['enable_multi_currency'] ? $_POST['enable_multi_currency'] : ENABLE_MULTI_CURRENCY, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_08_07_DESC; ?></td>
    <td><?php echo html_pull_down_menu('auto_update_currency', $sel_yes_no, $_POST['auto_update_currency'] ? $_POST['auto_update_currency'] : AUTO_UPDATE_CURRENCY, ''); ?></td>
  </tr>
  <tr>
    <td><?php echo CD_08_03_DESC; ?></td>
    <td><?php echo html_pull_down_menu('cfg_auto_update_check', $sel_yes_no, $_POST['cfg_auto_update_check'] ? $_POST['cfg_auto_update_check'] : CFG_AUTO_UPDATE_CHECK, ''); ?></td>
  </tr>

  <tr><th colspan="2" class="ui-widget-header"><?php echo TEXT_DEBUG; ?></th></tr>
  <tr>
    <td><?php echo CD_20_99_DESC; ?></td>
    <td><?php echo html_pull_down_menu('debug', $sel_yes_no, $_POST['debug'] ? $_POST['debug'] : DEBUG, ''); ?></td>
  </tr>
 </tbody>
</table>
</div>
