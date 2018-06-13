<?php
/*
 * View for managing GL chart of accounts
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
 * @version    2.x Last Update: 2017-11-09
 * @filesource /lib/view/module/phreebooks/accGLMgr.php
 */

namespace bizuno;

if (isset($viewData['values']['coa_blocked']) && !$viewData['values']['coa_blocked']) {
	$output['body'] .= "
	<fieldset><legend>".$viewData['lang']['coa_import_title']."</legend><p>".$viewData['lang']['coa_import_desc']."</p>\n".
	html5('sel_coa', $viewData['fields']['sel_coa']).
	html5('btn_coa_pre', $viewData['fields']['btn_coa_pre']).html5('btn_coa_imp', $viewData['fields']['btn_coa_imp'])."<br /><br />".
	html5('frmGlUpload', $viewData['forms']['frmGlUpload']).html5('file_coa',$viewData['fields']['file_coa']).html5('btn_coa_upl',$viewData['fields']['btn_coa_upl'])."
	</form><br />
	</fieldset>\n";
	$output['jsBody'][] = "
ajaxForm('frmGlUpload');
function previewGL() {
    if (jq('#popupGL').length) jq('#popupGL').remove();
    var newdiv1 = jq('<div id=\"popupGL\" title=\"".jsLang('btn_coa_preview')."\" class=\"easyui-window\"></div>');
    jq('body').append(newdiv1);
    jq('#popupGL').window({ width:800, height:600, closable:true, modal:true });
    jq('#popupGL').window('center');
    jq('#popupGL').html('<table id=\"dgPopupGL\"></table><script type=\"text/javascript\">loadPreview();<'+'/script>');
}
function loadPreview() {
    jq('#dgPopupGL').datagrid({ pagination:false,
        url:'".BIZUNO_AJAX."&p=phreebooks/chart/preview&chart='+jq('#sel_coa').val(),
        columns:[[
            {field:'id',title:'"   .jsLang('id')   ."',width: 50},
            {field:'type',title:'" .jsLang('type') ."',width:100},
            {field:'title',title:'".jsLang('title')."',width:200} ]]
    });
}";
} else {
	$output['body'] .= "<fieldset><legend>".$viewData['lang']['coa_import_title']."</legend><p>".$viewData['lang']['coa_import_blocked']."</p></fieldset>\n";
}
$output['body'] .= "<p>&nbsp;</p>\n";
htmlDatagrid($output, $viewData, 'dgChart');
$output['jsBody'][] = "jq('#dgChart').datagrid('clientPaging');";
