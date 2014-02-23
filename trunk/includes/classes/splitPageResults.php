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
//  Path: /includes/classes/splitPageResults.php
//
namespace core\classes;
class splitPageResults {
  	public	$current_page_number	= 1;
	public	$jump_page_displayed 	= false;
	public	$max_rows_per_page 		= MAX_DISPLAY_SEARCH_RESULTS;
	public	$page_prefix         	= '';
	public	$page_start				= 0;
	public	$total_num_rows			= 0;
	public  $total_num_pages		= 1;
  	
	function __construct($current_page_number, $query_num_rows) {
    	global $db;
    	if($query_num_rows == '') {
    		$temp = $db->Execute('SELECT FOUND_ROWS() AS found_rows;');
    		$query_num_rows = $temp->fields['found_rows'];
    	}
    	$this->total_num_rows		= $query_num_rows;
      	$this->current_page_number 	= max(1, $current_page_number);
		$this->total_num_pages		= ceil($this->total_num_rows / $this->max_rows_per_page);
		if ($this->total_num_pages == 0) $this->total_num_pages = 1;
      	if ($this->total_num_pages < $this->current_page_number) $this->current_page_number = max(1, $this->total_num_pages);
    }
    
    function display_links($page_name = 'list') {
	    $pages_array = array();
	    for ($i = 1; $i <= $this->total_num_pages; $i++) $pages_array[] = array('id' => $i, 'text' => $i);
	    if ($this->total_num_pages > 1) {
	        $display_links = '';
	        if ($this->current_page_number > 1) {
			  	$display_links .= html_icon('actions/media-skip-backward.png', TEXT_GO_FIRST, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_first', 'SSL') . '\'" style="cursor:pointer;"');
			  	$display_links .= html_icon('phreebooks/media-playback-previous.png', TEXT_GO_PREVIOUS, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_previous', 'SSL') . '\'" style="cursor:pointer;"');
	        } else {
			  	$display_links .= html_icon('actions/media-skip-backward.png', '', 'small', '');
			  	$display_links .= html_icon('phreebooks/media-playback-previous.png', '', 'small', '');
	        }
	        if (!$this->jump_page_displayed) { // only diplay pull down once (the rest are not read by browser)
			  	$display_links .= sprintf(TEXT_RESULT_PAGE, html_pull_down_menu($page_name, $pages_array, $this->current_page_number, 'onchange="jumpToPage(\'' . gen_get_all_get_params(array('list', 'action')) . '&action=go_page\')"'), $this->total_num_pages);
			  	$this->jump_page_displayed = true;
			} else {
				$display_links .= sprintf(TEXT_RESULT_PAGE, $this->current_page_number, $this->total_num_pages);
			}
	        if (($this->current_page_number < $this->total_num_pages) && ($this->total_num_pages != 1)) {
				$display_links .= html_icon('actions/media-playback-start.png', TEXT_GO_NEXT, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_next', 'SSL') . '\'" style="cursor:pointer;"');
				$display_links .= html_icon('actions/media-skip-forward.png', TEXT_GO_LAST, 'small', 'onclick="location.href = \'' . html_href_link(FILENAME_DEFAULT, gen_get_all_get_params(array('action')) . '&action=go_last', 'SSL') . '\'" style="cursor:pointer;"');
	        } else {
				$display_links .= html_icon('actions/media-playback-start.png', '', 'small', '');
				$display_links .= html_icon('actions/media-skip-forward.png', '', 'small', '');
	        }
	    } else {
	        $display_links = sprintf(TEXT_RESULT_PAGE, $this->total_num_pages, $this->total_num_pages);
	    }
	    return $display_links;
    }
    
    function display_ajax($page_name = 'list', $id = '') {
      	$display_links   = '';
      	$pages_array     = array();
      	for ($i = 1; $i <= $this->total_num_pages; $i++) $pages_array[] = array('id' => $i, 'text' => $i);
      	if ($this->total_num_pages > 1) {
        	if ($this->current_page_number > 1) {
		  		$display_links .= html_icon('actions/media-skip-backward.png', TEXT_GO_FIRST, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_first\')" style="cursor:pointer;"');
		  		$display_links .= html_icon('phreebooks/media-playback-previous.png', TEXT_GO_PREVIOUS, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_previous\')" style="cursor:pointer;"');
        	} else {
		  		$display_links .= html_icon('actions/media-skip-backward.png', '', 'small', '');
				$display_links .= html_icon('phreebooks/media-playback-previous.png', '', 'small', '');
        	}
        	if (!$this->jump_page_displayed) { // only diplay pull down once (the rest are not read by browser)
		  		$display_links .= sprintf(TEXT_RESULT_PAGE, html_pull_down_menu($page_name, $pages_array, $this->current_page_number, 'onchange="tabPage(\'' . $id . '\', \'go_page\')"'), $this->total_num_pages);
		  		$this->jump_page_displayed = true;
			} else {
		  		$display_links .= sprintf(TEXT_RESULT_PAGE, $this->current_page_number, $this->total_num_pages);
			}
        	if (($this->current_page_number < $this->total_num_pages) && ($this->total_num_pages != 1)) {
		  		$display_links .= html_icon('actions/media-playback-start.png', TEXT_GO_NEXT, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_next\')" style="cursor:pointer;"');
		  		$display_links .= html_icon('actions/media-skip-forward.png', TEXT_GO_LAST, 'small', 'onclick="tabPage(\'' . $id . '\', \'go_last\')" style="cursor:pointer;"');
        	} else {
		  	$display_links .= html_icon('actions/media-playback-start.png', '', 'small', '');
		  	$display_links .= html_icon('actions/media-skip-forward.png', '', 'small', '');
        	}
		} else {
        	$display_links .= sprintf(TEXT_RESULT_PAGE, $this->total_num_pages, $this->total_num_pages);
			$display_links .= html_hidden_field($page_name, '1');
      	}
      	return $display_links;
    }

    function display_count($text_output){
    	if ($text_output == '' || !is_string($text_output)) $text_output = TEXT_DISPLAY_NUMBER . TEXT_ITEMS;
      	$to_num = ($this->max_rows_per_page * $this->current_page_number);
      	if ($to_num > $this->total_num_rows) $to_num = $this->total_num_rows;
      	$from_num = ($this->max_rows_per_page * ($this->current_page_number - 1));
      	if ($to_num == 0) {
        	$from_num = 0;
      	} else {
        	$from_num++;
      	}
      	return sprintf($text_output, $from_num, $to_num, $this->total_num_rows);
    }

}
