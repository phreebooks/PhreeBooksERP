<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft      (www.PhreeSoft.com)       |

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
//  Path: /modules/phreeform/pages/admin/template_tab_tools.php
//
?>
<div title="<?php echo TEXT_TOOLS;?>" id="tab_tools">
<fieldset>
  <legend><?php echo TEXT_PHREEFORM_STUCTURE_VERIFICATION_AND_REBUILD; ?></legend>
  <p><?php echo PHREEFORM_TOOLS_REBUILD_DESC; ?></p>
  <p align="center"><?php echo TEXT_START_STRUCTURE_VERIFY_AND_REBUILD . ' ' . html_button_field('fix', TEXT_SUBMIT, 'onclick="submitToDo(\'fix\')"'); ?>
</fieldset>
</div>
