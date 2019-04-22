<?php
/*
 * Methods to render PhreeForm outputs, supports PDF, HTML, CSV, and XML
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
 * @version    3.x Last Update: 2019-04-10
 * @filesource /controller/module/phreeform/render.php
 */

namespace bizuno;

bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/functions.php", 'phreeformImport', 'function');

class phreeformRender
{
    public $moduleID = 'phreeform';

    function __construct()
    {
        global $critChoices; // @todo this needs to be fixed, used in phreeform/functions.php (probably should be put in here!)
        $this->lang = getLang($this->moduleID);
        $critChoices = $this->critChoices = [
            0  => '2:all:range:equal',
            1  => '0:yes:no',
            2  => '0:all:yes:no',
            3  => '0:all:active:inactive',
            4  => '0:all:printed:unprinted',
            6  => '1:equal',
            7  => '2:range',
            8  => '1:not_equal',
            9  => '1:in_list',
            10 => '1:less_than',
            11 => '1:greater_than'];
    }

    /**
     * Creates the structure for the open report pop up. Either a group or report will be created depending on the request
     * @global object $report
     * @param array $layout - Structure coming in
     * @return array - Modified $layout
     */
    public function open(&$layout=[])
    {
        global $report;
        $rID    = clean('rID',  'integer','get');
        $group  = clean('group','text',   'get');
        $reports= $group ? $this->phreeformGroupReports($group) : [];
        if (sizeof($reports) == 1) { $rID = $reports[0]['id']; } // for a single report in group, pre-select it
        if ($rID) {
            $dbData  = dbGetRow(BIZUNO_DB_PREFIX."phreeform", "id='$rID'");
            $report  = phreeFormXML2Obj($dbData['doc_data']);
            if (!is_object($report)) { return msgAdd("Mission Control ERROR, I cannot find the report referenced by id=$rID, this is bad!"); }
            msgDebug("\n Read date default before= ".$report->datedefault);
        } elseif ($group) {
            $report = new \stdClass();
            $report->title = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'title', "mime_type='dir' AND group_id='$group'");
            $report->title = lang($report->title); // translate if possible
            $report->mime_type = 'dir';
        } else { return msgAdd('PhreeFormOpen called without a group or report ID.'); }
        $hidden = ((isset($report->reporttype) && $report->reporttype=='frm') || $group) ? true : false;
        $report->xfilterlist = new \stdClass();
        $report->xfilterlist->fieldname= clean('xfld','text','get');
        $report->xfilterlist->default  = clean('xcr', 'text','get');
        $report->xfilterlist->min      = clean('xmin','text','get');
        $report->xfilterlist->max      = clean('xmax','text','get');
        $extras = $rID ? "&rID=$rID" : ''; // append extra fields
        if (clean('date','text','get'))  { $extras .= '&date='.clean('date','text','get'); }
        if (clean('xfld','text','get'))  { $extras .= '&xfld='.clean('xfld','text','get'); }
        if (clean('xcr', 'text','get'))  { $extras .= '&xcr=' .clean('xcr', 'text','get'); }
        if (clean('xmin','text','get'))  { $extras .= '&xmin='.clean('xmin','text','get'); }
        if (clean('xmax','text','get'))  { $extras .= '&xmax='.clean('xmax','text','get'); }
        if (clean('mID','integer','get')){ $extras .= '&mID=' .clean('mID','integer','get'); }
        $emailData = $this->emailProps($report);
        $js = $this->getViewRenderJS();
        $data = ['type'=>'page','title'=> $report->title,
            'toolbars'=> ['tbOpen'=>['icons'=>[
                'close'   => ['order'=>10, 'events'=>['onClick'=>"self.close();"]],
                'mimePdf' => ['order'=>30,'label'=>lang('pdf'),                   'events'=>['onClick'=>"jq('#fmt').val('pdf'); jq('#frmPhreeform').submit();"]],
                'mimeHtml'=> ['order'=>40,'label'=>lang('html'),'hidden'=>$hidden,'events'=>['onClick'=>"jq('#fmt').val('html');jq('#frmPhreeform').submit();"]],
                'mimeXls' => ['order'=>50,'label'=>lang('csv'), 'hidden'=>$hidden,'events'=>['onClick'=>"jq('#fmt').val('csv'); jq('#frmPhreeform').submit();"]],
                'mimeXML' => ['order'=>60,'label'=>lang('xml'), 'hidden'=>$hidden,'events'=>['onClick'=>"jq('#fmt').val('xml'); jq('#frmPhreeform').submit();"]]]]],
            'forms'   => ['frmPhreeform'=>['classes'=>['fileDownloadForm'],'attr'=>['type'=>'form','method'=>'post','action'=>BIZUNO_AJAX."&p=phreeform/render/render".$extras]]],
            'fields'  => [
                'id'        => ['attr'=>['type'=>'hidden','value'=>$rID]],
                'msgFrom'   => ['break'=>true,'label'=>lang('from'),    'lblStyle'=>['min-width'=>'60px'],'options'=>['width'=>500,'editable'=>true],'values'=>$emailData['valsFrom'],'attr'=>['type'=>'select','value'=>$emailData['defFrom']]],
                'msgTo'     => ['break'=>true,'label'=>lang('to'),      'lblStyle'=>['min-width'=>'60px'],'options'=>['width'=>500,'editable'=>true],'values'=>$emailData['valsTo'],  'attr'=>['type'=>'select','value'=>$emailData['defTo']]],
                'msgCC1'    => ['break'=>true,'label'=>lang('email_cc'),'lblStyle'=>['min-width'=>'60px'],'options'=>['width'=>500,'editable'=>true],'values'=>$emailData['valsTo'],  'attr'=>['type'=>'select','value'=>$emailData['defCC']]],
                'msgCC2'    => ['break'=>true,'label'=>lang('email_cc'),'lblStyle'=>['min-width'=>'60px'],'options'=>['width'=>500,'editable'=>true],'values'=>$emailData['valsTo'],  'attr'=>['type'=>'select']],
                'msgSubject'=> ['break'=>true,'label'=>lang('email_subject'),'lblStyle'=>['min-width'=>'60px'],'options'=>['width'=>600],'attr'=>['size'=>40,'value'=>$emailData['msgSubject']]],
                'msgBody'   => ['break'=>true,'label'=>lang('email_body'),'lblStyle'=>['min-width'=>'60px'],'attr' =>['type'=>'textarea','value'=>$emailData['msgBody'],'cols'=>'80','rows'=>'10']],
                'reports'   => $reports],
            'divs'    => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key'=>'tbOpen'],
                'heading'=> ['order'=>15,'type'=>'html',   'html'=>"<h1>$report->title</h1>\n",'<div id="winPhreeForm">'],
                'formBOF'=> ['order'=>20,'type'=>'form',   'key'=>'frmPhreeform'],
                'formEOF'=> ['order'=>90,'type'=>'html',   'html'=>"</form>"]],
            'jsHead'  => [
                'init'      => $js['jsHead'],
                'jqDownload'=> "jq.cachedScript('".BIZUNO_URL."../apps/jquery-file-download.js');"],
            'jsReady' => ['init'=>$js['jsReady']]];
        if ($rID) {
            $data['report']     = $report;
            $data['critChoices']= $this->critChoices;
            $data['dateChoices']= $this->filterDates($report->datelist);
        }
        $layout = array_replace_recursive($layout, $data);
        $layout['divs']['body'] = ['order'=>50,'type'=>'html','html'=>$this->getViewRender($layout)];
        msgDebug("\nsending data array = ".print_r($layout, true));
    }

    private function getViewRender($viewData)
    {
        $output  = html5('fmt',['attr'=>['type'=>'hidden','value'=>'pdf']])."\n";
        if (isset($viewData['report']) && $viewData['report']->datelist == 'a') {
            $output .= html5('critDateSel',['attr'=>['type'=>'hidden','value'=>'a']]);
            $output .= html5('critDateMin',['attr'=>['type'=>'hidden','value'=>'']]);
            $output .= html5('critDateMax',['attr'=>['type'=>'hidden','value'=>'']]);
        }
        $output .= '<p style="text-align:center">'."\n";
        $output .= html5('delivery', ['label'=>lang('browser'), 'attr'=>['type'=>'radio','value'=>'I', 'checked'=>'checked'],'events'=>['onClick'=>"jq('#rpt_email').hide('slow')"]])."\n";
        $output .= html5('delivery', ['label'=>lang('download'),'attr'=>['type'=>'radio','value'=>'D'],'events'=>['onClick'=>"jq('#rpt_email').hide('slow')"]])."\n";
        $output .= html5('delivery', ['label'=>lang('email'),   'attr'=>['type'=>'radio','value'=>'S'],'events'=>['onClick'=>"jq('#rpt_email').show('slow')"]])."\n";
        $output .= "</p>\n";
        $output .= '<div id="rpt_email" style="display:none">'."\n";
        $output .= html5('msgFrom',   $viewData['fields']['msgFrom']);
        $output .= html5('msgTo',     $viewData['fields']['msgTo']);
        $output .= html5('msgCC1',    $viewData['fields']['msgCC1']);
        $output .= html5('msgCC2',    $viewData['fields']['msgCC2']);
        $output .= html5('msgSubject',$viewData['fields']['msgSubject']);
        // convert the body text to \n form <br />
        if (isset($viewData['fields']['msgBody']['attr']['value'])) {
            $viewData['fields']['msgBody']['attr']['value'] = str_replace(['<br />','<br>'], "\n", $viewData['fields']['msgBody']['attr']['value']);
        }
        $output .= html5('msgBody', $viewData['fields']['msgBody']);
        $output .= '</div>'."\n";

        if (isset($viewData['report'])) {
            $output .= '<table style="border-collapse:collapse;margin-left:auto;margin-right:auto;">'."\n";
            $output .= '  <thead class="panel-header">'."\n";
            $output .= '    <tr><th colspan="4">'.lang('criteria').'</th></tr>'."\n";
            $output .= "  </thead>\n";
            $output .= "  <tbody>\n";
            $output .= '    <tr class="panel-header"><th colspan="2">&nbsp;</th><th>'.lang('from')."</th><th>".lang('to')."</th></tr>\n";
            if ($viewData['report']->datelist != '') {
                if ($viewData['report']->datelist == 'z') { // special case for period pull-down
                    $output .= '<tr><td colspan="4">'.html5('period', ['label'=>lang('period'),'values'=>dbPeriodDropDown(),'attr'=>['type'=>'select','value'=>getModuleCache('phreebooks', 'fy', 'period')]])."</td></tr>\n";
                } elseif ($viewData['report']->datelist != 'a') {
                    $dateArray = explode(':', $viewData['report']->datedefault);
                    $output .= "<tr>\n";
                    $output .= " <td>".lang('date')."</td>\n";
                    $output .= " <td>".html5('critDateSel', ['values'=>$viewData['dateChoices'],'attr'=>['type'=>'select','value'=>isset($dateArray[0])?$dateArray[0]:'']])."</td>\n";
                    $output .= " <td>".html5('critDateMin', ['attr'=>['type'=>'date','value'=>isset($dateArray[1])?$dateArray[1]:'']])."</td>\n";
                    $output .= " <td>".html5('critDateMax', ['attr'=>['type'=>'date','value'=>isset($dateArray[2])?$dateArray[2]:'']])."</td>\n";
                    $output .= "</tr>\n";
                }
            }
            if ($viewData['report']->reporttype == 'rpt' && !empty($viewData['report']->grouplist)) {
                $i = 1;
                $group_list   = [['id'=>'0', 'text'=>lang('none')]];
                $group_default= '';
                if (is_array($viewData['report']->grouplist)) { foreach ($viewData['report']->grouplist as $group) {
                    if (!empty($group->default))   { $group_default = $i; }
                    if (!empty($group->page_break)){ $group_break   = true; }
                    $group_list[] = ['id'=>$i, 'text'=>$group->title];
                    $i++;
                } }
                $output .= "    <tr>\n";
                $output .= "      <td>".$this->lang['phreeform_groups']."</td>\n";
                $output .= '      <td colspan="3">'.html5('critGrpSel', ['values'=>$group_list, 'attr'=>['type'=>'select', 'value'=>$group_default]]).' ';
                $output .= html5('critGrpChk', ['label'=>$this->lang['phreeform_page_break'], 'attr'=>['type'=>'checkbox']])."</td>\n";
                $output .= "    </tr>\n";
            }
            if (isset($viewData['report']->sortlist) && sizeof($viewData['report']->sortlist) > 0) {
                $i = 1;
                $sort_list   = [['id'=>'0', 'text'=>lang('none')]];
                $sort_default= '';
                if (is_array($viewData['report']->sortlist)) { foreach ($viewData['report']->sortlist as $sortitem) {
                    if (!empty($sortitem->default)) { $sort_default = $i; }
                    $sort_list[] = ['id' => $i, 'text' => !empty($sortitem->title) ? $sortitem->title : ''];
                    $i++;
                } }
                $output .= "    <tr>\n";
                $output .= "      <td>".$this->lang['phreeform_sorts']."</td>\n";
                $output .= '      <td colspan="3">'.html5('critSortSel', ['values'=>$sort_list, 'attr'=>['type'=>'select', 'value'=>$sort_default]]);
                $output .= "    </tr>\n";
                }
            if ($viewData['report']->reporttype == 'rpt') {
                $props = ['attr'=>['type'=>'checkbox']];
                if (!empty($viewData['report']->truncate)) { $props['attr']['checked'] = true; }
                $output .= "<tr><td>".$this->lang['truncate_fit'].'</td><td colspan="3">'.html5('critTruncate', $props)."</tr>\n";
                $output .= '<tr><td>'.lang('currency').'</td><td colspan="3">'.html5('iso', ['attr'=>['type'=>'selCurrency']])."</tr>\n";
            }
            if (isset($viewData['report']->filterlist) && $viewData['report']->filterlist <> '') {
                foreach ($viewData['report']->filterlist as $key => $LineItem) { // retrieve the dropdown based on the params field (dropdown type)
                    $CritBlocks = explode(':', $viewData['critChoices'][$LineItem->type]);
                    $numInputs = array_shift($CritBlocks); // will be 0, 1 or 2
                    $choices = [];
                    foreach ($CritBlocks as $value) { $choices[] = ['id'=>$value, 'text'=>lang($value)]; }
                    if (!empty($LineItem->visible)) {
                        $field_0 = html5('critFltrSel'.$key, ['values'=>$choices, 'attr'=>['type'=>'select','value'=>isset($LineItem->default)?$LineItem->default:'']]);
                        $field_1 = html5('fromvalue'  .$key, ['attr'=>['value'=>isset($LineItem->min)?$LineItem->min:'', 'size'=>"21", 'maxlength'=>"20"]]);
                        $field_2 = html5('tovalue'    .$key, ['attr'=>['value'=>isset($LineItem->max)?$LineItem->max:'', 'size'=>"21", 'maxlength'=>"20"]]);
                        $output .= "    <tr".($LineItem->visible ? '' : ' style="display:none"').">\n";
                        $output .= "      <td>".(empty($LineItem->title) ? '' : $LineItem->title)."</td>\n";
                        $output .= "      <td>".$field_0."</td>\n";
                        $output .= "      <td>".($numInputs >= 1 ? $field_1 : '&nbsp;')."</td>\n";
                        $output .= "      <td>".($numInputs == 2 ? $field_2 : '&nbsp;')."</td>\n";
                        $output .= "    </tr>\n";
                    }
                }
            }
            $output .= '  </tbody>'."\n";
            $output .= '</table>'."\n";
        }

        if (sizeof($viewData['fields']['reports']) > 1) {
          $output .= '    <div id="frm_select">'."\n";
          $output .= "    <br /><p>".$this->lang['phreeform_form_select']."</p>\n";
          foreach ($viewData['fields']['reports'] as $value) {
            $output .= "    <div>".html5('rID',['events'=>['onChange'=>"pfGetInfo();"],'attr'=>['type'=>'radio','value'=>$value['id']]]).'&nbsp;'.$value['text']."</div>\n";
          }
          $output .= "    </div>\n";
        } elseif (!isset($viewData['fields']['id']['attr']['value'])) {
            $output .= lang('msg_no_documents');
        } else {
            $output .= html5('rID', $viewData['fields']['id']);
        }
        return $output;
    }

    private function getViewRenderJS()
    {
        return [
            'jsHead' =>"function pfGetInfo() {
    var rID = jq('input[name=rID]:checked', '#frmPhreeform').val();
    var vars = [], hash;
    var q = document.URL.split('?')[1];
    if (q != undefined) {
        q = q.split('&');
        for (var i = 0; i < q.length; i++) { hash = q[i].split('='); vars.push(hash[1]); vars[hash[0]] = hash[1]; }
    }
    var params = '&rID='+rID;
    if (vars['xfld']) params += '&xfld='+vars['xfld'];
    if (vars['xcr'])  params += '&xcr=' +vars['xcr'];
    if (vars['xmin']) params += '&xmin='+vars['xmin'];
    if (vars['xmax']) params += '&xmax='+vars['xmax'];
    jq.ajax({ url:bizunoAjax+'&p=phreeform/render/phreeformBody'+params,
        success: function(json) { processJson(json);
            if (json.msgBody) {
                bizSelSet ('msgFrom',   json.defFrom);
                bizTextSet('msgSubject',json.msgSubject);
                jq('#msgBody').val(json.msgBody.replace(/<br \/>/g, \"\\n\"));
            }
        }
    });
}",
            'jsReady'=>"jq('#frmPhreeform').submit(function (e) {
    var delivery = jq('input:radio[name=delivery]:checked').val();
    var format = jq('#fmt').val();
    if (delivery == 'D' || format == 'csv' || format == 'xml') { // download
        jq.fileDownload(jq(this).attr('action'), {
            failCallback: function (response, url) { processJson(JSON.parse(response)); },
            httpMethod: 'POST',
            data: jq(this).serialize()
        });
        e.preventDefault(); //otherwise a normal form submit would occur
    } else if (delivery == 'S' || format == 'html') { // email
        jq('body').addClass('loading');
        jq.ajax({
            type:    'post',
            url:     jq('#frmPhreeform').attr('action'),
            data:    jq('#frmPhreeform').serialize(),
            success: function (data) { processJson(data); }
        });
        return false;
    } else { jq.messager.alert({title:'',msg:'".jsLang('please_wait')."',icon:'info',width:300}); }
});"];

    }
    /**
     * Retrieves a group of reports from the database from the encoded request
     * @param string $group - encoded group to retrieve available reports
     * @return array - ready to render in select DOM element or radio buttons
     */
    private function phreeformGroupReports(&$group)
    {
        $output = [];
        // alias customer payments to vendor payments (both to Bank Checks)
        if ($group == 'bnk:j22') { $group = 'bnk:j20'; }
        $result = dbGetMulti(BIZUNO_DB_PREFIX.'phreeform', "group_id='$group' AND mime_type<>'dir'", 'title');
        if (sizeof($result) < 1) { msgAdd(sprintf($this->lang['err_group_empty'], $group)); }
        foreach ($result as $row) { $output[] = ['id'=>$row['id'], 'text'=>$row['title']]; }
        return $output;
    }

    private function filterDates($datelist='abcdelfghijk')
    {
        $output = [];
        foreach (viewDateChoices() as $row) {
            if (strpos($datelist, $row['id']) !== false) { $output[] = $row; }
        }
        return $output;
    }

    /**
     * Generates and renders a report using the specified user request filters and a report id,
     * output is choice of PDF, HTML, XML, or CSV for reports, PDF for forms.
     * @global object $report - report structure
     * @param array $layout - Structure coming in
     * @return array - modified $layout
     */
    public function render(&$layout=[])
    {
        global $report;
        $data     = [];
        $rID      = clean('rID', 'integer', 'request'); // could come in as $_POST or $_GET
        if (!$rID) { return msgAdd("Not enough data provided to generate the report/form. rID = $rID"); }
        $format   = clean('fmt', 'text', 'post');
        $xmlReport= dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'doc_data', "id=$rID");
        $report   = phreeFormXML2Obj($xmlReport);
//      msgAdd("report = ".print_r($report, true));
        if (!empty($report->special_class)) { if (!$this->loadSpecialClass($report->special_class)) { return; } }
        $delivery   = clean('delivery',['format'=>'char','default'=>'D'], 'post');
        $msgFrom    = clean('msgFrom', ['format'=>'text','default'=>getUserCache('profile','email')],'post');
        $msgTo      = clean('msgTo',   ['format'=>'text','default'=>clean('rEmail','email','get')],  'post');
        $msgCC1     = clean('msgCC1',  'text','post');
        $msgCC2     = clean('msgCC2',  'text','post');
        $defSubject = $report->title.' '.lang('from').' '.getModuleCache('bizuno', 'settings', 'company', 'primary_name');
        $msgSubject = clean('msgSubject',['format'=>'text', 'default'=>$defSubject], 'post');
        $defBody    = isset($report->EmailBody) ? TextReplace($report->EmailBody) : sprintf(lang('email_body'), $report->title, getModuleCache('bizuno', 'settings', 'company', 'primary_name'));
        $msgBodyRaw = clean('msgBody',   ['format'=>'text', 'default'=>$defBody], 'post');
        $msgBody    = str_replace("\n", "<br />", $msgBodyRaw); // convert textbox nl to html br
        // read in user data and merge with report defaults
        if ($report->reporttype == 'rpt') {
            if (isset($report->grouplist) && is_array($report->grouplist)) { foreach ($report->grouplist as $key => $value) {
                $report->grouplist[$key]->default = (isset($_POST['critGrpSel']) && $_POST['critGrpSel'] == ($key+1)) ? 1 : 0;
            } }
        }
        $date    = clean('date',       ['format'=>'text','default'=>''], 'get');
        $critDate= clean('critDateSel',['format'=>'text','default'=>''], 'post');
        $period  = clean('period',     ['format'=>'integer','default'=>getModuleCache('phreebooks', 'fy', 'period')], 'post');
        msgDebug("\nTrying to get details for date = $date and critDate = $critDate and period = $period");
        if     (!empty($date))     { $report->datedefault = $date; } // should be encoded, this first as it means date override
        elseif (!empty($critDate)) { $report->datedefault = "$critDate:".$_POST['critDateMin'].':'.$_POST['critDateMax']; }
        elseif (!empty($period))   { $report->period = $period; $report->datedefault = $period; }
        if (isset($report->sortlist) && is_array($report->sortlist)) { foreach ($report->sortlist as $key => $value) {
            $report->sortlist[$key]->default = (isset($_POST['critSortSel']) && $_POST['critSortSel'] == ($key+1)) ? 1 : 0;
        } }
        if (isset($report->filterlist) && is_array($report->filterlist)) { foreach ($report->filterlist as $key => $value) { // Criteria Field Selection
            if (isset($_POST['critFltrSel'.$key])) { $value->default = $_POST['critFltrSel'.$key]; }
            if (isset($_POST['fromvalue'  .$key])) { $value->min     = $_POST['fromvalue'  .$key]; }
            if (isset($_POST['tovalue'    .$key])) { $value->max     = $_POST['tovalue'    .$key]; }
            $report->filterlist[$key] = $value;
        } }
        $report->xfilterlist = new \stdClass();
        $report->xfilterlist->fieldname= clean('xfld','text','get');
        $report->xfilterlist->default  = clean('xcr', 'text','get');
        $report->xfilterlist->min      = clean('xmin','text','get');
        $report->xfilterlist->max      = clean('xmax','text','get');
        msgDebug("\nWorking with report (after overrides) = ".print_r($report, true));
        $dataError = ['type'=>'page',
            'toolbars'=>['tbBack'=>['icons'=>['back'=>['events'=>['onClick'=>"window.history.back();"]]]]],
            'divs'    =>['null'=>['type'=>'toolbar','key'=>'tbBack']]];
        switch ($report->reporttype) {
            case 'frm':
                $data = $this->BuildForm($report, $delivery); // if we come back from this function it's for emailing the form
                if (!isset($data['pdf'])) { $layout = $dataError; return; } // no data, fail gracefully
                break;
            case 'rpt':
                $ReportData = '';
                $report->iso = clean('iso', 'alpha_num', 'post');
                $result = $this->BuildSQL($report);
                if ($result['level'] == 'success') { // Generate the output data array
                    $sql = $result['data'];
                    if (!isset($report->filter)) { $report->filter = new \stdClass(); }
                    $report->filter->text = $result['description']; // fetch the filter message
                    if (!$ReportData = BuildDataArray($sql, $report)) { $layout = $dataError; return; } // no data, fail gracefully
                    if ($format == 'pdf')  { $data = $this->GeneratePDFFile ($ReportData, $report, $delivery); }
                    if ($format == 'html') { $data = $this->GenerateHTMLFile($ReportData, $report); }
                    if ($format == 'csv')  { $data = $this->GenerateCSVFile ($ReportData, $report, $delivery); }
                    if ($format == 'xml')  { $data = $this->GenerateXMLFile ($ReportData, $report, $delivery); }
                } else { // Houston, we have a problem
                    return msgAdd($result['message'], $result['level']);
                }
                break;
        }
        if ($delivery=='S' && isset($data['filename'])) {
            $temp_file = BIZUNO_DATA."temp/{$data['filename']}";
            if (!$handle = fopen($temp_file, 'w')) { return msgAdd("Cannot open temp folder to write attachment, the email was not sent!"); }
            if (!fwrite($handle, $data['pdf'])) { return msgAdd("Cannot find attachment, the email was not sent!"); }
            fclose($handle);
            chmod($temp_file, 0644);
            // send the email
            $msgFrom= $this->getAddrInfo('msgFrom', getUserCache('profile','email'), getUserCache('profile','title'));
            $msgTo  = $this->getAddrInfo('msgTo');
            $msgCC1 = $this->getAddrInfo('msgCC1');
            $msgCC2 = $this->getAddrInfo('msgCC2');
            bizAutoLoad(BIZUNO_LIB."model/mail.php", 'bizunoMailer');
            $mail = new bizunoMailer($msgTo['email'], $msgTo['name'], $msgSubject, $msgBody, $msgFrom['email'], $msgFrom['name']);
            if (!empty($msgCC1)) { $mail->addToCC($msgCC1['email'], $msgCC1['name']); }
            if (!empty($msgCC2)) { $mail->addToCC($msgCC2['email'], $msgCC2['name']); }
            $mail->attach($temp_file);
            if ($mail->sendMail()) {
                msgAdd(sprintf(lang('msg_email_sent'), $msgTo['name']), 'success');
                $data = ['content'=>['action'=>'eval','actionData'=>"window.close();"]];
                unlink($temp_file);
                if (!empty($report->contactlog)) { // Update the contact record with information
                    if (isset($report->xfilterlist) && $report->xfilterlist->default=='equal') {
                        $vals = explode('.', $report->xfilterlist->fieldname);
                        $cID  = dbGetValue(BIZUNO_DB_PREFIX.$vals[0], $report->contactlog, "{$vals[1]}={$report->xfilterlist->min}", false);
                        if ($cID) {
                            $sql_data = [
                                'contact_id' => $cID,
                                'entered_by' => getUserCache('profile', 'contact_id', false, '0'),
                                'log_date'   => date('Y-m-d H:i:s'),
                                'action'     => $this->lang['mail_out'],
                                'notes'      => "Email: {$msgTo['name']} ({$msgTo['email']}), $msgSubject"];
                            msgDebug("\nReady to write sql data: ".print_r($sql_data, true));
                            dbWrite(BIZUNO_DB_PREFIX.'contacts_log', $sql_data);
                        }
                    }
                }
            }
        }
        $layout = array_replace_recursive($layout, $data);
    }

    private function getAddrInfo($idx, $defEmail='', $defName='')
    {
        $value = clean($idx, 'text', 'post');
        if (strpos($value, '<') === false) { // no bracket, check for just an email
            return strpos($value, '@') !== false ? ['name'=>trim($value), 'email'=>trim($value)] : [];
        }
        $name  = trim(substr($value, 0, strpos($value, '<')));
        $bofEm = substr($value, strpos($value, '<')+1);
        $email = trim(substr($bofEm, 0, strpos($bofEm, '>')));
        return ['name'=>empty($name) ? $email : $name, 'email'=>$email];
    }

    /**
     * Builds the database SQL statement, executes it and merges result with the report structure
     * @global object $report - Report structure after merge ready for TCPDF render
     * @param object $report - Report structure void of result data, typically the raw report from the db
     * @param char $delivery_method - what to do with the result, D - download, S - serial
     * @return type
     */
    private function BuildForm($report, $delivery_method = 'D')
    {
        global $report;
        bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/renderForm.php", 'PDF');
        // check for at least one field selected to show
        if (!$report->fieldlist) { // No fields are checked to show, that's bad
            return msgAdd(lang('PHREEFORM_NOROWS'), 'caution');
        }
        // Let's build the sql field list for the general data fields (not totals, blocks or tables)
        $strField = [];
        foreach ($report->fieldlist as $key => $field) { // check for a data field and build sql field list
            if (in_array($field->type, ['Data','BarCode','ImgLink','Tbl'])) { // then it's data field make sure it's not empty
                if (isset($field->settings->fieldname)) {
                    $strField[] = prefixTables($field->settings->fieldname).' AS d'.$key;
                    if (isset($report->skipnullfield) && $field->settings->fieldname == $report->skipnullfield) { $report->skipNullFieldIndex = 'd'.$key; }
                } else { // the field is empty, bad news, error and exit unless table with serialized data
                    if ($field->type != 'Tbl') {
                        msgDebug("Failed loading fields at index $key, report: ".print_r($report, true));
                        return msgAdd($this->lang['err_pf_field_empty']." index($key) $field->title");
                    }
                }
            }
        }
        $report->sqlField= implode(', ', $strField);
        sqlTable ($report); // fetch the tables to query
        sqlFilter($report); // fetch criteria and date filter info
        sqlSort  ($report); // fetch the sort order and add to group by string to finish ORDER BY string
        // We now have the sql, find out how many groups in the query (to determine the number of forms)
        $form_field_list = prefixTables($report->formbreakfield);
        if (isset($report->filenamefield) && $report->filenamefield<>'') { $form_field_list .= ', '.prefixTables($report->filenamefield); }
        $sql = "SELECT $form_field_list FROM $report->sqlTable";
        if ($report->sqlCrit) { $sql .= " WHERE $report->sqlCrit"; }
        $sql .= " GROUP BY ".prefixTables($report->formbreakfield);
        if ($report->sqlSort) { $sql .= " ORDER BY $report->sqlSort"; }
        // execute sql to see if we have data
        msgDebug("\nTrying to find results, sql = $sql");
        if (!$stmt = dbGetResult($sql)) { return msgAdd(lang('phreeform_output_none'), 'caution'); }
        $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        if (sizeof($result) == 0) { return msgAdd(lang('phreeform_output_none'), 'caution'); }

        // set the filename for download or email
        if (isset($report->filenameprefix) || isset($report->filenamefield)) {
            $report->filename  = isset($report->filenameprefix)? $report->filenameprefix : '';
            $report->filename .= isset($report->filenamefield) ? $result[0][stripTablename($report->filenamefield)] : '';
        } else {
            $report->filename = ReplaceNonAllowedCharacters($report->title);
        }
        $report->filename .= ".pdf";
        // create an array for each form
        $report->recordID = [];
        foreach ($result as $row) { $report->recordID[] = $row[stripTablename($report->formbreakfield)]; }
        // retrieve the company information
        for ($i = 0; $i < sizeof($report->fieldlist); $i++) {
            if ($report->fieldlist[$i]->type == 'CDta') {
                $report->fieldlist[$i]->settings->text = getModuleCache('bizuno', 'settings', 'company', $report->fieldlist[$i]->settings->fieldname);
            } elseif ($report->fieldlist[$i]->type == 'CBlk') {
                if (!isset($report->fieldlist[$i]->settings->boxfield)) {
                    return msgAdd($this->lang['err_pf_field_empty'].' '.$report->fieldlist[$i]->title);
                }
                $tField = '';
                foreach ($report->fieldlist[$i]->settings->boxfield as $entry) {
                    $value  = getModuleCache('bizuno', 'settings', 'company', $entry->fieldname);
                    $value  = isset($entry->processing) ? viewProcess($value, $entry->processing): $value;
                    $value  = isset($entry->formatting) ? viewFormat ($value, $entry->formatting): $value;
                    $tField.= isset($entry->separator)  ? AddSep($value, $entry->separator)      : $value;
                    msgDebug("\n Adding $value to textfield which is now $tField");
                }
                $report->fieldlist[$i]->settings->text = $tField;
            }
        }
        if (isset($report->serialform) && $report->serialform) {
            return $this->BuildSeq($report, $delivery_method); // build sequential form (receipt style)
        }
        return $this->BuildPDF($report, $delivery_method); // build standard PDF form, doesn't return if download
    }

    /**
     * For forms only - PDF style using TCPDF
     * @global object $report - report structure after database has been run and data has been added
     * @global array $posted_currencies - will be extracted from the data to determine ISO code for formatting
     * @param object $report - report with modified data
     * @param char $delivery_method - [default D, download] other options S to return with PDF formatted output
     * @return doesn't return if successful, user message if failure
     */
    private function BuildPDF($report, $delivery_method = 'D')
    {
        global $report, $posted_currencies;
        // Generate a form for each group element
        $output = [];
        if (!empty($report->special_class)) {
            $fqcn = "\\bizuno\\$report->special_class";
            $special_form = new $fqcn();
        }
        $pdf = new PDF();
        foreach ($report->recordID as $Fvalue) {
            // find the single line data from the query for the current form page
            $TrailingSQL = " FROM $report->sqlTable WHERE ".($report->sqlCrit ? "$report->sqlCrit AND " : '').prefixTables($report->formbreakfield)."='$Fvalue'";
            $report->FieldValues = [];
            $report->currentValues = false; // reset the stored processing values to save sql's
            if (!empty($report->special_class) && method_exists($special_form, 'load_query_results')) {
                $report->FieldValues  = $special_form->load_query_results($report->formbreakfield, $Fvalue);
            } elseif (strlen($report->sqlField) > 0) {
                msgDebug("\nExecuting sql = SELECT $report->sqlField $TrailingSQL");
                if (!$stmt = dbGetResult("SELECT $report->sqlField $TrailingSQL")) { return msgAdd("Error selecting data! See trace file.", 'trap'); }
                $report->FieldValues = $stmt->fetch(\PDO::FETCH_ASSOC);
            }
            //echo "\nTrying to find results, sql = $report->sqlField $TrailingSQL";
            $posted_currencies = ['currency' => getUserCache('profile', 'currency', false, 'USD'), 'currency_rate' => 1];
            if (sizeof(getModuleCache('phreebooks', 'currency', 'iso', false, [])) > 1 && strpos($report->sqlTable, BIZUNO_DB_PREFIX."journal_main") !== false) {
                $stmt  = dbGetResult("SELECT currency, currency_rate $TrailingSQL");
                $result= $stmt->fetch(\PDO::FETCH_ASSOC);
                $posted_currencies = ['currency'=>$result['currency'], 'currency_rate'=>$result['currency_rate']];
            }
            if (isset($report->skipNullFieldIndex) && !$report->FieldValues[$report->skipNullFieldIndex]) { continue; }
            msgDebug("\n Working with FieldValues = ".print_r($report->FieldValues, true));
            $pdf->StartPageGroup();
            foreach ($report->fieldlist as $key => $field) { // Build the text block strings
                if ($field->type == 'TBlk') {
                    if (!$field->settings->boxfield[0]->fieldname) {
                        return msgAdd($this->lang['err_pf_field_empty'] . $field->title);
                    }
                    if (!empty($report->special_class) && method_exists($special_form, 'load_text_block_data')) {
                        $tField = $special_form->load_text_block_data($field->settings->boxfield);
                    } else {
                        $strTxtBlk = $this->setFieldList($field->settings->boxfield);
                        msgDebug("\n Executing textblock sql = SELECT $strTxtBlk $TrailingSQL");
                        if (!$stmt= dbGetResult("SELECT $strTxtBlk $TrailingSQL")) { return msgAdd("Error selecting data! See trace file.", 'trap'); }
                        $result   = $stmt->fetch(\PDO::FETCH_ASSOC);
                        $tField   = '';
                        for ($i = 0; $i < sizeof($field->settings->boxfield); $i++) {
                            $temp   = isset($field->settings->boxfield[$i]->processing)? viewProcess($result['r'.$i], $field->settings->boxfield[$i]->processing) : $result['r'.$i];
                            $temp   = isset($field->settings->boxfield[$i]->formatting)? viewFormat ($temp, $field->settings->boxfield[$i]->formatting): $temp;
                            $tField.= isset($field->settings->boxfield[$i]->separator) ? AddSep     ($temp, $field->settings->boxfield[$i]->separator) : $temp;
                        }
                    }
                    msgDebug("\nSetting TextBlockData = ".$tField);
                    $report->fieldlist[$key]->settings->text = $tField;
                }
                if ($field->type == 'LtrTpl') { // letter template
                    if (!$field->settings->boxfield[0]->fieldname) {
                        return msgAdd($this->lang['err_pf_field_empty'] . $field->title);
                    }
                    $strTxtBlk = $this->setFieldList($field->settings->boxfield);
                    msgDebug("\n Executing textblock sql = SELECT $strTxtBlk $TrailingSQL");
                    if (!$stmt= dbGetResult("SELECT $strTxtBlk $TrailingSQL")) { return msgAdd("Error selecting data! See trace file.", 'trap'); }
                    $result   = $stmt->fetch(\PDO::FETCH_ASSOC);
                    msgDebug("\nResult fo template sql = ".print_r($result, true));
                    $tField   = $field->settings->ltrText;
                    for ($i = 0; $i < sizeof($field->settings->boxfield); $i++) {
                        $temp   = isset($field->settings->boxfield[$i]->processing)? viewProcess($result['r'.$i], $field->settings->boxfield[$i]->processing) : $result['r'.$i];
                        $temp   = isset($field->settings->boxfield[$i]->formatting)? viewFormat ($temp, $field->settings->boxfield[$i]->formatting): $temp;
                        $tField = str_replace($field->settings->boxfield[$i]->title, $temp, $tField);
                    }
                    $report->fieldlist[$key]->settings->text = $tField;
                }
            }
            $pdf->PageCnt = $pdf->PageNo(); // reset the current page numbering for this new form
            $pdf->AddPage();
            // Send the table
            foreach ($report->fieldlist as $key => $TableObject) {
                if ($TableObject->type <> 'Tbl') { continue; }
                if (!isset($TableObject->settings->boxfield)) { return msgAdd($this->lang['err_pf_field_empty'] . $TableObject->title); }
                // Build the sql
                $tblField   = '';
                $tblHeading = [];
                foreach ($TableObject->settings->boxfield as $TableField) { if (isset($TableField->title)) { $tblHeading[] = $TableField->title; } }
                $data = [];
                if (!empty($report->special_class) && method_exists($special_form, 'load_table_data')) {
                    $data = $special_form->load_table_data($TableObject->boxfield);
                } elseif (!empty($TableObject->settings->fieldname)) { // for field encoded data, typically json
                    msgDebug("\nProcessing Data for table: ".print_r($report->FieldValues["d$key"], true));
                    if (!empty($TableObject->settings->processing)) { // let the processing create the data array
                        $data = viewProcess($report->FieldValues["d$key"], $TableObject->settings->processing);
                    } else { // build it by sequence
                        $data = $this->setEncodedList($TableObject->settings->boxfield, $report->FieldValues["d$key"]);
                    }
                } else {
                    $tblField = $this->setFieldList($TableObject->settings->boxfield);
                    if (!$stmt = dbGetResult("SELECT $tblField $TrailingSQL")) { return msgAdd("Error selecting table data! See trace file.", 'trap'); }
                    $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                }
                array_unshift($data, $tblHeading); // set the first data element to the headings
                $TableObject->data = $data;
                $StoredTable = clone $TableObject;
                $pdf->FormTable($TableObject);
            }
            // Send the duplicate data table (only works if each form is contained in a single page [no multi-page])
            foreach ($report->fieldlist as $field) {
                if ($field->type <> 'TDup') { continue; }
                if (!$StoredTable) { return msgAdd(lang('PHREEFORM_EMPTYTABLE') . $field->title); }
                // insert new coordinates into existing table
                $StoredTable->abscissa = $field->abscissa;
                $StoredTable->ordinate = $field->ordinate;
                $pdf->FormTable($StoredTable);
            }
            foreach ($report->fieldlist as $key => $field) {
                // Set the totals (need to be on last printed page) - Handled in the Footer function in TCPDF
                if ($field->type == 'Ttl') {
                    if (!isset($field->settings->boxfield)) { return msgAdd($this->lang['err_pf_field_empty'].' '.$field->title); }
                    $report->fieldlist[$key]->settings->processing = isset($field->settings->processing) ? $field->settings->processing : '';
                    $report->fieldlist[$key]->settings->formatting = isset($field->settings->formatting) ? $field->settings->formatting : '';
                    if (!empty($report->special_class) && method_exists($special_form, 'load_total_results')) {
                        $report->FieldValues = $special_form->load_total_results($field);
                    } else {
                        $ttlField = [];
                        foreach ($field->settings->boxfield as $TotalField) { $ttlField[] = prefixTables($TotalField->fieldname); }
                        if (!$stmt = dbGetResult("SELECT SUM(".implode(' + ', $ttlField).") AS form_total $TrailingSQL")) { return msgAdd("Error selecting total data! See trace file."); }
                        $data = $stmt->fetch(\PDO::FETCH_ASSOC);
                        $report->FieldValues = $data['form_total'];
                    }
                    $temp = isset($field->settings->boxfield[0]->processing) ? viewProcess($report->FieldValues, $field->settings->boxfield[0]->processing) : $report->FieldValues;
                    if (!isset($field->settings->boxfield[0]->formatting)) { $field->settings->boxfield[0]->formatting = ''; }
                    $report->fieldlist[$key]->settings->text = viewFormat($temp, $field->settings->boxfield[0]->formatting);
                }
            }
            // set the printed flag field if provided
            if (isset($report->setprintedfield)) {
                $id_field = $report->formbreakfield;
                $temp     = explode('.', $report->setprintedfield);
                if (sizeof($temp) == 2) { // need the table name and field name
                    $sql = "UPDATE ".$temp[0]." SET ".$temp[1]."=".$temp[1]."+1 WHERE $report->formbreakfield='$Fvalue'";
                    dbGetResult($sql);
                }
            }
        }
        // Add additional headers needed for MSIE and send page
        header('Set-Cookie: fileDownload=true; path=/');
        header('Cache-Control: max-age=60, must-revalidate');
        header('Expires: 0');
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$report->filename.'"');
        $output['filename'] = isset($report->filename) ? $report->filename : 'document';
        $output['pdf']      = $pdf->Output($report->filename, $delivery_method);
        if ($delivery_method == 'S') { return $output; }
        msgDebugWrite();
        exit(); // needs to be here to properly render the pdf file if delivery_method = I or D
    }

    /**
     * @todo NOTE: This method needs to be completed and tested before put into operation
     * For forms only - Sequential mode, e.g. receipts
     * @global object $report - report structure after database has been run and data has been added
     * @global array $posted_currencies - will be extracted from the data to determine ISO code for formatting
     * @param object $report - report with modified data
     * @param char $delivery_method - [default D, download] other options S to return with PDF formatted output
     * @return doesn't return if successful, user message if failure
     */
    private function BuildSeq($report, $delivery_method='D')
    {
        global $report, $posted_currencies;
        // Generate a form for each group element
        $output = NULL;
        if ($report->special_class) {
            $fqcn = "\\bizuno\\$report->special_class";
            $special_form = new $fqcn();
        }
        foreach ($report->recordID as $formNum => $Fvalue) {
            // find the single line data from the query for the current form page
            $TrailingSQL = "FROM $report->sqlTable WHERE ".($report->sqlCrit ? $report->sqlCrit . " AND " : '').prefixTables($report->formbreakfield)."='$Fvalue'";
            if ($report->special_class) {
                $report->FieldValues  = $special_form->load_query_results($report->formbreakfield, $Fvalue);
            } else {
                if (!$stmt = dbGetResult("SELECT $report->sqlField $TrailingSQL")) { return msgAdd("Error selecting field values! See trace file."); }
                $report->FieldValues = $stmt->fetchAll(\PDO::FETCH_ASSOC);
            }
            // load the posted currency values
            $posted_currencies = ['currency' => getUserCache('profile', 'currency', false, 'USD'), 'currency_rate' => 1];
            if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1 && strpos($report->sqlTable, BIZUNO_DB_PREFIX."journal_main") !== false) {
                $stmt  = dbGetResult("SELECT currency, currency_rate $TrailingSQL");
                $result= $stmt->fetch(\PDO::FETCH_ASSOC);
                $posted_currencies = ['currency'=>$result['currency'], 'currency_rate'=>$result['currency_rate']];
            }
            foreach ($report->fieldlist as $field) {
                msgDebug("\nWorking with field $field->type and values: ".print_r($field, true));
                switch ($field->type) {
                    default:
                        $oneline = formatReceipt($field->text, $field->width, $field->align, $oneline);
                        break;
                    case 'Data':
                        $value   = viewFormat (viewProcess(array_shift($report->FieldValues), $field->settings->boxfield[0]->processing), $field->settings->boxfield[0]->formatting);
                        $oneline = formatReceipt($value, $field->width, $field->align, $oneline);
                        break;
                    case 'TBlk':
                        if (!$field->settings->boxfield[0]->fieldname) { return msgAdd($this->lang['err_pf_field_empty'] . $field->title); }
                        if ($report->special_class) {
                            $TextField = $special_form->load_text_block_data($field->settings->boxfield);
                        } else {
                            $strTxtBlk = $this->setFieldList($field->settings->boxfield);
                            $result    = dbGetResult("SELECT $strTxtBlk $TrailingSQL");
                            $TextField = '';
                            for ($i = 0; $i < sizeof($field->settings->boxfield); $i++) {
                                $temp = $field->settings->boxfield[$i]->processing ? viewProcess($result->fields['r'.$i], $field->settings->boxfield[$i]->processing) : $result->fields['r'.$i];
                                $temp = $field->settings->boxfield[$i]->formatting ? viewFormat($temp, $field->settings->boxfield[$i]->formatting) : $temp;
                                $TextField .= AddSep($temp, $field->settings->boxfield[$i]->separator);
                            }
                        }
                        $report->fieldlist[$field->type]->text = $TextField;
                        $oneline = $report->fieldlist[$field->type]->text;
                        break;
                    case 'Tbl':
/*        // Build the sql
        $tblField   = '';
        $tblHeading = [];
        foreach ($field->settings->boxfield as $TableField) { if (isset($TableField->title)) { $tblHeading[] = $TableField->title; } }
        $data = [];
        if (isset($report->special_class) && $report->special_class) {
            $data = $special_form->load_table_data($field->boxfield);
        } elseif (isset($field->settings->fieldname) && $field->settings->fieldname) {
            $fld = 'd'.$field->type;
            $data = viewFormat (viewProcess($report->FieldValues[$fld], $field->settings->processing), $field->settings->formatting);
        } else {
            $tblField = $this->setFieldList($field->settings->boxfield);
            if (!$stmt = dbGetResult("SELECT $tblField $TrailingSQL")) { return msgAdd("Error selecting table data! See trace file."); }
            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        }
        array_unshift($data, $tblHeading); // set the first data element to the headings
        $field->data = $data;
        $StoredTable = clone $field;
        $pdf->FormTable($field);
*/
                        if (!$field->settings->boxfield) { return msgAdd($this->lang['err_pf_field_empty'] . $field->title); }
                        //          $tblHeading = [];
                        //          foreach ($field->settings->boxfield as $TableField) $tblHeading[] = $TableField->title;
                        $data = [];
                        if ($report->special_class) {
                            $data = $special_form->load_table_data($field->settings->boxfield);
                        } else {
                            $tblField = $this->setFieldList($field->settings->boxfield);
                            if (!$stmt = dbGetResult("SELECT $tblField $TrailingSQL")) { return msgAdd("Error selecting table data! See trace file."); }
                            $data = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                        }
                        $field->data = $data;
                        $StoredTable = $field;
                        foreach ($data as $value) {
                            $temp = [];
                            foreach ($value as $data_key => $data_element) {
                                $offset = substr($data_key, 1);
                                $value  = viewFormat (viewProcess($data_element, $field->settings->boxfield[$offset]->processing), $field->settings->boxfield[$offset]->formatting);
                                $temp[].= formatReceipt($value, $field->settings->boxfield[$offset]->width, $field->settings->boxfield[$offset]->align);
                            }
                            $oneline .= implode("", $temp). "\n";
                        }
                        $field->rowbreak = 1;
                        break;
                    case 'TDup':
                        if (!$StoredTable) { return msgAdd(PHREEFORM_EMPTYTABLE . $field->title); }
                        // insert new coordinates into existing table
                        $StoredTable->abscissa = $field->abscissa;
                        $StoredTable->ordinate = $field->ordinate;
                        foreach ($StoredTable->data as $value) {
                            $temp = [];
                            foreach ($value as $data_key => $data_element) {
                                $value   = viewFormat (viewProcess($data_element, $report->boxfield[$data_key]->processing), $report->boxfield[$data_key]->formatting);
                                $temp[] .= formatReceipt($value, $field->width, $field->align);
                            }
                            $oneline = implode("", $temp);
                        }
                        $field->rowbreak = 1;
                        break;
                    case 'Ttl':
                        if (!$field->settings->boxfield) { return msgAdd($this->lang['err_pf_field_empty'] . $field->title); }
                        if ($report->special_class) {
                            $report->FieldValues = $special_form->load_total_results($field);
                        } else {
                            $ttlField = '';
                            foreach ($field->settings->boxfield as $TotalField) { $ttlField[] = prefixTables($TotalField->fieldname); }
                            $sql    = "SELECT SUM(".implode(' + ', $ttlField) . ") AS form_total $TrailingSQL";
                            if (!$stmt = dbGetResult("SELECT $tblField $TrailingSQL")) { return msgAdd("Error selecting ttl data! See trace file."); }
                            $result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                            $report->FieldValues = $result['form_total'];
                        }
                        $value   = viewFormat(viewProcess($report->FieldValues, $report->boxfield[0]->processing), $report->boxfield[0]->formatting);
                        $oneline = formatReceipt($value, $field->width, $field->align, $oneline);
                        break;
                }
                if ($field->rowbreak) {
                    $output .= $oneline . "\n";
                    $oneline = '';
                }
            }
            // set the printed flag field if provided
            if (isset($report->setprintedfield)) {
                $id_field = $report->formbreakfield;
                $temp     = explode('.', $report->setprintedfield);
                if (sizeof($temp) == 2) { // need the table name and field name
                    dbGetResult("UPDATE ".$temp[0]." SET ".$temp[1]." = ".$temp[1]."+1 WHERE $report->formbreakfield = '$Fvalue'");
                }
            }
            $output .= "\n\n\n\n"; // page break
        }
/*
        // FROM FORM GENERATOR - Add additional headers needed for MSIE and send page
        header('Set-Cookie: fileDownload=true; path=/');
        header('Cache-Control: max-age=60, must-revalidate');
        header('Expires: 0');
        header('Content-type: application/pdf');
        header('Content-Disposition: attachment; filename="'.$report->filename.'"');
        $output['filename'] = isset($report->filename) ? $report->filename : 'document';
        $output['pdf']      = $pdf->Output($report->filename, $delivery_method);
        if ($delivery_method == 'S') { return $output; }
        msgDebugWrite();
        exit(); // needs to be here to properly render the pdf file if delivery_method = I or D
*/
        if ($delivery_method == 'S') { return $output; }
        $FileSize = strlen($output);
        header("Content-type: application/text");
        header("Content-disposition: attachment; filename=" . $report->filenameprefix . ".txt; size=" . $FileSize);
        header('Pragma: cache');
        header('Cache-Control: public, must-revalidate, max-age=0');
        header('Connection: close');
        header('Expires: ' . date('r', time()+60*60));
        header('Last-Modified: ' . date('r', time()));
        msgDebugWrite();
        print $output;
        exit();
    }

    /**
     * Reports only - Extracts the information from a report structure and builds the database SQL to retrieve data
     * @param object $report - Report structure
     * @return array - SQL statement ready to execute and descriptive text with the filters for the report header
     */
    private function BuildSQL($report)
    {
        $display = $hidden = [];
        $index = 0;
        for ($i = 0; $i < sizeof($report->fieldlist); $i++) {
            if (!empty($report->fieldlist[$i]->visible)) {
                $display[] = prefixTables($report->fieldlist[$i]->fieldname) . " AS c" . $index;
                $index++;
            } else {
                $hidden[] = prefixTables($report->fieldlist[$i]->fieldname);
            }
        }
        if (empty($display)) { return ['level'=>'error','message'=>lang('PHREEFORM_NOROWS')]; }
        $displayed = array_merge($display, $hidden); // add the hidden rows at end for processing
        $strField  = implode(', ', $displayed);
        $filterdesc= lang('filters').': ';
        //fetch the groupings and build first level of SORT BY string (for sub totals)
        $strGroup  = NULL;
        if (isset($report->grouplist)) { for ($i = 0; $i < sizeof($report->grouplist); $i++) {
            if ($report->grouplist[$i]->default) {
                $strGroup   .= prefixTables($report->grouplist[$i]->fieldname);
                $filterdesc .= $this->lang['phreeform_groups'].' '.$report->grouplist[$i]->title.'; ';
            }
        } }
        // fetch the sort order and add to group by string to finish ORDER BY string
        $strSort = $strGroup;
        if (isset($report->sortlist)) { for ($i = 0; $i < sizeof($report->sortlist); $i++) {
            if ($report->sortlist[$i]->default) {
                $strSort    .= ($strSort <> '' ? ', ' : '') . prefixTables($report->sortlist[$i]->fieldname);
                $filterdesc .= $this->lang['phreeform_sorts'].' '.$report->sortlist[$i]->title.'; ';
            }
        } }
        sqlFilter($report); // fetch criteria and date filter info
        sqlTable ($report); // fetch the tables to query
        $sql = "SELECT $strField FROM $report->sqlTable";
        if ($report->sqlCrit) { $sql .= " WHERE $report->sqlCrit"; }
        if ($strSort)         { $sql .= " ORDER BY $strSort"; }
        return ['level'=>'success','data'=>$sql,'description'=>$filterdesc.$report->sqlCritDesc];
    }

    /**
     * Generates a PDF file with data from the report structure
     * @global object $report - globalized to allow usage in subclasses
     * @param array $data - results of the SQL statement containing the data
     * @param object $report
     * @param char $delivery_method - [default D, download] select S if return with PDF as a file.
     * @return does not return if successful, user message on fail
     */
    private function GeneratePDFFile($data, $report, $delivery_method = 'D')
    { // for pdf reports only
        global $report;
        bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/renderReport.php", 'PDF');
        $pdf = new PDF();
        $pdf->ReportTable($data);
        $ReportName = ReplaceNonAllowedCharacters($report->title).'.pdf';
        msgDebug("\nReady to download file...");
        msgDebugWrite();
        // Add additional headers needed for MSIE and send page
        header('Set-Cookie: fileDownload=true; path=/');
        header('Cache-Control: max-age=60, must-revalidate');
        header('Expires: 0');
        header('Content-type: application/pdf');
        header("Content-Disposition: attachment; filename='$ReportName'");
        $output = ['filename' => $ReportName];
        $output['pdf'] = $pdf->Output($ReportName, $delivery_method);
        if ($delivery_method == 'S') { return $output; }
        exit(); // needs to be here to properly render the pdf file if delivery_method = I or D
    }

    /**
     * Generates a HTML file with the SQL results and returns ready to render
     * @param array $data - source data from the SQL query
     * @param object $report - Report structure
     * @return array - raw HTML ready to render in a DIV through AJAX
     */
    private function GenerateHTMLFile($data, $report)
    { // for html reports only
        bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/renderHTML.php", 'HTML');
        $html = new HTML($data, $report);
        return ['content'=>['action'=>'divHTML','divID'=>'bodyCenter','html'=>$html->output]];
    }

    /**
     * Generates a file formatted in csv from $data to either download direct to browser or return as file for another delivery method
     * @param array $data - Source data
     * @param object $report - Report structure
     * @param type $delivery_method - [default D, download]
     * @return doen's return if successful, user message on failure
     */
    private function GenerateCSVFile($data, $report, $delivery_method='D')
    { // for csv reports only
        $CSVOutput = '';
        $temp = []; // Write the column headings
        foreach ($report->fieldlist as $value) {
            if (isset($value->visible) && $value->visible) { $temp[] = csvEncapsulate($value->title); }
        }
        $CSVOutput = implode(',', $temp) . "\n";
        foreach ($data as $myrow) {
            $Action = array_shift($myrow);
            $todo = explode(':', $Action); // contains a letter of the date type and title/groupname
            switch ($todo[0]) {
                case "r": // Report Total
                case "g": // Group Total
                    $Desc = ($todo[0] == 'g') ? $this->lang['group_total'] : $this->lang['report_total'];
                    $CSVOutput .= $Desc.' '.$todo[1] . "\n";
                    // Now fall through write the total data like any other data row
                case "d": // Data
                default:
                    $temp = [];
                    foreach ($myrow as $mycolumn) { $temp[] = csvEncapsulate($mycolumn); }
                    $CSVOutput .= implode(',', $temp) . "\n";
            }
        }
        $ReportName = ReplaceNonAllowedCharacters($report->title).'.csv';
        if ($delivery_method == 'S') { return ['filename' => $ReportName, 'pdf' => $CSVOutput]; }
        msgDebugWrite();
        header('Set-Cookie: fileDownload=true; path=/');
        header('Cache-Control: max-age=60, must-revalidate');
        header("Content-type: text/csv");
        header("Content-Disposition: attachment; filename=$ReportName");
        print $CSVOutput;
        exit();
    }

    /**
     * Generates a file formatted in XML from $data to either download direct to browser or return as file for another delivery method
     * @param array $data - Source data
     * @param object $report - Report structure
     * @param type $delivery_method - [default D, download]
     * @return doen's return if successful, user message on failure
     */
    private function GenerateXMLFile($data, $report, $delivery_method='D') // for xml reports only
    {
        $Heading = [];
        foreach ($report->fieldlist as $value) {
            if (isset($value->visible) && $value->visible) { $Heading[] = str_replace(' ', '', $value->title); }
        }
        foreach ($data as $myrow) {
            $xml .= '<Row>'."\n";
            $Action = array_shift($myrow);
            $todo = explode(':', $Action); // contains a letter of the date type and title/groupname
            switch ($todo[0]) {
                case "r": // Report Total
                case "g": // Group Total
                    $Desc = ($todo[0] == 'g') ? 'GroupTotal' : 'ReportTotal';
                    $xml .= "<$Desc>".$todo[1]."</$Desc>\n";
                    // Now fall through to write the total data like any other data row
                case "d": // Data
                default:
                    $i = 0;
                    foreach ($Heading as $title) {
                        //foreach ($myrow as $mycolumn) { // check for embedded commas and enclose in quotes
                        $xml .= "<$title>".$myrow[$i]."</$title>\n";
                        $i++;
                    }
            }
            $xml .= "</Row>\n";
        }
        $ReportName = ReplaceNonAllowedCharacters($report->title) . '.xml';
        if ($delivery_method == 'S') { return ['filename' => $ReportName, 'pdf' => $xml]; }
        msgDebugWrite();
        $output = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'."\n".$xml;
        header('Set-Cookie: fileDownload=true; path=/');
        header('Cache-Control: max-age=60, must-revalidate');
        header("Content-type: text/xml");
        header("Content-Disposition: attachment; filename=$ReportName");
        print $output;
        exit();
    }

    /**
     * Extracts and gets the runtime data to use in report headers
     * @param array $layout - Structure coming in
     * @return array - modified $layout
     */
    public function phreeformBody(&$layout=[])
    {
        global $report;
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd("Bad rID: $rID"); }
        $rptXML = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'doc_data', "id=$rID");
        // deprecate this line after WordPress Update from initial release
        if (strpos($rptXML, '<PhreeformReport>') === false) { $rptXML = '<root>'.$rptXML.'</root>'; }
        $report = parseXMLstring($rptXML);
        $report->xfilterlist = new \stdClass();
        $report->xfilterlist->fieldname= clean('xfld','text','get');
        $report->xfilterlist->default  = clean('xcr', 'text','get');
        $report->xfilterlist->min      = clean('xmin','text','get');
        $report->xfilterlist->max      = clean('xmax','text','get');
        msgDebug("\nWorking with report = ".print_r($report, true));
        $output = $this->emailProps($report);
        $output['msgBody'] = str_replace("\n", "<br />", $output['msgBody']);
        $layout = array_replace_recursive($layout, ['content'=>$output]);
    }

    /**
     * Substitutes static report data with user data in the header when generating an email
     * @param object $report - Report structure
     * @return array $output - Properties from search
     */
    private function emailProps($report='')
    {
        $output = ['defFrom'=>'', 'defTo'=>'', 'defCC'=>'', 'valsFrom'=>[], 'valsTo'=>[],
            'msgSubject'=> sprintf($this->lang['phreeform_email_subject'], $report->title, getModuleCache('bizuno', 'settings', 'company', 'primary_name')),
            'msgBody'   => isset($report->emailmessage) ? TextReplace($report->emailmessage) : sprintf(lang('phreeform_email_body'), $report->title, getModuleCache('bizuno', 'settings', 'company', 'primary_name'))];
        $this->getFromAddress($output, $report);
        $this->getToAddress($output, $report);
        return $output;
    }

    private function getFromAddress(&$output, $report)
    {
        $def  = !empty($report->defaultemail) ? $report->defaultemail : 'user';
        $user = getUserCache('profile', 'title')." <".getUserCache('profile', 'email').">";
        $vals = ['user'=>['id'=>$user, 'text'=>($user)]];
        $output['defFrom'] = $user;
        $map  = ['gen'=>'','ap'=>'_ap','ar'=>'_ar'];
        foreach ($map as $key => $suffix) {
            $name = getModuleCache('bizuno', 'settings', 'company', "contact$suffix");
            $email= getModuleCache('bizuno', 'settings', 'company', "email$suffix");
            if (empty($email)) { continue; }
            $val = (!empty($name) ? $name : $email)." <$email>";
            $vals[$key] = ['id'=>$val, 'text'=>($val)];
            if ($key == $def) { $output['defFrom'] = $val; }
        }
        $output['valsFrom'] = $vals;
    }

    private function getToAddress(&$output, $report)
    {
        if ($report->xfilterlist->fieldname <> 'journal_main.id' || $report->xfilterlist->default <> 'equal') { return; }
        $mID  = $report->xfilterlist->min;
        $data = dbGetRow(BIZUNO_DB_PREFIX.'journal_main', "id=$mID");
        if (empty($data)) { return $output; }
        $xKeys= $xVals = [];
        foreach ($data as $key => $val) { $xKeys[] = "%$key%"; $xVals[] = $val; }
        $sParts = isset($report->filenamefield) && $report->filenamefield ? explode('.', $report->filenamefield) : false;
        $title  = !empty($report->filenameprefix)? $report->filenameprefix: '';
        $title .= !empty($data[$sParts[1]])      ? $data[$sParts[1]]      : $report->title;
        $output['msgSubject']= sprintf($this->lang['phreeform_email_subject'], $title, getModuleCache('bizuno', 'settings', 'company', 'primary_name'));
        $output['msgBody']   = TextReplace($output['msgBody'], $xKeys, $xVals);
        $this->toNames = [];
        $output['valsTo'][]  = ['id'=>'', 'text'=>lang('select')];
        $name = !empty(trim($data['contact_b'])) ? trim($data['contact_b']) : trim($data['primary_name_b']);
        $this->extractAddresses($output, $name, $data['email_b']);
        if (sizeof($this->toNames)) { $output['valsTo'][0]['text'] .= " (".implode(', ', $this->toNames).")"; }
        if (empty($data['contact_id_b'])) { return; }
        $this->toNames = array_map('strtolower', $this->toNames);
        $cMulti = dbGetMulti(BIZUNO_DB_PREFIX.'contacts', "rep_id={$data['contact_id_b']}", '', ['id','contact_first','contact_last']);
        foreach ($cMulti as $cRow) {
            $aVals = dbGetValue(BIZUNO_DB_PREFIX.'address_book', ['primary_name', 'email'], "ref_id={$cRow['id']} AND type='m'");
            $cName = trim($cRow['contact_first'].' '.$cRow['contact_last']);
            $name = !empty($cName) ? $cName : (!empty($aVals['contact']) ? trim($aVals['contact']) : trim($aVals['primary_name']));
            if (empty($aVals['email'])) { continue; }
            $this->extractAddresses($output, $name, $aVals['email'], false);
        }
    }

    private function extractAddresses(&$output, $name='', $email=false, $primary=true)
    {
        $parts = explode(',', $email);  // handle comma separated email addresses
        foreach ($parts as $part) {
            $tEmail= strtolower(trim($part));
            $tName = strtolower(trim($name));
            if (empty($tEmail)) { continue; }
            if (strpos($tEmail, '@') !== false) { // assume valid email address
                $val = (!empty($name) ? $name : $part)." <".clean($tEmail, 'email').">";
                $output['valsTo'][$tEmail] = ['id'=>$val, 'text'=>$val];
                if (empty($output['defTo']) && ($primary || in_array($tName, $this->toNames))) { $output['defTo'] = $val; continue; }
                if (empty($output['defCC']) && ($primary || in_array($tName, $this->toNames))) { $output['defCC'] = $val; }
            } elseif ($primary) { // just a name or note
                $this->toNames[] = trim($part); // not a valid email, make it a note
            }
        }
    }

    private function loadSpecialClass($special_class) {
        if (file_exists (BIZUNO_LIB."controller/module/phreeform/extensions/$special_class.php")) {
            bizAutoLoad(BIZUNO_LIB."controller/module/phreeform/extensions/$special_class.php");
        } elseif (file_exists(BIZUNO_DATA."data/phreeform/extensions/$special_class.php")) {
            bizAutoLoad(BIZUNO_DATA."data/phreeform/extensions/$special_class.php");
        } else {
            return msgAdd("Cannot find special class: $special_class");
        }
        return true;
    }

    private function setFieldList($fields=[])
    {
        $output = []; // Build the fieldlist
        foreach ($fields as $idx => $entry) { $output[] = prefixTables($entry->fieldname)." AS r$idx"; }
        return implode(', ', $output);
    }

    private function setEncodedList($boxfield, $rows)
    {
//        msgDebug("\nboxfield = ".print_r($boxfield, true));
//        msgDebug("\nrows = ".print_r($rows, true));
        $data = [];
        foreach ($rows as $row) {
            $output = [];
            for ($i=0; $i<sizeof($boxfield); $i++) { $output["r$i"] = $row[$boxfield[$i]->fieldname]; }
            $data[] = $output;
        }
        msgDebug("\nreturning from setEncodedList with = ".print_r($data, true));
        return $data;
    }
}
