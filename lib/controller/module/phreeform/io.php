<?php
/*
 * Handles Input/Output operations generically for all modules
 *
 * NOTICE OF LICENSE
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.TXT.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/OSL-3.0
 *
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade Bizuno to newer
 * versions in the future. If you wish to customize Bizuno for your
 * needs please refer to http://www.phreesoft.com for more information.
 *
 * @name       Bizuno ERP
 * @author     Dave Premo, PhreeSoft <support@phreesoft.com>
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-03-06
 * @filesource /controller/module/phreeform/io.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreeform/functions.php");

class phreeformIo
{
	public $moduleID = 'phreeform';

	function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
    /**
     * Manager to handle report/form management, importing, exporting and installing
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function manager(&$layout=[])
    {
        $selMods  = [['id'=>'locale','text'=>'Bizuno (Core)']];
		$selLangs = [['id'=>'en_US', 'text'=>lang('language_title')]];
		$data     = [
            'pageTitle'=> lang('import'),
            'toolbars' => ['tbImport' => ['icons' => [
                'back' => ['order'=>10, 'events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=phreeform/main/manager'"]]]]],
		    'forms'    => ['frmImport' => ['attr'=> ['type'=>'form','action'=>BIZUNO_AJAX."&p=phreeform/io/importReport"]]],
		    'divs'     => [
                'toolbar'=> ['order'=>20, 'type'=>'toolbar', 'key'=>'tbImport'],
                'heading'=> ['type'=>'html','order'=>30, 'html'=>"<h1>".$this->lang['phreeform_import']."</h1>\n"],
                'body'   => ['order'=>50, 'label'=>$this->lang['phreeform_title_edit'], 'src'=>BIZUNO_LIB."view/module/phreeform/divRptImport.php"]],
		    'fields'=>  [
                'selModule'   => ['label'=>lang('module'),  'values'=>$selMods, 'attr'=>  ['type'=>'select']],
			    'selLang'     => ['label'=>lang('language'),'values'=>$selLangs,'attr'=>  ['type'=>'select']],
			    'btnSearch'   => ['attr'=>  ['type'=>'button', 'value'=>lang('search')],'events'=>  ['onClick'=>'importSearch()']],
			    'fileUpload'  => ['label'=>lang('select_file'), 'attr'=>  ['type'=>'file']],
			    'new_name'    => ['label'=>'('.lang('optional').') '.lang('msg_entry_rename'), 'attr'=>  ['width'=>'80']],
			    'btnUpload'   => ['attr'=>  ['type'=>'button','value'=>lang('upload')],
				     'events'=>  ['onClick'=>"jq('#imp_name').val(''); jq('#frmImport').submit();"]],
			    'cbReplace'   => ['label'=>$this->lang['msg_replace_existing'],'position'=>'after','attr'=>  ['type'=>'checkbox', 'value'=>'1']],
			    'btnImport'   => ['attr'=>  ['type'=>'button','value'=>$this->lang['btn_import_selected']],
				    'events' => ['onClick'=>"jq('#imp_name').val(jq('#selReports option:selected').val()); jq('#frmImport').submit();"]],
			    'btnImportAll'=> ['attr'=>  ['type'=>'button', 'value'=>$this->lang['btn_import_all']],
			  	  'events' => ['onClick'=>"jq('#imp_name').val('all'); jq('#frmImport').submit();"]]],
            'lang'=>['phreeform_reports_available'=>$this->lang['phreeform_reports_available']]];
		$layout = array_replace_recursive($layout, viewMain(), $data);
	}
	
    /**
     * Imports a report from either the default list of from an uploaded file
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function importReport(&$layout=[])
    {
        if (!$security = validateSecurity('phreeform', 'phreeform', 2)) { return; }
		$path    = BIZUNO_LIB."".clean('selModule','text', 'post');
		$lang    = clean('selLang',  ['format'=>'text', 'default'=>'en_US'], 'post');
		$replace = clean('cbReplace','boolean', 'post');
		$imp_name= clean('imp_name', 'text', 'post');
		$new_name= clean('new_name', 'text', 'post');
		if ($imp_name == 'all') {
			$cnt = 0;
			$files = @scandir("$path/$lang/reports/");
			foreach ($files as $imp_name) { if (substr($imp_name, -4) == '.xml') {
                if (phreeformImport('', $imp_name, "$path/$lang/reports/", true, $replace)) { $cnt++; }
            } }
			$title = lang('all')." $cnt ".lang('total');
			$rID   = 0;
		} else {
            if (!$result = phreeformImport($new_name, $imp_name, "$path/$lang/reports/", true, $replace)) { return; }
			$title = $result['title'];
			$rID   = $result['rID'];
		}
		msgLog(lang('phreeform_manager').': '.lang('import').": $title ($rID)");
		msgAdd(lang('phreeform_manager').': '.lang('import').": $title", 'success');
	}
	
	/**
     * Retrieves and exports a specified report/form in XML format
     * @return type
     */
    public function export()
    {
        if (!$security = validateSecurity('phreeform', 'phreeform', 3)) { return; }
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd('The report was not exported, the proper id was not passed!'); }
        if (!$row = dbGetRow(BIZUNO_DB_PREFIX."phreeform", "id='$rID'")) { return; }
		$report = phreeFormXML2Obj($row['doc_data']);
		unset($report->id);
        // reset the security
        $report->security = 'u:-1;g:-1';
		$xmlOutput = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n<!DOCTYPE xml>\n";
		$xmlOutput.= "<PhreeformReport>\n".object_to_xml($report)."</PhreeformReport>\n";
		$output = new \bizuno\io();
		$output->download('data', $xmlOutput, str_replace([' ','/','\\','"',"'"], '', $row['title']).'.xml');
	}
}
