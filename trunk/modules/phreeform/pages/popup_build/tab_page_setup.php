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
//  Path: /modules/phreeform/pages/popup_phreefrom/tab_page_setup.php
//

?>
<div title="<?php echo TEXT_PAGE_SETUP;?>" id="tab_page">
  <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
   <thead class="ui-widget-header">
    <tr><th colspan="3"><?php echo TEXT_TITLE; ?></th></tr>
   </thead>
   <tbody class="ui-widget-content">
    <tr>
	  <td colspan="3" align="center"><?php echo html_input_field('title', $report->title, 'size="60"'); ?></td>
    </tr>
    <tr class="ui-widget-header">
      <th><?php echo TEXT_DETAIL_DESCRIPTION; ?></th>
      <th colspan="2"><?php echo PHREEFORM_PGLAYOUT; ?></th>
    </tr>
    <tr>
      <td rowspan="2"><?php echo html_textarea_field('description', 80, 3, $report->description, ''); ?></td>
      <td align="right"><?php echo TEXT_PAPER; ?></td>
      <td><?php echo html_pull_down_menu('papersize', gen_build_pull_down($PaperSizes), $report->page->size); ?></td>
    </tr>
    <tr>
      <td><?php echo TEXT_ORIEN; ?></td>
      <td>
		<?php echo html_radio_field('paperorientation', 'P', ($report->page->orientation == 'P') ? true : false) . ' ' . TEXT_PORTRAIT . '<br />'; ?>
	  	<?php echo html_radio_field('paperorientation', 'L', ($report->page->orientation == 'L') ? true : false) . ' ' . TEXT_LANDSCAPE; ?>
	  </td>
    </tr>
    <tr class="ui-widget-header">
      <th><?php echo TEXT_EMAIL_MSG_DETAIL; ?></th>
      <th colspan="2"><?php echo PHREEFORM_PGMARGIN; ?></th>
    </tr>
    <tr>
      <td rowspan="4"><?php echo html_textarea_field('emailmessage', 80, 3, $report->emailmessage, ''); ?></td>
      <td align="right"><?php echo TEXT_TOP; ?></td>
      <td><?php echo html_input_field('margintop',    $report->page->margin->top,    'size="5" maxlength="3" style="text-align:right"') . ' ' . TEXT_MM; ?></td>
    </tr>
    <tr>
      <td align="right"><?php echo TEXT_BOTTOM; ?></td>
      <td><?php echo html_input_field('marginbottom', $report->page->margin->bottom, 'size="5" maxlength="3" style="text-align:right"') . ' ' . TEXT_MM; ?></td>
    </tr>
    <tr>
      <td align="right"><?php echo TEXT_LEFT; ?></td>
      <td><?php echo html_input_field('marginleft',   $report->page->margin->left,   'size="5" maxlength="3" style="text-align:right"') . ' ' . TEXT_MM; ?></td>
    </tr>
    <tr>
      <td align="right"><?php echo TEXT_RIGHT; ?></td>
      <td><?php echo html_input_field('marginright',  $report->page->margin->right,  'size="5" maxlength="3" style="text-align:right"') . ' ' . TEXT_MM; ?></td>
    </tr>
    </tbody>
  </table>
<?php if ($report->reporttype == 'rpt') { ?>
  <table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">
   <thead class="ui-widget-header">
    <tr><th colspan="8"><?php echo PHREEFORM_PGHEADER; ?></th></tr>
    <tr>
      <th>&nbsp;</th>
      <th><?php echo TEXT_SHOW;  ?></th>
      <th><?php echo TEXT_FONT;  ?></th>
      <th><?php echo TEXT_SIZE;  ?></th>
      <th><?php echo TEXT_COLOR; ?></th>
      <th><?php echo TEXT_ALIGN; ?></th>
    </tr>
   </thead>
   <tbody class="ui-widget-content">
    <tr>
      <td><?php echo TEXT_PGCOYNM; ?></td>
	  <td align="center"><?php echo html_checkbox_field('headingshow', '1', ($report->page->heading->show == '1') ? true : false); ?></td>
      <td align="center"><?php echo html_pull_down_menu('headingfont',  $kFonts,      $report->page->heading->font);  ?></td>
      <td align="center"><?php echo html_pull_down_menu('headingsize',  $kFontSizes,  $report->page->heading->size);  ?></td>
      <td align="center"><?php echo html_pull_down_menu('headingcolor', $kFontColors, $report->page->heading->color); ?></td>
      <td align="center"><?php echo html_pull_down_menu('headingalign', $kFontAlign,  $report->page->heading->align); ?></td>
    </tr>
    <tr>
      <td nowrap="nowrap"><?php echo PHREEFORM_PGTITL1 . ' ' . html_input_field('title1desc', $report->page->title1->text, 'size="30" maxlength="50"'); ?></td>
	  <td align="center"><?php echo html_checkbox_field('title1show', '1', ($report->page->title1->show == '1') ? true : false); ?></td>
      <td align="center"><?php echo html_pull_down_menu('title1font',  $kFonts,      $report->page->title1->font);  ?></td>
      <td align="center"><?php echo html_pull_down_menu('title1size',  $kFontSizes,  $report->page->title1->size);  ?></td>
      <td align="center"><?php echo html_pull_down_menu('title1color', $kFontColors, $report->page->title1->color); ?></td>
      <td align="center"><?php echo html_pull_down_menu('title1align', $kFontAlign,  $report->page->title1->align); ?></td>
    </tr>
    <tr>
      <td nowrap="nowrap"><?php echo PHREEFORM_PGTITL2 . ' ' . html_input_field('title2desc', $report->page->title2->text, 'size="30" maxlength="50"'); ?></td>
	  <td align="center"><?php echo html_checkbox_field('title2show', '1', ($report->page->title2->show == '1') ? true : false); ?></td>
      <td align="center"><?php echo html_pull_down_menu('title2font',  $kFonts,      $report->page->title2->font); ?></td>
      <td align="center"><?php echo html_pull_down_menu('title2size',  $kFontSizes,  $report->page->title2->size); ?></td>
      <td align="center"><?php echo html_pull_down_menu('title2color', $kFontColors, $report->page->title2->color); ?></td>
      <td align="center"><?php echo html_pull_down_menu('title2align', $kFontAlign,  $report->page->title2->align); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo PHREEFORM_PGFILDESC; ?></td>
      <td align="center"><?php echo html_pull_down_menu('filterfont',  $kFonts,      $report->page->filter->font); ?></td>
      <td align="center"><?php echo html_pull_down_menu('filtersize',  $kFontSizes,  $report->page->filter->size); ?></td>
      <td align="center"><?php echo html_pull_down_menu('filtercolor', $kFontColors, $report->page->filter->color); ?></td>
      <td align="center"><?php echo html_pull_down_menu('filteralign', $kFontAlign,  $report->page->filter->align); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo PHREEFORM_RPTDATA; ?></td>
      <td align="center"><?php echo html_pull_down_menu('datafont',  $kFonts,      $report->page->data->font); ?></td>
      <td align="center"><?php echo html_pull_down_menu('datasize',  $kFontSizes,  $report->page->data->size); ?></td>
      <td align="center"><?php echo html_pull_down_menu('datacolor', $kFontColors, $report->page->data->color); ?></td>
      <td align="center"><?php echo html_pull_down_menu('dataalign', $kFontAlign,  $report->page->data->align); ?></td>
    </tr>
    <tr>
      <td colspan="2"><?php echo PHREEFORM_TOTALS; ?></td>
      <td align="center"><?php echo html_pull_down_menu('totalsfont' , $kFonts,      $report->page->totals->font); ?></td>
      <td align="center"><?php echo html_pull_down_menu('totalssize',  $kFontSizes,  $report->page->totals->size); ?></td>
      <td align="center"><?php echo html_pull_down_menu('totalscolor', $kFontColors, $report->page->totals->color); ?></td>
      <td align="center"><?php echo html_pull_down_menu('totalsalign', $kFontAlign,  $report->page->totals->align); ?></td>
    </tr>
    </tbody>
  </table>
<?php } ?>
</div>
