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
//  Path: /modules/phreeform/pages/popup_build/box_html.php
//

// This template generates the property boxes for forms

// This function generates the common attributes for most boxes.
function box_build_attributes($properties, $i, $showtrunc = true, $showfont = true, $showborder = true, $showfill = true, $pre = '', $title = '') {
  global $kFonts, $kFontSizes, $kFontAlign, $kFontColors, $kLineSizes;
    $fields = array('font', 'size', 'align', 'color', 'bordershow', 'bordersize', 'bordercolor', 'fillshow', 'fillcolor');
	foreach ($fields as $value) {
      $temp = $pre . $value;
	  $$value  = $properties->$temp;
	}
  $output  = NULL;
  $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
  $output .= ' <thead class="ui-widget-header">'  . nl;
  $output .= '  <tr><th colspan="5">' . ($title ? $title : TEXT_ATTRIBUTES) . '</th></tr>' . nl;
  $output .= ' </thead>' . nl;
  $output .= ' <tbody class="ui-widget-content">' . nl;
  if ($showtrunc) {
    $output .= ' <tr>'  . nl;
    $output .= '  <td colspan="2">' . TEXT_TRUNCATE   . html_radio_field($pre.'box_trun_' . $i, '0', (!$properties->truncate) ? true : false) . TEXT_NO . html_radio_field($pre.'box_trun_' . $i, '1', ($properties->truncate) ? true : false) . TEXT_YES . '</td>' . nl;
    $output .= '  <td colspan="3">' . TEXT_DISPLAY_ON . html_radio_field($pre.'box_last_' . $i, '0', (!$properties->display || $properties->display == '0') ? true : false) . TEXT_ALL_PAGES . html_radio_field($pre.'box_last_' . $i, '1', ($properties->display == '1') ? true : false) . TEXT_FIRST_PAGE . html_radio_field($pre.'box_last_' . $i, '2', ($properties->display == '2') ? true : false) . TEXT_LAST_PAGE . '</td>' . nl;
    $output .= ' </tr>' . nl;
  }
  if ($showfont) {
    $output .= ' <tr class="ui-widget-header">'  . nl;
    $output .= '  <th>' . '&nbsp;'   . '</th>' . nl;
    $output .= '  <th>' . TEXT_STYLE . '</th>' . nl;
    $output .= '  <th>' . TEXT_SIZE  . '</th>' . nl;
    $output .= '  <th>' . TEXT_ALIGN . '</th>' . nl;
    $output .= '  <th>' . TEXT_COLOR . '</th>' . nl;
    $output .= ' </tr>' . nl;
    $output .= ' <tr>'  . nl;
    $output .= '  <td>' . TEXT_FONT .'</td>' . nl;
    $output .= '  <td>' . html_pull_down_menu($pre.'box_fnt_' . $i, $kFonts,     $font)  . '</td>' . nl;
    $output .= '  <td>' . html_pull_down_menu($pre.'box_size_'. $i, $kFontSizes, $size)  . '</td>' . nl;
    $output .= '  <td>' . html_pull_down_menu($pre.'box_aln_' . $i, $kFontAlign, $align) . '</td>' . nl;
    $output .= '  <td id="' . $pre.'box_td_' . $i . '" style="background-color:#' . convertPfColor($color) . '">' . nl;
    $output .= '    <div id="' . $pre.'box_whl_' . $i . '" 
	  onmousemove="moved(event, \''.$pre.'box_whl_' . $i . '\', \''.$pre.'box_td_' . $i . '\', \''.$pre.'box_clr_' . $i . '\')" 
	  onclick="setCustom(\''.$pre.'box_whl_' . $i . '\', \''.$pre.'sel_clr_' . $i . '\')" style="position:absolute; display:none; top:0px; left:0px; z-index:10000">
	  <img src="'.DIR_WS_MODULES.'phreeform/images/colorwheel.jpg" width="256" height="256" alt="" />' . nl;
    $output .= '  </div>' . nl;
    $output .= html_hidden_field($pre.'box_clr_' . $i, $color ? $color : '0:0:0') . nl;
    $output .= html_pull_down_menu($pre.'sel_clr_' . $i, $kFontColors, $color ? $color : '0:0:0', 'onchange="colorSet(\''.$pre.'sel_clr_' . $i . '\', \''.$pre.'box_td_' . $i . '\', \''.$pre.'box_clr_' . $i . '\')"') . nl;
    $output .= html_icon('categories/applications-graphics.png', TEXT_CUSTOM, 'small', 'onclick="showCustom(\''.$pre.'box_whl_' . $i . '\', \''.$pre.'sel_clr_' . $i . '\')"') . nl;
    $output .= '  </td>'. nl;
    $output .= ' </tr>' . nl;
  }
  if ($showborder) {
    $output .= ' <tr>'  . nl;
    $output .= '  <td>' . TEXT_BORDER_AREA . '</td>' . nl;
    $output .= '  <td>' . html_checkbox_field($pre.'box_bdr_' . $i, '1', ($bordershow) ? true : false) . '</td>' . nl;
    $output .= '  <td>' . html_pull_down_menu($pre.'box_bsz_' . $i, $kLineSizes, $bordersize) . TEXT_POINTS . '</td>' . nl;
    $output .= '  <td>' . '&nbsp;' . '</td>' . nl;
    $output .= '  <td id="'.$pre.'box_btd_' . $i . '" style="background-color:#' . convertPfColor($bordercolor) . '">' . nl;
    $output .= '    <div id="'.$pre.'box_bwhl_' . $i . '" 
	  onmousemove="moved(event, \''.$pre.'box_bwhl_' . $i . '\', \''.$pre.'box_btd_' . $i . '\', \''.$pre.'box_bclr_' . $i . '\')" 
	  onclick="setCustom(\''.$pre.'box_bwhl_' . $i . '\', \''.$pre.'sel_bclr_' . $i . '\')" style="position:absolute; display:none; top:0px; left:0px; z-index:10000">
	  <img src="'.DIR_WS_MODULES.'phreeform/images/colorwheel.jpg" width="256" height="256" alt="" />' . nl;
    $output .= '  </div>' . nl;
    $output .= html_hidden_field($pre.'box_bclr_' . $i, $bordercolor ? $bordercolor : '0:0:0') . nl;
    $output .= html_pull_down_menu($pre.'sel_bclr_' . $i, $kFontColors, $bordercolor ? $bordercolor : '0:0:0', 'onchange="colorSet(\''.$pre.'sel_bclr_' . $i . '\', \''.$pre.'box_btd_' . $i . '\', \''.$pre.'box_bclr_' . $i . '\')"') . nl;
    $output .= html_icon('categories/applications-graphics.png', TEXT_CUSTOM, 'small', 'onclick="showCustom(\''.$pre.'box_bwhl_' . $i . '\', \''.$pre.'sel_bclr_' . $i . '\')"') . nl;
    $output .= '  </td>'. nl;
    $output .= '</tr>' . nl;
  }
  if ($showfill) {
    $output .= '<tr>'  . nl;
    $output .= '  <td>'. TEXT_FILL_AREA . '</td>' . nl;
    $output .= '  <td>'. html_checkbox_field($pre.'box_fill_' . $i, '1', ($fillshow) ? true : false) . '</td>' . nl;
    $output .= '  <td>'. '&nbsp;' . '</td>' . nl;
    $output .= '  <td>'. '&nbsp;' . '</td>' . nl;
    $output .= '  <td id="'.$pre.'box_ftd_' . $i .'" style="background-color:#' . convertPfColor($fillcolor) .'">' . nl;
    $output .= '    <div id="'.$pre.'box_fwhl_' . $i . '" 
	  onmousemove="moved(event, \''.$pre.'box_fwhl_' . $i . '\', \''.$pre.'box_ftd_' . $i . '\', \''.$pre.'box_fclr_' . $i . '\')" 
	  onclick="setCustom(\''.$pre.'box_fwhl_' . $i . '\', \''.$pre.'sel_fclr_' . $i . '\')" style="position:absolute; display:none; top:0px; left:0px; z-index:10000">
	  <img src="'.DIR_WS_MODULES.'phreeform/images/colorwheel.jpg" width="256" height="256" alt="" />' . nl;
    $output .= '  </div>' . nl;
    $output .= html_hidden_field($pre.'box_fclr_' . $i, $fillcolor ? $fillcolor : '0:0:0') . nl;
    $output .= html_pull_down_menu($pre.'sel_fclr_' . $i, $kFontColors, $fillcolor ? $fillcolor : '0:0:0', 'onchange="colorSet(\''.$pre.'sel_fclr_' . $i . '\', \''.$pre.'box_ftd_' . $i . '\', \''.$pre.'box_fclr_' . $i . '\')"') . nl;
    $output .= html_icon('categories/applications-graphics.png', TEXT_CUSTOM, 'small', 'onclick="showCustom(\''.$pre.'box_fwhl_' . $i . '\', \''.$pre.'sel_fclr_' . $i . '\')"') . nl;
    $output .= '  </td>'. nl;
    $output .= '</tr>'  . nl;
  }
  $output .= '</tbody></table>' . nl;
  return $output;
}

function box_build($properties, $i) {
  global $kFonts, $kFontSizes, $kFontAlign, $kFontColors, $cFields;
  global $kFields, $kTblFields, $pFields, $tProcessing, $BarCodeTypes;
  $output  = NULL;
  switch ($properties->type) {
    case 'BarCode':
      $output  = '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <th>' . TEXT_FIELDNAME . '</th>' . nl;
      $output .= '    <th>' . TEXT_TYPE . '</th>' . nl;
      $output .= '   </tr>' . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '   <tr>' . nl;
      $output .= '    <td>' . html_combo_box     ('box_fld_'  . $i . '[]', $kFields, $properties->boxfield[0]->fieldname, 'onclick="updateFieldList(this)"') . '</td>' . nl;
      $output .= '    <td>' . html_pull_down_menu('box_proc_' . $i . '[]', gen_build_pull_down($BarCodeTypes), $properties->boxfield[0]->processing) . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
	  $output .= box_build_attributes($properties, $i, false, false);
	  break;
    case 'CBlk':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tr><td>' . nl;
      $output .= '  <table id="box_Cblk' . $i . '" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><thead class="ui-widget-header">' . nl;
      $output .= '    <tr><th colspan="4">' . TEXT_FIELD_LIST .'</th></tr>' . nl;
      $output .= '    <tr>' . nl;
      $output .= '      <th>' . TEXT_FIELDNAME . '</th>' . nl;
      $output .= '      <th>' . TEXT_SEPARATOR . '</th>' . nl;
      $output .= '      <th>' . TEXT_PROCESSING. '</th>' . nl;
      $output .= '      <th>' . TEXT_ACTION    . '</th>' . nl;
      $output .= '    </tr>' . nl;
      $output .= '	</thead><tbody class="ui-widget-content">' . nl;
      for ($j = 0; $j < sizeof($properties->boxfield); $j++) {
        $output .= '		  <tr>' . nl;
        $output .= '		    <td>' . html_pull_down_menu('box_fld_' . $i . '[]', $cFields,    $properties->boxfield[$j]->fieldname) . '</td>' . nl;
        $output .= '		    <td>' . html_pull_down_menu('box_proc_'. $i . '[]', $tProcessing,$properties->boxfield[$j]->processing) . '</td>' . nl;
        $output .= '		    <td>' . html_pull_down_menu('box_fmt_' . $i . '[]', $pFields,    $properties->boxfield[$j]->formatting) . '</td>' . nl;
        $output .= '		    <td nowrap="nowrap" align="right">';
		$output .= html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_cblk_' . $i . '_' . $j) . chr(10);
        $output .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"');
        $output .= '			</td>' . nl;
        $output .= '		  </tr>' . nl;
      }
      $output .= '    </tbody></table>' . nl;
      $output .= '  </td>' . nl;
      $output .= '  <td valign="bottom">' . html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'box_Cblk\', \'add\', ' . $i . ')"') . '</td>' . nl;
      $output .= '</tr></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
	  $output .= '<script type="text/javascript">tableInit[tableCount] = \'box_Cblk' . $i . '\'; tableCount++;</script>' . nl;
	  break;
    case 'CDta':
      $output  = '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <th>' . TEXT_FIELDNAME . '</th>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <td align="center">' . html_pull_down_menu('box_fld_' . $i . '[]', $cFields, $properties->boxfield[0]->fieldname) . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
	  break;
	case 'Data':
      $output  = '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <th>' . PHREEFORM_TBLFNAME . '</th>' . nl;
      $output .= '    <th>' . TEXT_PROCESSING . '</th>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <td>' . html_combo_box     ('box_fld_'  . $i . '[]', $kFields, $properties->boxfield[0]->fieldname, 'onclick="updateFieldList(this)"')  . '</td>' . nl;
      $output .= '    <td>' . html_pull_down_menu('box_proc_' . $i . '[]', $pFields, $properties->boxfield[0]->processing) . '</td>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= ' </tbody></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
	  break;
	case 'Img':
      $output  = '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <td>' . TEXT_CURRENT_IMAGE . '</td>' . nl;
      $output .= '    <td align="center">' . nl;
	  $output .= !$properties->filename ? TEXT_NO_IMAGE_SELECTED : html_image(PF_WEB_MY_REPORTS . 'images/' . $properties->filename, '', '', '32');
      $output .= '    </td>'  . nl;
      $output .= '  </tr>'  . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <th colspan="2">' . PHREEFORM_IMAGESEL . '</th>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <td>' . html_radio_field('img_sel_' . $i, 'U', false) . TEXT_UPLOAD_MAGE . '</td>' . nl;
      $output .= '    <td>' . html_file_field ('img_upload_' . $i) . '</td>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <td>' . html_radio_field   ('img_sel_' . $i, 'S', true) . TEXT_STORED_IMAGES . '</td>' . nl;
      $output .= '    <td>' . html_pull_down_menu('img_file_' . $i, ReadImages(), $properties->filename, 'size="6"') . '</td>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= ' </tbody></table>' . nl;
	  break;
	case 'ImgLink':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <th>' . PHREEFORM_TBLFNAME . '</th>' . nl;
      $output .= '    <th>' . TEXT_PROCESSING . '</th>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <td>' . html_combo_box     ('box_fld_'  . $i . '[]', $kFields, $properties->boxfield[0]->fieldname, 'onclick="updateFieldList(this)"')  . '</td>' . nl;
      $output .= '    <td>' . html_pull_down_menu('box_proc_' . $i . '[]', $pFields, $properties->boxfield[0]->processing) . '</td>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= '  <tr class="ui-widget-header"><th colspan="2">' . TEXT_IMAGE_LINK . '</th></tr>' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <td align="center" colspan="2">' . html_input_field('box_txt_' . $i, $properties->text ? $properties->text : DIR_WS_MY_FILES, 'size="40"') . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
//	  $output .= box_build_attributes($properties, $i, false, false, false, false);
	  break;
	case 'Line':
      $output  = '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <th colspan="3">' . PHREEFORM_LINE_TYPE . '</th>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <td>' . html_radio_field('box_ltype_' . $i, 'H', (!$properties->linetype || $properties->linetype == 'H') ? true : false) . TEXT_HORIZONTAL . '</td>' . nl;
      $output .= '    <td>' . html_radio_field('box_ltype_' . $i, 'V', ($properties->linetype == 'V') ? true : false) . TEXT_VERTICAL . '</td>' . nl;
      $output .= '    <td>' . TEXT_LENGTH . ' ' . html_input_field('box_len_' . $i, $properties->length, 'size="4" maxlength="3"') . '</td>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= '  <tr class="ui-widget-header">'   . nl;
      $output .= '    <th colspan="3">' . PHREEFORM_ENDPOS . '</th>' . nl;
      $output .= '  </tr>'  . nl;
      $output .= '  <tr>'   . nl;
      $output .= '    <td>' . html_radio_field('box_ltype_' . $i, 'C', ($properties->linetype == 'C') ? true : false) . TEXT_CUSTOM . '</td>' . nl;
      $output .= '    <td>' . TEXT_ABSCISSA . html_input_field('box_eabs_' . $i, $properties->endabscissa, 'size="4" maxlength="3"') . '</td>' . nl;
      $output .= '    <td>' . TEXT_ORDINATE . html_input_field('box_eord_' . $i, $properties->endordinate, 'size="4" maxlength="3"') . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
	  $output .= box_build_attributes($properties, $i, false, false, true, false);
	  break;
	case 'PgNum':
	  $output .= box_build_attributes($properties, $i, false);
	  break;
	case 'Rect':
	  $output .= box_build_attributes($properties, $i, false, false, true, true);
	  break;
	case 'Tbl':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tr><td>' . nl;
      $output .= '  <table id="box_Tbl' . $i . '" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><thead class="ui-widget-header">' . nl;
      $output .= '    <tr><th colspan="9">' . TEXT_FIELD_LIST . '</th></tr>' . nl;
      $output .= '    <tr>' . nl;
      $output .= '      <th>' . TEXT_FIELDNAME   . '</th>' . nl;
      $output .= '      <th>' . TEXT_DESCRIPTION . '</th>' . nl;
      $output .= '      <th>' . TEXT_PROCESSING  . '</th>' . nl;
      $output .= '      <th>' . TEXT_FONT        . '</th>' . nl;
      $output .= '      <th>' . TEXT_SIZE        . '</th>' . nl;
      $output .= '      <th>' . TEXT_ALIGN       . '</th>' . nl;
      $output .= '      <th>' . TEXT_COLOR       . '</th>' . nl;
      $output .= '      <th>' . TEXT_WIDTH       . '</th>' . nl;
      $output .= '      <th>' . '&nbsp;'         . '</th>' . nl;
      $output .= '    </tr>' . nl;
      $output .= '	</thead><tbody class="ui-widget-content">' . nl;
      for ($j = 0; $j < sizeof($properties->boxfield); $j++) {
        $output .= '	  <tr>' . nl;
        $output .= '	    <td nowrap="nowrap">' . html_combo_box('box_fld_' . $i . '[]', $kTblFields, $properties->boxfield[$j]->fieldname, 'onclick="updateFieldList(this)"', '220px', '', 'box_fld_' . $i . $j) . '</td>' . nl;
        $output .= '	    <td>' . html_input_field   ('box_desc_'. $i . '[]', $properties->boxfield[$j]->description, 'size="15"') . '</td>' . nl;
        $output .= '	    <td>' . html_pull_down_menu('box_proc_'. $i . '[]', $pFields,     $properties->boxfield[$j]->processing) . '</td>' . nl;
        $output .= '	    <td>' . html_pull_down_menu('box_fnt_' . $i . '[]', $kFonts,      $properties->boxfield[$j]->font) . '</td>' . nl;
        $output .= '	    <td>' . html_pull_down_menu('box_size_'. $i . '[]', $kFontSizes,  $properties->boxfield[$j]->size) . '</td>' . nl;
        $output .= '	    <td>' . html_pull_down_menu('box_aln_' . $i . '[]', $kFontAlign,  $properties->boxfield[$j]->align) . '</td>' . nl;
        $output .= '	    <td>' . html_pull_down_menu('box_clr_' . $i . '[]', $kFontColors, $properties->boxfield[$j]->color) . '</td>' . nl;
        $output .= '	    <td>' . html_input_field   ('box_wid_' . $i . '[]', $properties->boxfield[$j]->width, 'size="4" maxlength="4"') . '</td>' . nl;
        $output .= '	    <td nowrap="nowrap" align="right">' . nl; 
		$output .= html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_tbl_' . $i . '_' . $j);
        $output .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"');
        $output .= '</td>' . nl;
        $output .= '	  </tr>' . nl;
      }
      $output .= '  </tbody></table>' . nl;
      $output .= '</td>' . nl;
      $output .= '<td valign="bottom">' . html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'box_Tbl\', \'add\', ' . $i . ')"') . '</td>' . nl;
      $output .= '</tr></table>' . nl;
	  $output .= box_build_attributes($properties, $i, false, true,  true, true, 'h', PHREEFORM_TABLE_HEADING_PROP);
	  $output .= box_build_attributes($properties, $i, false, false, true, true, '',  TEXT_TABLE_BODY_PROPERTIES);
	  $output .= PHREEFORM_FORM_TABLE_NOTES;
	  $output .= '<script type="text/javascript">tableInit[tableCount] = \'box_Tbl'  . $i . '\'; tableCount++;</script>' . nl;
	  break;
	case 'TBlk':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tr><td>' . nl;
      $output .= '  <table id="box_Tblk' . $i . '" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><thead class="ui-widget-header">' . nl;
      $output .= '    <tr><th colspan="4">' . TEXT_FIELD_LIST . '</th></tr>' . nl;
      $output .= '    <tr>' . nl;
      $output .= '      <th>' . PHREEFORM_TBLFNAME . '</th>' . nl;
      $output .= '      <th>' . TEXT_SEPARATOR  . '</th>' . nl;
      $output .= '      <th>' . TEXT_PROCESSING  . '</th>' . nl;
      $output .= '      <th>' . TEXT_ACTION     . '</th>' . nl;
      $output .= '    </tr>' . nl;
      $output .= '</thead><tbody class="ui-widget-content">' . nl;
      for ($j = 0; $j < sizeof($properties->boxfield); $j++) {
        $output .= '  <tr>' . nl;
        $output .= '    <td>' . html_combo_box     ('box_fld_' . $i . '[]', $kFields,    $properties->boxfield[$j]->fieldname, 'onclick="updateFieldList(this)"', '220px', '', 'box_fld_' . $i . '_' . $j) . '</td>' . nl;
        $output .= '    <td>' . html_pull_down_menu('box_proc_'. $i . '[]', $tProcessing,$properties->boxfield[$j]->processing) . '</td>' . nl;
        $output .= '	<td>' . html_pull_down_menu('box_fmt_' . $i . '[]', $pFields,    $properties->boxfield[$j]->formatting) . '</td>' . nl;
        $output .= '    <td nowrap="nowrap" align="right">' . nl;
		$output .= html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_tblk_' . $i . '_' . $j) . chr(10);
		$output .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"');
        $output .= '    </td>' . nl;
        $output .= '  </tr>' . nl;
      }
      $output .= '</tbody></table>' . nl;
      $output .= '</td>' . nl;
      $output .= '<td valign="bottom">' . html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'box_Tblk\', \'add\', ' . $i . ')"');
      $output .= '</td></tr></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
	  $output .= '<script type="text/javascript">tableInit[tableCount] = \'box_Tblk' . $i . '\'; tableCount++;</script>' . nl;
	  break;
	case 'TDup':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <td align="center">' . TEXT_NO_PROPERTIES . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
	  break;
	case 'LtrTpl':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <th>' . PHREEFORM_TEXTDISP . '</th>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <td>' . html_textarea_field('box_txt_' . $i, '50', '20', $properties->text) . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
      break;
	case 'LtrData':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tr><td>' . nl;
      $output .= '  <table id="box_LtrData' . $i . '" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><thead class="ui-widget-header">' . nl;
      $output .= '    <tr><th colspan="9">' . TEXT_FIELD_LIST . '</th></tr>' . nl;
      $output .= '    <tr>' . nl;
      $output .= '      <th>' . TEXT_FIELDNAME   . '</th>' . nl;
      $output .= '      <th>' . TEXT_DESCRIPTION . '</th>' . nl;
      $output .= '      <th>' . TEXT_PROCESSING  . '</th>' . nl;
      $output .= '      <th>' . '&nbsp;'         . '</th>' . nl;
      $output .= '    </tr>' . nl;
      $output .= '	</thead><tbody class="ui-widget-content">' . nl;
      for ($j = 0; $j < sizeof($properties->boxfield); $j++) {
        $output .= '	  <tr>' . nl;
        $output .= '	    <td nowrap="nowrap">' . html_combo_box('box_fld_' . $i . '[]', $kTblFields, $properties->boxfield[$j]->fieldname, 'onclick="updateFieldList(this)"', '220px', '', 'box_fld_' . $i . $j) . '</td>' . nl;
        $output .= '	    <td>' . html_input_field   ('box_desc_'. $i . '[]', $properties->boxfield[$j]->description, 'size="15"') . '</td>' . nl;
        $output .= '	    <td>' . html_pull_down_menu('box_proc_'. $i . '[]', $pFields,     $properties->boxfield[$j]->processing) . '</td>' . nl;
        $output .= '	    <td nowrap="nowrap" align="right">' . nl; 
		$output .= html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_tbl_' . $i . '_' . $j);
        $output .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"');
        $output .= '</td>' . nl;
        $output .= '	  </tr>' . nl;
      }
      $output .= '  </tbody></table>' . nl;
      $output .= '</td>' . nl;
      $output .= '<td valign="bottom">' . html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'box_LtrData\', \'add\', ' . $i . ')"') . '</td>' . nl;
      $output .= '</tr></table>' . nl;
	  $output .= '<script type="text/javascript">tableInit[tableCount] = \'box_LtrData'  . $i . '\'; tableCount++;</script>' . nl;
	  break;
	case 'Text':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . nl;
      $output .= ' <thead class="ui-widget-header">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <th>' . PHREEFORM_TEXTDISP . '</th>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      $output .= '  <tr>' . nl;
      $output .= '    <td>' . html_textarea_field('box_txt_' . $i, '50', '3', $properties->text) . '</td>' . nl;
      $output .= '  </tr>' . nl;
      $output .= ' </tbody></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
	  break;
	case 'Ttl':
      $output .= '<table class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tr><td>' . nl;
      $output .= '  <table id="box_Ttl' . $i . '" class="ui-widget" style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><thead class="ui-widget-header">' . nl;
      $output .= '    <tr><th colspan="3">' . TEXT_FIELD_LIST . '</th></tr>' . nl;
      $output .= '    <tr>' . nl;
      $output .= '      <th>' . PHREEFORM_TBLFNAME . '</th>' . nl;
      $output .= '      <th>' . TEXT_PROCESSING . '</th>' . nl;
      $output .= '      <th>' . TEXT_ACTION     . '</th>' . nl;
      $output .= '    </tr>' . nl;
      $output .= ' </thead><tbody class="ui-widget-content">' . nl;
      for ($j = 0; $j < sizeof($properties->boxfield); $j++) {
        $output .= '  <tr>' . nl;
        $output .= '    <td>' . html_combo_box     ('box_fld_' . $i . '[]', $kFields, $properties->boxfield[$j]->fieldname, 'onclick="updateFieldList(this)"', '220px', '', 'box_fld_' . $i . $j) . '</td>' . nl;
        $output .= '    <td>' . html_pull_down_menu('box_proc_'. $i . '[]', $pFields, $properties->boxfield[$j]->processing) . '</td>' . nl;
        $output .= '    <td nowrap="nowrap" align="right">' . nl;
		$output .= html_icon('actions/view-fullscreen.png',   TEXT_MOVE,   'small', 'style="cursor:move"', '', '', 'move_ttl_' . $i . '_' . $j) . chr(10);
		$output .= html_icon('emblems/emblem-unreadable.png', TEXT_DELETE, 'small', 'onclick="if (confirm(\''.TEXT_DELETE_ENTRY.'\')) $(this).parent().parent().remove();"');
        $output .= '    </td>' . nl;
        $output .= '  </tr>' . nl;
      }
      $output .= '</tbody></table>' . nl;
      $output .= '</td>' . nl;
      $output .= '<td valign="bottom">' . html_icon('actions/list-add.png', TEXT_ADD, 'small', 'onclick="rowAction(\'box_Ttl\', \'add\', ' . $i . ')"');
      $output .= '</td></tr></table>' . nl;
	  $output .= box_build_attributes($properties, $i);
	  $output .= '<script type="text/javascript">tableInit[tableCount] = \'box_Ttl'  . $i . '\'; tableCount++;</script>' . nl;
	  break;
  }
  return $output;
}

?>