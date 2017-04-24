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
//  Path: /modules/phreemail/classes/install.php
//
namespace phreemail\classes;
// Release History
// 1.0 - created
// New Database Tables
define('TABLE_PHREEMAIL',			DB_PREFIX . 'phreemail');
define('TABLE_PHREEMAIL_DIR',		DB_PREFIX . 'phreemail_dir');
define('TABLE_PHREEMAIL_LIST',		DB_PREFIX . 'phreemail_list');
define('TABLE_PHREEMAIL_ATTACH',	DB_PREFIX . 'phreemail_attach');
// directory
define('PHREEMAIL_DIR_ATTACHMENTS',  DIR_FS_MY_FILES . $_SESSION['user']->company . '/phreemail/attachments/');

class admin extends \core\classes\admin {
	public $id 			= 'phreemail';
	public $text		= MODULE_PHREEMAIL_TITLE;
	public $description = MODULE_PHREEMAIL_DESCRIPTION;
	public $version		= '1.0';

	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
			'contacts' 		=> 4.0,
			'phreedom'   	=> 4.0,
		  	'phreebooks'	=> 4.0,
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'phreemail',
		);
	    // Load tables
	    //@todo maybe the toaddress_id and fromadress_id can be removed.
		$this->tables = array(
		  TABLE_PHREEMAIL => "CREATE TABLE ".TABLE_PHREEMAIL."  (
	  		`email_id` int(11) NOT NULL auto_increment,
	  		`message_id` varchar(255) NOT NULL default '0',
	  		`toaddress_id` int(11) NOT NULL default '0',
	  		`fromaddress_id` int(11) NOT NULL default '0',
	  		`toaddress` varchar(255) NOT NULL default '',
	  		`fromaddress` varchar(255) NOT NULL default '',
	  		`reply_toaddress` varchar(255) NOT NULL default '',
	  		`senderaddress` varchar(255) NOT NULL default '',
	  		`account` varchar(255) NOT NULL default '',
	  		`date` datetime NOT NULL default '0000-00-00 00:00:00',
	  		`maildate` datetime NOT NULL default '0000-00-00 00:00:00',
	  		`udate` datetime NOT NULL default '0000-00-00 00:00:00',
	  		`database_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  		`read_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  		`reply_date` datetime NOT NULL default '0000-00-00 00:00:00',
	  		`recent` tinyint(3) NOT NULL default '0',
	  		`unseen` tinyint(3) NOT NULL default '0',
	  		`flagged` tinyint(3) NOT NULL default '0',
	  		`answered` tinyint(3) NOT NULL default '0',
	  		`deleted` tinyint(3) NOT NULL default '0',
	  		`draft` tinyint(3) NOT NULL default '0',
	  		`subject` varchar(255) default NULL,
	  		`message` text  NOT NULL,
	  		`message_html` text  NOT NULL,
	  		`size` int(11) NOT NULL default '0',
	  		`reply_id` int(11) NOT NULL default '0',
	  		PRIMARY KEY  (`email_id`),
	  		KEY `message_id` (`message_id`),
	  		KEY `from` (`fromaddress`)
		) ENGINE=MyISAM;",

		TABLE_PHREEMAIL_DIR => "CREATE TABLE ".TABLE_PHREEMAIL_DIR." (
	  		`IDdir` int(11) NOT NULL auto_increment,
	  		`IDsubdir` int(11) NOT NULL default '0',
	  		`Sort` int(11) NOT NULL default '0',
	  		`Name` varchar(25) NOT NULL default '',
	  		`Status` tinyint(3) NOT NULL default '0',
	  		`CatchMail` varchar(150) NOT NULL default '',
	  		`Icon` varchar(250)  NOT NULL default '',
	  		PRIMARY KEY  (`IDdir`),
	  		KEY `IDsubdir` (`IDsubdir`)
		) ENGINE=MyISAM;",

		TABLE_PHREEMAIL_LIST => "CREATE TABLE ".TABLE_PHREEMAIL_LIST." (
	  		`IDlist` int(11) NOT NULL auto_increment,
	  		`Email` varchar(255) NOT NULL default '',
	  		`Type` char(2) NOT NULL default 'B',
	  		PRIMARY KEY  (`IDlist`),
	  		KEY `Email` (`Email`)
			) ENGINE=MyISAM;",

		TABLE_PHREEMAIL_ATTACH => "CREATE TABLE ". TABLE_PHREEMAIL_ATTACH ." (
	  		`ID` int(11) NOT NULL auto_increment,
	  		`IDEmail` int(11) NOT NULL default '0',
	  		`FileNameOrg` varchar(255) NOT NULL default '',
	  		`Filename` varchar(255) NOT NULL default '',
	  		PRIMARY KEY  (`ID`),
	  		KEY `IDEmail` (`IDEmail`)
			) ENGINE=MyISAM;",
		);
		
		// Set the menus
		$this->mainmenu["customers"]->submenu ["email"] 		= new \core\classes\menuItem (70, 	TEXT_EMAIL,			'action=LoadEmailMgrPage');
		//		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["email"]  = new \core\classes\menuItem (sprintf(TEXT_MODULE_ARGS, TEXT_EMAIL), sprintf(TEXT_MODULE_ARGS, TEXT_EMAIL),	'module=email&amp;page=admin');
		
	}

	function after_ValidateUser(\core\classes\basis &$basis) {
  		\core\classes\messageStack::debug_log("\n\n*************** Retrieving Mail from ".EMAIL_SMTPAUTH_MAILBOX." *******************");
		try{
	  		$mail = new \phreemail\classes\phreemail();
			$mail->connect('', '', EMAIL_SMTPAUTH_MAILBOX, '');
			if ($mail->error_count != 0 ){
				\core\classes\messageStack::add($mail->ErrorInfo, 'error');
			}else{
				$mail->do_action();
			}
		}catch (\Exception $exception){
			\core\classes\messageStack::add($exception->getMessage(), 'error');
		}
		\core\classes\messageStack::debug_log("\n\n*************** End Retrieving Mail from ".EMAIL_SMTPAUTH_MAILBOX." *******************");
		try{
			\core\classes\messageStack::debug_log("\n\n*************** Retrieving Mail from {$_SESSION['user']->admin_email} *******************");
			$mail = new \phreemail\classes\phreemail();
			$mail->connect('', '', $_SESSION['user']->admin_email, '');
			if ($mail->error_count != 0 ){
				\core\classes\messageStack::add($mail->ErrorInfo, 'error');
			}else{
				$mail->do_action();
			}
		}catch (\Exception $exception){
			\core\classes\messageStack::add($exception->getMessage(), 'error');
		}
		\core\classes\messageStack::debug_log("\n\n*************** End Retrieving Mail from {$_SESSION['user']->admin_email} *******************");
  }
  
  function after_editContact(\core\classes\basis &$basis) {
  	\core\classes\messageStack::debug_log("executing ".__METHOD__ );
  	?><script type="text/javascript">
			$(document).ready(function(){
				
				$('#email_table').datagrid({
					url:		"index.php?action=loadEmailHistory",
					queryParams: {
						contact_id: '<?php echo $basis->cInfo->contact->id;?>',
						dataType: 'json',
				        contentType: 'application/json',
				        async: false,
					},
					width: '100%',
					height: '500px',
					onBeforeLoad:function(){
						console.log('loading of the email history datagrid');
					},
					onLoadSuccess: function(data){
						console.log('the loading of the email history datagrid was succesfull');
						$.messager.progress('close');
						if(data.error_message) $.messager.alert('<?php echo TEXT_ERROR?>',data.error_message);
					},
					onLoadError: function(){
						console.error('the loading of the email history datagrid resulted in a error');
						$.messager.progress('close');
						$.messager.alert('<?php echo TEXT_ERROR?>','Load error for table email notes');
					},
					onDblClickRow: function(index , row){
						console.log('a row in the email history was double clicked');
						$('#email_table').datagrid('expandRow', index);
					},
					toolbar: "#email_toolbar",
					remoteSort:	false,
					fitColumns:	true,
					idField:	"email_id",
					singleSelect:true,
					sortName:	"date",
					sortOrder: 	"dsc",
					loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				    view: detailview,
				    detailFormatter:function(index,row){
				        return '<div class="ddv"></div>';
				    },
				    onExpandRow: function(index, row){
				        //@todo write this 
				    }
				});
			})
			
			</script>
			<div data-options="region:'east',split:true,collapsed:false,collapsible:true,minWidth:'50px',title:'<?php echo TEXT_EMAILS?>'" style="width:250px">
					<table id='email_table'>
					    <thead>
					   		<tr>
					        	<th data-options="field:'toaddress',sortable:true, align:'left'"><?php echo TEXT_TO;?></th>
				    	        <th data-options="field:'fromaddress',sortable:true, align:'left'"><?php echo TEXT_FROM?></th>	
				    	        <th data-options="field:'date',sortable:true, align:'right', formatter: function(value,row,index){ return formatDateTime(value)}"><?php echo TEXT_DATE?></th>
						        <th data-options="field:'status',sortable:true, align:'left'"><?php echo TEXT_STATUS?></th>
						        <th data-options="field:'subject',sortable:false, align:'left'"><?php echo TEXT_SUBJECT?></th>		        
					    	</tr>
					   	</thead>
					</table>
					<div id="email_toolbar">
				        <a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newEmail()"><?php echo sprintf(TEXT_NEW_ARGS, TEXT_EMAIL); //@todo?></a>
				    </div>
			</div>
		<?php 				
	}
	
	function getAllEmails(){//@todo add filter and max.
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $admin->DataBase->prepare("SELECT * FROM " . TABLE_PHREEMAIL . " ORDER BY email_id DESC");
		$sql->execute();
		$results = $sql->fetchAll(\PDO::FETCH_ASSOC);
		return $results;
	}
}
?>