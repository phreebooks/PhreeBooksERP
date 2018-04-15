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
 * @version    2.x Last Update: 2018-02-13
 * @filesource /lib/view/module/phreeform/winFieldSettings.php
 */

namespace bizuno;

// This function generates the bizuno attributes for most boxes.
function box_build_attributes($data, $showtrunc=true, $showfont=true, $showborder=true, $showfill=true, $pre='', $title='')
{
	$output  = '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">' . "";
	$output .= ' <thead class="panel-header"><tr><th colspan="5">'.($title ? $title : lang('settings'))."</th></tr></thead>";
	$output .= " <tbody>";
	if ($showtrunc) {
		$output .= " <tr>";
		$output .= '  <td colspan="2">'.$data['lang']['truncate_fit'].html5('truncate',$data['fields']['truncate']) . "</td>";
		$output .= '  <td colspan="3">'.$data['lang']['display_on']  .html5('display', $data['fields']['display']) . "</td>";
		$output .= " </tr>";
	}
	if ($showfont) {
		$output .= ' <tr class="panel-header"><th>&nbsp;'.'</th><th>'.lang('style').'</th><th>'.lang('size').'</th><th>'.$data['lang']['align'].'</th><th>'.$data['lang']['color']."</th></tr>";
		$output .= " <tr>";
		$output .= "  <td>".lang('font')."</td>";
		$output .= "  <td>".html5($pre.'font',  $data['fields'][$pre.'font']) . "</td>";
		$output .= "  <td>".html5($pre.'size',  $data['fields'][$pre.'size']) . "</td>";
		$output .= "  <td>".html5($pre.'align', $data['fields'][$pre.'align']). "</td>";
		$output .= "  <td>".html5($pre.'color', $data['fields'][$pre.'color']). "</td>";
		$output .= " </tr>";
	}
	if ($showborder) {
		$output .= " <tr>";
		$output .= "  <td>".$data['lang']['border'] . "</td>";
		$output .= "  <td>".html5($pre.'bshow', $data['fields'][$pre.'bshow'])."</td>";
		$output .= "  <td>".html5($pre.'bsize', $data['fields'][$pre.'bsize']).$data['lang']['points']."</td>";
		$output .= "  <td>&nbsp;</td>";
		$output .= "  <td>".html5($pre.'bcolor', $data['fields'][$pre.'bcolor'])."</td>";
		$output .= "</tr>";
	}
	if ($showfill) {
		$output .= "<tr>";
		$output .= '  <td>'. $data['lang']['fill_area'] . "</td>";
		$output .= '  <td>'.html5($pre.'fshow',  $data['fields'][$pre.'fshow'])."</td>";
		$output .= "  <td>&nbsp;</td>";
		$output .= "  <td>&nbsp;</td>";
		$output .= "  <td>".html5($pre.'fcolor', $data['fields'][$pre.'fcolor'])."</td>";
		$output .= "</tr>";
	}
	$output .= "</tbody></table>";
	return $output;
}

// START BOX GENERATION
$output['body'] .= "<h1>".$data['title']."</h1>";
$output['body'] .= html5('frmFieldSettings', $data['form']['frmFieldSettings']);

switch ($data['fields']['type']['attr']['value']) {
	case 'BarCode':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.lang('fieldname')."</th><th>".$data['lang']['processing']."</th><th>".$data['lang']['formatting']."</th></tr></thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= '    <td>'.html5('fieldname',  $data['fields']['fieldname']) ."</td>";
		$output['body'] .= '    <td>'.html5('processing', $data['fields']['processing'])."</td>";
		$output['body'] .= '    <td>'.html5('formatting', $data['fields']['formatting'])."</td>";
		$output['body'] .= "   </tr>";
		$output['body'] .= '   <tr><td colspan="2">'.$data['lang']['phreeform_barcode_type'].' '.html5('barcode', $data['fields']['barcodes'])."</td></tr>";
		$output['body'] .= "  </tbody></table>";
		$output['body'] .= box_build_attributes($data, false, false);
		break;
	case 'CDta':
	case 'Data':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.lang('fieldname')."</th><th>".$data['lang']['processing']."</th><th>".$data['lang']['formatting']."</th></tr></thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= '    <td>'.html5('fieldname',  $data['fields']['fieldname']) ."</td>";
		$output['body'] .= '    <td>'.html5('processing', $data['fields']['processing'])."</td>";
		$output['body'] .= '    <td>'.html5('formatting', $data['fields']['formatting'])."</td>";
		$output['body'] .= "  </tr></tbody></table>";
		$output['body'] .= box_build_attributes($data);
		break;
	case 'ImgLink':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.lang('fieldname')."</th><th>".$data['lang']['processing']."</th><th>".$data['lang']['formatting']."</th></tr></thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= '    <td>'.html5('fieldname',  $data['fields']['fieldname']) ."</td>";
		$output['body'] .= '    <td>'.html5('processing', $data['fields']['processing'])."</td>";
		$output['body'] .= '    <td>'.html5('formatting', $data['fields']['formatting'])."</td>";
		$output['body'] .= "  </tr></tbody></table>";
		$output['body'] .= box_build_attributes($data, false, false);
		break;
	case 'Img':
        $imgSrc = isset($data['fields']['img_file']['attr']['value']) ? $data['fields']['img_file']['attr']['value'] : "";
        $imgDir = dirname($imgSrc).'/';
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;"><tbody>';
		$output['body'] .= '  <tr><td><div id="imdtl_img_file"></div>'.html5('img_file', $data['fields']['img_file']).'</td></tr></tbody></table>';
		$output['jsBody'][] = "imgManagerInit('img_file', '$imgSrc', '$imgDir', 'images/');";
		break;
	case 'Line':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th colspan="3">'.$data['lang']['phreeform_line_type']."</th></tr></thead>";
		$output['body'] .= " <tbody>";
		$output['body'] .= "  <tr><td>".html5('linetype', $data['fields']['linetype']).' '.html5('length', $data['fields']['length'])."</td></tr>";
		$output['body'] .= "  <tr><td>".$data['lang']['end_position'].' '.html5('endAbscissa', $data['fields']['endAbscissa']).' '.html5('endOrdinate', $data['fields']['endOrdinate'])."</td></tr>";
		$output['body'] .= " </tbody></table>";
		$output['body'] .= box_build_attributes($data, false, false, true, false);
		break;
	case 'LtrTpl':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.$data['lang']['phreeform_text_disp']."</td></tr></thead>";
		$output['body'] .= " <tbody><tr><td>".html5('ltrText', $data['fields']['ltrText'])."</td></tr></tbody>";
		$output['body'] .= "</table>";
		$output['body'] .= box_build_attributes($data);
		break;
	case 'TDup':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <tbody><tr><td style="text-align:center">'.$data['lang']['msg_no_settings']."</td></tr></tbody>";
		$output['body'] .= "</table>";
		break;
	case 'Text':
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header"><tr><th>'.$data['lang']['phreeform_text_disp']."</td></tr></thead>";
		$output['body'] .= " <tbody><tr><td>".html5('text', $data['fields']['text'])."</td></tr></tbody>";
		$output['body'] .= "</table>";
		$output['body'] .= box_build_attributes($data);
		break;
	case 'Tbl':
		$output['body'] .= box_build_attributes($data, false, true,  true, true, 'h', lang('heading'));
		$output['body'] .= box_build_attributes($data, false, false, true, true, '',  lang('body'));
		$output['body'] .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">';
		$output['body'] .= ' <thead class="panel-header">';
		$output['body'] .= '  <tr><th colspan="2">'.$data['lang']['encoded_table_title']."</th></tr>";
		$output['body'] .= "  <tr><th>".lang('fieldname')."</th><th>".$data['lang']['processing']."</th><th>".$data['lang']['formatting']."</th></tr>";
		$output['body'] .= " </thead>";
		$output['body'] .= " <tbody><tr>";
		$output['body'] .= "  <td>".html5('fieldname',  $data['fields']['fieldname']) ."</td>";
		$output['body'] .= '  <td>'.html5('processing', $data['fields']['processing'])."</td>";
		$output['body'] .= '  <td>'.html5('formatting', $data['fields']['formatting'])."</td>";
		$output['body'] .= " </tr></tbody></table>";
		break;
	case 'PgNum':  $output['body'] .= box_build_attributes($data, false);        break;
	case 'Rect':   $output['body'] .= box_build_attributes($data, false, false); break;
	case 'CBlk':
	case 'TBlk':   $output['body'] .= box_build_attributes($data); break;
	case 'Ttl':    $output['body'] .= box_build_attributes($data); break;
}

if (isset($data['javascript']['dataFieldValues'])) {
	$output['jsBody'][] = $data['javascript']['dataFieldValues'];
	htmlDatagrid($output, $data, 'fields');
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
