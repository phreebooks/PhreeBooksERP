<?php

require_once(DIR_FS_WORKING	. "functions/phreewiki.php");
require_once(DIR_FS_WORKING . "defaults.php");
	
//////////////////////////////////////////////////////////////
if( isset($_GET['title']) )	{
	$title = $_GET['title'];
	if( get_magic_quotes_gpc() ) {
		$title = stripslashes($title);
	}
}
//////////////////////////////////////////////////////////////	
if( isset($_GET['tags']) ) {
	//obtain query string
	$q = rawurldecode($_SERVER['QUERY_STRING'] );		//decode signs
	$start = strpos($q,"tags")+5;			//add 5 to remove "tags="
	$end = strpos($q,"&",$start);			//end position with "&"
	
	if( $end !== FALSE && $end>$start ){		//truncate string to required value
		$q = substr($q,$start,$end-$start);
	}else{
		$q = substr($q,$start);			//last to end of string if end not found
	}
	$yesTags = array();
	$noTags = array();
	
	//split tags by + and -
	$tags = preg_split('![+-]!', $q, -1, PREG_SPLIT_NO_EMPTY);

	//separate into arrays
	foreach( $tags as $t ) {
		$signPos = strpos($q,$t)-1;
		if( strcmp($q[$signPos],"-") == 0 ) {
			$noTags[]  = trim($t);
		}else{
			$yesTags[] = trim($t);
		}
	}
}
//////////////////////////////////////////////////////////////
//check if getting revision
if( isset($_GET['title']) )	{
	$tiddlers = select_all_tiddler_versions($title);
	$t = array();
	foreach( $tiddlers as $tid ) {
		$tid['title'] .= " version ".$tid['version'];
		$t[] = $tid;
	}
	$tiddlers = $t;
}elseif( isset($_GET['tags']) )	{
	$tiddlers = select_all_tiddler_by_tags($yesTags, $noTags);
}else{
	$tiddlers = select_all_tiddlers();
}

	
$include_header   = true;
$include_footer   = false;
$include_template = 'template_main.php';
define('PAGE_TITLE', BOX_PHREEWIKI_MODULE);