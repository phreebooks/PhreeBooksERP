<?php
/*
 * Sets the view structure for the home and menu header views, each dashboard is loaded through ajax
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
 * @version    2.x Last Update: 2017-05-14

 * @filesource /lib/view/module/bizuno/divDashboard.php
 */

namespace bizuno;
$opts = '';
if (clean('lost',   'cmd','get') == 'true') { $opts .= '&lost=true'; }
if (clean('newuser','cmd','get') == 'true') { $opts .= '&newuser=true'; }

$cols = isset($data['cols']) ? $data['cols'] : 3;
$width = round(100/$cols, 0);

$output['jsBody'][] = "
var menuID = '".$data['menu_id']."';
var panels = new Array();
function getPanelOptions(id) {
	for (var i=0; i<panels.length; i++) if (panels[i].id == id) return panels[i];
	return undefined;
}
function getPortalState(){
	var aa = [];
	for (var columnIndex=0; columnIndex<$cols; columnIndex++){
		var cc = [];
		var panels = jq('#dashboard').portal('getPanels', columnIndex);
		for (var i=0; i<panels.length; i++) cc.push(panels[i].attr('id'));
		aa.push(cc.join(','));
	}
	return aa.join(':');
}
function addPanels(json) {
	if (json.message) displayMessage(json.message);
	for (var i=0; i<json.Dashboard.length; i++) { panels.push(json.Dashboard[i]); }
	var portalState = json.State;
	var columns     = portalState.split(':');
	for (var columnIndex=0; columnIndex<columns.length; columnIndex++){
		var cc = columns[columnIndex].split(',');
		for (var j=0; j<cc.length; j++) {
			var options = getPanelOptions(cc[j]);
			if (options) {
				var p = jq('<div></div>').attr('id',options.id).appendTo('body');
                var panelHref = options.href;
                options.href = '';
				p.panel(options);
				p.panel({ href:panelHref,onBeforeClose:function() { if (confirm('".jsLang('msg_confirm_delete')."')) { dashboardDelete(this); } else { return false } } });
				jq('#dashboard').portal('add',{ panel:p, columnIndex:columnIndex });
			}
		}
	}
}
jq(function(){
	jq('#dashboard').portal({
		border:false,
		onStateChange:function(){
			var state = getPortalState();
			jq.ajax({ url:'".BIZUNO_AJAX."&p=bizuno/dashboard/organize&menuID='+menuID+'&state='+state, });
		}
	});
	jq.ajax({
		url: '".BIZUNO_AJAX."&p=bizuno/dashboard/render$opts&menuID='+menuID,
		success: addPanels
	});
});";

if ($data['menu_id'] <> 'portal') {
    $output['body'] .= '<div class="datagrid-toolbar" style="min-height:32px;">'."\n";
    if (getUserCache('profile', 'biz_id', false, 0)) {
        $output['body'] .= '  <a href="'.BIZUNO_HOME.'&p=bizuno/dashboard/manager&menuID='.$data['menu_id'].'">'.$data['lang']['msg_add_dashboards']."</a>\n";
    }
    $output['body'] .= "</div>\n";
}
$output['body'] .= '<div id="dashboard" style="clear:both;">'."\n";
for ($i=0; $i<$cols; $i++) {
    $output['body'] .= '	<div style="width:'.$width.'%"></div>'."\n";
}
$output['body'] .= "</div>\n";
