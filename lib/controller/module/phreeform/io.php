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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-02-20
 * @filesource /controller/module/phreeform/io.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/functions.php", 'phreeformSecurity', 'function');

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
        $selMods = [['id'=>'locale','text'=>'Bizuno (Core)']];
        $selLangs= [['id'=>'en_US', 'text'=>lang('language_title')]];
        $fields= [
            'selModule'   => ['label'=>lang('module'),  'values'=>$selMods, 'attr'=>['type'=>'select']],
            'selLang'     => ['label'=>lang('language'),'values'=>$selLangs,'attr'=>['type'=>'select']],
//          'btnSearch'   => ['attr' =>['type'=>'button', 'value'=>lang('search')],'events'=>['onClick'=>'importSearch();']],
            'fileUpload'  => ['label'=>$this->lang['import_upload_report'],'attr'=>['type'=>'file']],
            'new_name'    => ['label'=>'('.lang('optional').') '.lang('msg_entry_rename'),'attr'=>['width'=>'80']],
            'btnUpload'   => ['attr' =>['type'=>'button','value'=>lang('import')],'events'=>['onClick'=>"jq('#imp_name').val(''); jq('#frmImport').submit();"]],
            'cbReplace'   => ['label'=>$this->lang['msg_replace_existing'],'position'=>'after','attr'=>['type'=>'checkbox']],
            'btnImport'   => ['attr' =>['type'=>'button','value'=>$this->lang['btn_import_selected']],'events'=>['onClick'=>"jq('#imp_name').val(jq('#selReports option:selected').val()); jq('#frmImport').submit();"]],
            'btnImportAll'=> ['attr' =>['type'=>'button','value'=>$this->lang['btn_import_all']],'events'=>['onClick'=>"jq('#imp_name').val('all'); jq('#frmImport').submit();"]],
        ];
        $data  = [
            'title'=> lang('import'),
            'toolbars' => ['tbImport'=>['icons'=>[
                'back' => ['order'=>10,'events'=>['onClick'=>"location.href='".BIZUNO_HOME."&p=phreeform/main/manager'"]]]]],
            'divs'     => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbImport'],
                'heading'=> ['order'=>15,'type'=>'html',   'html'=>"<h1>".$this->lang['phreeform_import']."</h1>"],
                'formBOF'=> ['order'=>20,'type'=>'form',   'key'=>'frmImport'],
                'body'   => ['order'=>50,'type'=>'html',   'html'=>$this->getViewMgr($fields)],
                'formEOF'=> ['order'=>90,'type'=>'html',   'html'=>"</form>"],],
            'forms'    => ['frmImport'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=phreeform/io/importReport"]]],
            'fields'   => $fields,
            'jsReady'  => ['init'=>"ajaxForm('frmImport');"]];
        $layout = array_replace_recursive($layout, viewMain(), $data);
    }

    private function getViewMgr($fields=[])
    {
        return html5('imp_name',['attr'=>['type'=>'hidden']]).'
 <table class="ui-widget" style="border-style:none;margin-left:auto;margin-right:auto;">
  <tbody>
   <tr>
    <td>'.html5('new_name', $fields['new_name']).'</td>
    <td>'.html5('cbReplace', $fields['cbReplace']).'</td>
   </tr>
   <tr class="panel-header"><th colspan="2">&nbsp;</th></tr>
   <tr>
    <td>'.html5('fileUpload', $fields['fileUpload']).'</td>
    <td style="text-align:right;">'.html5('btnUpload', $fields['btnUpload']).'</td>
   </tr>
   <tr class="panel-header"><th colspan="2">'.$this->lang['phreeform_reports_available'].'</th></tr>
   <tr>
     <td>'.html5('selModule',$fields['selModule']).html5('selLang',$fields['selLang']).'</td>
     <td style="text-align:right;">'.html5('btnImportAll',$fields['btnImportAll']).'</td>
   </tr>
   <tr><td colspan="2">'.ReadDefReports('selReports').'</td></tr>
   <tr><td colspan="2" style="text-align:right;">'.html5('btnImport',$fields['btnImport'])."</td></tr>
  </tbody>\n</table>";
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
