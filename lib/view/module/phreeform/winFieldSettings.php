<?php
/*
 * View for field settings boxes in PhreeForm design
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-06-06
 * @filesource /lib/view/module/phreeform/winFieldSettings.php
 */

namespace bizuno;

// This function generates the bizuno attributes for most boxes.
function box_build_attributes($viewData, $showtrunc=true, $showfont=true, $showborder=true, $showfill=true, $pre='', $title='')
{
	$output  = '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . "";
	$output .= ' <thead class="panel-header"><tr><th colspan="5">'.($title ? $title : lang('settings'))."</th></tr></thead>";
	$output .= " <tbody>";
	if ($showtrunc) {
		$output .= " <tr>";
		$output .= '  <td colspan="2">'.$viewData['lang']['truncate_fit'].html5('truncate',$viewData['fields']['truncate']) . "</td>";
		$output .= '  <td colspan="3">'.$viewData['lang']['display_on']  .html5('display', $viewData['fields']['display']) . "</td>";
		$output .= " </tr>";
	}
	if ($showfont) {
		$output .= ' <tr class="panel-header"><th>&nbsp;'.'</th><th>'.lang('style').'</th><th>'.lang('size').'</th><th>'.$viewData['lang']['align'].'</th><th>'.$viewData['lang']['color']."</th></tr>";
		$output .= " <tr>";
		$output .= "  <td>".lang('font')."</td>";
		$output .= "  <td>".html5($pre.'font',  $viewData['fields'][$pre.'font']) . "</td>";
		$output .= "  <td>".html5($pre.'size',  $viewData['fields'][$pre.'size']) . "</td>";
		$output .= "  <td>".html5($pre.'align', $viewData['fields'][$pre.'align']). "</td>";
		$output .= "  <td>".html5($pre.'color', $viewData['fields'][$pre.'color']). "</td>";
		$output .= " </tr>";
	}
	if ($showborder) {
		$output .= " <tr>";
		$output .= "  <td>".$viewData['lang']['border'] . "</td>";
		$output .= "  <td>".html5($pre.'bshow', $viewData['fields'][$pre.'bshow'])."</td>";
		$output .= "  <td>".html5($pre.'bsize', $viewData['fields'][$pre.'bsize']).$viewData['lang']['points']."</td>";
		$output .= "  <td>&nbsp;</td>";
		$output .= "  <td>".html5($pre.'bcolor', $viewData['fields'][$pre.'bcolor'])."</td>";
		$output .= "</tr>";
	}
	if ($showfill) {
		$output .= "<tr>";
		$output .= '  <td>'. $viewData['lang']['fill_area'] . "</td>";
		$output .= '  <td>'.html5($pre.'fshow',  $viewData['fields'][$pre.'fshow'])."</td>";
		$output .= "  <td>&nbsp;</td>";
		$output .= "  <td>&nbsp;</td>";
		$output .= "  <td>".html5($pre.'fcolor', $viewData['fields'][$pre.'fcolor'])."</td>";
		$output .= "</tr>";
	}
	$output .= "</tbody></table>";
	return $output;
}

// START BOX GENERATION
$output['body'] .= "<h1>".$viewData['title']."</h1>";
$output['body'] .= html5('frmFieldSettings', $viewData['forms']['frmFieldSettings']);

switch ($viewData['fields']['type']['attr']['value']) {
	case 'BarCode':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.lang('fieldname')."</th><th>".$viewData['lang']['processing']."</th><th>".$viewData['lang']['formatting']."</th></tr></thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= '    <td>'.html5('fieldname',  $viewData['fields']['fieldname']) ."</td>";
		$output['body'] .= '    <td>'.html5('processing', $viewData['fields']['processing'])."</td>";
		$output['body'] .= '    <td>'.html5('formatting', $viewData['fields']['formatting'])."</td>";
		$output['body'] .= "   </tr>";
		$output['body'] .= '   <tr><td colspan="2">'.$viewData['lang']['phreeform_barcode_type'].' '.html5('barcode', $viewData['fields']['barcodes'])."</td></tr>";
		$output['body'] .= "  </tbody></table>";
		$output['body'] .= box_build_attributes($viewData, false, false);
		break;
	case 'CDta':
	case 'Data':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.lang('fieldname')."</th><th>".$viewData['lang']['processing']."</th><th>".$viewData['lang']['formatting']."</th></tr></thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= '    <td>'.html5('fieldname',  $viewData['fields']['fieldname']) ."</td>";
		$output['body'] .= '    <td>'.html5('processing', $viewData['fields']['processing'])."</td>";
		$output['body'] .= '    <td>'.html5('formatting', $viewData['fields']['formatting'])."</td>";
		$output['body'] .= "  </tr></tbody></table>";
		$output['body'] .= box_build_attributes($viewData);
		break;
	case 'ImgLink':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.lang('fieldname')."</th><th>".$viewData['lang']['processing']."</th><th>".$viewData['lang']['formatting']."</th></tr></thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= '    <td>'.html5('fieldname',  $viewData['fields']['fieldname']) ."</td>";
		$output['body'] .= '    <td>'.html5('processing', $viewData['fields']['processing'])."</td>";
		$output['body'] .= '    <td>'.html5('formatting', $viewData['fields']['formatting'])."</td>";
		$output['body'] .= "  </tr></tbody></table>";
		$output['body'] .= box_build_attributes($viewData, false, false);
		break;
	case 'Img':
        $imgSrc = isset($viewData['fields']['img_file']['attr']['value']) ? $viewData['fields']['img_file']['attr']['value'] : "";
        $imgDir = dirname($imgSrc).'/';
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tbody>';
		$output['body'] .= '  <tr><td><div id="imdtl_img_file"></div>'.html5('img_file', $viewData['fields']['img_file']).'</td></tr></tbody></table>';
		$output['jsBody'][] = "imgManagerInit('img_file', '$imgSrc', '$imgDir', 'images/');";
		break;
	case 'Line':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th colspan="3">'.$viewData['lang']['phreeform_line_type']."</th></tr></thead>";
		$output['body'] .= " <tbody>";
		$output['body'] .= "  <tr><td>".html5('linetype', $viewData['fields']['linetype']).' '.html5('length', $viewData['fields']['length'])."</td></tr>";
		$output['body'] .= "  <tr><td>".$viewData['lang']['end_position'].' '.html5('endAbscissa', $viewData['fields']['endAbscissa']).' '.html5('endOrdinate', $viewData['fields']['endOrdinate'])."</td></tr>";
		$output['body'] .= " </tbody></table>";
		$output['body'] .= box_build_attributes($viewData, false, false, true, false);
		break;
	case 'LtrTpl':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.$viewData['lang']['phreeform_text_disp']."</td></tr></thead>";
		$output['body'] .= " <tbody><tr><td>".html5('ltrText', $viewData['fields']['ltrText'])."</td></tr></tbody>";
		$output['body'] .= "</table>";
		$output['body'] .= box_build_attributes($viewData);
		break;
	case 'TDup':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <tbody><tr><td style="text-align:center">'.$viewData['lang']['msg_no_settings']."</td></tr></tbody>";
		$output['body'] .= "</table>";
		break;
	case 'Text':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.$viewData['lang']['phreeform_text_disp']."</td></tr></thead>";
		$output['body'] .= " <tbody><tr><td>".html5('text', $viewData['fields']['text'])."</td></tr></tbody>";
		$output['body'] .= "</table>";
		$output['body'] .= box_build_attributes($viewData);
		break;
	case 'Tbl':
		$output['body'] .= box_build_attributes($viewData, false, true,  true, true, 'h', lang('heading'));
		$output['body'] .= box_build_attributes($viewData, false, false, true, true, '',  lang('body'));
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header">';
		$output['body'] .= '  <tr><th colspan="3">'.$viewData['lang']['encoded_table_title']."</th></tr>";
		$output['body'] .= "  <tr><th>".lang('fieldname')."</th><th>".$viewData['lang']['processing']."</th><th>".$viewData['lang']['formatting']."</th></tr>";
		$output['body'] .= " </thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= "  <td>".html5('fieldname',  $viewData['fields']['fieldname']) ."</td>";
		$output['body'] .= '  <td>'.html5('processing', $viewData['fields']['processing'])."</td>";
		$output['body'] .= '  <td>'.html5('formatting', $viewData['fields']['formatting'])."</td>";
		$output['body'] .= " </tr></tbody></table>";
		break;
	case 'PgNum':  $output['body'] .= box_build_attributes($viewData, false);        break;
	case 'Rect':   $output['body'] .= box_build_attributes($viewData, false, false); break;
	case 'CBlk':
	case 'TBlk':   $output['body'] .= box_build_attributes($viewData); break;
	case 'Ttl':    $output['body'] .= box_build_attributes($viewData); break;
}

if (isset($viewData['javascript']['dataFieldValues'])) {
	$output['jsBody'][] = $viewData['javascript']['dataFieldValues'];
	htmlDatagrid($output, $viewData, 'fields');
}
$output['body'] .= "</form>";
$output['jsBody'][] = "
var fieldIndex = 0;
jq('#frmFieldSettings').submit(function (e) {
	var fData = jq('form#frmFieldSettings').serializeObject();
	if (jq('#dgFieldValues').length) {
	    jq('#dgFieldValues').edatagrid('saveRow');
	    var items = jq('#dgFieldValues').datagrid('getData');
	    if (items) fData.boxfield = items.rows;
	}
	jq('#dgFields').datagrid('updateRow', { index: fieldIndex, row: { settings: JSON.stringify(fData) } });
	jq('#dgFields').datagrid('enableDnd');
	jq('#win_settings').window('close');
	e.preventDefault();
});";
