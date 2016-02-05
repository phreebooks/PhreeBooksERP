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
require_once (DIR_FS_ADMIN . 'modules/phreemail/config.php');
class admin extends \core\classes\admin {
	public $id 			= 'phreemail';
	public $text		= MODULE_PHREEMAIL_TITLE;
	public $description = MODULE_PHREEMAIL_DESCRIPTION;
	public $version		= '1.0';

	function __construct() {
		$this->prerequisites = array( // modules required and rev level for this module to work properly
		  'phreedom'   => 3.3,
		  'inventory'  => 3.3,
		  'phreebooks' => 3.3,
		);
		// add new directories to store images and data
		$this->dirlist = array(
		  'phreemail',
		);
	    // Load tables
	    //@todo maybe the toaddress_id and fromadress_id can be removed.
		$this->tables = array(
		  TABLE_PHREEMAIL => "CREATE TABLE ".TABLE_PHREEMAIL."  (
	  		`id` int(11) NOT NULL auto_increment,
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
	  		PRIMARY KEY  (`ID`),
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

		TABLE_PHREEMAIL_WORDS => "CREATE TABLE ". TABLE_PHREEMAIL_WORDS ." (
	  		`IDw` int(11) NOT NULL auto_increment,
	  		`Word` varchar(100)  NOT NULL default '',
	  		PRIMARY KEY  (`IDw`),
	  		KEY `Word` (`Word`)
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
	}

	function install($path_my_files, $demo = false) {
	    global $admin, $messageStack;
		parent::install($path_my_files, $demo);
	  	$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_WORDS . " VALUES(1, 'viagvra');");
	  	$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_WORDS . " VALUES(2, 'rjolex');");
	  	$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_WORDS . " VALUES(3, 'viajagra');");
		$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_LIST  . " VALUES (1, 'spam@spamserver.com', 'B');");
		$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_DIR   . " VALUES (1, 0, 0, 'Spam', 1, '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_DIR   . " VALUES (2, 0, 1, 'Trash', 1, '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_DIR   . " VALUES (3, 0, 2, 'Orders', 1, '', '');");
		$admin->DataBase->query("INSERT INTO " . TABLE_PHREEMAIL_DIR   . " VALUES (4, 0, 3, 'Personal', 1, '', '');");
		parent::install();
	}

  function Aafter_ValidateUser(\core\classes\basis &$basis) {//@todo
  		global $admin;
  		\core\classes\messageStack::debug_log("\n\n*************** Retrieving Mail from ".EMAIL_SMTPAUTH_MAILBOX." *******************");
		try{
	  		$mail = new \phreemail\classes\phreemail();
			$mail->connect('', '', EMAIL_SMTPAUTH_MAILBOX, '');
			if ($mail->error_count != 0 ){
				\core\classes\messageStack::add($mail->ErrorInfo, 'error');
			}else{
				//while(!$mail->EOF){
					$mail->do_action();
					//$mail->MoveNext();
				//}
			}

			/*while(!$mail->EOF){
				$mail->do_action();
				$mail->MoveNext();
			}*/
		}catch (\Exception $exception){
			\core\classes\messageStack::add($exception->getMessage(), 'error');
		}
		\core\classes\messageStack::debug_log("\n\n*************** End Retrieving Mail from ".EMAIL_SMTPAUTH_MAILBOX." *******************");
		try{
			\core\classes\messageStack::debug_log("\n\n*************** Retrieving Mail from ".$_SESSION['admin_email']." *******************");
			$mail = new \phreemail\classes\phreemail();
			$mail->connect('', '', $_SESSION['admin_email'], '');
//			$mail->get_all_emails();
			if ($mail->error_count != 0 ){
				\core\classes\messageStack::add($mail->ErrorInfo, 'error');
			}else{
				//while(!$mail->EOF){
					$mail->do_action();
					//$mail->MoveNext();
				//}
			}
			/*
			while(!$mail->EOF){
				$mail->do_action();
				$mail->MoveNext();
			}*/
			\core\classes\messageStack::debug_log("\n\n*************** End Retrieving Mail from ".$_SESSION['admin_email']." *******************");
		}catch (\Exception $exception){
			\core\classes\messageStack::add($exception->getMessage(), 'error');
		}
		if ( DEBUG )   $messageStack->write_debug();
  }

}
?>