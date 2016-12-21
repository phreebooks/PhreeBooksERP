<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /modules/contacts/pages/main/template_detail.php
//
echo "<div data-options=\"region:'center'\">";
echo "<form id='contacts' style='padding:10px 20px 10px 40px;' method='post' enctype='multipart/form-data'>";
// include hidden fields
echo \core\classes\htmlElement::hidden('payment_id') . chr(10);
echo \core\classes\htmlElement::hidden('attachments', serialize($basis->cInfo->contact->attachments)) . chr(10);


$basis->cInfo->contact->fields->display($basis->cInfo->contact);
// Build the page
$custom_path = DIR_FS_MODULES . 'contacts/custom/pages/main/extra_tabs.php';
if (file_exists($custom_path)) { include($custom_path); }

function tab_sort($a, $b) {
	if ($a['order'] == $b['order']) return 0;
	return ($a['order'] > $b['order']) ? 1 : -1;
}
usort($basis->cInfo->contact->tab_list, 'tab_sort');
?>
<div id="mainToolbar" data-options="region:'north'" >
    <a href="#" class="easyui-linkbutton" iconCls="icon-cancel" onclick="location.href = 'index.php?action=LoadContactMgrPage&type=<?php echo $basis->cInfo->contact->type;?>'"><?php echo TEXT_UNDO;?></a>
   	<a href="#" class="easyui-linkbutton" iconCls="icon-save"   onclick="$('#contacts').submit();"><?php echo TEXT_SAVE;?></a>
   	<a href="#" class="easyui-linkbutton" iconCls="icon-help"   onclick="loadHelp('editContact');"><?php echo TEXT_HELP;?></a>
   	<a href="#" class="easyui-linkbutton" iconCls="icon-email"  onclick="deleteContact()"><?php echo TEXT_MAIL;?></a>//@todo
   	<h1><?php echo "{$basis->cInfo->contact->page_title_edit} - ({$basis->cInfo->contact->short_name}) "; ?></h1>
</div>
		<div id="Tabs" class="easyui-tabs" border="false" plain="true" >
		<?php
		foreach ($basis->cInfo->contact->tab_list as $value) {
			if (file_exists(DIR_FS_MODULES . "contacts/custom/pages/main/{$value['file']}.php")) {
				include(DIR_FS_MODULES . "contacts/custom/pages/main/{$value['file']}.php");
		  	} else {
				include(DIR_FS_MODULES . "contacts/pages/main/{$value['file']}.php");
		  	}
		}
		echo $basis->cInfo->contact->fields->extra_tab_html;// user added extra tabs
		?>
		</div>
	</form>
</div>
<script type="text/javascript">
	document.title = '<?php echo "{$basis->cInfo->contact->page_title_edit} - ({$basis->cInfo->contact->short_name}) "; ?>';
$('#Tabs').tabs({
	onAdd:function(title, index){
		console.log('we are adding = '+ title+' index= '+index);
	},
	onLoadError:function(){
		console.error('There has been a error loading the tabs');
	},
})
$('#contacts').form({
	queryParams: {
		action: 'saveContact',
		id: '<?php echo $basis->cInfo->contactid;?>',
		dataType: 'json',
        contentType: 'application/json',
        async: false
	},
	onSubmit :function(){
		if($('#editAddress').form('validate')){
			console.log('the address form is valid');
			if ($(this).form('validate')){
				return true;
			}else{
				console.error('the contact form is not valid');
			}
		}else{
			console.error('the address form is not valid');
			return false;
		}
	},
	success: function(data){
    	data = eval('('+data+')');
    	console.log('data returned is '+json.stringify(data));
        if (data.error_message){
        	console.error(data.error_message);
            $.messager.show({ title: '<?php echo TEXT_ERROR?>', msg: data.error_message });
        }else{
        	$('#editMainAddress').form(submit,{queryParams:function(param){param.contactid = data.id}});//@todo test
        	location.href = 'index.php?action=LoadContactMgrPage&type=<?php echo $basis->cInfo->contact->type;?>';
        }
    }
})
</script>