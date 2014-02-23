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
//  Path: /modules/phreedom/pages/main/template_main.php
//
echo html_form('login', FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=validate', 'post', 'onsubmit="return submit_wait();"').chr(10);
if ($single_company)  echo html_hidden_field('company',  $companies[1]['file']) . chr(10);
if ($single_language) echo html_hidden_field('language', $languages[0]['id']) . chr(10);
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
			    <td colspan="2"><?php echo $messageStack->output(); ?></td>
			  </tr>
              <tr>
                <td width="35%">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_LOGIN_NAME; ?></td>
                <td width="65%"><?php echo html_input_field('admin_name', (isset($_POST['admin_name']) ? $_POST['admin_name'] : ''), '', true); ?></td>
              </tr>
              <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_LOGIN_PASS; ?></td>
                <td><?php echo html_password_field('admin_pass', '', true); ?></td>
              </tr>
<?php if (!$single_company) { ?>
              <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_LOGIN_COMPANY; ?></td>
                <td><?php echo html_pull_down_menu('company', $_SESSION['companies'], $company_index, '', true); ?></td>
              </tr>
<?php } ?>
<?php if (!$single_language) { ?>
              <tr>
                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo TEXT_LOGIN_LANGUAGE; ?></td>
                <td><?php echo html_pull_down_menu('language', $languages, $language_index, '', true); ?></td>
              </tr>
<?php } ?>
              <tr>
                <td colspan="2" align="right">&nbsp;
				  <div id="wait_msg" style="display:none;"><?php echo TEXT_FORM_PLEASE_WAIT; ?></div>
				  <?php echo html_submit_field('submit', TEXT_LOGIN_BUTTON); ?>
				</td>
              </tr>
              <tr>
                <td colspan="2"><?php echo '<a href="' . html_href_link(FILENAME_DEFAULT, 'module=phreedom&amp;page=main&amp;action=pw_lost_req', 'SSL') . '">' . TEXT_PASSWORD_FORGOTTEN . '</a>'; ?></td>
              </tr>
              <tr>
                <td colspan="2">
<?php echo TEXT_COPYRIGHT; ?> (c) 2008 - 2013 <a href="http://www.PhreeSoft.com">PhreeSoft, LLC</a><br />
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
