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
//  Path: /modules/phreedom/dashboards/my_notes/my_notes.php
//
// Revision history
// 2011-07-01 - Added version number for revision control
namespace phreedom\dashboards\my_notes;
class my_notes extends \core\classes\ctl_panel {
	public $description	 		= CP_MY_NOTES_DESCRIPTION;
	public $security_id  		= SECURITY_ID_MY_PROFILE;
	public $text		 		= CP_MY_NOTES_TITLE;
	public $version      		= '4.0';

	function panelContent(){
		?>
		
		<div id='my_notes'></div>
		
		<script type="text/javascript">
		$('#my_notes').datalist({
			url:		"index.php?action=getNotes",
			queryParams: { 
				user_id: <?php echo $_SESSION['user']->admin_id ?>,
				dashboard: 'my_notes',
				dataType: 'json',
		        contentType: 'application/json',
		        async: false,
			},
            checkbox: true,
            selectOnCheck: false,
			onBeforeLoad:function(){
				console.log('loading of the My Notes datagrid');
			},
			onLoadSuccess: function(data){
				console.log('the loading of the My Notes was succesfull');
				$.messager.progress('close');
			},
			onLoadError: function(){
				console.error('the loading of the My Notes resulted in a error');
				$.messager.progress('close');
				$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table My Notes');
			},
			onCheck: function(index , row){
				console.log('a row in the My Notes was checked');
				//@todo open order
			},
			remoteSort:	true,
			fitColumns:	true,
			idField:	"id",
			singleSelect:true,
			sortName:	"post_date",
			sortOrder: 	"dsc",
			loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
			rowStyler: function(index,row){
				if (row.closed == '1') return 'background-color:pink;';
			},
		});
		</script><?php
	}
}
?>