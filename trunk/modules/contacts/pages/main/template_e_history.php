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
//  Path: /modules/contacts/pages/main/template_e_history.php
//
?>
<div title="<?php echo TEXT_HISTORY;?>" id="tab_history">
  <fieldset>
    <legend><?php echo ACT_ACT_HISTORY; ?></legend>
	  <table class="ui-widget" style="border-collapse:collapse;width:100%;">
		<tbody class="ui-widget-content">
	  <tr>
	    <td width="50%"><?php echo constant('ACT_' . strtoupper($type) . '_FIRST_DATE') . ' ' . gen_locale_date($cInfo->first_date); ?></td>
	  </tr>
	  <tr>
	    <td width="50%"><?php echo constant('ACT_' . strtoupper($type) . '_LAST_DATE1') . ' ' . gen_locale_date($cInfo->last_update); ?></td>
	  </tr>
	   </tbody>
	</table>
  </fieldset>
</div>