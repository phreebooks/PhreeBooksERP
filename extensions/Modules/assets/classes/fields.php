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
//  Path: /modules/assets/classes/fields.php
//
namespace assets\classes;
class fields extends \core\classes\fields{
	public  $help_path   = '07.04.05';
	public  $title       = '';
	public  $module      = 'assets';
	public  $db_table    = TABLE_ASSETS;
	public  $type_params = 'asset_type';
	public  $extra_buttons = '';
  
	public function __construct(){
  		gen_pull_language('assets');
  		$this->type_array[] = array('id' =>'vh', 'text' => TEXT_VEHICLE);
  		$this->type_array[] = array('id' =>'bd', 'text' => TEXT_BUILDING);
  		$this->type_array[] = array('id' =>'fn', 'text' => TEXT_FURNITURE);
  		$this->type_array[] = array('id' =>'pc', 'text' => TEXT_COMPUTER);
  		$this->type_array[] = array('id' =>'te', 'text' => TEXT_EQUIP);
  		$this->type_array[] = array('id' =>'ld', 'text' => TEXT_LAND);
  		$this->type_array[] = array('id' =>'sw', 'text' => TEXT_SOFTWARE);
	 	$this->type_desc    = ASSETS_ENTRY_ASSETS_TYPE;
    	parent::__construct();    
	}
}
?>