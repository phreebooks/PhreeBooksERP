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
//  Path: /modules/contacts/classes/admin.php
//
namespace contacts\classes;
require_once (DIR_FS_ADMIN . 'modules/contacts/config.php');
class admin extends \core\classes\admin {
	public $sort_order  = 3;
	public $id 			= 'contacts';
	public $description = MODULE_CONTACTS_DESCRIPTION;
	public $core		= true;
	public $version		= '4.0.2-dev';

	function __construct() {
		$this->text = sprintf(TEXT_MODULE_ARGS, TEXT_CONTACTS);
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.6,
		  'phreebooks' => 3.6,
		);
		// Load configuration constants for this module, must match entries in admin tabs
	    $this->keys = array(
		  'ADDRESS_BOOK_CONTACT_REQUIRED'        => '0',
		  'ADDRESS_BOOK_ADDRESS1_REQUIRED'       => '1',
		  'ADDRESS_BOOK_ADDRESS2_REQUIRED'       => '0',
		  'ADDRESS_BOOK_CITY_TOWN_REQUIRED'      => '1',
		  'ADDRESS_BOOK_STATE_PROVINCE_REQUIRED' => '1',
		  'ADDRESS_BOOK_POSTAL_CODE_REQUIRED'    => '1',
		  'ADDRESS_BOOK_TELEPHONE1_REQUIRED'     => '0',
		  'ADDRESS_BOOK_EMAIL_REQUIRED'          => '0',
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'contacts',
		  'contacts/main',
		);
		// Load tables
		$this->tables = array(
		  TABLE_ADDRESS_BOOK => "CREATE TABLE " . TABLE_ADDRESS_BOOK . " (
			  address_id int(11) NOT NULL auto_increment,
			  ref_id int(11) NOT NULL default '0',
			  type char(2) NOT NULL default '',
			  primary_name varchar(32) NOT NULL default '',
			  contact varchar(32) NOT NULL default '',
			  address1 varchar(32) NOT NULL default '',
			  address2 varchar(32) NOT NULL default '',
			  city_town varchar(24) NOT NULL default '',
			  state_province varchar(24) NOT NULL default '',
			  postal_code varchar(10) NOT NULL default '',
			  country_code char(3) NOT NULL default '',
			  telephone1 VARCHAR(20) NULL DEFAULT '',
			  telephone2 VARCHAR(20) NULL DEFAULT '',
			  telephone3 VARCHAR(20) NULL DEFAULT '',
			  telephone4 VARCHAR(20) NULL DEFAULT '',
			  email VARCHAR(48) NULL DEFAULT '',
			  website VARCHAR(48) NULL DEFAULT '',
			  notes text,
			  PRIMARY KEY (address_id),
			  KEY customer_id (ref_id,type)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_CONTACTS => "CREATE TABLE " . TABLE_CONTACTS . " (
		  	  class VARCHAR( 255 ) NOT NULL DEFAULT '',
			  id int(11) NOT NULL auto_increment,
			  type char(1) NOT NULL default 'c',
			  short_name varchar(32) NOT NULL default '',
			  inactive enum('0','1') NOT NULL default '0',
			  title varchar(32) default NULL,
			  contact_first varchar(32) default NULL,
			  contact_middle varchar(32) default NULL,
			  contact_last varchar(32) default NULL,
			  store_id varchar(15) NOT NULL default '',
			  gl_type_account varchar(15) NOT NULL default '',
			  gov_id_number varchar(16) NOT NULL default '',
			  dept_rep_id varchar(16) NOT NULL default '',
			  account_number varchar(16) NOT NULL default '',
			  special_terms varchar(32) NOT NULL default '0',
			  price_sheet varchar(32) default NULL,
	          tax_id INT(11) default '-1',
	          attachments text,
			  first_date date NOT NULL default '0000-00-00',
			  last_update date default NULL,
			  last_date_1 date default NULL,
			  last_date_2 date default NULL,
			  PRIMARY KEY (id),
			  KEY type (type),
			  KEY short_name (short_name)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_CONTACTS_LOG => "CREATE TABLE " . TABLE_CONTACTS_LOG . " (
			  log_id int(11) NOT NULL auto_increment,
			  contact_id int(11) NOT NULL default '0',
			  entered_by int(11) NOT NULL default '0',
			  log_date datetime NOT NULL default '0000-00-00',
			  crmaction varchar(32) NOT NULL default '',
			  notes text,
			  PRIMARY KEY (log_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_DEPARTMENTS => "CREATE TABLE " . TABLE_DEPARTMENTS . " (
			  id int(11) NOT NULL auto_increment,
			  description_short varchar(30) NOT NULL default '',
			  description varchar(30) NOT NULL default '',
			  subdepartment enum('0','1') NOT NULL default '0',
			  primary_dept_id int(11) NOT NULL default '0',
			  department_type tinyint(4) NOT NULL default '0',
			  department_inactive enum('0','1') NOT NULL default '0',
			  PRIMARY KEY (id),
			  KEY type (department_type)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_DEPT_TYPES => "CREATE TABLE " . TABLE_DEPT_TYPES . " (
			  id int(11) NOT NULL auto_increment,
			  description varchar(30) NOT NULL default '',
			  PRIMARY KEY (id)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_PROJECTS_COSTS => "CREATE TABLE " . TABLE_PROJECTS_COSTS . " (
			  cost_id int(8) NOT NULL auto_increment,
			  description_short varchar(16) collate utf8_unicode_ci NOT NULL default '',
			  description_long varchar(64) collate utf8_unicode_ci NOT NULL default '',
			  cost_type varchar(3) collate utf8_unicode_ci default NULL,
			  inactive enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  PRIMARY KEY (cost_id),
			  KEY description_short (description_short)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
		  TABLE_PROJECTS_PHASES => "CREATE TABLE " . TABLE_PROJECTS_PHASES . " (
			  phase_id int(8) NOT NULL auto_increment,
			  description_short varchar(16) collate utf8_unicode_ci NOT NULL default '',
			  description_long varchar(64) collate utf8_unicode_ci NOT NULL default '',
			  cost_type varchar(3) collate utf8_unicode_ci default NULL,
			  cost_breakdown enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  inactive enum('0','1') collate utf8_unicode_ci NOT NULL default '0',
			  PRIMARY KEY (phase_id),
			  KEY description_short (description_short)
			) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci",
	    );
		// Set the menus
		$this->mainmenu["customers"]->submenu ["contact"] 	= new \core\classes\menuItem (10, 	TEXT_CUSTOMERS,		'action=LoadContactMgrPage&amp;type=c', SECURITY_ID_MAINTAIN_CUSTOMERS);
		$this->mainmenu["customers"]->submenu ["crm"] 		= new \core\classes\menuItem (15, 	TEXT_CRM,			'action=LoadContactMgrPage&amp;type=i');
		$this->mainmenu["vendors"]->submenu   ["contact"]	= new \core\classes\menuItem (10, 	TEXT_VENDORS,		'action=LoadContactMgrPage&amp;type=v', SECURITY_ID_MAINTAIN_VENDORS);
		$this->mainmenu["employees"]->submenu ["contact"] 	= new \core\classes\menuItem (10, 	TEXT_EMPLOYEES,		'action=LoadContactMgrPage&amp;type=e', SECURITY_ID_MAINTAIN_EMPLOYEES);
		$this->mainmenu["company"]->submenu   ["branches"] 	= new \core\classes\menuItem (10, 	TEXT_BRANCHES,		'action=LoadContactMgrPage&amp;type=b', SECURITY_ID_MAINTAIN_BRANCH, 'ENABLE_MULTI_BRANCH');
		$this->mainmenu["customers"]->submenu ["projects"] 	= new \core\classes\menuItem (60, 	TEXT_PROJECTS,		'action=LoadContactMgrPage&amp;type=j', SECURITY_ID_MAINTAIN_PROJECTS);
		$this->mainmenu["company"]->submenu ["configuration"]->submenu ["contacts"]  = new \core\classes\menuItem (sprintf(TEXT_MODULE_ARGS, TEXT_CONTACTS), sprintf(TEXT_MODULE_ARGS, TEXT_CONTACTS),	'module=contacts&amp;page=admin');
	    parent::__construct();
	}

	function install($path_my_files, $demo = false) {
	    global $admin;
	    parent::install($path_my_files, $demo);
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_num')) $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_cust_id_num VARCHAR( 16 ) NOT NULL DEFAULT 'C10000';");
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_num')) $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_vend_id_num VARCHAR( 16 ) NOT NULL DEFAULT 'V10000';");
		if (!$admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num'))  $admin->DataBase->query("ALTER TABLE " . TABLE_CURRENT_STATUS . " ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '10000';");
		require_once(DIR_FS_MODULES . 'phreedom/functions/phreedom.php');
		\core\classes\fields::sync_fields('contacts', TABLE_CONTACTS);
	}

  	function upgrade(\core\classes\basis &$basis) {
    	parent::upgrade($basis);
    	if (version_compare($this->status, '3.3', '<') ) {
	  		$basis->DataBase->query("ALTER TABLE " . TABLE_CONTACTS . " CHANGE short_name short_name VARCHAR(32) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT ''");
		  	if ($basis->DataBase->table_exists(DB_PREFIX . 'contacts_extra_fields')) $basis->DataBase->exec("DROP TABLE " . DB_PREFIX . "contacts_extra_fields");
		}
		if (version_compare($this->status, '3.5', '<') ) {
	  		if ( $basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_desc')) $basis->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_desc");
	  		if ( $basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_desc')) $basis->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_desc");
	  		if (!$basis->DataBase->field_exists(TABLE_CONTACTS, 'attachments')) $basis->DataBase->exec("ALTER TABLE " . TABLE_CONTACTS . " ADD attachments TEXT NOT NULL AFTER tax_id");
    	}
    	if (version_compare($this->status, '3.7', '<') ) {
      		if (!$basis->DataBase->field_exists(TABLE_CONTACTS_LOG, 'entered_by')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS_LOG." ADD entered_by INT(11) NOT NULL DEFAULT '0' AFTER contact_id");
    	}
		if (!$basis->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_num')){
    		$result = $basis->DataBase->exec("SELECT MAX(short_name + 1) AS new  FROM ".TABLE_CONTACTS." WHERE TYPE = 'i'");
			$basis->DataBase->exec("ALTER TABLE ".TABLE_CURRENT_STATUS." ADD next_crm_id_num VARCHAR( 16 ) NOT NULL DEFAULT '{$result->fields['new']}';");
		}
		if (version_compare($this->status, '4.0', '<') ) {
			if (!$basis->DataBase->field_exists(TABLE_CONTACTS, 'class')) $basis->DataBase->exec("ALTER TABLE ".TABLE_CONTACTS." ADD class VARCHAR( 255 ) NOT NULL DEFAULT '' FIRST");
			$sql = $basis->DataBase->exec("UPDATE ".TABLE_CONTACTS." SET class = CONCAT('contacts\\\\classes\\\\type\\\\', type) WHERE class = '' ");
		}
		if (version_compare($this->status, '4.0.1', '<') ) {
			// fake install crm module 
			$basis->DataBase->write_configure('MODULE_CRM_STATUS', 1);
			if ($basis->DataBase->field_exists(TABLE_CONTACTS, 'last_date_1'))  $basis->DataBase->exec("ALTER TABLE " . TABLE_CONTACTS . " DROP last_date_1");
			if ($basis->DataBase->field_exists(TABLE_CONTACTS, 'last_date_2'))  $basis->DataBase->exec("ALTER TABLE " . TABLE_CONTACTS . " DROP last_date_2");
		}
		if (version_compare($this->status, '4.0.2', '<') ) {
			if (!$basis->DataBase->field_exists(TABLE_CONTACTS, 'title')) {
				$basis->DataBase->exec("ALTER TABLE " . TABLE_CONTACTS . " ADD title varchar(32) default NULL AFTER inactive");
				$basis->DataBase->exec("UPDATE " . TABLE_CONTACTS . " SET title = contact_middle, contact_middle = '' WHERE type = 'i'");
				$basis->DataBase->exec("UPDATE " . TABLE_ADDRESS_BOOK . " SET website = CONCAT('http://', website) WHERE website NOT LIKE 'http%'and website <> ''");
			}
			//@todo update website in database so that it starts with http://
		}
		\core\classes\fields::sync_fields('contacts', TABLE_CONTACTS);
  	}

	function delete($path_my_files) {
	    global $admin;
	    parent::delete($path_my_files);
	    if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_num'))  $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_num");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_cust_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_cust_id_desc");
	    if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_num'))  $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_num");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_vend_id_desc')) $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_vend_id_desc");
		if ($admin->DataBase->field_exists(TABLE_CURRENT_STATUS, 'next_crm_id_desc'))  $admin->DataBase->exec("ALTER TABLE " . TABLE_CURRENT_STATUS . " DROP next_crm_id_desc");
		$admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_FIELDS . " WHERE module_id = 'contacts'");
		$admin->DataBase->exec("DELETE FROM " . TABLE_EXTRA_TABS   . " WHERE module_id = 'contacts'");
	}

	function load_reports() {
		$id = $this->add_report_heading(TEXT_CUSTOMERS,   'cust');
		$this->add_report_folder($id, TEXT_REPORTS,       'cust', 'fr');
		$id = $this->add_report_heading(TEXT_EMPLOYEES,   'hr');
		$this->add_report_folder($id, TEXT_REPORTS,       'hr',   'fr');
		$id = $this->add_report_heading(TEXT_VENDORS,     'vend');
		$this->add_report_folder($id, TEXT_REPORTS,       'vend', 'fr');
		parent::load_reports();
	}

	/**
	 * this function will load the contact manager page
	 */
	function LoadContactMgrPage(\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->observer->send_menu($basis);
		if (! isset($basis->cInfo->type)) $basis->cInfo->type = 'c'; // default to customer
		switch ($basis->cInfo->type) {
			case 'b': $contact = TEXT_BRANCH;	break;
			case 'c': $contact = TEXT_CUSTOMER;	break;
			case 'e': $contact = TEXT_EMPLOYEE;	break;
			case 'i': $contact = TEXT_CRM;		break;
			case 'j': $contact = TEXT_PROJECT;	break;
			case 'v': $contact = TEXT_VENDOR;	break;
		}
		?>
		<div data-options="region:'center'">
		    <table id="dg" title="<?php echo sprintf(TEXT_MANAGER_ARGS, $contact);?>" style="height:500px;padding:50px;">
	        	<thead>
	            	<tr>
	            		<th data-options="field:'short_name',sortable:true"><?php echo sprintf(TEXT_ARGS_ID, $contact);?></th>
	               		<th data-options="field:'name',sortable:true"><?php echo TEXT_NAME_OR_COMPANY?></th>
	            	   	<th data-options="field:'address1',sortable:true"><?php echo TEXT_ADDRESS1?></th>
	    	           	<th data-options="field:'city_town',sortable:true"><?php echo TEXT_CITY_TOWN?></th>
	        	       	<th data-options="field:'state_province',sortable:true"><?php echo TEXT_STATE_PROVINCE?></th>
	        	       	<th data-options="field:'postal_code',sortable:true"><?php echo TEXT_POSTAL_CODE?></th>
	        	       	<th data-options="field:'telephone1',sortable:true"><?php echo TEXT_TELEPHONE?></th>
	            	</tr>
	        	</thead>
	    	</table>
	    	<div id="toolbar">
	    		<a class="easyui-linkbutton" iconCls="icon-edit" plain="true" onclick="editContact()"><?php echo sprintf(TEXT_EDIT_ARGS, $contact);?></a>
		        <a class="easyui-linkbutton" iconCls="icon-add" plain="true" onclick="newContact()"><?php echo sprintf(TEXT_NEW_ARGS, $contact);?></a>
	        	<a class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteContact()"><?php echo sprintf(TEXT_DELETE_ARGS, $contact);?></a>
	        	<?php echo \core\classes\htmlElement::checkbox('contact_show_inactive', TEXT_SHOW_INACTIVE, '1', false,'onchange="doSearch()"' );?>
	        	<div style="float: right;"> <?php echo \core\classes\htmlElement::search('search_text','doSearch');?></div>
	    	</div>
	    	<div id="win" class="easyui-window">
	    		<div id="contactToolbar" style="margin:2px 5px;">
					<a class="easyui-linkbutton" iconCls="icon-undo" plain="true" onclick="closeWindow()"><?php echo TEXT_CANCEL?></a>
					<?php if (\core\classes\user::validate($basis->cInfo->contact->security_token, true) < 2){?>
					<a class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="saveContact()" ><?php echo TEXT_SAVE?></a>
					<?php }?>
					<a class="easyui-linkbutton" iconCls="icon-help" plain="true" onclick="loadHelp()"><?php TEXT_HELP?></a>
				</div>
			</div>
    	</div>	
		<script type="text/javascript">
			document.title = '<?php echo sprintf(BOX_STATUS_MGR, $contact); ?>';
	    	function doSearch(value){
	    		console.log('A search was requested.');
	        	$('#dg').datagrid('load',{
	        		search_text: $('#search_text').val(),
	        		dataType: 'json',
	                contentType: 'application/json',
	                async: false,
	                type: '<?php echo $basis->cInfo->type;?>',
	                contact_show_inactive: document.getElementById('contact_show_inactive').checked ? 1 : 0,
	        	});
	    	}

	        function newContact(){
	        	$.messager.progress();
	            $('#win').window('open').window('center').window('setTitle','<?php echo sprintf(TEXT_NEW_ARGS, $contact);?>');
	            $('#win').window('refresh', "index.php?action=newContact");
	            $('#win').window('resize');
	        }
	        
	        function editContact(){
		        $('#win').window('open').window('center').window('setTitle','<?php echo sprintf(TEXT_EDIT_ARGS, $contact);?>');
	        }
	        
			$('#dg').datagrid({
				url:		"index.php?action=GetAllContacts",
				queryParams: {
					type: '<?php echo $basis->cInfo->type;?>',
					dataType: 'json',
	                contentType: 'application/json',
	                async: false,
				},
				onLoadSuccess: function(data){
					console.log('the loading of the datagrid was succesfull');
					$.messager.progress('close');
					if(data.total == 0) $.messager.alert('<?php echo TEXT_ERROR?>',"<?php echo TEXT_NO_RESULTS_FOUND?>");
				},
				onLoadError: function(){
					console.error('the loading of the datagrid resulted in a error');
					$.messager.progress('close');
					$.messager.alert('<?php echo TEXT_ERROR?>','Load error:'+arguments.responseText);
				},
				onDblClickRow: function(index , row){
					console.log('a row in the datagrid was double clicked');
					document.location = "index.php?action=editContact&contactid="+ row.contactid;
					//$('#win').window('open').window('center').window('setTitle',"<?php echo TEXT_EDIT?>"+ ' ' + row.name);
				},
				remoteSort:	false,
				idField:	"contactid",
				fitColumns:	true,
				singleSelect:true,
				sortName:	"short_name",
				sortOrder: 	"asc",
				loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				toolbar: 	"#toolbar",
				rowStyler: function(index,row){
					if (row.inactive == '1') return 'background-color:pink;';
				},
			});
			
			$('#win').window({
	        	href:		"index.php?action=editContact",
				closed: true,
				title:	"<?php echo sprintf(TEXT_EDIT_ARGS, $contact);?>",
				fit:	true,
				queryParams: {
					type: '<?php echo $basis->cInfo->type;?>',
					dataType: 'html',
	                contentType: 'text/html',
	                async: false,
				},
				onLoadError: function(){
					console.error('the loading of the window resulted in a error');
					$.messager.alert('<?php echo TEXT_ERROR?>');
					$.messager.progress('close');
				},
				onOpen: function(){
					$.messager.progress('close');
				},
				onBeforeLoad: function(param){
					var row = $('#dg').datagrid('getSelected');
					param.contactid = row.contactid;
				},
			});
			
			$('#contacts').form({
			    url: "index.php?action=saveContact",
			    onSubmit: function(param){
			        param.type = '<?php echo $basis->cInfo->contact->type; ?>';
			        param.id	 = '<?php echo $basis->cInfo->contact->id; ?>';
					var isValid = $(this).form('validate');
					if (!isValid){
						console.error('the form field are not validated');
						$.messager.progress('close');	// hide progress bar while the form is invalid
					}
					console.log('the form field are validated');
					return isValid;	// return false will stop the form submission
				},  
				success: function(data){
					console.log('successfully saved contact info. close window and refresh datagrid.');
					console.log('data received = '+ data);
					$.messager.progress('close');	// hide progress bar while submit successfully
					$('#win').window('close');
					$('#dg').datagrid('reload'); 
				},
				onLoadSuccess: function(data){
					console.log('successfully loaded the form.');
				}, 
				onLoadError: function(){
					console.error('there was a error during loading of the form.');
				}, 
				toolbar:contactToolbar, 
			});

			function closeWindow(){
				$.messager.progress();
				$('#contacts').form('clear');
				console.log('close contact window');
				$('#win').window('close', true);
			}

			function saveContact(){
				console.log('save contact info');
				$.messager.progress();
			    $('#contacts').submit();
			}			
		</script><?php 
		$basis->observer->send_footer($basis);
	}
	
	function GetAllContacts (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ . print_r($basis->cInfo, true));
		if (isset($basis->cInfo->dept_rep_id)) {
			$criteria[] = "c.dept_rep_id = '{$basis->cInfo->dept_rep_id}'";
		}else{
			$criteria[] = "a.type = '{$basis->cInfo->type}m'";
		}
		if (isset($basis->cInfo->search_text) && $basis->cInfo->search_text <> '') {
			$search_fields = array('a.primary_name', 'a.contact', 'a.telephone1', 'a.telephone2', 'a.address1',
					'a.address2', 'a.city_town', 'a.postal_code', 'c.short_name');
			// hook for inserting new search fields to the query criteria.
			if (is_array($extra_search_fields)) $search_fields = array_merge($search_fields, $extra_search_fields);
			$criteria[] = '(' . implode(" like '%{$basis->cInfo->search_text}%' or ", $search_fields) . " like '%{$basis->cInfo->search_text}%')";
		}
		if ($basis->cInfo->contact_show_inactive == false) $criteria[] = "(c.inactive = '0' or c.inactive = '')"; // inactive flag
		$search = (sizeof($criteria) > 0) ? (' where ' . implode(' and ', $criteria)) : '';
		$query_raw = "SELECT id as contactid, short_name, title, CASE WHEN c.type = 'e' OR c.type = 'i' THEN CONCAT(contact_first , ' ',contact_last) ELSE primary_name END AS name, contact_last, contact_first, contact_middle, contact, account_number, gov_id_number, address1, address2, city_town, state_province, postal_code, telephone1, telephone2, telephone3, telephone4, email, website, inactive, c.type, address_id, country_code FROM ".TABLE_CONTACTS." c LEFT JOIN ".TABLE_ADDRESS_BOOK." a ON c.id = a.ref_id $search ORDER BY {$basis->cInfo->sort} {$basis->cInfo->order}";
		$sql = $basis->DataBase->prepare($query_raw);
		$sql->execute();
		$results = $sql->fetchAll(\PDO::FETCH_ASSOC);
		$temp = array();
		$temp["total"] = sizeof($results);
		$temp["rows"] = $results;
		echo json_encode($temp);
	}

	function loadAddresses (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE ref_id = {$basis->cInfo->contact_id} AND type LIKE '%{$basis->cInfo->address_type}' ORDER BY primary_name");
		$sql->execute();
		$results = $sql->fetchAll(\PDO::FETCH_ASSOC);
		$temp = array();
		$temp["total"] = sizeof($results);
		$temp["rows"] = $results;
		echo json_encode($temp);
	}
	
	function editAddress (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ ); 
		if ($basis->cInfo->table == ''){
			$sql = $basis->DataBase->prepare("SELECT * FROM ".TABLE_ADDRESS_BOOK." WHERE ref_id = {$basis->cInfo->contact_id} AND type LIKE '%{$basis->cInfo->address_type}' ORDER BY primary_name");
			$sql->execute();
			$basis->cInfo = (object)$sql->fetch(\PDO::FETCH_ASSOC);
			\core\classes\messageStack::debug_log("variables are ".print_r($basis->cInfo, true) );
		}
		?>
		<form id='editAddress'  method="post">					<?php
		echo \core\classes\htmlElement::hidden('address_id', $basis->cInfo->address_id);
		echo \core\classes\htmlElement::hidden('type', $basis->cInfo->type);
		echo \core\classes\htmlElement::hidden('ref_id', $basis->cInfo->contact_id);
		echo \core\classes\htmlElement::hidden('isNewRecord', false);?>
			<div style="margin-left:50px;text-align:right;float:left;"><?php
			echo 	\core\classes\htmlElement::textbox("primary_name",	 	TEXT_NAME_OR_COMPANY,'size="33" maxlength="32"', 	$basis->cInfo->primary_name, 	true). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("contact",	 		TEXT_ATTENTION, 	'size="33" maxlength="32"', 	$basis->cInfo->contact, 		ADDRESS_BOOK_CONTACT_REQUIRED). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("address1" , 		TEXT_ADDRESS1, 		'size="33" maxlength="32"', 	$basis->cInfo->address1, 		ADDRESS_BOOK_ADDRESS1_REQUIRED). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("address2" , 		TEXT_ADDRESS2, 		'size="33" maxlength="32"', 	$basis->cInfo->address2, 		ADDRESS_BOOK_ADDRESS2_REQUIRED). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("city_town" , 		TEXT_CITY_TOWN, 	'size="33" maxlength="32"', 	$basis->cInfo->city_town, 		ADDRESS_BOOK_CITY_TOWN_REQUIRED). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("state_province",	TEXT_STATE_PROVINCE,'size="25" maxlength="24"', 	$basis->cInfo->state_province,	ADDRESS_BOOK_STATE_PROVINCE_REQUIRED). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("postal_code" , 		TEXT_POSTAL_CODE, 	'size="11" maxlength="10"', 	$basis->cInfo->postal_code, 	ADDRESS_BOOK_POSTAL_CODE_REQUIRED). '<br>' .chr(13);
				?>
			</div>
			<div style="margin-left:50px;text-align:right;"> <?php
			echo	\core\classes\htmlElement::textbox("telephone1", 		TEXT_TELEPHONE, 	'size="22" maxlength="21"', $basis->cInfo->telephone1, ADDRESS_BOOK_TELEPHONE1_REQUIRED). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("telephone2", 		TEXT_TELEPHONE, 	'size="22" maxlength="21"', $basis->cInfo->telephone2). '<br>' .chr(13).
			 		\core\classes\htmlElement::textbox("telephone3", 		TEXT_FAX,			'size="22" maxlength="21"', $basis->cInfo->telephone3). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("telephone4", 		TEXT_MOBILE_PHONE, 	'size="22" maxlength="21"', $basis->cInfo->telephone4). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("email", 			TEXT_EMAIL_ADDRESS, 'size="51" maxlength="50" data-options="validType:\'email\'"', $basis->cInfo->email). '<br>' .chr(13).
					\core\classes\htmlElement::textbox("website", 			TEXT_WEBSITE, 		'size="51" maxlength="50" data-options="validType:\'url\'"', $basis->cInfo->website). '<br>' .chr(13).
					\core\classes\htmlElement::combobox("country_code", 	TEXT_COUNTRY,		$_SESSION['language']->get_countries_dropdown(), ($basis->cInfo->country_code =='') ? COMPANY_COUNTRY : $basis->cInfo->country_code). '<br>' .chr(13);
			  	?>
		  	</div>
		    <div data-options="region:'south'" style="padding:5px 0;text-align:right;padding-right:100px;clear: both;	">
			   	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="saveAddress1(this)"><?php echo TEXT_SAVE?></a>
	           	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="cancelAddress1(this)"><?php echo TEXT_CLEAR?></a>
	           	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteAddress()"><?php echo sprintf(TEXT_DELETE_ARGS, $contact);?></a>
	        </div>
         	<script type="text/javascript">
	            function saveAddress1(target){
	            	console.log('save address was clicked');
	            	var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	                var index = parseInt(tr.attr('datagrid-row-index'));
	                $('#<?php echo $basis->cInfo->table?>').datagrid('selectRow', index);
	            	$('#editAddress').form('submit');
	            }
	            function cancelAddress1(target){
	                var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	                var index = parseInt(tr.attr('datagrid-row-index'));
	                <?php 
	                if ($basis->cInfo->table == 'billing_address'){
	                	echo 'cancelBilling (index)';
	                }else{
	                	echo 'cancelShipping (index)';
	                }	?>
	            }
	            
	            function deleteAddress(){
	            	console.log('delete address was clicked');
	                var row = $('#<?php echo $basis->cInfo->table?>').datagrid('getSelected');
	                var index = $('#<?php echo $basis->cInfo->table?>').datagrid('getRowIndex', row);
	                if (row){
	                    $.messager.confirm('<?php echo TEXT_CONFORM?>','<?php echo sprintf(TEXT_ARE_YOU_SURE_YOU_WANT_TO_DELETE_ARGS, TEXT_ADDRESS)?>',function(r){
	                        if (r){
	                        	$.post('index.php?action=deleteAddress',{address_id:row.address_id, dataType: 'json', async: false, contentType: 'application/json'},function(result){
	                                if (result.success){
	                                	$('#<?php echo $basis->cInfo->table?>').datagrid('deleteRow', index);
	                                } else {
	                                    $.messager.show({    // show error message
	                                        title: '<?php echo TEXT_ERROR?>',
	                                        msg: result.error_message
	                                    });
	                                }
	                            },'json');
	                        }
	                    });
	                }
	            }
	            
	            $('#editAddress').form({
	        		url:'index.php?action=saveAddress',
	        		queryParams: {
						dataType: 'json',
				        contentType: 'application/json',
				        async: false,
					},
	                onSubmit: function(param){
	        			console.log('submitting Address Relation form ');
	                },
	                onLoadSuccess: function(data){
	                	console.log('succesfull loaded form data '+JSON.stringify(data));
		            },
	                success: function(data){
	                	data = eval('('+data+')');
	                    if (data.error_message){
	                    	console.error(data.error_message);
	                        $.messager.show({ title: '<?php echo TEXT_ERROR?>', msg: data.error_message });
	                    }else{
		                    data.isNewRecord = false;
		                    var row = $('#<?php echo $basis->cInfo->table?>').datagrid('getSelected');
			                var index = $('#<?php echo $basis->cInfo->table?>').datagrid('getRowIndex', row);
			                $('#<?php echo $basis->cInfo->table?>').datagrid('updateRow',{index: index, row: data });
			                $('#<?php echo $basis->cInfo->table?>').datagrid('collapseRow',index);
	                    }
	                }
	                
	            });
        	</script>
		</form><?php
	}
	
	function saveAddress  (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		try{
			$temp = $basis->cInfo;
			$sql = $basis->DataBase->prepare("INSERT INTO " . TABLE_ADDRESS_BOOK . " (`address_id`, `ref_id`, `type`, `primary_name`, `contact`, `address1`, `address2`, `city_town`, `state_province`,`postal_code`, `country_code`, `telephone1`, `telephone2`, `telephone3`, `telephone4`, `email`, `website`) VALUES (:address_id, :ref_id, :type, :primary_name, :contact, :address1, :address2, :city_town, :state_province, :postal_code, :country_code, :telephone1, :telephone2, :telephone3, :telephone4, :email, :website) ON DUPLICATE KEY UPDATE primary_name = :primary_name, contact = :contact, address1 = :address1, address2 = :address2, city_town = :city_town, state_province = :state_province, postal_code = :postal_code, country_code = :country_code, telephone1 = :telephone1, telephone2 = :telephone2, telephone3 = :telephone3, telephone4 = :telephone4, email = :email, website = :website, type = :type");
			$sql->execute(array(':address_id' => $temp->address_id, ':ref_id' => $temp->ref_id, ':type' => "{$temp->type}m", ':primary_name' => $temp->primary_name, ':contact' => $temp->contact, ':address1' => $temp->address1, ':address2' => $temp->address2, ':city_town' => $temp->city_town, ':state_province' => $temp->state_province, ':postal_code' => $temp->postal_code, ':country_code' => $temp->country_code, ':telephone1' => $temp->telephone1, ':telephone2' => $temp->telephone2, ':telephone3' => $temp->telephone3, ':telephone4' => $temp->telephone4, ':email' => $temp->email, ':website' => $temp->website));
			if($temp->address_id == ''){//find new contact id.
				$temp->address_id = $basis->DataBase->lastInsertId();
			}
			$temp->success = true;
			$temp->message = TEXT_SAVED_SUCCESSFULLY;
		}catch (\Exception $e) {
			$temp->success = false;
			$temp->error_message = $e->getMessage();
		}
		echo json_encode($temp);
	}
	
	function deleteAddress  (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		try{
			$temp = $basis->cInfo;
			$basis->DataBase->exec("DELETE FROM ".TABLE_ADDRESS_BOOK ." WHERE address_id={$temp->address_id}");
			$temp->success = true;
			$temp->message = TEXT_SAVED_SUCCESSFULLY;
		}catch (\Exception $e) {
			$temp->success = false;
			$temp->error_message = $e->getMessage();
		}
		echo json_encode($temp);
	}
	
	function editContactRelation (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if ($basis->cInfo->contactid == '') {
			$sql = $basis->DataBase->prepare("SELECT next_crm_id_num FROM ".TABLE_CURRENT_STATUS);
			$sql->execute();
			$result = $sql->fetch(\PDO::FETCH_ASSOC);
			$temp  = $result['next_crm_id_num'];
		}
		?>
		<form id='editRelation'  method="post">
			<?php echo \core\classes\htmlElement::hidden('dept_rep_id', $basis->cInfo->contact_id);
			echo \core\classes\htmlElement::hidden('type', 'i');
			echo \core\classes\htmlElement::hidden('isNewRecord', false);
			echo \core\classes\htmlElement::hidden('contactid');
			echo \core\classes\htmlElement::hidden('address_id');?>
				<div style="margin-left:50px;text-align:right;float:right"> <?php
				echo 	\core\classes\htmlElement::textbox("title", 			TEXT_TITLE, 	'size="33" maxlength="32"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("contact_middle", 	TEXT_MIDDLE_NAME,	'size="33" maxlength="32"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("contact_last", 		TEXT_LAST_NAME, 	'size="33" maxlength="32"', null, true). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("gov_id_number", 	TEXT_TWITTER_ID, 	'size="17" maxlength="16"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("telephone1", 		TEXT_TELEPHONE, 	'size="22" maxlength="21"', null, ADDRESS_BOOK_TELEPHONE1_REQUIRED). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("telephone2", 		TEXT_TELEPHONE, 	'size="22" maxlength="21"'). '<br>' .chr(13).
				 		\core\classes\htmlElement::textbox("telephone3", 		TEXT_FAX,			'size="22" maxlength="21"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("telephone4", 		TEXT_MOBILE_PHONE, 	'size="22" maxlength="21"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("email", 			TEXT_EMAIL_ADDRESS, 'size="51" maxlength="50" data-options="validType:\'email\'"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("website", 			TEXT_WEBSITE, 		'size="51" maxlength="50" data-options="validType:\'url\'"'). '<br>' .chr(13).
						\core\classes\htmlElement::combobox("country_code", 	TEXT_COUNTRY,		$_SESSION['language']->get_countries_dropdown(), COMPANY_COUNTRY). '<br>' .chr(13);
			  	?>
			  	</div>
				<div style="text-align:right"><?php
				echo \core\classes\htmlElement::checkbox("inactive", 		TEXT_INACTIVE , '1', false). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("short_name", 		TEXT_CONTACT_ID, 	'size="21" maxlength="20"', $temp). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("contact_first", 	TEXT_FIRST_NAME, 	'size="33" maxlength="32"', null, true). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("account_number",	TEXT_FACEBOOK_ID, 	'size="17" maxlength="16"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("primary_name",	 	TEXT_NAME_OR_COMPANY,'size="33" maxlength="32"'). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("contact",	 		TEXT_ATTENTION, 	'size="33" maxlength="32"', null, ADDRESS_BOOK_CONTACT_REQUIRED). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("address1" , 		TEXT_ADDRESS1, 		'size="33" maxlength="32"', null, ADDRESS_BOOK_ADDRESS1_REQUIRED). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("address2" , 		TEXT_ADDRESS2, 		'size="33" maxlength="32"', null, ADDRESS_BOOK_ADDRESS2_REQUIRED). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("city_town" , 		TEXT_CITY_TOWN, 	'size="33" maxlength="32"', null, ADDRESS_BOOK_CITY_TOWN_REQUIRED). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("state_province",	TEXT_STATE_PROVINCE,'size="25" maxlength="24"', null, ADDRESS_BOOK_STATE_PROVINCE_REQUIRED). '<br>' .chr(13).
						\core\classes\htmlElement::textbox("postal_code" , 		TEXT_POSTAL_CODE, 	'size="11" maxlength="10"', null, ADDRESS_BOOK_POSTAL_CODE_REQUIRED). '<br>' .chr(13);
					?>
				</div>
		    	<div data-options="region:'south'" style="padding:5px 0;text-align:right;padding-right:100px">
			    	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-save" plain="true" onclick="save1(this)"><?php echo TEXT_SAVE?></a>
	            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-cancel" plain="true" onclick="cancel1(this)"><?php echo TEXT_CLEAR?></a>
	            	<a href="javascript:void(0)" class="easyui-linkbutton" iconCls="icon-remove" plain="true" onclick="deleteContact()"><?php echo sprintf(TEXT_DELETE_ARGS, $contact);?></a>
	        	</div>
        	<script type="text/javascript">
	            function save1(target){
	            	console.log('save contact was clicked');
	            	var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	                var index = parseInt(tr.attr('datagrid-row-index'));
	                $('#cdg').datagrid('selectRow', index);
	            	$('#editRelation').form('submit');
	            }
	            function cancel1(target){
	                var tr = $(target).closest('.datagrid-row-detail').closest('tr').prev();
	                var index = parseInt(tr.attr('datagrid-row-index'));
	                cancelContact(index);
	            }
	            $('#editRelation').form({
	        		url:'index.php?action=saveContactRelation',
	        		queryParams: {
						dataType: 'json',
				        contentType: 'application/json',
				        async: false
					},
	                onSubmit: function(param){
	        			console.log('submitting Contact Relation form ');
	                },
	                onLoadSuccess: function(data){
	                	console.log('succesfull loaded form data ');
		            },
	                success: function(data){
	                	data = eval('('+data+')');
	                    if (data.error_message){
	                    	console.error(data.error_message);
	                        $.messager.show({ title: '<?php echo TEXT_ERROR?>', msg: data.error_message });
	                    }else{
		                    data.isNewRecord = false;
		                    var row = $('#cdg').datagrid('getSelected');
		                    var index = $('#cdg').datagrid('getRowIndex', row);
		                    $('#cdg').datagrid('updateRow',{
		                        index: index,
		                        row: data
		                    });
		                    $('#cdg').datagrid('collapseRow',index);
	                    }
	                }
	                
	            });
        	</script>
    	</form><?php 
	}
	
	function saveContactRelation (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$temp = $basis->cInfo;
		try{
			if ($temp->type == '' || $temp->type == 0 ) $temp->type = 'i';
			$temp->inactive = $temp->inactive? '1':'0';
			$date = new \core\classes\DateTime();
			$sql = $basis->DataBase->prepare("INSERT INTO " . TABLE_CONTACTS . " (`class`, `id`, `type`, `short_name`, `inactive`, `title`, `contact_first`, `contact_middle`, `contact_last`, `dept_rep_id`, `gov_id_number`, `account_number`,`first_date`, `last_update`) VALUES (:class, :id, :type, :short_name, :inactive, :title, :contact_first, :contact_middle, :contact_last, :dept_rep_id, :gov_id_number, :account_number, :first_date, :last_update) ON DUPLICATE KEY UPDATE short_name = :short_name, inactive = :inactive, title= :title, contact_first = :contact_first, contact_middle = :contact_middle, gov_id_number = :gov_id_number, account_number = :account_number, contact_last = :contact_last, last_update = :last_update");
			$sql->execute(array(':class' => "contacts\\classes\\type\\{$temp->type}", ':id' => $temp->contactid,':type' => $temp->type, ':short_name' => $temp->short_name, ':inactive' => $temp->inactive,':title' => $temp->title ,':contact_first' => $temp->contact_first, ':contact_middle' => $temp->contact_middle, ':contact_last' => $temp->contact_last, ':dept_rep_id' => $temp->dept_rep_id, ':first_date' => $date->format( 'Y-m-d' ), ':last_update' => $date->format( 'Y-m-d' ), 'gov_id_number' => $temp->gov_id_number, ':account_number' => $temp->account_number));
			if($temp->contactid == ''){//find new contact id.
				$temp->contactid = $basis->DataBase->lastInsertId();
				\core\classes\messageStack::debug_log("contactID =  {$temp->contactid}");
				if($temp->isNewRecord){
			  		$next_id = string_increment($temp->short_name);
			  		\core\classes\messageStack::debug_log("nextID =  {$next_id} table = " .TABLE_CURRENT_STATUS);
			  		\core\classes\messageStack::debug_log("UPDATE ".TABLE_CURRENT_STATUS." SET next_crm_id_num = {$next_id}");
					$sql = $basis->DataBase->exec ("UPDATE ".TABLE_CURRENT_STATUS." SET next_crm_id_num = {$next_id}");
				}
			}
			$sql = $basis->DataBase->prepare("INSERT INTO " . TABLE_ADDRESS_BOOK . " (`address_id`, `ref_id`, `type`, `primary_name`, `contact`, `address1`, `address2`, `city_town`, `state_province`,`postal_code`, `country_code`, `telephone1`, `telephone2`, `telephone3`, `telephone4`, `email`, `website`) VALUES (:address_id, :ref_id, :type, :primary_name, :contact, :address1, :address2, :city_town, :state_province, :postal_code, :country_code, :telephone1, :telephone2, :telephone3, :telephone4, :email, :website) ON DUPLICATE KEY UPDATE primary_name = :primary_name, contact = :contact, address1 = :address1, address2 = :address2, city_town = :city_town, state_province = :state_province, postal_code = :postal_code, country_code = :country_code, telephone1 = :telephone1, telephone2 = :telephone2, telephone3 = :telephone3, telephone4 = :telephone4, email = :email, website = :website, type = :type");
			$sql->execute(array(':address_id' => $temp->address_id, ':ref_id' => $temp->contactid, ':type' => "{$temp->type}m", ':primary_name' => $temp->primary_name, ':contact' => $temp->contact, ':address1' => $temp->address1, ':address2' => $temp->address2, ':city_town' => $temp->city_town, ':state_province' => $temp->state_province, ':postal_code' => $temp->postal_code, ':country_code' => $temp->country_code, ':telephone1' => $temp->telephone1, ':telephone2' => $temp->telephone2, ':telephone3' => $temp->telephone3, ':telephone4' => $temp->telephone4, ':email' => $temp->email, ':website' => $temp->website));
			if($temp->address_id == ''){//find new contact id.
				$temp->address_id = $basis->DataBase->lastInsertId();
			}
			$temp->success = true;
			$temp->message = TEXT_SAVED_SUCCESSFULLY;
		}catch (\Exception $e) {
			$temp->success = false;
			$temp->error_message = $e->getMessage();
		}
		if (in_array($temp->type, array('e','i')) ){
			$temp->name = $temp->contact_first . ' ' .$temp->contact_last;
		}else{
			$temp->name = $temp->primary_name;
		}
		echo json_encode($temp);
	}
	
	/**
	 * will create new contact depending on type
	 * @param unknown $basis
	 */
	function NewContact (\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$temp = "\\contacts\\classes\\type\\{$basis->cInfo->type}";
		$contact = new $temp();
		\core\classes\user::validate_security($contact->security_level, 2);
		if (! isset($basis->cInfo->type)) $basis->cInfo->type = 'c'; // default to customer
		$sql = $basis->DataBase->prepare("INSERT INTO ".TABLE_CONTACTS." (class, type ) VALUES ('" . addcslashes(get_class($contact), '\\') . "', '{$contact->type}')");
		$sql->execute();
		$basis->cInfo->cID =  $basis->DataBase->lastInsertId('id');
		$this->editContact($basis);
	}

	/**
	 * this function will load the contact page
	 */
	function editContact(\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		$basis->observer->send_menu($basis);
		if ( isset($basis->cInfo->rowSeq)) $basis->cInfo->contactid = $basis->cInfo->rowSeq;
		if ($basis->cInfo->contactid == '') throw new \core\classes\userException("contactid variable isn't set can't execute method editContact ");
		$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_CONTACTS . " WHERE id = {$basis->cInfo->contactid}");
		$sql->execute();
		$basis->cInfo->contact = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);

		for ($i = 1; $i < 13; $i++) {
			$j = ($i < 10) ? '0' . $i : $i;
			$basis->cInfo->expires_month[] = array('id' => sprintf('%02d', $i), 'text' => $j . '-' . strftime('%B',mktime(0,0,0,$i,1,2000)));
		}
		$today = getdate();
		for ($i = $today['year']; $i < $today['year'] + 10; $i++) {
			$year = strftime('%Y',mktime(0,0,0,1,1,$i));
			$basis->cInfo->expires_year[] = array('id' => $year, 'text' => $year);
		}
		// load the tax rates
		$basis->cInfo->tax_rates       = ord_calculate_tax_drop_down($basis->cInfo->contact->type, true);
		$sql = $basis->DataBase->prepare("SELECT id, CONCAT(contact_first , ' ',contact_last) as text FROM ".TABLE_CONTACTS." WHERE type='e'");
		$sql->execute();
		$basis->cInfo->contact->sales_rep_array = $sql->fetchAll(\PDO::FETCH_ASSOC);
		$basis->page_title  = "{$basis->cInfo->contact->page_title_edit} - ({$basis->cInfo->contact->short_name}) {$basis->cInfo->contact->address[m][0]->primary_name}";
		include(DIR_FS_MODULES . "contacts/pages/main/js_include.php");
		include(DIR_FS_MODULES . "contacts/pages/main/template_detail.php");
		$basis->observer->send_footer($basis);
	}

	
	function saveContact (\core\classes\basis &$basis) {
		try{
			if ($basis->cInfo->id == '') throw new \core\classes\userException("id variable isn't set can't execute method SaveContact ");
			if ($_POST['crm_date']) $_POST['crm_date'] = gen_db_date($_POST['crm_date']);
			if ($_POST['due_date']) $_POST['due_date'] = gen_db_date($_POST['due_date']);
			$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_CONTACTS . " WHERE id = {$basis->cInfo->id}");
			$sql->execute();
			$basis->cInfo->contact = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);
			// error check
			$basis->cInfo->contact->data_complete();
			// start saving data
			$basis->cInfo->contact->save();
			// Check attachments
			$result = $basis->DataBase->query("SELECT attachments FROM ".TABLE_CONTACTS." WHERE id = {$basis->cInfo->contact->id}");
			$attachments = $result['attachments'] ? unserialize($result['attachments']) : array();
			for ($image_id = 0; $image_id <= sizeof($attachments); $image_id++) {
				if (isset($_POST['rm_attach_'.$image_id])) {
					@unlink("{$this->dir_attachments}contacts_{$basis->cInfo->contact->id}_{$image_id}.zip");
					unset($attachments[$image_id]);
				}
			}
			if (is_uploaded_file($_FILES['file_name']['tmp_name'])) { // find an image slot to use
				$image_id = 0;
				while (true) {
					if (!file_exists("{$this->dir_attachments}contacts_{$basis->cInfo->contact->id}_{$image_id}.zip")) break;
					$image_id++;
				}
				saveUploadZip('file_name', "{$this->dir_attachments}contacts_{$basis->cInfo->contact->id}_{$image_id}.zip");
				$attachments[$image_id] = $_FILES['file_name']['name'];
			}
			$sql = $basis->DataBase->prepare("UPDATE ".TABLE_CONTACTS." SET attachments = '".serialize($attachments). "' WHERE id = {$basis->cInfo->contact->id}");
			$sql->execute();
			$temp->success = true;
			$temp->message = TEXT_SAVED_SUCCESSFULLY;
		}catch (\Exception $e) {
			$temp->success = false;
			$temp->error_message = $e->getMessage();
		}
		echo json_encode($temp);
	}

	function DeleteContact (\core\classes\basis &$basis) {
		if ($basis->cInfo->contactid == '') throw new \core\classes\userException("contactid variable isn't set can't execute method DeleteContact ");
		$sql = $basis->DataBase->prepare("SELECT * FROM " . TABLE_CONTACTS . " WHERE id = {$basis->cInfo->contactid}");
		$sql->execute();
		$basis->cInfo->contact = $sql->fetch(\PDO::FETCH_CLASS | \PDO::FETCH_CLASSTYPE);
		// error check
		$basis->cInfo->contact->delete();
		$temp = $basis->cInfo; 
		if( $basis->cInfo->contentType == "application/json"){
			$temp->success = true;
			$temp->message = TEXT_SAVED_SUCCESSFULLY;
			echo json_encode($temp);
		}else{
			$basis->addEventToStack('LoadContactMgrPage');
		}
	}

	function ContactAttachmentDownload (\core\classes\basis &$basis) {
		$cID   = db_prepare_input($_POST['id']);
		$imgID = db_prepare_input($_POST['rowSeq']);
		$filename = "contacts_{$cID}_{$imgID}.zip";
		if (file_exists($this->dir_attachments . $filename)) {
			$backup = new \phreedom\classes\backup();
			$backup->download($this->dir_attachments, $filename, true);
		}
	}

	function ContactAttachmentDownloadFirst (\core\classes\basis &$basis) {
	//	case 'dn_attach': // download from list, assume the first document only
		$cID   = db_prepare_input($_POST['rowSeq']);
		$result = $basis->DataBase->query("SELECT attachments FROM ".TABLE_CONTACTS." WHERE id = $cID");
		$attachments = unserialize($result['attachments']);
		foreach ($attachments as $key => $value) {
			$filename = "contacts_{$cID}_{$key}.zip";
			if (file_exists($this->dir_attachments . $filename)) {
				$backup = new \phreedom\classes\backup();
				$backup->download($this->dir_attachments, $filename, true);
			}
		}
	}

	/**
	 * this function loads the contacts pop up to select and return a contact
	 * @param unknown $basis
	 */
	function LoadContactsAccountsPopUp(\core\classes\basis &$basis) {
		\core\classes\messageStack::debug_log("executing ".__METHOD__ );
		if (! isset($basis->cInfo->type)) $basis->cInfo->type = 'c'; // default to customer
		if (! isset($basis->cInfo->fill)) $basis->cInfo->fill = 'bill'; // default
		if (! isset($basis->cInfo->jID)) throw new \core\classes\userException(TEXT_JOURNAL_ID_NOT_DEFINED); 
		switch ($basis->cInfo->type) {
			case 'b': $contact = TEXT_BRANCH;	break;
			case 'c': $contact = TEXT_CUSTOMER;	break;
			case 'e': $contact = TEXT_EMPLOYEE;	break;
			case 'i': $contact = TEXT_CRM;		break;
			case 'j': $contact = TEXT_PROJECT;	break;
			case 'v': $contact = TEXT_VENDOR;	break;
		}
		switch ($basis->cInfo->jID) {
			case  6: $search_journal = 4;  break;
			case  7: $search_journal = 6;  break;
			case 12: $search_journal = 10; break;
			case 13: $search_journal = 12; break;
		}
		?>
		<div data-options="region:'center'">
			<script type="text/javascript" src="includes/easyui/plugins/datagrid-groupview.js"></script>
		    <table id="dg" title="<?php echo TEXT_PLEASE_SELECT;?>" style="height:500px;padding:50px;">
	        	<thead>
	            	<tr>
	            		<th data-options="field:'short_name',sortable:true"><?php echo sprintf(TEXT_ARGS_ID, $contact);?></th>
	               		<th data-options="field:'name',sortable:true"><?php echo TEXT_NAME_OR_COMPANY?></th>
	            	   	<th data-options="field:'address1',sortable:true"><?php echo TEXT_ADDRESS1?></th>
	    	           	<th data-options="field:'city_town',sortable:true"><?php echo TEXT_CITY_TOWN?></th>
	        	       	<th data-options="field:'state_province',sortable:true"><?php echo TEXT_STATE_PROVINCE?></th>
	        	       	<th data-options="field:'postal_code',sortable:true"><?php echo TEXT_POSTAL_CODE?></th>
	        	       	<th data-options="field:'telephone1',sortable:true"><?php echo TEXT_TELEPHONE?></th>
	            	</tr>
	        	</thead>
	    	</table>
	    	<div id="toolbar">
	    		<?php echo \core\classes\htmlElement::checkbox('contact_show_inactive', TEXT_SHOW_INACTIVE, '1', false,'onchange="doSearch()"' );?>
	        	<div style="float: right;"> <?php echo \core\classes\htmlElement::search('search_text','doSearch');?></div>
	    	</div>
    	</div>	
		<script type="text/javascript">
			document.title = '<?php echo TEXT_CONTACT_SEARCH; ?>';
	    	function doSearch(value){
	    		console.log('A search was requested.');
	        	$('#dg').datagrid('load',{
	        		search_text: $('#search_text').val(),
	        		dataType: 'json',
	                contentType: 'application/json',
	                async: false,
	                type: '<?php echo $basis->cInfo->type;?>',
	                contact_show_inactive: document.getElementById('contact_show_inactive').checked ? 1 : 0,
	        	});
	    	}
	        
			$('#dg').datagrid({
				url:		"index.php?action=GetAllContactsAndJournals",
				queryParams: {
					type: '<?php echo $basis->cInfo->type;?>',
					jID: '<?php echo $search_journal;?>',
					open_only: true,
					dataType: 'json',
	                contentType: 'application/json',
	                async: false,
				},
				onLoadSuccess: function(data){
					console.log('the loading of the datagrid was succesfull');
					$.messager.progress('close');
					if(data.total == 0) $.messager.alert('<?php echo TEXT_ERROR?>',"<?php echo TEXT_NO_RESULTS_FOUND?>");
				},
				onLoadError: function(){
					console.error('the loading of the datagrid resulted in a error');
					$.messager.progress('close');
					$.messager.alert('<?php echo TEXT_ERROR?>','Load error:'+arguments.responseText);
				},
				onDblClickRow: function(index , row){
					alert(row);
					console.log('a contact in the datagrid was double clicked');
					var fill = '<?php echo $basis->cInfo->fill?>';
					if (fill == 'ship') {
					    var ship_only = true;
					    window.opener.clearAddress('ship');
					} else {
					    var ship_only = false;
					    window.opener.ClearForm();
					}
					window.opener.ajaxOrderData(row.contactid, 0, <?php echo $basis->cInfo->jID;?>, false, ship_only);
					self.close();
				},
				remoteSort:	false,
				idField:	"contactid",
				fitColumns:	true,
				singleSelect:true,
				sortName:	"short_name",
				sortOrder: 	"asc",
				toolbar: 	"#toolbar",
				loadMsg:	"<?php echo TEXT_PLEASE_WAIT?>",
				rowStyler: function(index,row){
					if (row.inactive == '1') return 'background-color:pink;';
				},
                view:groupview,
                groupField:'contactid',
                groupFormatter:function(value,rows){
                    return value + ' - ' + rows.length + ' Item(s)';
                },
			});
		</script><?php 
	}

	function LoadTermsPopUp (\core\classes\basis &$basis) {
		$temp = "\\contacts\\classes\\type\\{$basis->cInfo->type}";
		$basis->cInfo->contact = new $temp;
		$basis->cInfo->cal_terms = array(
				'name'      => 'dateReference',
				'form'      => 'popup_terms',
				'fieldname' => 'due_date',
				'imagename' => 'btn_terms',
				'default'   => '',
				'params'    => array('align' => 'left'),
		);

		$basis->page			= 'popup_terms';
		$basis->page_title 		= TEXT_PAYMENT_TERMS;
	}
	
	function ContactGetChartData (\core\classes\basis &$basis) {
		$basis->cInfo->type  = pie;
		$basis->cInfo->width  = 600;
		$basis->cInfo->height  = 400;
		switch ($basis->cInfo->fID) {
			case 'annual_sales':
				$basis->cInfo->type       = 'column';
				$basis->cInfo->title      = TEXT_MONTHLY_SALES;
				$basis->cInfo->label_text = TEXT_DATE;
				$basis->cInfo->value_text = TEXT_TOTAL;
				if (!$basis->cInfo->cID)  throw new \core\classes\userException('There is no contact id');
				$date = new \core\classes\DateTime();
				$date->modify("-1 year");
				$date->modify("first day of this month");
				$sql = $basis->DataBase->prepare("SELECT month(post_date) as month, year(post_date) as year, sum(total_amount) as total FROM ".TABLE_JOURNAL_MAIN."
						WHERE bill_acct_id = {$basis->cInfo->cID} and journal_id in (12,13) and post_date >= '".$date->format('Y-m-d')."' group by year, month LIMIT 12");
				$sql->execute();
				for ($i=0; $i<12; $i++) {
					$result = $sql->fetch(\PDO::FETCH_LAZY);
					if ($result['year'] == $date->format('Y') && $result['month'] == $date->format('m')) {
						$value = $result['total'];
					} else {
						$value = 0;
					}
					$basis->cInfo->data[] = array(
							'label' => $date->format('Y-m'),
							'value' => $value,
					);
					$date->modify("first day of next month");
				}
				break;
			default:
				throw new \core\classes\userException("Don't know report {$basis->cInfo->fID}");
		}
	}
	

	function load_demo() {
		global $admin;
		// Data for table `address_book` @todo add class to sql and keys
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_ADDRESS_BOOK);
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (1, 1, 'vm', 'Obscure Video', '', '1354 Triple A Ave', '', 'Chatsworth', 'CA', '93245', 'USA', '800.345.5678', '', '', '', 'obsvid@obscurevideo.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (2, 2, 'cm', 'CompuHouse Computer Systems', '', '8086 Intel Ave', '', 'San jose', 'CA', '94354', 'USA', '800-555-1234', '', '', '', 'sales@compuhouse.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (3, 3, 'vm', 'Speedy Electronics, Inc.', '', '777 Lucky Street', 'Unit #2B', 'San Jose', 'CA', '92666', 'USA', '802-555-9876', '', '', '', 'custserv@speedyelec.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (4, 4, 'cm', 'Computer Repair Services', '', '12932 136th Ave.', 'Suite A', 'Denver', 'CO', '80021', 'USA', '303-555-5469', '', '', '', 'servive@comprepair.net', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (5, 5, 'vm', 'LCDisplays Corp.', '', '28973 Pixel Place', '', 'Los Angeles', 'CA', '90001', 'USA', '800-555-5548', '', '', '', 'cs@lcdisplays.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (6, 6, 'vm', 'Big Box Corp', '', '11 Square St', '', 'Longmont', 'CO', '80501', 'USA', '303-555-9652', '', '', '', 'big.box@yahoo.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (7, 7, 'cm', 'John Smith Jr.', '', '13546 Euclid Ave', '', 'Ontario', 'CA', '92775', 'USA', '818-555-1000', '', '', '', 'jsmith@aol.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (8, 8, 'cm', 'Jim Baker', '', '995 Maple Street', 'Unit #56', 'Northglenn', 'CO', '80234', 'USA', 'unlisted', '', '', '', 'jb@hotmail.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (9, 9, 'cm', 'Lisa Culver', '', '1005 Gillespie Dr', '', 'Boulder', 'CO', '80303', 'USA', '303-555-6677', '', '', '', 'lisa@myveryownemailaddress.net', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (10, 10, 'cm', 'Parts Locator LLC', '', '55 Sydney Hwy', '', 'Deerfield Beach', 'FL', '33445', 'USA', '215-555-0987', '', '', '', 'parts@partslocator.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (11, 11, 'vm', 'Accurate Input, LLC', '', '1111 Stuck Key Ave', '', 'Burbank', 'CA', '91505', 'USA', '800-555-1267', '', '818-555-5555', '', 'sales@accurate.com', 'www.AccurateInput.com', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (12, 12, 'vm', 'BackMeUp Systems, Inc', '', '1324 44th Ave.', '', 'New York', 'NY', '10019', 'USA', '212-555-9854', '', '', '', 'sales@backmeup.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (13, 13, 'vm', 'Closed Cases', 'Fernando', '23 Frontage Rd', '', 'New York', 'NY', '10019', 'USA', '888-555-6322', '800-555-5716', '', '', 'custserv@closedcases.net', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (14, 14, 'vm', 'MegaWatts Power Supplies', '', '11 Joules St.', '', 'Denver', 'CO', '80234', 'USA', '303-222-5617', '', '', '', 'help@hotmail.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (15, 15, 'vm', 'Slipped Disk Corp.', 'Accts. Receivable', '1234 Main St', 'Suite #1', 'La Verne', 'CA', '91750', 'USA', '714-555-0001', '', '', '', 'sales@slippedisks.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (16, 16, 'em', 'John Smith', '', '123 Birch Ave', 'Apt 12', 'Anytown', 'CO', '80234', 'USA', '303-555-3451', '', '', '', 'john@mycompany.com', '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_ADDRESS_BOOK . " VALUES (17, 17, 'em', 'Mary Johnson', '', '6541 First St', '', 'Anytown', 'CO', '80234', 'USA', '303-555-7426', '', '', '', 'nary@mycomapny.com', '', '');");
		// Data for table `contacts`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_CONTACTS);
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (1, 'v', 'Obscure Video', '0', '', '', '', '', '2000', '', '', '', '3:1:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (2, 'c', 'CompuHouse', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (3, 'v', 'Speedy Electronics', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (4, 'c', 'Computer Repair', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (5, 'v', 'LCDisplays', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (6, 'v', 'Big Box', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (7, 'c', 'Smith, John', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (8, 'c', 'JimBaker', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (9, 'c', 'Culver', '0', '', '', '', '', '4000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (10, 'c', 'PartsLocator', '0', '', '', '', '', '4000', '', '', '', '3:0:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (11, 'v', 'Accurate Input', '0', '', '', '', '', '2000', '', '', 'SK200706', '3:0:10:30:2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (12, 'v', 'BackMeUp', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (13, 'v', 'Closed Cases', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (14, 'v', 'MegaWatts', '0', '', '', '', '', '2000', '', '', 'MW20070301', '0::::2500.00', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (15, 'v', 'Slipped Disk', '0', '', '', '', '', '2000', '', '', '', '0::::2500.00', '', '', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (16, 'e', 'John', '0', 'John', '', 'Smith', '', 'b', '', 'Sales', '', '::::', '', '0', '', now(), NULL, NULL, NULL);");
		$admin->DataBase->query("INSERT INTO " . TABLE_CONTACTS . " (`id`, `type`, `short_name`, `inactive`, `contact_first`, `contact_middle`, `contact_last`, `store_id`, `gl_type_account`, `gov_id_number`, `dept_rep_id`, `account_number`, `special_terms`, `price_sheet`, `tax_id`, `attachments`, `first_date`, `last_update`, `last_date_1`, `last_date_2`) VALUES (17, 'e', 'Mary', '0', 'Mary', '', 'Johnson', '', 'e', '', 'Accounting', '', '::::', '', '0', '', now(), NULL, NULL, NULL);");
		// Data for table `departments`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_DEPARTMENTS);
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('1', 'Sales', 'Sales', '0', '', 2, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('2', 'Administration', 'Administration and Operations', '0', '', 1, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('3', 'Accounting', 'Accounting and Finance', '0', '', 1, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('4', 'Shipping', 'Shipping Operation', '0', '', 4, '0');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPARTMENTS . " VALUES ('5', 'Warehouse', 'Materials Receiving', '0', '', 4, '0');");
		// Data for table `departments_types`
		$admin->DataBase->query("TRUNCATE TABLE " . TABLE_DEPT_TYPES);
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (1, 'Administration');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (2, 'Sales and Marketing');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (3, 'Manufacturing');");
		$admin->DataBase->query("INSERT INTO " . TABLE_DEPT_TYPES . " VALUES (4, 'Shipping & Receiving');");
		parent::load_demo();
	}
}
?>