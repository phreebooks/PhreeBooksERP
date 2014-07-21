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
//  Path: /modules/phreebooks/pages/budget/template_main.php
//
echo html_form('budget', FILENAME_DEFAULT, gen_get_all_get_params(array('action'))) . chr(10);
// include hidden fields
echo html_hidden_field('action', '') . chr(10);
// customize the toolbar actions
$toolbar->icon_list['cancel']['params'] = 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, '', 'SSL') . '\'"';
$toolbar->icon_list['open']['show']     = false;
$toolbar->icon_list['delete']['show']   = false;
$toolbar->icon_list['save']['params']   = 'onclick="submitToDo(\'save\')"';
if ($security_level < 2) $toolbar->icon_list['save']['show'] = false;
$toolbar->icon_list['print']['show']    = false;
$toolbar->add_icon('new',  'onclick="if (confirm(\'' . GL_CLEAR_ACTUAL_CONFIRM . '\')) submitToDo(\'clear_fy\')"', $order = 20);
$toolbar->icon_list['new']['text']      = TEXT_BUDGET_CLEAR_HINT;
$toolbar->add_icon('copy', 'onclick="if (confirm(\'' . GL_COPY_ACTUAL_CONFIRM  . '\')) submitToDo(\'copy_fy\')"',  $order = 25);
$toolbar->icon_list['copy']['text']     = GL_BUDGET_COPY_HINT;
if (count($extra_toolbar_buttons) > 0) foreach ($extra_toolbar_buttons as $key => $value) $toolbar->icon_list[$key] = $value;
$toolbar->add_help();
echo $toolbar->build_toolbar(); 
// Build the page
?>
<h1><?php echo GL_BUDGET_HEADING_TITLE; ?></h1>
  <div style="text-align:center"><?php echo '<p>' . GL_BUDGET_INTRO_TEXT . '</p>'; ?></div>
  <div style="text-align:center">
    <?php echo TEXT_GL_ACCOUNT; ?>
    <?php echo html_pull_down_menu('gl_acct', gen_coa_pull_down(), $gl_acct, 'onchange="submit()"'); ?>
    <?php echo html_icon('actions/view-refresh.png', TEXT_LOAD_ACCT_PRIOR, 'small', 'onclick="fetchAcct();"'); ?>
    <?php echo html_icon('actions/window-new.png', TEXT_CLEAR, 'small', 'onclick="copyBudget(\'clear\');"'); ?>
    <?php echo GL_FISCAL_YEAR; ?>
    <?php echo html_pull_down_menu('fy', get_fiscal_year_pulldown(), $fy, 'onchange="submit()"'); ?>
  </div>
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto">
   <thead class="ui-widget-header">
	<tr>
	  <th><?php echo TEXT_PERIOD_DATES; ?></th>
	  <th><?php echo TEXT_PRIOR_FY; ?></th>
	  <th>&nbsp;</th>
	  <th><?php echo TEXT_BUDGET; ?></th>
	  <th>&nbsp;</th>
	  <th><?php echo TEXT_NEXT_FY; ?></th>
	</tr>
   </thead>
   <tbody class="ui-widget-content">
	<?php
	$i = 0;
	foreach ($fy_array as $value) { 
	  echo '<tr>' . chr(10);
	  echo '  <td align="center">' . $value['period'] . html_hidden_field('id_' . $i, $value['id']) . '</td>' . chr(10);
	  echo '  <td align="center">' . html_input_field('prior_'  . $i, $currencies->format($value['prior']), 'readonly="readonly" style="text-align:right"') . '</td>' . chr(10);
	  echo '  <td>&nbsp;</td>' . chr(10);
	  echo '  <td align="center">' . html_input_field('budget_' . $i, $currencies->format($value['budget']), 'style="text-align:right"') . '</td>' . chr(10);
	  echo '  <td>&nbsp;</td>' . chr(10);
	  echo '  <td align="center">' . html_input_field('next_'   . $i, $currencies->format($value['next']), 'readonly="readonly" style="text-align:right"') . '</td>' . chr(10);
	  echo '</tr>' . chr(10);
	  $i++;
	} ?>
	<tr>
	  <?php
	  echo '  <th align="right">' . TEXT_TOTAL . '</th>' . chr(10);
	  echo '  <th align="center">' . html_input_field('prior', '', 'readonly="readonly" style="text-align:right"') . '</th>' . chr(10);
	  echo '  <th align="center">' . html_icon('actions/go-next.png', GL_TEXT_COPY_PRIOR, 'small', 'onclick="copyBudget(\'prior\');"') . '</th>' . chr(10);
	  echo '  <th align="center">';
	  echo html_input_field('total', '', 'style="text-align:right"');
	  echo html_icon('actions/edit-undo.png', GL_TEXT_ALLOCATE, 'small', 'onclick="copyBudget(\'spread\');"');
	  echo '   </th>' . chr(10);
	  echo '  <th align="center">' . html_icon('actions/go-previous.png', GL_TEXT_COPY_NEXT, 'small', 'onclick="copyBudget(\'next\');"') . '</th>' . chr(10);
	  echo '  <th align="center">' . html_input_field('next', '', 'readonly="readonly" style="text-align:right"') . '</th>' . chr(10);
	  ?>
	</tr>
   </tbody>
  </table>
</form>
