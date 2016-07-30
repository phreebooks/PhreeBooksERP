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
	public $version		= '4.0-dev';

	function __construct() {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_CRM);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 4.0,
		  'phreebooks' => 4.0,
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
			if (!$basis->DataBase->field_exists(TABLE_CONTACTS_LOG, 'crmaction')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS_LOG." CHANGE `action` `crmaction` VARCHAR(32) NOT NULL DEFAULT '';");
		}
  	}

	function delete($path_my_files) {
	    global $admin;
	    parent::delete($path_my_files);
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_crm_id_desc");
	}
	
	function after_editContact(\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		?><script type="text/javascript">
			$('#contacts_tabs').tabs('add',{
				title:'<?php echo TEXT_NOTES;?>',
				content:'Tab Body',
				selected: false,
				index: 4,
			});
			</script>
		<?php 				
	}
	
	function loadCRMHistory (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT l.log_id, l.entered_by, l.contact_id, u.display_name as user_name, l.log_date, l.crmaction, l.notes, c.contact_first, c.contact_last, a.primary_name, CASE WHEN c.contact_last != '' THEN CONCAT(c.contact_first,' ',c.contact_middle,' ',c.contact_last) ELSE a.primary_name END AS name FROM ".TABLE_CONTACTS_LOG." AS l JOIN ".TABLE_CONTACTS." AS c ON l.contact_id = c.id JOIN ".TABLE_ADDRESS_BOOK." AS a ON c.id = a.ref_id JOIN ".TABLE_USERS." AS u ON l.entered_by = u.admin_id WHERE (c.dept_rep_id ={$basis->cInfo->contact_id} OR c.id ={$basis->cInfo->contact_id})");
		$sql->execute();
		$results = $sql->fetchAll(\PDO::FETCH_ASSOC);
		$temp = array();
		$temp["total"] = sizeof($results);
		$temp["rows"] = $results;
		echo json_encode($temp);
	}
	
	function editCRM (\core\classes\basis &$basis) {
		$sql = $basis->DataBase->prepare("SELECT id, CONCAT (contact_first,' ',contact_last) as text FROM ".TABLE_CONTACTS." WHERE type='e'");
		$sql->execute();
		$all_employees       = $sql->fetchAll();
		$crm_actions = array(
			array('id'=> '', 		'text'	=> TEXT_NONE),
			array('id'=> 'new', 	'text'	=> sprintf(TEXT_NEW_ARGS, TEXT_CALL)),
			array('id'=> 'ret', 	'text'	=> TEXT_RETURNED_CALL),
			array('id'=> 'flw', 	'text'	=> TEXT_FOLLOW_UP_CALL),
			array('id'=> 'inac', 	'text'	=> TEXT_INACTIVE),
			array('id'=> 'lead', 	'text'	=> sprintf(TEXT_NEW_ARGS, TEXT_LEAD)),
			array('id'=> 'mail_in', 'text'	=> TEXT_EMAIL_RECEIVED),
			array('id'=> 'mail_out','text'	=> TEXT_EMAIL_SEND),
		);
	?>
		<form id="crm_form" method="post">
			<?php echo html_hidden_field('log_id', '');?>
			<?php echo html_hidden_field('contact_id', $basis->cInfo->contact_id);?>
			<table class="dv-table" style="width:100%;border:1px solid #ccc;padding:5px;margin-top:5px;">
				<tbody>
					<tr>
						<td style="width:80px;"><?php echo TEXT_SALES_REP; ?></td>
				   		<td> <?php echo html_pull_down_menu('entered_by', $all_employees); ?></td>
					</tr>
	            	<tr>
	            		<td style="width:80px;"><?php echo TEXT_DATE; ?></td>
		        		<td><?php echo html_date_time_field('log_date'); ?></td>
	            	</tr>
	            	<tr>
	            		<td style="width:80px;"><?php echo TEXT_ACTION; ?></td>
						<td><?php echo html_pull_down_menu('crmaction', $crm_actions); ?></td>
	            	</tr>
	            	<tr>
			        	<td style="width:80px;"><?php echo TEXT_NOTE; ?></td>
	            		<td><?php echo html_textarea_field('notes', 60, 1, '', ''); ?></td>
	            	</tr>
	            </tbody>
            </table>
            <div style="padding:5px 0;text-align:right;padding-right:100px">
		    	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save1()"><?php echo TEXT_SAVE?></a>
            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="cancel1(this)"><?php echo TEXT_CLEAR?></a>
        	</div>
        	<script type="text/javascript">
	            function save1(){
	            	$('#crm_form').form('submit');
//	                var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
//	                var index = parseInt(tr.attr('datagrid-row-index'));
//	                saveCRM(index+1);
	            }
	            function cancel1(target){
	                var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	                var index = parseInt(tr.attr('datagrid-row-index'));
	                console.log(index)
	                cancelCRM(index+1);
	            }
	            $('#crm_form').form({
	        		url:'index.php?action=saveCRM',
	        		novalidate: true,
	                onSubmit: function(param){
	        			console.log('submitting form');
//	                    return true;
	                },
	                success: function(data){
	                	console.log('succesfull submitted form');
	                    data = eval('('+data+')');
	                    data.isNewRecord = false;
	                    var row = $('#notes_table').datagrid('getSelected');
	                    var index = $('#notes_table').datagrid('getRowIndex', row);
	                    $('#notes_table').datagrid('collapseRow',index);
	                    $('#notes_table').datagrid('updateRow',{
	                        index: index,
	                        row: data
	                    });
	                }
	                
	            });
        	</script>
	   </form><?php 
	}
	
	function saveCRM (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__. print_r($basis->cInfo, true) );
		$sql = $this->prepare("INSERT INTO " . TABLE_CONTACTS_LOG . " (`log_id`, `contact_id`, `entered_by`, `log_date`, `action`, `notes`) VALUES (:log_id, :contact_id, :entered_by, :log_date, :action, :notes) ON DUPLICATE KEY UPDATE contact_id = :contact_id, enterd_by = :entered_by, log_date= :log_date, action= :action, notes = :notes");
		$sql->execute(array(':log_id' => $basis->cInfo->log_id,':contact_id' => $basis->cInfo->contact_id, ':entered_by' => $basis->cInfo->enterd_by, ':log_date' => $basis->cInfo->log_date, ':action' => $basis->cInfo->action,':notes' => $basis->cInfo->notes));
		$temp["success"] = true;
		$temp["message"] = TEXT_SAVED_SUCCESSFULLY;
		echo json_encode($temp);
	}
}
?>