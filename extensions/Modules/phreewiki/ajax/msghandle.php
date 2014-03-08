<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |
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
//  Path: /modules/phreewiki/ajax/msghandle.php
//
$security_id =  validate_ajax_user();
/**************  include page specific files    *********************/
include_once(DIR_WS_MODULES."phreewiki/functions/phreewiki.php");
include_once(DIR_WS_MODULES."phreewiki/defaults.php");

$xml = false ;
	
//////////////////////////////////////////////////////////initial checking and required functions////////////////////////////////////////
if( !isset($_REQUEST['action']) ) {
	$return_result = "no action found";		
}
	

switch ($_REQUEST['action']){	
	case "revisionList":
	case "revisionDisplay":
		//list of revision
		if( !isset($_REQUEST['title']) ){
			$return_result = PHREEWIKI_NO_TITLE;	
			break;
		}
		
		$title = $_REQUEST['title'];
		$result = select_all_tiddler_versions($title);		//get all required version
		
		//print revision list
		if( strcmp($_REQUEST['action'],"revisionList") == 0 ) {

			for( $i=sizeof($result)-1 ; $i>=0 ; $i-- ) {
				$xml .= "<revisionList>\n";
				$xml .= "\t" . xmlEntry("modified", $result[$i]['modified']);
				$xml .= "\t" . xmlEntry("version",  $result[$i]['version']);
				$xml .= "\t" . xmlEntry("modifier", $result[$i]['modifier']);
				$xml .= "</revisionList>\n";
			}
			
		}else{//get detailed info
			if( sizeof($result) == 1 ) {
				$return_result = PHREEWIKI_ERROR_REVISION_NOT_FOUND;	
				break;
			}
			for( $i=sizeof($result)-1; $i>=0; $i-- ) {
				if( $result[$i]['version'] == $_REQUEST['revision'] ) {
					$xml .= "<version>\n";
					$xml .= "\t" . xmlEntry("title", 		$result[$i]['title']);
					$xml .= "\t" . xmlEntry("body",  		$result[$i]['body']);
					$xml .= "\t" . xmlEntry("modifier",	$result[$i]['modifier']);
					$xml .= "\t" . xmlEntry("modified", $result[$i]['modified']);
					$xml .= "\t" . xmlEntry("created",	$result[$i]['created']);
					$xml .= "\t" . xmlEntry("tags",  		$result[$i]['tags']);
					$xml .= "\t" . xmlEntry("version",  $result[$i]['version']);
					$xml .= "\t" . xmlEntry("fields",  	$result[$i]['fields']);
					$xml .= "</version>\n";
					$i=-1;
				}
			}
		}
		
	break;
	
	case "saveTiddler":
		//strip all slashes first and readd them before adding to SQL
		$tiddler = $_REQUEST['tiddler'];
		$oldtitle = $_REQUEST['otitle'];
		$omodified =$_REQUEST['omodified'];
		//exit($tiddler.'  omo  '.$omodified);
		$tiddler = tiddler_htmlToArray($tiddler);
		$ntiddler = tiddler_create($tiddler[0]['title'], 
									$tiddler[0]['body'], 
									$tiddler[0]['modifier'], 
									$tiddler[0]['modified'], 
									$tiddler[0]['tags'], 
									"","","",
									$tiddler[0]['fields']);

		//append modifier as tag
		if( PHREEWIKI_APPEND_MODIFIER )	{
			$modifier_add = $ntiddler['modifier'];
			if( strpos($modifier_add, " ") !== FALSE )	{
				$modifier_add = "[[".$modifier_add."]]";
			}
			if( strpos($ntiddler['tags'],$modifier_add)===FALSE ) {
				$ntiddler['tags'] .= " ".$modifier_add;
			}
		}
		
		//save entry 
		$return_result = saveTiddly( $oldtitle, $omodified, $ntiddler);
	break;

//////////////////////////////////////////////////////////removeTiddler//////////////////////////////////////////////////////////////
	case "removeTiddler" :
		//remove quotes
		$title = $_REQUEST['title'];
		$title = tiddler_bodyEncode($title);
		$tiddler = select_tiddler_by_title($title);
		$return_result = deleteTiddler($tiddler);	

	break;
//////////////////////////////////////////////////////////rss//////////////////////////////////////////////////////////////
	case "rss" :
		//remove slashes
		$body = $_REQUEST['rss'];

		//check authorization
		if ($security_id < 1) {
			$return_result =  ERROR_NO_PERMISSION ;		//return error to display in displayMessage and make iframe idle
			break;
		}
		//save to file
		$fhandle = fopen(DIR_FS_ADMIN."/modules/phreewiki/rss.xml",'w');
		if( $fhandle===FALSE ) {
			$return_result = PHREEWIKI_ERROR_RSS_FILE_CREATE ;		//return error to display in displayMessage and make iframe idle
			break;
		}
		if( fwrite($fhandle,$body)== FALSE ) {
			$return_result .=  PHREEWIKI_ERROR_RSS_FILE_WRITE ;		//return error to display in displayMessage and make iframe idle
			break;
		}
		$return_result = PHREEWIKI_NOTICE_RSS_CREATED ;		//return error to display in displayMessage and make iframe idle
		
	break;

//////////////////////////////////////////////////////////saveChanges//////////////////////////////////////////////////////////////
	case "upload":
		//remove slashes
		$body = $_REQUEST['upload'];
		
		//check authorization
		if ($security_id < 1) {
			exit(ERROR_NO_PERMISSION );		//return error to display in displayMessage and make iframe idle
		}
		//convert HTML to array form and insert into DB
		//WARNING: everything will be overwritten so beware
		$tiddler_array = tiddler_htmlToArray($body);
		$result = false ;
		foreach( $tiddler_array as $ntiddler ) {
			$tiddler = select_tiddler_by_title($ntiddler['title']);
			$result .= '" ';
			$result .= $ntiddler['title'] .' = ' ;
			$result .= saveTiddly($tiddler['title'],$tiddler['modified'],$ntiddler);
			$result .= ' " ';			
		}
		$return_result =  PHREEWIKI_NOTICE_UPLOAD_STORE_AREA_COMPLETE . ': ' . $result ;		//return error to display in displayMessage and make iframe idle
	}


$xml .= xmlEntry("Message", $return_result );		//return error to display in displayMessage and make iframe idle



echo createXmlHeader() . $xml . createXmlFooter();
ob_end_flush();
session_write_close();
die;	
?>
