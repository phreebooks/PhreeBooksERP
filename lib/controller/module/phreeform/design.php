<?php
/*
 * PhreeForm designer methods for report/form designing
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
 * @version    2.x Last Update: 2018-06-05
 * @filesource /controller/module/phreeform/design.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/phreeform/functions.php");

class phreeformDesign
{
    public $moduleID = 'phreeform';
    
    function __construct()
    {
        $this->lang = getLang($this->moduleID);
        $this->critChoices = [
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
        $this->dateChoices = [ // used to build pulldowns for filtering
			['id'=>'a', 'text'=>lang('all')],
			['id'=>'b', 'text'=>lang('range')],
			['id'=>'c', 'text'=>lang('today')],
			['id'=>'d', 'text'=>lang('dates_this_week')],
			['id'=>'e', 'text'=>lang('dates_wtd')],
			['id'=>'l', 'text'=>lang('dates_this_period')],
			['id'=>'f', 'text'=>lang('dates_month')],
			['id'=>'g', 'text'=>lang('dates_mtd')],
			['id'=>'h', 'text'=>lang('dates_quarter')],
			['id'=>'i', 'text'=>lang('dates_qtd')],
			['id'=>'j', 'text'=>lang('dates_this_year')],
			['id'=>'k', 'text'=>lang('dates_ytd')]];
        $this->emailChoices = [
            ['id'=>'user','text'=>lang('phreeform_current_user')],
            ['id'=>'gen', 'text'=>lang('address_book_contact_m')],
            ['id'=>'ap',  'text'=>lang('address_book_contact_p')],
            ['id'=>'ar',  'text'=>lang('address_book_contact_r')],
        ];
    }

    /**
     * Generates the structure to render a report/form editor
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
		$rID = clean('rID', 'integer', 'get');
		if ($rID) {
			$dbData = dbGetRow(BIZUNO_DB_PREFIX."phreeform", "id='$rID'");
			$type   = $dbData['mime_type'];
			$dbData['report']    = phreeFormXML2Obj($dbData['doc_data']);
			$dbData['report']->id= $rID;
			msgDebug("\nRead report: ".print_r($dbData['report'], true));
		} else {
            $type= clean('type', 'cmd', 'get');
			$dbData = ['id'=>0,'title'=>'','mime_type'=>$type,'security'=>'u:-1;g:-1','create_date'=>date('Y-m-d'),'settings'=>'','report'=>$this->setNewReport($type)];
		}
		$data   = [
            'pageTitle' => $this->lang['phreeform_title_edit'].' - '.($rID ? $dbData['title'] : lang('new')),
			'reportType'=>$type,
			'toolbars'  => ['tbEdit'=>  ['icons' => [
                'back'   => ['order'=>10, 'events'=>  ['onClick'=>"location.href='".BIZUNO_HOME."&p=phreeform/main/manager'"]],
				'save'   => ['order'=>20, 'events'=>  ['onClick'=>"jq('#frmPhreeform').submit();"]],
				'preview'=> ['order'=>30, 'events'=>  ['onClick'=>"jq('#xChild').val('print'); jq('#frmPhreeform').submit();"]],
				'help'   => ['order'=>99, 'index' =>'']]]],
			'forms'     => ['frmPhreeform' => ['attr'=>  ['type'=>'form','action'=>BIZUNO_AJAX."&p=phreeform/design/save"]]],
			'tabs'      => ['tabPhreeForm' => ['divs'=>  [
                'page'    => ['order'=>10, 'label'=>$this->lang['phreeform_title_page'],  'src'=>BIZUNO_LIB."view/module/phreeform/tabPage.php"],
				'db'      => ['order'=>20, 'label'=>$this->lang['phreeform_title_db'],    'type'=>'datagrid', 'key'=>'tables'],
				'fields'  => ['order'=>30, 'label'=>$this->lang['phreeform_title_field'], 'type'=>'datagrid', 'key'=>'fields'],
				'filters' => ['order'=>40, 'label'=>lang('filters'),  'src'=>BIZUNO_LIB."view/module/phreeform/tabFilters.php"],
				'settings'=> ['order'=>50, 'label'=>lang('settings'), 'src'=>BIZUNO_LIB."view/module/phreeform/tabSettings.php"]]]],
			'divs' => [
                'toolbar'=> ['order'=>20,'type'=>'toolbar', 'key'=>'tbEdit'],
				'heading'=> ['order'=>30,'type'=>'html',    'html'=>"<h1>".$this->lang['phreeform_title_edit'].' - '.($rID ? $dbData['title'] : lang('new'))."</h1>\n"],
				'body'   => ['order'=>50,'label'=>$this->lang['phreeform_title_edit'], 'src'=>BIZUNO_LIB."view/module/phreeform/divRptEdit.php"]],
            'jsHead'=> ['phreeform' => "jq.cachedScript('".BIZUNO_URL."controller/module/phreeform/phreeform.js?ver=".MODULE_BIZUNO_VERSION."');"],
			'fields'   => $this->editLayout($dbData['report']),
			'notes'    => 'TBD Notes Here',
			'datagrid'=>  [
                'tables' => $this->dgTables ('dgTables'),
				'fields' => $this->dgFields ('dgFields', $type),
				'groups' => $this->dgGroups ('dgGroups', $type),
				'sort'   => $this->dgOrder  ('dgSort',   $type),
				'filters'=> $this->dgFilters('dgFilters',$type)],
			'structure'=>  ['selDate'=>$this->dateChoices],
            'lang' => $this->lang];
		$data['javascript'] = $data['fields']['javascript'];
		unset($data['fields']['javascript']);
		$data['javascript']['fonts']     = "var dataFonts = "     .json_encode(phreeformFonts())     .";";
		$data['javascript']['sizes']     = "var dataSizes = "     .json_encode(phreeformSizes())     .";";
		$data['javascript']['aligns']    = "var dataAligns = "    .json_encode(phreeformAligns())    .";";
		$data['javascript']['types']     = "var dataTypes = "     .json_encode($this->phreeformTypes()) .";";
		$data['javascript']['barcodes']  = "var dataBarCodes = "  .json_encode(phreeformBarCodes())  .";";
		$data['javascript']['processing']= "var dataProcessing = ".json_encode(phreeformProcessing()).";";
		$data['javascript']['formatting']= "var dataFormatting = ".json_encode(phreeformFormatting()).";";
		$data['javascript']['separators']= "var dataSeparators = ".json_encode(phreeformSeparators()).";";
		$data['javascript']['bizData']   = "var bizData = "       .json_encode(phreeformCompany())   .";";
		$data['javascript']['fTypes']    = "var filterTypes = "   .json_encode($this->filterTypes($this->critChoices)).";";
		$layout = array_replace_recursive($layout, viewMain(), $data);
	}

    /**
     * Generates the list of filters sourced by $arrValues
     * @param array $arrValues - 
     * @return type
     */
    private function filterTypes($arrValues)
    {
        $output = [];
        foreach ($arrValues as $key => $value) {
            $value = substr($value, 2);
            $temp = explode(':', $value);
            $words = [];
            foreach ($temp as $word) { $words[] = !empty($this->lang[$word]) ? $this->lang[$word] : lang($word); }
            $output[] = ['id'=>"$key", 'text'=>implode(':', $words)];
        }
        return $output;
    }

    /**
     * Creates the fields for the report settings
     * @param object $report - current report settings
     * @return array - structure for generating the tabs/fields
     */
    private function editLayout($report)
    {
        if (strpos($report->groupname, ':')===false) { $report->groupname = $report->groupname.':rpt'; } // for old phreebooks reports
		$selFont  = phreeformFonts();
		$selSize  = phreeformSizes();
		$selAlign = phreeformAligns();
        switch($report->reporttype) {
            case 'frm': 
            case 'lst': $groups = getModuleCache('phreeform', 'frmGroups'); break;
            default:    $groups = getModuleCache('phreeform', 'rptGroups'); break; // default to report
        }
		$data = [
            'id'            => ['attr'=>['type'=>'hidden', 'value'=>isset($report->id)?$report->id:'0']],
			'Title'         => ['label'=>lang('title'), 'attr'=>  ['size'=>64, 'maxlength'=>64, 'value'=>(isset($report->title) ? $report->title :'')]],
			'Description'   => ['attr'=>['type'=>'textarea', 'cols'=>80, 'rows'=>4, 'value'=>(isset($report->description) ?$report->description :'')]],
			'SpecialClass'  => ['label'=>$this->lang['phreeform_special_class'], 'attr'=>  ['size'=>32, 'maxlength'=>32, 'value'=>(isset($report->special_class) ? $report->special_class :'')]],
            'EmailSubject'  => ['attr'=>['width'=>60, 'value'=>isset($report->emailsubject)?$report->emailmessage:'']],
			'EmailBody'     => ['attr'=>['type'=>'textarea', 'cols'=>80, 'rows'=>4, 'value'=>(isset($report->emailmessage)?$report->emailmessage:'')]],
			'rptType'       => ['attr'=>['type'=>'hidden', 'value'=>isset($report->reporttype)?$report->reporttype:'rpt']],
			'Serial'        => ['label'=>$this->lang['lbl_serial_form'], 'attr'=>  ['type'=>'checkbox']],
			'Group'         => ['label'=>lang('group_list'), 'values'=>$groups,'attr'=>['type'=>'select', 'value'=>$report->groupname]],
			'DatePeriod'    => ['attr'=>['type'=>'radio']],
			'DateList'      => ['position'=>'after', 'attr'=>  ['type'=>'checkbox', 'value'=>(isset($report->datelist)?$report->datelist:'a')]],
			'DateField'     => ['label'=>$this->lang['phreeform_date_field'], 'attr'=>['size'=>60, 'value'=>(isset($report->datefield)?$report->datefield:'')], 'events'=>  ['onClick'=>'comboFields(this);']],
			'DateDefault'   => ['label'=>$this->lang['date_default_selected'],'values'=>$this->dateChoices, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->datedefault) ? $report->datedefault : '')]],
			'PrintedField'  => ['label'=>$this->lang['lbl_set_printed_flag'], 'attr'=>['size'=>32, 'value'=>(isset($report->setprintedfield)?$report->setprintedfield:'')],'events'=>  ['onClick'=>'comboFields(this);']],
			'ContactLog'    => ['label'=>$this->lang['lbl_phreeform_contact'],'attr'=>['size'=>32, 'value'=>(isset($report->contactlog)     ?$report->contactlog:'')],     'events'=>  ['onClick'=>'comboFields(this);']],
			'DefaultEmail'  => ['label'=>$this->lang['lbl_phreeform_email'],  'values'=>$this->emailChoices,'attr'=>  ['type'=>'select', 'value'=>(isset($report->defaultemail) ? $report->defaultemail : 'user')]],
			'FormBreakField'=> ['label'=>$this->lang['page_break_field'],     'attr'=>['size'=>32, 'value'=>(isset($report->formbreakfield) ?$report->formbreakfield:'')], 'events'=>  ['onClick'=>'comboFields(this);']],
			'SkipNullField' => ['label'=>$this->lang['lbl_skip_null'],'attr'=>  ['size'=>32, 'value'=>(isset($report->skipnullfield)?$report->skipnullfield:'')], 'events'=>  ['onClick'=>'comboFields(this);']],
			'FilenamePrefix'=> ['label'=>lang('prefix'),    'attr'=>['size'=>10, 'value'=>(isset($report->filenameprefix) ? $report->filenameprefix : '')]],
			'FilenameField' => ['label'=>lang('fieldname'), 'attr'=>['size'=>32, 'value'=>(isset($report->filenamefield)?$report->filenamefield:'')], 'events'=>  ['onClick'=>'comboFields(this);']],
			'BreakField'    => ['label'=>lang('phreeform_field_break'),'attr'=>  ['maxlength'=>64]],
			'SecurityUsers' => ['values'=>listUsers(true, true, false),'attr'=>  ['type'=>'select', 'size'=>10, 'multiple'=>'multiple']],
			'SecurityGroups'=> ['values'=>listRoles(true, false),'attr'=>  ['type'=>'select', 'size'=>10, 'multiple'=>'multiple']],
			'SecUsersAll'   => ['label'=>lang('all_users'), 'attr'=>['type'=>'checkbox','checked'=>true]],
			'SecGroupsAll'  => ['label'=>lang('all_groups'),'attr'=>['type'=>'checkbox','checked'=>true]],
			'PageSize'      => ['label'=>$this->lang['phreeform_paper_size'],   'values'=>phreeformPages($this->lang),'attr'=>['type'=>'select',             'value'=>(isset($report->page->size)          ?$report->page->size           :'LETTER:216:279')]],
			'PageOrient'    => ['label'=>$this->lang['phreeform_orientation'],  'values'=>phreeformOrientation($this->lang), 'attr'=>  ['type'=>'select',      'value'=>(isset($report->page->orientation)   ?$report->page->orientation    :'P')]],
			'MarginTop'     => ['label'=>$this->lang['phreeform_margin_top'],   'styles'=>  ['text-align'=>'right'], 'attr'=>['size'=>'4', 'maxlength'=>'3', 'value'=>(isset($report->page->margin->top)   ?$report->page->margin->top    :'8')]],
			'MarginBottom'  => ['label'=>$this->lang['phreeform_margin_bottom'],'styles'=>  ['text-align'=>'right'], 'attr'=>['size'=>'4', 'maxlength'=>'3', 'value'=>(isset($report->page->margin->bottom)?$report->page->margin->bottom :'8')]],
			'MarginLeft'    => ['label'=>$this->lang['phreeform_margin_left'],  'styles'=>  ['text-align'=>'right'], 'attr'=>['size'=>'4', 'maxlength'=>'3', 'value'=>(isset($report->page->margin->left)  ?$report->page->margin->left   :'8')]],
			'MarginRight'   => ['label'=>$this->lang['phreeform_margin_right'], 'styles'=>  ['text-align'=>'right'], 'attr'=>['size'=>'4', 'maxlength'=>'3', 'value'=>(isset($report->page->margin->right) ?$report->page->margin->right  :'8')]],
			'TextTruncate'  => ['label'=>$this->lang['truncate_fit'],   'attr'=>  ['type'=>'checkbox', 'checked'=>(isset($report->truncate) ?'1':'0')]],
			'TotalOnly'     => ['label'=>$this->lang['show_total_only'],'attr'=>  ['type'=>'checkbox', 'checked'=>(isset($report->totalonly)?'1':'0')]],
			'HeadingShow'   => ['attr'=>  ['type'=>'checkbox', 'checked'=>(isset($report->heading->show)                  ?'1':'0')]],
			'HeadingFont'   => ['values'=>$selFont,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->heading->font) ?$report->heading->font :'helvetica')]],
			'HeadingSize'   => ['values'=>$selSize,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->heading->size) ?$report->heading->size :'12')]],
			'HeadingColor'  => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->heading->color)          ?convertHex($report->heading->color):'#000000'), 'size'=>10]],
			'HeadingAlign'  => ['values'=>$selAlign, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->heading->align)?$report->heading->align:'C')]],
			'Title1Show'    => ['attr'=>  ['type'=>'checkbox', 'checked'=>(isset($report->title1->show)                   ?'1':'0')]],
			'Title1Text'    => ['attr'=>  ['type'=>'text', 'value'=>(isset($report->title1->text)                         ?$report->title1->text:'%reportname%')]],
			'Title1Font'    => ['values'=>$selFont,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->title1->font)  ?$report->title1->font :'helvetica')]],
			'Title1Size'    => ['values'=>$selSize,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->title1->size)  ?$report->title1->size :'10')]],
			'Title1Color'   => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->title1->color)           ?convertHex($report->title1->color):'#000000'), 'size'=>10]],
			'Title1Align'   => ['values'=>$selAlign, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->title1->align) ?$report->title1->align:'C')]],
			'Title2Show'    => ['attr'=>  ['type'=>'checkbox', 'checked'=>(isset($report->title2->show)                   ?'1':'0')]],
			'Title2Text'    => ['attr'=>  ['type'=>'text', 'value'=>(isset($report->title2->text)                         ?$report->title2->text:'Report Generated %date%')]],
			'Title2Font'    => ['values'=>$selFont,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->title2->font)  ?$report->title2->font :'helvetica')]],
			'Title2Size'    => ['values'=>$selSize,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->title2->size)  ?$report->title2->size :'10')]],
			'Title2Color'   => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->title2->color)           ?convertHex($report->title2->color):'#000000'), 'size'=>10]],
			'Title2Align'   => ['values'=>$selAlign, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->title2->align) ?$report->title2->align:'C')]],
			'FilterFont'    => ['values'=>$selFont,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->filter->font)  ?$report->filter->font :'helvetica')]],
			'FilterSize'    => ['values'=>$selSize,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->filter->size)  ?$report->filter->size :'8')]],
			'FilterColor'   => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->filter->color)           ?convertHex($report->filter->color):'#000000'), 'size'=>10]],
			'FilterAlign'   => ['values'=>$selAlign, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->filter->align) ?$report->filter->align:'L')]],
			'DataFont'      => ['values'=>$selFont,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->data->font)    ?$report->data->font :'helvetica')]],
			'DataSize'      => ['values'=>$selSize,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->data->size)    ?$report->data->size :'10')]],
			'DataColor'     => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->data->color)             ?convertHex($report->data->color):'#000000'), 'size'=>10]],
			'DataAlign'     => ['values'=>$selAlign, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->data->align)   ?$report->data->align:'C')]],
			'TotalFont'     => ['values'=>$selFont,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->totals->font)  ?$report->totals->font :'helvetica')]],
			'TotalSize'     => ['values'=>$selSize,  'attr'=>  ['type'=>'select', 'value'=>(isset($report->totals->size)  ?$report->totals->size :'10')]],
			'TotalColor'    => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->totals->color)           ?convertHex($report->totals->color):'#000000'), 'size'=>10]],
			'TotalAlign'    => ['values'=>$selAlign, 'attr'=>  ['type'=>'select', 'value'=>(isset($report->totals->align) ?$report->totals->align:'L')]]];
		// set the checkboxes
        if (isset($report->serialform) && $report->serialform) { $data['Serial']['attr']['checked'] = 'checked'; }
		// set up the security
		$temp   = explode(";", $report->security);
		$users  = substr($temp[0], 2);
		$groups = substr($temp[1], 2);
        if ($users <> '-1') { 
            unset($data['SecUsersAll']['attr']['checked']);
            $data['SecurityUsers']['attr']['value']  = explode(":", $users);
        }
        if ($groups <> '-1') { 
            unset($data['SecGroupsAll']['attr']['checked']);
            $data['SecurityGroups']['attr']['value'] = explode(":", $groups);
        }
		$data['javascript']['dataTables'] = isset($report->tables)    ? formatDatagrid($report->tables,    'dataTables') : "var dataTables = [];\n";
		$data['javascript']['dataFields'] = isset($report->fieldlist) ? formatDatagrid($report->fieldlist, 'dataFields') : "var dataFields = [];\n";
		$data['javascript']['dataGroups'] = isset($report->grouplist) ? formatDatagrid($report->grouplist, 'dataGroups') : "var dataGroups = [];\n";
		$data['javascript']['dataOrder']  = isset($report->sortlist)  ? formatDatagrid($report->sortlist,  'dataOrder')  : "var dataOrder = [];\n";
		$data['javascript']['dataFilters']= isset($report->filterlist)? formatDatagrid($report->filterlist,'dataFilters'): "var dataFilters = [];\n";
		// set the session tables for dynamic field generation
        $tmp = [];
        if (isset($report->tables) && is_array($report->tables)) { 
            foreach ($report->tables as $table) { $tmp[] = $table->tablename; }
        }
        setModuleCache('phreeform', 'designCache', 'tables', $tmp);
		return $data;
	}
	
	/**
     * Generates the structure for saving a report/form after editing
     * 
     * @todo This is raw post and needs to be cleaned before saving, dgTables, etc. are serialized arrays and need to be cleaned with 'json' or 'stringify'
     * 
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function save(&$layout=[]) 
    {
        $request = $_POST;
        if (isset($request['serialform']) && $request['serialform']) { $request['serialform'] = '1'; }
		$rID   = clean('id', 'integer', 'post');
		$xChild= isset($request['xChild']) ? clean($request['xChild'], 'text') : false;
        if (!$security = validateSecurity('phreeform', 'phreeform', $rID?3:2)) { return; }
		$report = array_to_object($request);
		if (strlen($report->tables))     { $temp = clean($report->tables, 'jsonObj');     $report->tables    = $temp->rows; }
		if (strlen($report->fieldlist))  { $temp = clean($report->fieldlist, 'jsonObj');  $report->fieldlist = $temp->rows; }
		if (strlen($report->grouplist))  { $temp = clean($report->grouplist, 'jsonObj');  $report->grouplist = $temp->rows; }
		if (strlen($report->sortlist))   { $temp = clean($report->sortlist, 'jsonObj');   $report->sortlist  = $temp->rows; }
		if (strlen($report->filterlist)) { $temp = clean($report->filterlist, 'jsonObj'); $report->filterlist= $temp->rows; }
		if (is_array($report->fieldlist)){ foreach ($report->fieldlist as $key => $value) {
			msgDebug("\n Processing fieldlist key = $key");
            if (isset($value->settings) && is_string($value->settings)) { $report->fieldlist[$key]->settings = json_decode($value->settings); }
        } }
		msgDebug("\n\nDecrypted get object = ".print_r($report, true));
		// security
		$users = 'u:-1';
        if     (!empty($request['user_all']))  { $users = 'u:-1'; }
        elseif (!isset($request['users'])) { $users = 'u:0'; } // none
        elseif ( isset($request['users']) && $request['users'][0] <> '')  { $users = 'u:'.implode(':', $request['users']); }
		$groups = 'g:-1';
        if     (!empty($request['group_all'])) { $groups = 'g:-1'; }
        elseif (!isset($request['groups'])) { $groups = 'g:0'; } // none
        elseif ( isset($request['groups']) && $request['groups'][0] <> '') { $groups = 'g:'.implode(':', $request['groups']); }
		$report->security = "$users;$groups";
		unset($report->user_all);
		unset($report->group_all);
		unset($report->users);
		unset($report->groups);
		// date choices
        if (isset($request['DatePeriod']) && $request['DatePeriod'] == 'p') { $report->datelist = 'z'; } // periods only
		else {
			$temp = '';
            if (!isset($report->datelist)) { $report->datelist = ''; }
            if (!is_object($report->datelist)) { $report->datelist = [$report->datelist]; }
            foreach ($report->datelist as $key => $value) { $temp .= $value; }
			$report->datelist = $temp;
		}
		unset($report->DatePeriod);
		unset($report->id);
		$xmlReport = "<PhreeformReport>\n".object_to_xml($report)."</PhreeformReport>";
		// fix for easyui leaving stuff in datagrid submit
		$xmlReport = str_replace("<_selected><![CDATA[1]]></_selected>\n", '', $xmlReport);
		$parent_id = dbGetValue(BIZUNO_DB_PREFIX."phreeform", 'id', "group_id='$report->groupname' AND mime_type='dir'");
		msgDebug("\n for group = $report->groupname Found parent_id = $parent_id");
		$sqlData  = [
            'parent_id'  => $parent_id,
			'group_id'   => $report->groupname,
			'mime_type'  => $report->reporttype,
			'title'      => $report->title,
			'last_update'=> date('Y-m-d'),
			'security'   => $report->security,
			'doc_data'   => $xmlReport,
            ];
        if (!$rID) { $sqlData['create_date'] = date('Y-m-d'); }
		msgDebug("\n\nDecrypted report xml string = ".$xmlReport);
		$result = dbWrite(BIZUNO_DB_PREFIX."phreeform", $sqlData, $rID?'update':'insert', "id=$rID");
        if (!$rID) { $rID = $_POST['id'] = $result; }
		msgAdd(lang('phreeform_manager').'-'.lang('save')." $report->title", 'success');
        $jsonAction = "jq('#id').val($rID);";
		switch ($xChild) { // child screens to spawn
            case 'print': $jsonAction .= " winOpen('phreeformOpen', 'phreeform/render/open&rID=$rID');"; break;
		}
        $layout = array_replace_recursive($layout, ['content'=>  ['action'=>'eval','actionData'=>$jsonAction]]);
	}

	/**
     * Generates the structure for the datagrid for report/form tables
     * @param string $name - DOM field name
     * @return array - structure ready to render
     */
    private function dgTables($name)
    {
		return [
            'id'   => $name,
			'type' => 'edatagrid',
			'tip'  => $this->lang['tip_phreeform_database_syntax'],
			'attr' => [
                'toolbar'     => "#{$name}Toolbar",
			    'singleSelect'=>true,
				'idField'     => 'id'],
			'events' => [
                'data'         => 'dataTables',
				'onAfterEdit'  => "function(rowIndex, rowData, changes) { sessionTables(); }",
				'onLoadSuccess'=> "function() { jq(this).datagrid('enableDnd'); }",
				'onClickCell'  => "function(rowIndex) {
					switch (icnAction) { case 'trash': jq('#$name').edatagrid('destroyRow', rowIndex); break; }
					icnAction = '';
				}"],
			'source' => [
                'actions' => [
                    'new'   => ['order'=>10, 'html'=>  ['icon'=>'add',   'size'=>'small', 'events'=>  ['onClick'=>"jq('#$name').edatagrid('addRow');"]]],
					'verify'=> ['order'=>20, 'html'=>  ['icon'=>'verify','size'=>'small', 'events'=>  ['onClick'=>"verifyTables();"]]]]],
			'columns' => [
                'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>150],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'move' => ['icon'=>'move', 'size'=>'small','order'=>20],
						'trash'=> ['icon'=>'trash','size'=>'small','order'=>50,'events'=>['onClick'=>"icnAction='trash';"]]]],
				'join_type' => ['order'=>10, 'label'=>$this->lang['join_type'], 'attr'=>  ['width'=>100, 'resizable'=>true],
					'events'=> ['editor'=>"{type:'combobox',options:{editable:false,mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getTablesJoin',valueField:'id',textField:'text'}}"]],
				'tablename' => ['order'=>20, 'label'=>$this->lang['table_name'], 'attr'=>  ['width'=>200, 'resizable'=>true],
					'events'=> ['editor'=>"{type:'combobox',options:{mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getTables',valueField:'id',textField:'text'}}"]],
				'relationship'=>  ['order'=>30, 'label'=>lang('relationship'), 'attr'=>  ['width'=>300,'resizable'=>true,'editor'=>'text']]]];
	}
	
	/**
     * Pulls the table fields used to build the selection list for report fields
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function getTables(&$layout)
    {
		$tables = [];
		$stmt   = dbGetResult("SHOW TABLES LIKE '".BIZUNO_DB_PREFIX."%'");
		$result = $stmt->fetchAll(\PDO::FETCH_ASSOC);
		msgDebug("\nTables array returned = ".print_r($result, true));
		foreach ($result as $value) {
			$table = str_replace(BIZUNO_DB_PREFIX, '', array_shift($value));
			$tables[] = '{"id":"'.$table.'","text":"'.lang($table).'"}';
		}
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>"[".implode(',',$tables)."]"]);
	}

	/**
	 * This function collects the current list of tables during an edit in a session variable for dynamic field list generation
	 */
	public function getTablesSession()
    {
		$data = clean('data', 'text', 'get');
		$tmp = [];
		$tables = explode(":", $data);
        if (sizeof($tables) > 0) { foreach ($tables as $table) { 
            if ($table) { $tmp[] = $table; }
        } }
        setModuleCache('phreeform', 'designCache', 'tables', $tmp);
	}

	/**
     * Sets the selection choices for tables when one or more are added to the report/form
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function getTablesJoin(&$layout)
    {
		$content = '[
			  { "id":"JOIN",                    "text":"JOIN", "selected":true},
			  { "id":"LEFT JOIN",               "text":"LEFT JOIN"},
			  { "id":"RIGHT JOIN",              "text":"RIGHT JOIN"},
			  { "id":"INNER JOIN",              "text":"INNER JOIN"},
			  { "id":"CROSS JOIN",              "text":"CROSS JOIN"},
			  { "id":"STRAIGHT_JOIN",           "text":"STRAIGHT JOIN"},
			  { "id":"LEFT OUTER JOIN",         "text":"LEFT OUTER JOIN"},
			  { "id":"RIGHT OUTER JOIN",        "text":"RIGHT OUTER JOIN"},
			  { "id":"NATURAL LEFT JOIN",       "text":"NATURAL LEFT JOIN"},
			  { "id":"NATURAL RIGHT JOIN",      "text":"NATURAL RIGHT JOIN"},
			  { "id":"NATURAL LEFT OUTER JOIN", "text":"NATURAL LEFT OUTER JOIN"},
			  { "id":"NATURAL RIGHT OUTER JOIN","text":"NATURAL RIGHT OUTER JOIN"}
			]';
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>$content]);
	}

	/**
     * Generates the structure for the datagrid for report/form item fields
     * @param string $name - DOM field name
     * @return array - datagrid structure ready to render
     */
	private function dgFields($name, $type='rpt')
    {
		$data = [
            'id'  => $name,
			'type'=> 'edatagrid',
			'tip' => $this->lang['tip_phreeform_field_settings'],
			'attr'=> [
                'toolbar' => "#{$name}Toolbar",
				'idField' => 'id',
				'singleSelect'=>true],
			'events' => [
                'data'         => "dataFields",
				'onClickCell'  => "function(rowIndex) {
    switch (icnAction) {
		case 'trash':
            jq('#$name').edatagrid('destroyRow', rowIndex);
            break;
		case 'settings': 
            jq('#dgFields').datagrid('acceptChanges');
            var rowData = jq('#dgFields').datagrid('getData');
			jsonAction('phreeform/design/getFieldSettings', rowIndex, JSON.stringify(rowData.rows[rowIndex]));
			break;
    }
	icnAction = '';
}",
        		'onLoadSuccess'=> "function() { jq(this).datagrid('enableDnd'); }"],
			'source' => [
                'actions' => ['new'=>['order'=>10,'html'=>['icon'=>'add','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]]];
		switch ($type) {
			case 'frm':
			case 'ltr':
				$data['columns'] = [
                    'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>100],
						'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
						'actions'=> [
                            'fldMove' => ['order'=>20,'icon'=>'move',    'size'=>'small'],
							'fldTrash'=> ['order'=>50,'icon'=>'trash',   'size'=>'small','events'=>  ['onClick'=>"icnAction='trash';"]],
							'flsProp' => ['order'=>60,'icon'=>'settings','size'=>'small','events'=> ['onClick'=>"jq('#$name').datagrid('disableDnd'); icnAction='settings';"]]]],
					'boxfield'=> ['order'=> 0, 'attr'=>  ['type'=>'textarea', 'hidden'=>'true']],
					'title'   => ['order'=>20, 'label'=>lang('title'),   'attr'=>  ['width'=>200,'resizable'=>true,'editor'=>'text']],
					'abscissa'=> ['order'=>30, 'label'=>$this->lang['abscissa'],'attr'=>  ['width'=> 80,'resizable'=>true,'editor'=>'text']],
					'ordinate'=> ['order'=>40, 'label'=>$this->lang['ordinate'],'attr'=>  ['width'=> 80,'resizable'=>true,'editor'=>'text']],
					'width'   => ['order'=>50, 'label'=>lang('width'),   'attr'=>  ['width'=> 80,'resizable'=>true,'editor'=>'text']],
					'height'  => ['order'=>60, 'label'=>lang('height'),  'attr'=>  ['width'=> 80,'resizable'=>true,'editor'=>'text']],
					'type'    => ['order'=>70, 'label'=>lang('type'),    'attr'=>  ['width'=>200,'resizable'=>true],
						'events'=>  [
                            'editor'   =>"{type:'combobox',options:{editable:false,valueField:'id',textField:'text',data:dataTypes}}",
							'formatter'=>"function(value,row){ return getTextValue(dataTypes, value); }"]]];
				break;
			case 'rpt':
			default:
				$data['columns'] = [
                    'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>60],
						'events' => ['formatter'=>$name.'Formatter'],
						'actions'=> [
                            'fldMove' => ['order'=>20,'icon'=>'move', 'size'=>'small'],
							'fldTrash'=> ['order'=>50,'icon'=>'trash','size'=>'small','events'=>  ['onClick'=>"icnAction='trash';"]]]],
					'fieldname' => ['order'=>5, 'label' => lang('fieldname'), 'attr'=>  ['width'=>200, 'resizable'=>true], 
						'events'=>  ['editor'=>"{type:'combobox',options:{mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getFields',valueField:'id',textField:'text'}}"]],
					'title' => ['order'=>10, 'label' => lang('title'), 'attr'=>  ['width'=>150, 'resizable'=>true, 'editor'=>'text']],
					'break' => ['order'=>20, 'label' => $this->lang['column_break'], 'attr'=>  ['width'=>80, 'resizable'=>true], 
						'events'=>  ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}"]],
					'width' => ['order'=>30, 'label' => lang('width'), 'attr'=>  ['width'=>80, 'resizable'=>true,'editor'=>'text']],
					'widthTotal' => ['order'=>40, 'label' => $this->lang['total_width'], 'attr'=>  ['width'=>80, 'resizable'=>true]],
					'visible' => ['order'=>50, 'label' => lang('show'), 'attr'=>  ['width'=>50, 'resizable'=>true], 
						'events'=>  ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}"]],
					'processing' => ['order'=>60, 'label' => $this->lang['processing'], 'attr'=>  ['width'=>160, 'resizable'=>true],
						'events'=>  ['editor'=>"{type:'combobox',options:{editable:false,data:dataProcessing,valueField:'id',textField:'text',groupField:'group'}}"]],
					'formatting' => ['order'=>70, 'label' => lang('format'), 'attr'=>  ['width'=>160, 'resizable'=>true],
						'events'=>  ['editor'=>"{type:'combobox',options:{editable:false,data:dataFormatting,valueField:'id',textField:'text',groupField:'group'}}"]],
					'total' => ['order'=>80, 'label' => lang('total'), 'attr'=>  ['width'=>50, 'resizable'=>true], 
						'events'=>  ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}"]],
					'align' => ['order'=>90, 'label' => lang('align'), 'attr'=>  ['width'=>75, 'resizable'=>true],
						'events'=>  [
                            'editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:dataAligns}}",
							'formatter'=>"function(value){ return getTextValue(dataAligns, value); }"]]];
		}
		return $data;
	}
	
	/**
     * Generates the list of tables available to use in generating a report
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function getFields(&$layout=[])
    {
        $output = [];
		$output[] = '{"id":"","text":"'.lang('none').'"}';
        $tables = getModuleCache('phreeform', 'designCache', 'tables');
		foreach ($tables as $table) {
			$struct = dbLoadStructure(BIZUNO_DB_PREFIX.$table);
			foreach ($struct as $value) {
				$label = isset($value['label']) ? $value['label'] : $value['tag'];
				$output[] = '{"id":"'.$value['table'].'.'.$value['field'].'","text":"'.lang($table).'.'.$label.'"}';
			}
		}
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>"[".implode(',',$output)."]"]);
	}

	/**
     * Pulls the field values from a json encoded string and sets them in the structure for the field pop up
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function getFieldSettings(&$layout=[])
    {
        $index = clean('rID', 'integer', 'get');
		$fData = clean('data', 'jsonObj','get');
		msgDebug("\njson decoded data = ".print_r($fData, true));
        if (!isset($fData->type)) { return msgAdd("No type received, I do not know what to display!"); }
		$settings = isset($fData->settings) ? json_decode($fData->settings) : '';
		msgDebug("\nReceived index: $index and settings array: ".print_r($settings, true));
		$pageShow  = [['id'=>'0','text'=>$this->lang['page_all']], ['id'=>'1','text'=>$this->lang['page_first']], ['id'=>'2','text'=>$this->lang['page_last']]];
		$lineTypes = [['id'=>'H','text'=>$this->lang['horizontal']], ['id'=>'V','text'=>$this->lang['vertical']], ['id'=>'C','text'=>lang('custom')]];
		$linePoints= [];
        for ($i=1; $i<7; $i++) { $linePoints[] = ['id'=>$i,'text'=>$i]; }
		$selFont = phreeformFonts();
		$data = [
            'title'   => lang('settings').(isset($settings->title) ? ' - '.$settings->title : ''),
			'toolbars'=> ['tbFields' =>  ['icons' => [
                'fldClose'=> ['order'=> 10,'icon'=>'close','label'=>lang('close'),'events'=>['onClick'=>"jq('#dgFields').datagrid('enableDnd'); jq('#win_settings').window('close');"]],
				'fldSave' => ['order'=> 20,'icon'=>'save', 'label'=>lang('save'), 'events'=>['onClick'=>"fieldIndex=$index; jq('#frmFieldSettings').submit();"]]]]],
			'forms'   => ['frmFieldSettings' => ['attr'=>  ['type'=>'form']]],
			'divs'    => [
                'toolbar'       => ['order'=>30, 'type'=>'toolbar', 'key'=>'tbFields'],
				'field_settings'=> ['order'=>50, 'src'=>BIZUNO_LIB."view/module/phreeform/winFieldSettings.php"]],
			'fields'  => [
                'index'      => ['attr'=>  ['type'=>'hidden', 'value'=>$index]],
				'type'       => ['attr'=>  ['type'=>'hidden', 'value'=>$fData->type]],
				'boxField'   => ['attr'=>  ['type'=>'hidden', 'value'=>'']],
				'fieldname'  => ['attr'=>  ['size'=>'32', 'value'=>isset($settings->fieldname) ? $settings->fieldname :''],
					'events' => ['onClick'=>"comboFields(this, '$fData->type');"]],
				'barcodes'   => ['attr'=>  ['size'=>'32', 'value'=>isset($settings->barcode)? $settings->barcode:''], 
					'events' => ['onClick'=>"comboBarCodes(this);"]],
				'processing' => ['attr'=>  ['size'=>'32', 'value'=>isset($settings->processing)? $settings->processing:''], 
					'events' => ['onClick'=>"comboProcessing(this);"]],
				'formatting' => ['attr'=>  ['size'=>'32', 'value'=>isset($settings->formatting)? $settings->formatting:''], 
					'events' => ['onClick'=>"comboFormatting(this);"]],
				'text'       => ['attr'=>  ['type'=>'textarea', 'size'=>'80',          'value'=>isset($settings->text)    ? $settings->text     : '']],
				'ltrText'    => ['attr'=>  ['type'=>'textarea', 'size'=>'80',          'value'=>isset($settings->ltrText) ? $settings->ltrText  : '']],
				'linetype'   => ['values'=>$lineTypes,   'attr'=>  ['type'=>'select',  'value'=>isset($settings->linetype)? $settings->linetype :'']],
				'length'     => ['label'=>lang('length'),'attr'=>  ['size'=>'10',      'value'=>isset($settings->length)  ? $settings->length   : '']],
				'font'       => ['values'=>$selFont, 'attr'=>  ['type'=>'select',      'value'=>isset($settings->font)    ? $settings->font     :'']],
				'size'       => ['values'=>phreeformSizes(), 'attr'=>['type'=>'select','value'=>isset($settings->size)    ? $settings->size     :'10']],
				'align'      => ['values'=>phreeformAligns(),'attr'=>['type'=>'select','value'=>isset($settings->align)   ? $settings->align    :'L']],
				'color'      => ['classes'=>['easyui-color'],'attr'=>['value'=>isset($settings->color) ? convertHex($settings->color) :'#000000', 'size'=>10]],
				'truncate'   => ['attr'=>  ['type'=>'checkbox', 'value'=>'1']],
				'display'    => ['values'=>$pageShow, 'attr'=>['type'=>'select', 'value'=>isset($settings->display) ? $settings->display  : '0']],
				'totals'     => ['attr'=>  ['type'=>'checkbox', 'value'=>'1']],
				'bshow'      => ['attr'=>  ['type'=>'checkbox', 'value'=>'1']],
				'bsize'      => ['values'=>$linePoints, 'attr'=>['type'=>'select', 'value'=>isset($settings->bsize)   ? $settings->bsize    :'1']],
				'bcolor'     => ['classes'=>['easyui-color'],'attr'=>['value'=>isset($settings->bcolor)  ? convertHex($settings->bcolor)   :'#000000', 'size'=>10]],
				'fshow'      => ['attr'=>  ['type'=>'checkbox', 'value'=>'1']],
				'fcolor'     => ['classes'=>['easyui-color'],'attr'=>['value'=>isset($settings->fcolor)  ? convertHex($settings->fcolor)   :'#000000', 'size'=>10]],
				'hfont'      => ['values'=>$selFont, 'attr'=>['type'=>'select','value'=>isset($settings->hfont)   ? $settings->hfont     :'']],
				'hsize'      => ['values'=>phreeformSizes(), 'attr'=>['type'=>'select','value'=>isset($settings->hsize)   ? $settings->hsize     :'10']],
				'halign'     => ['values'=>phreeformAligns(),'attr'=>['type'=>'select','value'=>isset($settings->halign)  ? $settings->halign    :'L']],
				'hcolor'     => ['classes'=>['easyui-color'],'attr'=>['value'=>isset($settings->hcolor)  ? convertHex($settings->hcolor)    :'#000000', 'size'=>10]],
				'hbshow'     => ['attr'=>  ['type'=>'checkbox', 'value'=>'1']],
				'hbsize'     => ['values'=>$linePoints, 'attr'=>['type'=>'select', 'value'=>isset($settings->hbsize)  ? $settings->hbsize    :'1']],
				'hbcolor'    => ['classes'=>['easyui-color'],'attr'=>['value'=>isset($settings->hbcolor) ? convertHex($settings->hbcolor)   :'#000000', 'size'=>10]],
				'hfshow'     => ['attr'=>  ['type'=>'checkbox', 'value'=>'1']],
				'hfcolor'    => ['classes'=>['easyui-color'],'attr'=>['value'=>isset($settings->hfcolor) ? convertHex($settings->hfcolor)   :'#000000', 'size'=>10]],
			    'endAbscissa'=> ['label'=>$this->lang['abscissa'],'attr'=>['size'=>'5']],
				'endOrdinate'=> ['label'=>$this->lang['ordinate'],'attr'=>['size'=>'5']],
				'img_cur'    => ['attr'=>['type'=>'hidden']],
				'img_file'   => ['attr'=>['type'=>'hidden']],
				'img_upload' => ['attr'=>['type'=>'file']]],
			'content'  => [
                'action'=> 'window',
				'id'    => 'win_settings',
				'title' => lang('settings'),
				'width' => 1000],
            'lang'=> $this->lang];
		// set some checkboxes
        if (!empty($settings->truncate)) { $data['fields']['truncate']['attr']['checked']= 'checked'; }
        if (!empty($settings->totals))   { $data['fields']['totals']['attr']['checked']  = 'checked'; }
        if (!empty($settings->bshow))    { $data['fields']['bshow']['attr']['checked']   = 'checked'; }
        if (!empty($settings->fshow))    { $data['fields']['fshow']['attr']['checked']   = 'checked'; }
        if (!empty($settings->hbshow))   { $data['fields']['hbshow']['attr']['checked']  = 'checked'; }
        if (!empty($settings->hfshow))   { $data['fields']['hfshow']['attr']['checked']  = 'checked'; }
		if (!empty($settings->img_file)) {
			$data['fields']['img_cur'] = ['attr'=>  ['type'=>'img', 'src'=>BIZUNO_URL_FS."&src=".getUserCache('profile', 'biz_id')."/images/$settings->img_file", 'height'=>'32']];
			$data['fields']['img_file']['attr']['value'] = $settings->img_file;
		}
		if (in_array($fData->type, ['CBlk', 'LtrTpl', 'Tbl', 'TBlk', 'Ttl'])) {
            if (!isset($settings->boxfield)) { $settings->boxfield = (object)[]; }
			msgDebug("\nWorking with box data = ".print_r($settings->boxfield, true));
			$data['javascript']['dataFieldValues']= formatDatagrid($settings->boxfield, 'dataFieldValues');
			$data['datagrid']['fields'] = $this->dgFieldValues('dgFieldValues', $fData->type);
		}
		$layout = array_replace_recursive($layout, $data);
	}

	/**
     * Generates the structure for the datagrid for form fields properties pop up
     * @param string $name - DOM field name
     * @return array - structure ready to render
     */
    private function dgFieldValues($name, $type) 
    {
		$data = [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => [
                'toolbar'     => "#{$name}Toolbar",
				'idField'     => 'id',
			    'singleSelect'=>true],
			'events' => [
                'data'         => 'dataFieldValues',
				'onClickCell'  => "function(rowIndex) {
    switch (icnAction) {
		case 'trash': jq('#$name').edatagrid('destroyRow', rowIndex);break;
    }
	icnAction = '';
}"],
			'source' => [
                'actions' => ['new' => ['order'=>10, 'html'=>  ['icon'=>'add', 'size'=>'small', 'events'=>  ['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns' => [
                'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>70],
                    'events' => ['formatter'=>"function(value,row,index){ return {$name}Formatter(value,row,index); }"],
					'actions'=> [
                        'move'  => ['icon'=>'move', 'size'=>'small','order'=>20],
						'trash' => ['icon'=>'trash','size'=>'small','order'=>50,'events'=>  ['onClick'=>"icnAction='trash';"]]]],
				'title'		 => ['order'=>10, 'label'=>lang('title'), 'attr'=>  ['width'=>150, 'resizable'=>true, 'editor'=>'text']],
				'processing' => ['order'=>20, 'label' => $this->lang['processing'], 'attr'=>  ['width'=>160, 'resizable'=>true],
					'events' => [
                        'formatter'=>"function(value,row){ return getTextValue(dataProcessing, value); }",
						'editor'   =>"{type:'combobox',options:{editable:false,data:dataProcessing,valueField:'id',textField:'text',groupField:'group'}}"]],
				'formatting' => ['order'=>30, 'label' => lang('format'), 'attr'=>  ['width'=>160, 'resizable'=>true],
					'events' => [
                        'formatter'=>"function(value,row){ return getTextValue(dataFormatting, value); }",
						'editor'   =>"{type:'combobox',options:{editable:false,data:dataFormatting,valueField:'id',textField:'text',groupField:'group'}}"]],
				'separator'  => ['order'=>40, 'label'=>lang('separator'),
					'attr'=>  ['width'=>160, 'resizable'=>true, 'hidden'=>in_array($type, ['CBlk','TBlk']) ? false : true],
					'events' => [
                        'formatter'=>"function(value,row){ return getTextValue(dataSeparators, value); }",
						'editor'   =>"{type:'combobox',options:{editable:false,data:dataSeparators,valueField:'id',textField:'text'}}"]],
				'font' => ['order'=>50, 'label'=>lang('font'), 'attr'=>  ['width'=>80, 'resizable'=>true], 
					'events' => [
                        'formatter'=>"function(value,row){ return getTextValue(dataFonts, value); }",
						'editor'   =>"{type:'combobox',options:{valueField:'id',textField:'text',data:dataFonts}}"]],
				'size' => ['order'=>60, 'label'=>lang('size'), 'attr'=>  ['width'=>80, 'resizable'=>true], 
					'events' => ['editor'=>"{type:'combobox',options:{valueField:'id',textField:'text',data:dataSizes}}"]],
				'align' => ['order'=>70, 'label' => lang('align'), 'attr'=>  ['width'=>80, 'resizable'=>true],
					'events' => [
                        'formatter'=>"function(value,row){ return getTextValue(dataAligns, value); }",
						'editor'   =>"{type:'combobox',options:{valueField:'id',textField:'text',data:dataAligns,}}"]],
				'color' => ['order'=>80, 'label'=>lang('color'), 'attr'=>  ['width'=>80, 'resizable'=>true],
					'events'=>  ['editor'=>"{type:'color',options:{value:'#000000'}}"]],
				'width' => ['order'=>90, 'label'=>lang('width'), 
					'attr'=>  ['width'=>50, 'editor'=>'text', 'resizable'=>true, 'align'=>'right', 'hidden'=>$type=='Tbl'?false:true]],
                ],
            ];//'DataColor' => ['classes'=>['easyui-color'], 'attr'=>  ['value'=>(isset($report->data->color) ?$report->data->color:'#000000'), 'size'=>10]],
		switch ($type) {
//			case 'CDta':  // N/A - no datagrid used for this
			case 'CBlk':
				$data['columns']['fieldname'] = ['order'=>5, 'label'=>lang('fieldname'), 'attr'=>  ['width'=>200, 'resizable'=>true], 
					'events' => ['editor'=>"{type:'combobox',options:{editable:false,data:bizData,valueField:'id',textField:'text'}}"]];
			case 'TBlk':
			case 'Ttl':
				if (!isset($data['columns']['fieldname'])) {
					$data['columns']['fieldname'] = ['order'=>5, 'label'=>lang('fieldname'), 'attr'=>  ['width'=>200, 'resizable'=>true],
						'events' => ['editor'=>"{type:'combobox',options:{editable:false,mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getFields',valueField:'id',textField:'text'}}"]];
				}
				unset($data['columns']['title']);
				unset($data['columns']['font']);
				unset($data['columns']['size']);
				unset($data['columns']['align']);
				unset($data['columns']['color']);
				unset($data['columns']['width']);
				break;
			default:
				$data['columns']['fieldname'] = ['order'=>5, 'label'=>lang('fieldname'), 'attr'=>  ['width'=>200, 'resizable'=>true],
					'events' => ['editor'=>"{type:'combobox',options:{mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getFields',valueField:'id',textField:'text'}}"]];
				break;
		}
		return $data;
	}
	
	/**
     * Generates the structure for the datagrid for report/form groups
     * @param string $name - DOM field name
     * @param string $type - choices are rpt (report) OR frm (form)
     * @return array - structure ready to render
     */
	private function dgGroups($name, $type='rpt')
    {
		$data = [
            'id'     => $name,
			'type'   => 'edatagrid',
			'attr'   => [
                'title'       => lang('group_list'),
				'toolbar'     => '#'.$name.'Toolbar',
			    'singleSelect'=> true,
				'idField'     => 'id',
                ],
			'events' => [
                'data'         => 'dataGroups',
				'onLoadSuccess'=> "function() { jq(this).datagrid('enableDnd'); }",
				'onClickCell'  => "function(rowIndex) { if (icnAction=='trash') { jq('#$name').edatagrid('destroyRow', rowIndex); } icnAction = ''; }",
                ],
			'source' => [
                'actions' => [
                    'new' => ['order'=>10, 'html'=>  ['icon'=>'add', 'size'=>'small', 'events'=>  ['onClick'=>"jq('#$name').edatagrid('addRow');"]]],
                    ],
                ],
			'columns' => [
                'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>60],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'move' => ['icon'=>'move', 'size'=>'small','order'=>20],
						'trash'=> ['icon'=>'trash','size'=>'small','order'=>50,'events'=>  ['onClick'=>"icnAction='trash';"]],
                        ],
                    ],
				'fieldname' => ['order'=>10, 'label' => lang('fieldname'), 'attr'=>  ['width'=>250,'resizable'=>true], 
					'events'=> ['editor'=>"{type:'combobox',options:{editable:false,mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getFields',valueField:'id',textField:'text'}}"]],
				'title'     => ['order'=>20, 'label' => lang('title'),     'attr'=>  ['width'=>150,'resizable'=>true, 'editor'=>'text']],
				'default'   => ['order'=>30, 'label' => lang('default'),   'attr'=>  ['width'=>120,'resizable'=>true], 
					'events'=> ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}"]],
				'page_break'=> ['order'=>40, 'label' => $this->lang['page_break'],'attr'=>  ['width'=>120,'resizable'=>true], 
					'events'=> ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}"]],
				'processing'=> ['order'=>50, 'label' => $this->lang['processing'],'attr'=>  ['width'=>200,'resizable'=>true], 
					'events'=> ['editor'=>"{type:'combobox',options:{editable:false,data:dataProcessing,valueField:'id',textField:'text',groupField:'group'}}"]],
				'formatting'=> ['order'=>50, 'label' => lang('format'),'attr'=>  ['width'=>200,'resizable'=>true], 
					'events'=> ['editor'=>"{type:'combobox',options:{editable:false,data:dataFormatting,valueField:'id',textField:'text',groupField:'group'}}"]],
                    ],
            ];
		return $data;
	}
	
	/**
     * Generates the structure for the datagrid for report/form sort order selections
     * @param string $name - DOM field name
     * @param string - choices are report (rpt) or form (frm)
     * @return array datagrid structure
     */
	private function dgOrder($name, $type='rpt')
    {
		return [
            'id'     => $name,
			'type'   => 'edatagrid',
			'attr'   => ['title'=>$this->lang['sort_list'],'toolbar'=>"#{$name}Toolbar",'singleSelect'=>true,'idField'=>'id'],
			'events' => [
                'data'         => 'dataOrder',
				'onLoadSuccess'=> "function() { jq(this).datagrid('enableDnd'); }",
				'onClickCell'  => "function(rowIndex) { if (icnAction=='trash') { jq('#$name').edatagrid('destroyRow', rowIndex); } icnAction = ''; }",
                ],
			'source' => [
                'actions' => [
                    'new' => ['order'=>10, 'html'=>  ['icon'=>'add', 'size'=>'small', 'events'=>  ['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns' => [
                'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>60],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'move' => ['icon'=>'move', 'size'=>'small','order'=>20],
						'trash'=> ['icon'=>'trash','size'=>'small','order'=>50,'events'=>  ['onClick'=>"icnAction='trash';"]]],
                    ],
				'fieldname' => ['order'=>10, 'label'=>lang('fieldname'), 'attr'=>  ['width'=>250, 'resizable'=>'true'], 
					'events'=> ['editor'=>"{type:'combobox',options:{editable:false,mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getFields',valueField:'id',textField:'text'}}"]],
				'title'   => ['order'=>20, 'label'=>lang('title'), 'attr' => ['width'=>150, 'resizable'=>'true', 'editor'=>'text']],
				'default' => ['order'=>30, 'label'=>lang('default'), 'attr'=>  ['width'=>120], 
					'events'=> ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}", 'resizable'=>'true']]],
            ];
	}
	
	/**
     * Generates the structure for the datagrid for report/form filter selections
     * @param string $name - DOM field name
     * @return array - structure
     */
    private function dgFilters($name, $type='rpt')
    {
		return [
            'id'   => $name,
			'type' => 'edatagrid',
			'attr' => [
                'title'       => $this->lang['filter_list'],
				'toolbar'     => '#'.$name.'Toolbar',
			    'singleSelect'=> true,
				'idField'     => 'id'],
			'events' => [
                'data'         => 'dataFilters',
				'onLoadSuccess'=>"function() { jq(this).datagrid('enableDnd'); }",
				'onClickCell'  => "function(rowIndex) { if (icnAction=='trash') { jq('#$name').edatagrid('destroyRow', rowIndex); } icnAction = ''; }"],
			'source' => [
                'actions' => ['new'=>['order'=>10,'html'=>['icon'=>'add','size'=>'small','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]]],
			'columns' => [
                'action' => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>60],
					'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
					'actions'=> [
                        'move' => ['icon'=>'move', 'size'=>'small','order'=>20],
						'trash'=> ['icon'=>'trash','size'=>'small','order'=>50,'events'=>  ['onClick'=>"icnAction='trash';"]]]],
				'fieldname' => ['order'=>10, 'label' => lang('fieldname'), 'attr'=>  ['width'=>250, 'resizable'=>true], 
					'events'=> ['editor'=>"{type:'combobox',options:{mode:'remote',url:'".BIZUNO_AJAX."&p=phreeform/design/getFields',valueField:'id',textField:'text'}}"]],
				'title'  => ['order'=>20, 'label' => lang('title'),'attr'=>  ['width'=>150, 'editor'=>'text', 'resizable'=>true]],
				'visible'=> ['order'=>30, 'label' => lang('show'), 'attr'=>  ['width'=>120, 'resizable'=>true], 
					'events'=> ['editor'=>"{type:'checkbox',options:{on:'1',off:''}}"]],
				'type'   => ['order'=>40, 'label' => lang('type'), 'attr'=>  ['width'=>200, 'resizable'=>true], 
					'events'=>  [
                        'editor'   =>"{type:'combobox',options:{editable:false,valueField:'id',textField:'text',data:filterTypes}}",
						'formatter'=>"function(value,row){ return getTextValue(filterTypes, value); }"]],
				'min'=> ['order'=>50, 'label'=>lang('min'), 'attr'=>  ['width'=>100, 'editor'=>'text', 'resizable'=>true]],
				'max'=> ['order'=>60, 'label'=>lang('max'), 'attr'=>  ['width'=>100, 'editor'=>'text', 'resizable'=>true]]]];
	}
    
    /**
     * Sets some defaults for a new report/form/serial form
     * @param string $type - choices are rpt (report [default]), frm (form) OR lst (list)
     * @return StdClass
     */
    private function setNewReport($type='rpt') 
    {
        $report = new \StdClass;
        $report->reporttype = $type;
        $report->groupname = in_array($type, ['frm', 'lst']) ? "misc:misc" : "misc:$type";
        $report->security = 'u:-1;g:-1';
        return $report;
    }

    /**
     * Creates a list of report/form field types which determine the properties allowed
     * @return type
     */
    function phreeformTypes()
    {
        return [
            ['id'=>'Data',    'text'=> $this->lang['fld_type_data_line']],
            ['id'=>'TBlk',    'text'=> $this->lang['fld_type_data_block']],
            ['id'=>'Tbl',     'text'=> $this->lang['fld_type_data_table']],
            ['id'=>'TDup',    'text'=> $this->lang['fld_type_data_table_dup']],
            ['id'=>'Ttl',     'text'=> $this->lang['fld_type_data_total']],
            ['id'=>'LtrTpl',  'text'=> $this->lang['fld_type_letter_tpl']],
            ['id'=>'Text',    'text'=> $this->lang['fld_type_fixed_txt']],
            ['id'=>'Img',     'text'=> $this->lang['fld_type_image']],
            ['id'=>'ImgLink', 'text'=> $this->lang['fld_type_image_link']],
            ['id'=>'Rect',    'text'=> $this->lang['fld_type_rectangle']],
            ['id'=>'Line',    'text'=> $this->lang['fld_type_line']],
            ['id'=>'CDta',    'text'=> $this->lang['fld_type_biz_data']],
            ['id'=>'CBlk',    'text'=> $this->lang['fld_type_biz_block']],
            ['id'=>'PgNum',   'text'=> $this->lang['fld_type_page_num']],
            ['id'=>'BarCode', 'text'=> $this->lang['fld_type_barcode']]];
    }
}