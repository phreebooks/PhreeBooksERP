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
//  Path: /modules/phreedom/pages/main/template_main.php
//
echo html_form('login', FILENAME_DEFAULT, 'action=ValidateUser', 'post', 'onsubmit="return submit_wait();"').chr(10);
?>
<div style="margin-left:25%;margin-right:25%;margin-top:50px;">
	  <table class="ui-widget">
        <thead class="ui-widget-header">
        <tr height="70">
          <th style="text-align:right"><img src="modules/phreedom/images/phreesoft_logo.png" alt="Phreedom Business Toolkit" height="50" /></th>
        </tr>
        </thead>
        <tbody class="ui-widget-content">
        <tr>
          <td>
		    <table>
			  <tr>
			    <td colspan="2"><?php if(is_object($messageStack)) echo $messageStack->output(); ?></td>
			  </tr>
              <tr>
                <td width="35%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_USERNAME; ?>:</td>
                <td width="65%"><?php echo html_input_field('admin_name', (isset($basis->cInfo->admin_name) ? $basis->cInfo->admin_name : ''), '', true); ?></td>
              </tr>
              <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_PASSWORD; ?>:</td>
                <td><?php echo html_password_field('admin_pass', '', true); ?></td>
              </tr>
<?php if (sizeof($_SESSION['companies']) != 1) { ?>
              <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo sprintf(TEXT_SELECT_ARGS, TEXT_COMPANY); ?></td>
                <td><?php echo html_pull_down_menu('company', $_SESSION['companies'], \core\classes\user::get_company(), '', true); ?></td>
              </tr>
<?php } else{
		echo html_hidden_field('company',  $_SESSION['company']) . chr(10);
}?>
<?php if (sizeof($_SESSION['languages']) != 1) { ?>
              <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_SELECT_LANGUAGE; ?>: </td>
                <td><?php echo html_pull_down_menu('language', $_SESSION['languages'], \core\classes\user::get_language(), '', true); ?></td>
              </tr>
<?php } else{
			echo html_hidden_field('language', $_SESSION['language']) . chr(10);
}?>
              <tr>
                <td colspan="2" align="right">&nbsp;
				  <div id="wait_msg" style="display:none;"><?php echo TEXT_FORM_PLEASE_WAIT; ?></div>
				  <?php echo html_submit_field('submit', TEXT_LOGIN); ?>
				</td>
              </tr>
              <tr>
                <td colspan="2"><?php echo '<a href="' . html_href_link(FILENAME_DEFAULT, 'action=LoadLostPassword', 'SSL') . '">' . TEXT_RESEND_PASSWORD . '</a>'; ?></td>
              </tr>
              <tr>
                <td colspan="2">
<?php echo TEXT_COPYRIGHT; ?> (c) 2008-2014 <a href="http://www.PhreeSoft.com">PhreeSoft</a><br />
<?php echo sprintf(TEXT_COPYRIGHT_NOTICE, '<a href="' . DIR_WS_MODULES . 'phreedom/language/en_us/manual/ch01-Introduction/license.html">' . TEXT_HERE . '</a>'); ?>
				</td>
              </tr>
            </table>
	      </td>
        </tr>
        </tbody>
      </table>
</div>
</form>
