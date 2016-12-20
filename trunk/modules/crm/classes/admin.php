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
//  Path: /modules/crm/classes/admin.php
//
namespace crm\classes;
class admin extends \core\classes\admin {
	public $sort_order  = 5;
	public $id 			= 'crm';
	public $description = MODULE_CRM_DESCRIPTION;
	public $core		= true;
	public $version		= '4.0.2-dev';
	public $crm_actions;

	function __construct() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_CRM);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'contacts'   => '4.0.2',
		);
		
		// Load tables
		$this->tables = array(
		  TABLE_CONTACTS_LOG => "CREATE TABLE " . TABLE_CONTACTS_LOG . " (
			  log_id int(11) NOT NULL auto_increment,
			  contact_id int(11) NOT NULL default '0',
			  entered_by int(11) NOT NULL default '0',
			  log_date datetime NOT NULL default '0000-00-00',
			  crmaction varchar(32) NOT NULL default '',
			  notes text,
			  PRIMARY KEY (log_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	    );
		$this->crm_actions = array(
			''     => TEXT_NONE,
			'0'    => TEXT_NONE,
			'new'  => sprintf(TEXT_NEW_ARGS, TEXT_CALL),
			'ret'  => TEXT_RETURNED_CALL,
			'flw'  => TEXT_FOLLOW_UP_CALL,
			'inac' => TEXT_INACTIVE,
			'lead' => sprintf(TEXT_NEW_ARGS, TEXT_LEAD),
			'mail_in'  => TEXT_EMAIL_RECEIVED,
			'mail_out' => TEXT_EMAIL_SEND,
	);
		// Set the menus
		$this->mainmenu["customers"]->submenu ["crm"] 		= new \core\classes\menuItem (15, 	TEXT_CRM,			'action=LoadContactMgrPage&amp;type=i');
//		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["crm"]  = new \core\classes\menuItem (sprintf(TEXT_MODULE_ARGS, TEXT_CRM), sprintf(TEXT_MODULE_ARGS, TEXT_CRM),	'module=crm&amp;page=admin');
	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $admin;
	    parent::install($path_my_files, $demo);
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num'))  $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '10000';");
		require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
		\core\classes\fields::sync_fields('contacts', TABLE_CONTACTS);
	}

  	function upgrade(\core\classes\basis &$basis) {
    	parent::upgrade($basis);
    	if (version_compare($this->status, '3.7', '<') ) {
      		if (!$basis->DataBase->field_exists(TABLE_CONTACTS_LOG, 'entered_by')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS_LOG." ADD entered_by INT(11) NOT NULL DEFAULT '0' AFTER contact_id");
    	}
		if (version_compare($this->status, '4.0.1', '<') ) {
			if (!$basis->DataBase->field_exists(TABLE_CONTACTS_LOG, 'crmaction')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS_LOG." CHANGE `action` `crmaction` VARCHAR(32) NOT NULL DEFAULT ''");
		}
  	}

	function delete($path_my_files) {
	    global $admin;
	    parent::delete($path_my_files);
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_crm_id_desc");
	}
	
	function after_editContact(\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		?>
		<script type="text/javascript">
			$(document).ready(function(){
				$('#Tabs').tabs('add',{
		 			id:'notes_panel',
		            title:"<?php echo TEXT_NOTES;?>",
		            selected: false
		        });
				document.getElementById('notes_panel').appendChild(document.getElementById('notes_content'));
	
				$('#notes_table').datagrid({
					url:		"index.php?action=loadCRMHistory",
					queryParams: {
						contact_id: '<?php echo $basis->cInfo->contact->id;?>',
						dataType: 'json',
				        contentType: 'application/json',
				        async: false,
					},
					width: '100%',
					height: '500px',
					onBeforeLoad:function(){
						console.log('loading of the crm history datagrid');
					},
					onLoadSuccess: function(data){
						console.log('the loading of the crm history datagrid was succesfull');
						$.messager.progress('close');
					},
					onLoadError: function(){
						console.error('the loading of the crm history datagrid resulted in a error');
						$.messager.progress('close');
						$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table crm notes');
					},
					onDblClickRow: function(index , row){
						console.log('a row in the crm history was double clicked');
						$('#notes_table').datagrid('expandRow', index);
					},
					toolbar: "#notes_toolbar",
					remoteSort:	false,
					fitColumns:	true,
					idField:	"log_id",
					singleSelect:true,
					sortName:	"log_date",
					sortOrder: 	"dsc",
					loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				    view: detailview,
				    detailFormatter:function(index,row){
				        return '<div class="ddv"></div>';
				    },
				    onExpandRow: function(index, row){
				        var ddv = $(this).datagrid('getRowDetail',index).find('div.ddv');
				        ddv.panel({
				            border:false,
				            queryParams: {
				            	contentType:'inlineForm',
						        async: false,
							},
				            cache:true,
				            href:'index.php?action=editCRM&contact_id=<?php echo $basis->cInfo->contact->id;?>',
				            loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				            onLoad:function(){
				                $('#notes_table').datagrid('fixDetailRowHeight',index);
				                $('#notes_table').datagrid('selectRow',index);
								console.log('a start loading the form');
				                $('#notes_table').datagrid('getRowDetail',index).find('form').form('load',row);
				            },
				            onBeforeLoad:function(param){
				        		console.log('loading the crm form');
				        	},
				        	onLoadSuccess:function(data){
				        		console.log('the loading the crm form was succesfull');
				        		$.messager.progress('close');
				        	},
				            onLoadError: function(){
				        		console.error('the loading of the crm form resulted in a error');
				        		$.messager.progress('close');
				        		$.messager.alert('<?php echo TEXT_ERROR?>','Load error for crm form');
				        	},
				        });
				    }
				});
			})
			
			function ConvertCrmAction (value){
				<?php foreach ($this->crm_actions as $key => $action){
					echo "if(value == '{$key}') return '$action';" .chr(13);
				}?>
			}

			function newCRM(){
				console.log('new crm was clicked');
			    $('#notes_table').datagrid('appendRow',{isNewRecord:true});
			    var index = $('#notes_table').datagrid('getRows').length - 1;
			    $('#notes_table').datagrid('selectRow', index);
			    $('#notes_table').datagrid('expandRow', index);
			    $('#notes_table').datagrid('getRowDetail',index).find('form').form('reset');
			}

			function cancelCRM(index){
				console.log('cancel crm was clicked');
			    var row = $('#notes_table').datagrid('getRows')[index];
			    if (row.isNewRecord){
			        $('#notes_table').datagrid('deleteRow',index);
			    } else {
			        $('#notes_table').datagrid('collapseRow',index);
			    }
			}
						
			</script>
			<fieldset id='notes_content'>
				<div style="float:right;width:50%">
					<table id='notes_table' title="<?php echo TEXT_HISTORY; ?>">
					    <thead>
					   		<tr>
					        	<th data-options="field:'name',sortable:true, align:'left'"><?php echo TEXT_WITH;?></th>
				    	        <th data-options="field:'user_name',sortable:true, align:'left'"><?php echo TEXT_ENTERED_BY?></th>	
				    	        <th data-options="field:'log_date',sortable:true, align:'right', formatter: function(value,row,index){ return formatDateTime(value)}"><?php echo TEXT_DATE?></th>
						        <th data-options="field:'crmaction',sortable:true, align:'left', formatter: function(value,row,index){ return ConvertCrmAction(value)}"><?php echo TEXT_ACTION?></th>
						        <th data-options="field:'notes',sortable:false, align:'left'"><?php echo TEXT_NOTE?></th>		        
					    	</tr>
					   	</thead>
					</table>
					<div id="notes_toolbar">
				        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newCRM()"><?php echo sprintf(TEXT_NEW_ARGS, TEXT_CRM); ?></a>
				    </div>
				</div>
			    <div style="width:50%"><?php echo html_textarea_field("address[{$basis->cInfo->contact->type}m][notes]", 60, 30, $basis->cInfo->contact->address[$basis->cInfo->contact->type.'m']['notes']); ?></div>
			  </fieldset>
		<?php 				
	}
	
	function loadCRMHistory (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT DISTINCT l.log_id, l.entered_by, l.contact_id, u.display_name as user_name, l.log_date, l.crmaction, l.notes, c.contact_first, c.contact_last, a.primary_name, CASE WHEN c.contact_last != '' THEN CONCAT(c.contact_first,' ',c.contact_middle,' ',c.contact_last) ELSE a.primary_name END AS name FROM ".TABLE_CONTACTS_LOG." AS l JOIN ".TABLE_CONTACTS." AS c ON l.contact_id = c.id JOIN ".TABLE_ADDRESS_BOOK." AS a ON c.id = a.ref_id JOIN ".TABLE_USERS." AS u ON l.entered_by = u.admin_id WHERE (c.dept_rep_id ={$basis->cInfo->contact_id} OR c.id ={$basis->cInfo->contact_id})");
		$sql->execute();
		$results = $sql->fetchAll(\PDO::FETCH_ASSOC);
		
		$basis->cInfo->total = sizeof($results);
		$basis->cInfo->rows = $results;
	}
	
	function editCRM (\core\classes\basis &$basis) {
		$sql = $basis->DataBase->prepare("SELECT id, CONCAT (contact_first,' ',contact_last) as text FROM ".TABLE_CONTACTS." WHERE type='e'");
		$sql->execute();
		$all_employees       = $sql->fetchAll();
	?>
		<form id="crm_form" method="post">
			<?php echo  \core\classes\htmlElement::hidden('log_id').chr(13).
						\core\classes\htmlElement::hidden('contact_id', $basis->cInfo->contact_id).chr(13).
						\core\classes\htmlElement::combobox('entered_by', TEXT_SALES_REP, $all_employees). '<br/>'.chr(13).
				   		\core\classes\htmlElement::dateAndTime('log_date', TEXT_DATE_AND_TIME). '<br/>'.chr(13).
	            		\core\classes\htmlElement::combobox('crmaction', TEXT_ACTION, $this->crm_actions). '<br/>'.chr(13).
	            		\core\classes\htmlElement::textarea('notes', TEXT_NOTE, null, "style='width:100px,heigth:50px'"). '<br/>'.chr(13)?>
            <div style="padding:5px 0;text-align:right;padding-right:100px">
		    	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save1(this)"><?php echo TEXT_SAVE?></a>
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="cancel1(this)"><?php echo TEXT_CLEAR?></a>
        	</div>
        	<script type="text/javascript">
	            function save1(target){
	            	console.log('save crm was clicked');
	            	$('#crm_form').form('submit');
	            }
	            function cancel1(target){
	                var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	                var index = parseInt(tr.attr('datagrid-row-index'));
	                cancelCRM(index);
	            }
	            $('#crm_form').form({
	        		url:'index.php?action=saveCRM',
	        		queryParams: {
						dataType: 'json',
				        contentType: 'application/json',
				        async: false
					},
					onLoadSuccess: function(data){
	                	$('#log_date').datetimebox('setValue', formatDateTime(data.log_date));	
	                	console.log('succesfull loaded form data = '+JSON.stringify(data));
		            },
			        onSubmit: function(param){
						console.log('submitting crm form');
			            return $(this).form('validate');
			        },
			        success: function(data){
			        	console.log('succesfull submitted crm form');
	                	data = eval('('+data+')');
	                	console.log(data);
	                    if (data.error_message){
	                    	console.error(data.error_message);
	                        $.messager.show({ title: '<?php echo TEXT_ERROR?>', msg: data.error_message });
	                    }else{
		                    data.isNewRecord = false;
		                    var row = $('#notes_table').datagrid('getSelected');
		                    var index = $('#notes_table').datagrid('getRowIndex', row);
		                    $('#notes_table').datagrid('collapseRow',index);
		                    $('#notes_table').datagrid('updateRow',{
		                        index: index,
		                        row: data
		                    });
	                    }
			        }
	            });
        	</script>
	   </form><?php 
	}
	
	function saveCRM (\core\classes\basis &$basis) {
		$date = \core\classes\DateTime::db_date_time_format(trim($basis->cInfo->log_date));
		$basis->cInfo->log_date = $date;
		$sql = $basis->DataBase->prepare("INSERT INTO " . TABLE_CONTACTS_LOG . " (`log_id`, `contact_id`, `entered_by`, `log_date`, `crmaction`, `notes`) VALUES (:log_id, :contact_id, :entered_by, :log_date, :action, :notes) ON DUPLICATE KEY UPDATE contact_id = :contact_id, entered_by = :entered_by, log_date = :log_date, crmaction = :action, notes = :notes");
		if ($sql->execute(array(':log_id' => $basis->cInfo->log_id,':contact_id' => $basis->cInfo->contact_id, ':entered_by' => $basis->cInfo->entered_by, ':log_date' => $date, ':action' => $basis->cInfo->crmaction,':notes' => $basis->cInfo->notes))){
			$basis->cInfo->success = true;
			$basis->cInfo->message = TEXT_SAVED_SUCCESSFULLY;
		}else{
			$basis->cInfo->success = false;
			$basis->cInfo->error_message = TEXT_SAVED_FAILED;
		}
	}
}
?>