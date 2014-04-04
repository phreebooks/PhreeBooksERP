<?php
/*
 * default saving function
 */
function saveTiddly($otitle, $omodified, $ntiddler, $overwrite=0)	{
  global $lock_title;
  if( strlen($ntiddler['title']) == 0 ) 			return PHREEWIKI_NO_TITLE;
	//lock title check
  if( in_array($ntiddler['title'], $lock_title) )	return PHREEWIKI_WARNING_IN_LOCKED_ARRAY;

  $tiddler = select_tiddler_by_title($ntiddler['title']);			//tiddler with title $title
  $otiddler = select_tiddler_by_title($otitle);		//tiddler with $otitle

	//insert tiddler if both are not found
	if( sizeof($tiddler)== 0 && sizeof($otiddler)== 0 ) {
		$ntiddler['creator'] = $ntiddler['modifier'];
		$ntiddler['created'] = $ntiddler['modified'];
		$ntiddler['version'] = 1;
		return insert_tiddler($ntiddler);
	}

	//if old title not exist, but new title exist, treat as overwrite (new tiddler overwrite another)
	//both $otitle and $title exist and title different, $title overwrite $otitle, delete $title
	//$otitle exist, $title not exist, treat as editting/modify (rename  tiddler)
	if( sizeof($tiddler)!= 0 && sizeof($otiddler)!= 0 ) {
		//if old title exist, check if old modified time matches to new one
		if( strcmp($otiddler['modified'],$omodified)!= 0 ) {		//ask to reload if modified date differs
			return PHREEWIKI_WARNING_TIDDLER_NEED_RELOAD;
		}
		if( $overwrite == 2 && strcmp($tiddler['title'], $otitle ) == 0 ) {		//append
			$ntiddler['body'] 	= $tiddler['body'] . $ntiddler['body'];
			$ntiddler['tags']		= $tiddler['tags'] . $ntiddler['tags'];
		}
		$ntiddler['creator'] 	= $otiddler['creator'];
		$ntiddler['created'] 	= $otiddler['created'];
		$ntiddler['version'] 	= $otiddler['version'] + 1;
		return tiddler_update($otiddler, $ntiddler);
	}
	return PHREEWIKI_CAN_NOT_PROCESS;
}
/*
 * insert tiddlers
 */

function insert_tiddler($tiddler , $install = false) {
	global $lock_title;
	print_r($lock_title);
	\core\classes\user::validate_security(SECURITY_PHREEWIKI_MGT, 2); // security check
	if ( sizeof($tiddler)== 0 )																						return PHREEWIKI_WARNING_TIDDLER_NOT_FOUND;
  	if ( !$install == true && in_array($tiddler['title'], $lock_title) )	return PHREEWIKI_WARNING_IN_LOCKED_ARRAY;
  	db_perform( TABLE_PHREEWIKI , $tiddler, 'insert');
	$new_id = db_insert_id();
	//insert backup if required
	if( PHREEWIKI_VERSIONING ==1)  {
		//set inserted record id as oid
		$tiddler = tiddler_create_backup($tiddler, $new_id);
		db_perform( TABLE_PHREEWIKI_VERSION , $tiddler, 'insert');
	}
	return TRUE;
}

/*
 *  update tiddlers
 */

function tiddler_update($oldtiddler, $newtiddler) {
	global $lock_title;
	unset($newtiddler['id']);
	\core\classes\user::validate_security(SECURITY_PHREEWIKI_MGT, 3); // security check
	if ( sizeof($tiddler)== 0 )											return PHREEWIKI_WARNING_TIDDLER_NOT_FOUND;
 	if ( in_array($tiddler['title'], $lock_title) )	return PHREEWIKI_WARNING_IN_LOCKED_ARRAY;
	db_perform(TABLE_PHREEWIKI, $newtiddler, 'update', 'id = "' . $oldtiddler['id']. '"');
	//insert backup if required
	if( PHREEWIKI_VERSIONING == 1 ) {
		//set inserted record id as oid
		$tiddler = tiddler_create_backup($newtiddler, $oldtiddler['id']);
		db_perform( TABLE_PHREEWIKI_VERSION , $tiddler, 'insert');
	}
	return TRUE;
}

/*
 * delete tiddlers
 */

function deleteTiddler($tiddler) {
  global $db, $lock_title;
  \core\classes\user::validate_security(SECURITY_PHREEWIKI_MGT, 2); // security check
  if ( sizeof($tiddler)== 0 )											return PHREEWIKI_WARNING_TIDDLER_NOT_FOUND;
  if ( in_array($tiddler['title'], $lock_title) )	return PHREEWIKI_WARNING_IN_LOCKED_ARRAY;

  $result = $db->Execute("DELETE FROM ". TABLE_PHREEWIKI ." WHERE `id` = '". $tiddler['id'] ."'");
  if( $result === FALSE ) {
    return GL_ERROR_NO_DELETE;
  }
  return sprintf(TEXT_DELETE_SUCCESSFUL, BOX_PHREEWIKI_MODULE , $tiddler['title']);
}

/*
 * selecting tiddlers
 */

function select_all_tiddlers() {
	global $db;
  	$security_id = \core\classes\user::validate(SECURITY_PHREEWIKI_MGT);
  	$output_array = array();
  	$result = $db->Execute("SELECT * FROM ".TABLE_PHREEWIKI);
  	$i=0;
  	while(!$result->EOF){
		foreach ($result->fields as $key => $value) {
			$output_array[$i][$key] = $value;
		}
		$i++;
		$result->MoveNext();
	}
  	return $output_array;
}

function select_tiddler_by_title($title) {
  global $db;
  $security_id = \core\classes\user::validate(SECURITY_PHREEWIKI_MGT);
  $tiddlers = $db->Execute("SELECT * FROM ".TABLE_PHREEWIKI."  where title ='" . $title . "'");
  foreach($tiddlers as $t) {
		if( strcmp($t['title'],$title)==0 ) {
	  	return $t;
		}
  }
  return array();		//only return 1 title
}

function select_all_tiddler_versions($title) {
	global $db;
	$security_id = \core\classes\user::validate(SECURITY_PHREEWIKI_MGT);
	//get current tiddler id
	$tiddler_id = select_tiddler_by_title($title);
	if( sizeof($tiddler_id)<>0 ) {
		$tiddlers = $db->Execute("select * from " . TABLE_PHREEWIKI_VERSION . " where oid = '" . $tiddler_id['id'] . "'");
		if(!$tiddlers->RecordCount()== 0){
			$return_tiddlers = array();
			$i = 0;
			while (!$tiddlers->EOF) {
				foreach ($tiddlers->fields as $key => $value){
					$return_tiddlers[$i][$key] = $value;
				}
				$i++;
				$tiddlers->MoveNext();
			}
		}
		return $return_tiddlers;		//tiddlers would be in the form array("<title>"=>array("title"=>"<title>", .....
	}
	return array();
}

function select_all_tiddler_by_tags($yesTags,$noTags) {
	$security_id = \core\classes\user::validate(SECURITY_PHREEWIKI_MGT);
	//get all data from db
	$tiddlers = select_all_tiddlers();
	if( sizeof($tiddlers)>0 ) {
		$return_tiddlers = array();
		foreach( $tiddlers as $t ) {
			//check for tags
			$tag = tiddler_breakTag($t['tags']);
			$tmp = array_merge($tag,$noTags);
			if( sizeof($tmp) == sizeof(array_flip(array_flip($tmp))) )	{	//if no $noTags, continue
				$tmp = array_merge($tag,$yesTags);
				//if no yesTags, assume only want to remove some tag thus all but $noTags are returned
				//if $yesTags exist, display only if $yesTags is in tiddler
				if( sizeof($yesTags)==0 || sizeof($tmp) != sizeof(array_flip(array_flip($tmp))) ) {
					$return_tiddlers[$t['title']] = $t;
				}
			}
		}
		return $return_tiddlers;		//tiddlers would be in the form array("<title>"=>array("title"=>"<title>", .....
	}
	return array();
}

/*
 * create
 */

function tiddler_create($title, $body="", $modifier="", $modified="", $tags="", $id="", $creator="", $created="", $fields="", $version=1) {
	$tiddler = array(
		'id'        =>	preg_replace("![^0-9]!","",$id),		//if empty, leave it as empty. otherwise make it as int
		'title'			=>  $title,
		'body' 			=>  $body,
		'modifier'	=>	$modifier,
		'modified'	=>  preg_replace("![^0-9]!","",$modified),
		'creator'		=> 	$creator,
		'created' 	=>  preg_replace("![^0-9]!","",$created),
		'tags' 			=>  $tags,
		'fields'	 	=>  $fields,
		'version'		=>  preg_replace("![^0-9]!","",$version),
		);
	return $tiddler;
}

function tiddler_create_backup($tiddler_create, $oid="") {
	$tiddler = array(
		'oid' 		=>	preg_replace("![^0-9]!","",$oid),
		'title'		=> 	$tiddler_create['title'],
		'body'		=>	$tiddler_create['body'],
		'modifier'	=>	$tiddler_create['modifier'],
		'modified'	=> 	preg_replace("![^0-9]!","",$tiddler_create['modified']),
		'tags'		=> 	$tiddler_create['tags'],
		'fields'	=> 	$tiddler_create['fields'],
		'version'	=> 	preg_replace("![^0-9]!","",$tiddler_create['version']),
		);
	return $tiddler;
}

/*
 * misc
 */

function tiddler_outputDIV($tiddler) {
	$return = '<div tiddler="'. $tiddler["title"].'" modifier="'. $tiddler["modifier"].'" modified="'. $tiddler["modified"].'" created="'.  $tiddler["created"].'" tags="'. $tiddler["tags"].'" temp.ccTversion="'. $tiddler["version"].'"'. $tiddler["fields"] .'>'. $tiddler["body"] .'</div>';
	return $return;
}

function tiddler_bodyEncode($body) {
	$body = str_replace('\\',"\\s",$body);		//replace'\' with '\s'
	$body = str_replace("\n","\\n",$body);		//replace newline with '\n'
	$body = str_replace("\r","",$body);		//return character is not required
	$body = htmlspecialchars($body);		//replace <, >, &, " with their html code
	return $body;
}

function tiddler_bodyDecode($body) {
	$body = str_replace("&quot;","\"",$body);
	$body = str_replace("&#039;","'",$body);
	$body = str_replace("&lt;","<",$body);
	$body = str_replace("&gt;",">",$body);
	$body = str_replace("&amp;","&",$body);
	$body = str_replace("\\n","\n",$body);		//replace newline with '\n'
	$body = str_replace('\\s',"\\",$body);		//replace'\' with '\s'
	return 	$body;
}

function user_getUsername()	{
	global $db;
	$result = $db->Execute('Select display_name From '.TABLE_USERS.' Where admin_id ="'.$_SESSION['admin_id'].'"');
	$names = explode(' ',$result->fields['display_name']);
	While ($name =array_shift($names)){
		$u .=ucfirst(strtolower($name));
	}
	return $u;
}

function tiddler_breakTag($tagStr)	{
	$array = array();
	//obtain and remove [[tags]]
	$r=0;
	$e=0;		//ending tag position
	while( ($r=strpos( $tagStr, "[[", 0))!==FALSE && ($e=strpos( $tagStr, "]]", $r))!==FALSE ) {//$e > $r so will use $r to find $e
		$tag = substr($tagStr, $r+2, $e-$r-2);
		$array[] = $tag;
		$tagStr = str_replace('[['.$tag.']]'," ",$tagStr);
	}
	//obtain regular tags separate by space
	//put in all tags into $array
	$array = array_merge($array,explode(" ",$tagStr));
	//strip empty string and trim tags
	$return = array();
	foreach($array as $t)	{
		if(strlen($t)>0){
			$return[] = trim($t);
		}
	}
	return $return;
}

function tiddler_htmlToArray($html)	{

	$tiddlers=preg_grep("!<div.+tiddler=!",explode("\n",$html));		//only ones with "<div tiddler=" is accepted
	$result = array();
	foreach($tiddlers as $tid)	{	//for each line of tiddler
		$t = $tid;
		//first take body out
		$r['body'] = trim(preg_replace("!(<div[^>]*>|</div>)!","",$t));
		$t = preg_replace("!(<div |>.*</div>)!","",$t);		//take away body and begining <div tag

		//define useful regex
		$reg_remove = "!([^=]*=\"|\")!";		//reg ex for removing something=" and "

		//take out the rest of the info
		$reg = "!tiddler=\"[^\"]*\"!";
		preg_match($reg, $t, $tmp);				//obtain string from tiddler
		$t = preg_replace($reg, "", $t);		//remove data from div string
		$r['title'] = trim(preg_replace($reg_remove,"",$tmp[0]));		//remove unwanted string and add to array

		$reg = "!modifier=\"[^\"]*\"!";
		preg_match($reg, $t, $tmp);				//obtain string from tiddler
		$t = preg_replace($reg, "", $t);		//remove data from div string
		$r['modifier'] = trim(preg_replace($reg_remove,"",$tmp[0]));		//remove unwanted string and add to array

		$reg = "!modified=\"[^\"]*\"!";
		preg_match($reg, $t, $tmp);				//obtain string from tiddler
		$t = preg_replace($reg, "", $t);		//remove data from div string
		$r['modified'] = trim(preg_replace($reg_remove,"",$tmp[0]));		//remove unwanted string and add to array

		$reg = "!created=\"[^\"]*\"!";
		preg_match($reg, $t, $tmp);				//obtain string from tiddler
		$t = preg_replace($reg, "", $t);		//remove data from div string
		$r['created'] = trim(preg_replace($reg_remove,"",$tmp[0]));		//remove unwanted string and add to array

		$reg = "!tags=\"[^\"]*\"!";
		preg_match($reg, $t, $tmp);				//obtain string from tiddler
		$t = preg_replace($reg, "", $t);		//remove data from div string
		$r['tags'] = trim(preg_replace($reg_remove,"",$tmp[0]));		//remove unwanted string and add to array
		//remove "temp." fields as they are temporary
		$t = preg_replace("!temp[.][^\"]*=\"[^\"]*\"!", "", $t);
		$t = str_replace("  ", " ", $t);		//remove double-space

		//trim and put everything into fields
		$r['fields'] = trim($t);

		//add to result array
		$result[] = $r;
	}
	return $result;
}

function install_plugin($pluginName, $pluginValue) {
  //check if title existed
  if( sizeof(select_tiddler_by_title($pluginValue))==0 ) {
  	if (($temp = @file_get_contents(DIR_FS_MODULES."phreewiki/javascript/plugins/$pluginName.js")) === false)  throw new \core\classes\userException(sprintf(ERROR_READ_FILE, 		"phreewiki/javascript/plugins/$pluginName.js"));
	//if not exist, insert into db
	$t = tiddler_create($pluginValue,
	   		                  tiddler_bodyEncode($temp),
			                  	"ccTiddly",
												  date("YmdHi"),
												  "systemConfig excludeSearch excludeLists",
												  "",
												  "ccTiddly",
												  date("YmdHi"));
	  if( !insert_tiddler($t, true)== True ) throw new \core\classes\userException("cant install plugin $pluginName for phreewiki");
  }
  return true;
}
