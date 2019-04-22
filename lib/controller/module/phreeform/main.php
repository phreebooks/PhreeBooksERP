<?php
/*
 * Main methods for Phreeform
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
 * @version    3.x Last Update: 2019-02-15
 * @filesource /controller/module/phreeform/main.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/functions.php", 'phreeformSecurity', 'function');

class phreeformMain
{
    public  $moduleID = 'phreeform';
    private $limit    = 20; // limit the number of results for recent reports

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $this->security = getUserCache('security', 'phreeform');
    }

    /**
     * Generates the structure for the PhreeForm home page
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('phreeform', 'phreeform', 1)) { return; }
        $rID = clean('rID', ['format'=>'integer','default'=>0], 'get');
        $gID = clean('gID', 'text', 'get');
        if (!$rID && $gID) { // no node so look for group
            $rID = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'id', "group_id='$gID:rpt' AND mime_type='dir'");
        }
        $divSrch= html5('', ['options'=>['mode'=>"'remote'",'url'=>"'".BIZUNO_AJAX."&p=$this->moduleID/main/search'",'editable'=>'true','idField'=>"'id'",'textField'=>"'text'",'width'=>250,'panelWidth'=>400,
            'onClick'=>"function (row) { jq('#accDocs').accordion('select', 1); jq('#divDetail').panel('refresh', bizunoAjax+'&p=$this->moduleID/main/edit&rID='+row.id); }"],'attr'=>['type'=>'select']]);
        $data   = ['title'=> lang('reports'),
            'divs'     => ['title'=> ['order'=>10,'type'=>'html',     'html'=>"<h1>{$this->lang['title']}</h1>"],
                'toolbar'  => ['order'=>10,'type'=>'toolbar','key' =>'tbPhreeForm'],
                'body'    => ['order'=>50,'type'=>'divs','classes'=>['areaView'],'attr'=>['id'=>'docBody'],'divs'=>[
                    'accDoc' => ['order'=>20,'type'=>'divs','styles'=>['height'=>'100%'],'classes'=>['block33'],'attr'=>['id'=>'divAcc'],'divs'=>[
                        'accMgr'=> ['order'=>20,'type'=>'accordion','key' =>'accDocs']]],
                    'myDocs' => ['order'=>50,'type'=>'divs','classes'=>['block33'],'attr'=>['id'=>'myDocs'],'divs'=>[
                        'search'=> ['order'=>10,'type'=>'panel','key'=>'docSearch'],
                        'panel' => ['order'=>30,'type'=>'panel','key'=>'docBookMk']]],
                    'newDocs'=> ['order'=>70,'type'=>'divs','classes'=>['block33'],'attr'=>['id'=>'newDocs'],'divs'=>[
                        'panel' => ['order'=>30,'type'=>'panel','key'=>'docRecent']]]]]],
            'toolbars' => ['tbPhreeForm'=>['icons'=>[
                'mimeRpt' => ['order'=>30,'icon'=>'mimeTxt','hidden'=>($this->security>1)?false:true,'events'=>['onClick'=>"hrefClick('$this->moduleID/design/edit&type=rpt', 0);"],
                    'label'=>$this->lang['new_report']],
                'mimeFrm' => ['order'=>40,'icon'=>'mimeDoc','hidden'=>($this->security>1)?false:true,'events'=>['onClick'=>"hrefClick('$this->moduleID/design/edit&type=frm', 0);"],
                    'label'=>$this->lang['new_form']],
                'import'  => ['order'=>90,'hidden'=>($this->security>1)?false:true, 'events'=>['onClick'=>"hrefClick('phreeform/io/manager');"]]]]],
            'accordion'=> ['accDocs'=>['styles'=>['height'=>'100%'],'divs'=>[ // 'attr'=>['halign'=>'left'], crashes older versions of Chrome and Safari
                'divTree'  => ['order'=>10,'label'=>$this->lang['my_reports'],'type'=>'divs','styles'=>['overflow'=>'auto','padding'=>'10px'], // 'attr'=>['titleDirection'=>'up'],
                    'divs'=>[
                        'toolbar'=> ['order'=>10,'type'=>'fields','keys'=>['expand','collapse']],
                        'tree'   => ['order'=>50,'type'=>'tree',  'key' =>'treePhreeform']]],
                'divDetail'=> ['order'=>30,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]], // 'attr'=>['titleDirection'=>'up'],
            'panels'   => [
                'docSearch' => ['styles'=>['text-align'=>'center'],'options'=>['title'=>"'".jsLang('search')."'"],'html'=>$divSrch],
                'docBookMk' => ['options'=>['title'=>"'".$this->lang['my_favorites']."'",  'collapsible'=>'true','href'=>"'".BIZUNO_AJAX."&p=$this->moduleID/main/favorites'"],'html'=>'&nbsp;'],
                'docRecent' => ['options'=>['title'=>"'".$this->lang['recent_reports']."'",'collapsible'=>'true','href'=>"'".BIZUNO_AJAX."&p=$this->moduleID/main/recent'"],   'html'=>'&nbsp;']],
            'tree'     => ['treePhreeform'=>['classes'=>['easyui-tree'],'attr'=>['url'=>BIZUNO_AJAX."&p=phreeform/main/managerTree"],'events'=>[
                'onClick'  => "function(node) { if (typeof node.id != 'undefined') {
    if (jq('#treePhreeform').tree('isLeaf', node.target)) { jq('#accDocs').accordion('select', 1); jq('#divDetail').panel('refresh', bizunoAjax+'&p=$this->moduleID/main/edit&rID='+node.id); }
    else { jq('#treePhreeform').tree('toggle', node.target); } } }"]]],
            'fields'   => [
                'expand'  => ['events'=>['onClick'=>"jq('#treePhreeform').tree('expandAll');"],  'attr'=>['type'=>'button','value'=>lang('expand_all')]],
                'collapse'=> ['events'=>['onClick'=>"jq('#treePhreeform').tree('collapseAll');"],'attr'=>['type'=>'button','value'=>lang('collapse_all')]]]];
        if ($rID) {
            $data['tree']['treePhreeform']['events']['onLoadSuccess'] = "function() { var node=jq('#treePhreeform').tree('find',$rID); jq('#treePhreeform').tree('expandTo',node.target);
jq('#treePhreeform').tree('expand', node.target); }";
        }
        $layout = array_replace_recursive($layout, viewMain(), $data);
        return;
    }

    /**
     * Gets the available forms/reports from a JSON call in the database and returns to populate the treegrid
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function managerTree(&$layout=[])
    {
        $result = dbGetMulti(BIZUNO_DB_PREFIX."phreeform", '', 'mime_type, title');
        // filter by security
        $output = [];
        foreach ($result as $row) {
            if ($row['security']=='u:0;g:0') { // restore orphaned reports
                dbWrite(BIZUNO_DB_PREFIX."phreeform", ['security'=>'u:-1;g:-1','last_update'=>date('Y-m-d')], 'update', "id={$row['id']}");
            }
            if (phreeformSecurity($row['security'])) { $output[] = $row; }
        }
        msgDebug("\n phreeform number of rows returned: ".sizeof($output));
        $data = ['id'=>'-1','text'=>lang('home'),'children'=>viewTree($output, 0)];
        trimTree($data);
        msgDebug("\nSending data = ".print_r($data, true));
        $layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>'['.json_encode($data).']']);
    }

    /**
     * Builds the right div for details of a requested report/form. Returns div html
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function edit(&$layout=[])
    {
        if (!$security = validateSecurity('phreeform', 'phreeform', 1)) { return; }
        $rID    = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('bad_record_id')); }
        $report = dbGetRow(BIZUNO_DB_PREFIX."phreeform", "id='$rID'");
        if ($report['mime_type'] == 'dir') { return; } // folder, just return to do nothing
        $details= phreeFormXML2Obj($report['doc_data']);
        $report['description'] = !empty($details->description) ? $details->description : '';
        $data   = ['type'=>'divHTML',
            'divs' => ['divDetail'=>['order'=>50,'type'=>'divs','divs'=>[
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbReport'],
                'body'   => ['order'=>50,'type'=>'html','html'=>$this->getViewReport($report)]]]],
            'toolbars'  => ['tbReport'=>['hideLabels'=>true,'icons'=>[
                'open'  => ['order'=>10,'events'=>['onClick'=>"winOpen('phreeformOpen', 'phreeform/render/open&rID=$rID');"]],
                'edit'  => ['order'=>20,'hidden'=>($security>1)?false:true,
                    'events'=>['onClick'=>"window.location.href='".BIZUNO_HOME."&p=phreeform/design/edit&rID='+$rID;"]],
                'rename'=> ['order'=>30,'hidden'=>($security>2)?false:true,
                    'events'=>['onClick'=>"var title=prompt('".lang('msg_entry_rename')."'); if (title !== null) { jsonAction('phreeform/main/rename', $rID, title); }"]],
                'copy'  => ['order'=>40,'hidden'=>($security>1)?false:true,
                    'events'=>['onClick'=>"var title=prompt('".lang('msg_entry_rename')."'); if (title !== null) { jsonAction('phreeform/main/copy', $rID, title); }"]],
                'trash' => ['order'=>50,'hidden'=>($security>3)?false:true,
                    'events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('phreeform/main/delete', $rID, '');"]],
                'export'=> ['order'=>60,'hidden'=>($security>2)?false:true,
                    'events'=>['onClick'=>"window.location.href='".BIZUNO_AJAX."&p=phreeform/io/export&rID='+$rID;"]]]]],
            'report' => $report];
        $layout = array_replace_recursive($layout, $data);
    }

    private function getViewReport($report)
    {
        return "<h1>".$report['title']."</h1>
          <fieldset>".$report['description']."</fieldset>
          <div>
            ".lang('id').": {$report['id']}<br />
            ".lang('type').": {$report['mime_type']}<br />
            ".lang('create_date').": ".viewFormat($report['create_date'], 'date')."<br />
            ".lang('last_update').': '.viewFormat($report['last_update'], 'date')."</div>\n";
    }

    /**
     * Generates the structure and executes the report/form renaming operation
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function rename(&$layout=[])
    {
        if (!$security = validateSecurity('phreeform', 'phreeform', 3)) { return; }
        $rID    = clean('rID',  'integer', 'get');
        $title  = clean('data', 'text', 'get');
        if (empty($rID) || empty($title)) { return msgAdd($this->lang['err_rename_fail']); }
        $strXML = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'doc_data', "id=$rID");
        $report = parseXMLstring($strXML);
        $report->title = $title;
        $docData = '<PhreeformReport>'.object_to_xml($report).'</PhreeformReport>';
        $sql_data = ['title'=>$title, 'doc_data'=>$docData, 'last_update'=>date('Y-m-d')];
        dbWrite(BIZUNO_DB_PREFIX."phreeform", $sql_data, 'update', "id='$rID'");
        msgLog(lang('phreeform_manager').'-'.lang('rename')." $title ($rID)");
        $data  = ['content'=>['action'=>'eval','actionData'=>"jq('#treePhreeform').tree('reload'); jq('#docBookMk').panel('refresh'); jq('#docRecent').panel('refresh'); jq('#divDetail').panel('refresh', bizunoAjax+'&p=$this->moduleID/main/edit&rID=$rID');"]];
        $layout= array_replace_recursive($layout, $data);
    }

    /**
     * Generates the structure take a report and create a copy, add to the database
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function copy(&$layout=[])
    {
        if (!$security = validateSecurity('phreeform', 'phreeform', 2)) { return; }
        $rID   = clean('rID',  'integer', 'get');
        $title = clean('data', 'text', 'get');
        if (empty($rID) || empty($title)) { return msgAdd($this->lang['err_copy_fail']); }
        $row = dbGetRow(BIZUNO_DB_PREFIX."phreeform", "id=$rID");
        unset($row['id']);
        $row['title'] = $title;
        $row['create_date'] = date('Y-m-d');
        $row['last_update'] = date('Y-m-d');
        $report = parseXMLstring($row['doc_data']);
        $report->title = $title;
        $row['doc_data'] = '<PhreeformReport>'.object_to_xml($report).'</PhreeformReport>';
        $newID = dbWrite(BIZUNO_DB_PREFIX."phreeform", $row);
        if ($newID) {
            msgLog(lang('phreeform_manager').'-'.lang('copy')." - $title ($rID=>$newID)");
            $_GET['rID'] = $newID;
        }
        $data  = ['content'=>['action'=>'eval','actionData'=>"jq('#treePhreeform').tree('reload'); jq('#docBookMk').panel('refresh'); jq('#docRecent').panel('refresh'); jq('#divDetail').panel('refresh', bizunoAjax+'&p=$this->moduleID/main/edit&rID=$newID');"]];
        $layout= array_replace_recursive($layout, $data);
    }

    /**
     * Creates the structure to to accept a database record id and deletes a report
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!$security = validateSecurity($this->moduleID, $this->moduleID, 4)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd('The report was not deleted, the proper id was not passed!'); }
        $title = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'title', "id='$rID'");
        msgLog(lang('phreeform_manager').'-'.lang('delete')." - $title ($rID)");
        $data  = ['content'=>['action'=>'eval','actionData'=>"jq('#accDocs').accordion('select', 0); jq('#treePhreeform').tree('reload'); jq('#docBookMk').panel('refresh'); jq('#docRecent').panel('refresh');"],
            'dbAction' => [BIZUNO_DB_PREFIX."phreeform" => "DELETE FROM ".BIZUNO_DB_PREFIX."phreeform WHERE id=$rID"]];
        $layout= array_replace_recursive($layout, $data);
    }

        /**
     *
     * @param type $docs
     * @return type
     */
    public function favorites(&$layout=[])
    {
        if (!$security = validateSecurity($this->moduleID, $this->moduleID, 1)) { return; }
        $myID  = getUserCache('profile', 'admin_id', false, 0);
        $dbData= dbGetMulti(BIZUNO_DB_PREFIX."phreeform", "mime_type<>'dir' AND bookmarks LIKE ('%:$myID:%')", 'title');
        foreach ($dbData as $key => $doc) { if (!validateUsersRoles($doc['security'])) { unset($dbData[$key]); } }
        $output= sortOrder($dbData, 'title');
        $html  = html5('', ['classes'=>['easyui-datalist'],'options'=>['lines'=>'true','onClickRow'=>"function (idx, row) { if (!row.value) { return; } jq('#accDocs').accordion('select', 1); jq('#divDetail').panel('refresh', bizunoAjax+'&p=$this->moduleID/main/edit&rID='+row.value); }"],'attr'=>['type'=>'ul']]);
        if (sizeof($output) > 0) {
            foreach ($output as $doc) {
                $html .= html5('', ['options'=>['value'=>$doc['id']],'attr'=>['type'=>'li']]);
                $html .= html5('', ['icon'=>viewMimeIcon($doc['mime_type']), 'size'=>'small', 'label'=>$doc['title']]).' '.$doc['title']."</li>";
            }
        } else { $html .= html5('', ['options'=>['value'=>0],'attr'=>['type'=>'li']]).lang('msg_no_documents')."</li>"; }
        $html .= "</ul>";
        $data = ['type'=>'divHTML','divs'=>['body'=>['order'=>50,'type'=>'html','html'=>$html]]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     *
     * @param type $docs
     * @param type $limit
     * @return type
     */
    public function recent(&$layout=[])
    {
        if (!$security = validateSecurity($this->moduleID, $this->moduleID, 1)) { return; }
        $output = $temp = [];
        $dbData = dbGetMulti(BIZUNO_DB_PREFIX."$this->moduleID", "mime_type<>'dir'");
        foreach ($dbData as $key => $value) { $temp[$key] = $value['last_update']; }
        array_multisort($temp, SORT_DESC, $dbData);
        $cnt = 0;
        foreach ($dbData as $doc) {
            if (validateUsersRoles($doc['security'])) { $output[] = $doc; $cnt++; }
            if ($cnt >= $this->limit) { break; }
        }
        $html  = html5('', ['classes'=>['easyui-datalist'],'options'=>['lines'=>'true','onClickRow'=>"function (idx, row) { if (!row.value) { return; } jq('#accDocs').accordion('select', 1); jq('#divDetail').panel('refresh', bizunoAjax+'&p=$this->moduleID/main/edit&rID='+row.value); }"],'attr'=>['type'=>'ul']]);
        if (sizeof($output) > 0) {
            foreach ($output as $doc) {
                $html .= html5('', ['options'=>['value'=>$doc['id']],'attr'=>['type'=>'li']]);
                $html .= html5('', ['icon'=>viewMimeIcon($doc['mime_type']), 'size'=>'small', 'label'=>$doc['title']]).' '.viewDate($doc['last_update']).'-'.$doc['title']."</li>";
            }
        } else { $html .= html5('', ['options'=>['value'=>0],'attr'=>['type'=>'li']]).lang('msg_no_documents')."</li>"; }
        $html .= "</ul>";
        $data = ['type'=>'divHTML','divs'=>['body'=>['order'=>50,'type'=>'html','html'=>$html]]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     *
     * @param type $layout
     */
    public function search(&$layout=[])
    {
        $search = getSearch(['search','q']);
        if (empty($search)) {
            $output[] = ['id'=>'','text'=>lang('no_results')];
        } else {
            $dbData = dbGetMulti(BIZUNO_DB_PREFIX."$this->moduleID", "mime_type<>'dir' AND title LIKE ('%$search%')", 'title');
            foreach ($dbData as $row) {
                if (validateUsersRoles($row['security'])) { $output[] = ['id'=>$row['id'],'text'=>$row['title']]; }
            }
        }
         $layout = array_replace_recursive($layout, ['type'=>'raw','content'=>json_encode($output)]);
    }
}
