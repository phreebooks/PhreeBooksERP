<?php
/*
 * Methods to handle custom fields
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
 * @version    3.x Last Update: 2018-12-23
 * @filesource /lib/controller/module/bizuno/fields.php
 */

namespace bizuno;

class bizunoFields
{
    public $moduleID = 'bizuno';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
    /**
     * entry point for custom fields, can be put inside of tabs or stand alone
     * @param array modified $layout
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $module= clean('module','text', 'get');
        $table = clean('table', 'text', 'get');
        if (!$module || !$table) { return msgAdd("Module and/or table information missing!"); }
        $layout = array_replace_recursive($layout, ['type'=>'divHTML',
            'divs'     => ['fields'   =>['order'=>60, 'type'=>'accordion','key' =>'accFields']],
            'accordion'=> ['accFields'=>['divs'=>[
                'manager'=> ['order'=>30,'label'=>$this->lang['custom_field_manager'],'type'=>'datagrid','key'=>'dgFields'],
                'detail' => ['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]],
            'datagrid' => ['dgFields'=>$this->dgFields('dgFields', $module, $table, $security)]]);
    }

    /**
     * Datagrid call to list rows of custom fields datagrid
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $module= clean('module','text', 'get');
        $table = clean('table', 'text', 'get');
        if (!$module || !$table) { return msgAdd("Module and/or table information missing!"); }
        $output = [];
        $values = dbLoadStructure(BIZUNO_DB_PREFIX.$table);
        foreach ($values as $settings) { if ($settings['tab'] != 0) { $output[] = [
            'id'     => $settings['field'],
            'label'  => $settings['label'],
            'field'  => $settings['field'],
            'order'  => $settings['order'],
            'type'   => $settings['attr']['type'],
            'default'=> $settings['default']];
        } }
        $total = sizeof($output);
        $page = clean('page', ['format'=>'integer','default'=>1], 'post');
        $rows = clean('rows', ['format'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')], 'post');
        $sort = clean('sort', ['format'=>'text','default'=>'label'], 'post');
        $order= clean('order',['format'=>'text','default'=>'asc'], 'post');
        $temp = [];
        foreach ($output as $key => $value) { $temp[$key] = $value[$sort]; }
        array_multisort($temp, $order=='desc'?SORT_DESC:SORT_ASC, $output);
        $slice  = array_slice($output, ($page-1)*$rows, $rows);
        $layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>json_encode(['total'=>$total, 'rows'=>$slice])]);
    }

    /**
     * Datagrid structure for listing and retrieving custom fields 
     * @param string $name - datagrid name
     * @param string $module - module name
     * @param string $table - database table name to add/delete fields
     * @param integer $security - user security settings
     * @return array - structure of datagrid
     */
    private function dgFields($name, $module, $table, $security=0)
    {
        return ['id' => $name,
            'attr'   => ['toolbar'=>"#{$name}Toolbar", 'idField'=>'field', 'url'=>BIZUNO_AJAX."&p=bizuno/fields/managerRows&module=$module&table=$table"],
            'events' => ['onDblClickRow'=> "function(rowIndex, rowData) { accordionEdit('accFields', 'dgFields', 'detail', '".lang('details')."', 'bizuno/fields/edit&module=$module&table=$table', rowData.id); }"],
            'source' => ['actions'=>['newField'=>['order'=>10,'icon'=>'new','events'=>['onClick'=>"accordionEdit('accFields','dgFields','detail', '".lang('details')."', 'bizuno/fields/edit&module=$module&table=$table', 0);"]]]],
            'columns'=> [
                'id' => ['order'=>0,'attr'=>['hidden'=>true]],
                'action' => ['order'=>1,'label'=>lang('action'),'attr'=>['width'=>40, 'formatter'=>$name.'Formatter'],
                    'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"],
                    'actions'=> ['delete' => ['order'=>90,'icon'=>'trash','hidden'=>$security==4?false:true,
                        'events' => ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/fields/delete&table=$table', 0, 'idTBD');"]]]],
                'field'  => ['label'=>lang('field'),  'order'=>10,'attr'=>['width'=>100,'sortable'=>true,'resizable'=>true]],
                'label'  => ['label'=>lang('label'),  'order'=>20,'attr'=>['width'=>160,'sortable'=>true,'resizable'=>true]],
                'order'  => ['label'=>lang('order'),  'order'=>30,'attr'=>['width'=>80,'resizable'=>true]],
                'type'   => ['label'=>lang('type'),   'order'=>40,'attr'=>['width'=>80,'resizable'=>true]],
                'default'=> ['label'=>lang('default'),'order'=>50,'attr'=>['width'=>80,'resizable'=>true]]]];
    }

    /**
     * Method to edit a custom field
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function edit(&$layout=[])
    {
        $module= clean('module','text', 'get');
        $table = clean('table', 'text', 'get');
        $field = clean('rID',   'text', 'get');
        if (!$security = validateSecurity('bizuno', 'admin', $field?3:2)) { return; }
        if (!$module || !$table) { return msgAdd("Module and/or table information missing!"); }
        if ($field) { msgAdd($this->lang['xf_msg_edit_warn'], 'caution'); }
        $struc = dbLoadStructure(BIZUNO_DB_PREFIX.$table);
        $props = $field ? $struc[$field] : ['attr'=>['type'=>'text']];
        msgDebug("\n Working with field properties: ".print_r($props, true));
        $tabs  = $gList = [];
        $groups= [['id'=>'', 'text'=>'']];
        foreach (getModuleCache($module, 'tabs') as $tID => $settings) { $tabs[] = ['id'=>$tID, 'text'=>$settings['title']];}
        foreach ($struc as $value) { 
            if (empty($value['group'])) { continue; }
            if (!in_array($value['group'], $gList)) {
                $gList[] = $value['group'];
                $groups[]= ['id'=>$value['group'], 'text'=>$value['group']];
            }
        }
        $jsBody = "jq('#group').combobox({ 
    data:grpData, valueField:'id', textField:'id', width:100, delay:1000,
    onChange: function (newVal) {
        var datas = jq('#group').combobox('options').data;
        datas.push({ id:newVal });
        jq('#group').combobox('loadData', datas);
        jq('#group').combobox('setValue', newVal);
    }
});";
        $data = ['type'=>'divHTML',
            'divs'    => ['detail' =>['order'=>50,'type'=>'divs','divs'=>[
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key' =>'tbField'],
                'formBOF'=> ['order'=>15,'type'=>'form',   'key' =>'frmField'],
                'body'   => ['order'=>50,'type'=>'html',   'html'=>$this->getViewFields($props, $module, $table, $field)],
                'formEOF'=> ['order'=>85,'type'=>'html',   'html'=>"</form>"]]]],
            'toolbars'=> ['tbField'=>['icons' =>[
                'new' => ['order'=>20,'events'=>['onClick'=>"accordionEdit('accFields','dgFields','detail','".lang('details')."','bizuno/fields/edit&module=$module&table=$table', 0);"]],
                'save'=> ['order'=>40,'events'=>['onClick'=>"jq('#frmField').submit();"]]]]],
            'forms'   => ['frmField'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/fields/save"]]],
            'jsHead'  => [
                'tabData' => "var tabData=".json_encode($tabs).";",
                'grpData' => "var grpData=".json_encode($groups).";"],
            'jsBody'  => ['init'=>$jsBody],
            'jsReady' => ['init'=>"ajaxForm('frmField');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    private function getViewFields($props, $module, $table, $field)
    {
        $type  = isset($props['attr']['type']) ? $props['attr']['type'] : 'text';
        $ints  = viewKeyDropdown(['tinyint'=>'-127 '.lang('to').' 127', 'smallint'=>'-32,768 '.lang('to').' 32,768',
            'mediumint'=>'-8,388,608 '.lang('to').' 8,388,607', 'int'=>'-2,147,483,648 '.lang('to').' 2,147,483,647',
            'bigint'   =>lang('greater_than').' 2,147,483,648']);
        $floats= viewKeyDropdown(['float'=>$this->lang['xf_lbl_db_float'], 'double'=>$this->lang['xf_lbl_db_double']]);
        $checks= viewKeyDropdown(['0'=>lang('unchecked'),'1'=>lang('checked')]);
        $fields= [
            'module'          => ['attr'=>['type'=>'hidden','value'=>$module]],
            'table'              => ['attr'=>['type'=>'hidden','value'=>$table]],
            'id'              => ['attr'=>['type'=>'hidden','value'=>$field]], // holds old_field_name
            'field'              => ['label'=>$this->lang['xf_lbl_field'],'position'=>'after','attr'=>['value'=>$field]],
            'label'              => ['label'=>$this->lang['xf_lbl_label'],'position'=>'after','attr'=>['value'=>isset($props['label'])?$props['label']:'']],
            'tag'              => ['label'=>$this->lang['xf_lbl_tag'],  'position'=>'after','attr'=>['value'=>isset($props['tag'])?$props['tag']:'']],
            'tab'              => ['label'=>$this->lang['xf_lbl_tab'],  'classes' =>['easyui-combobox'],'position'=>'after',
                'options'=>['data'=>'tabData','width'=>200,'valueField'=>"'id'",'textField'=>"'text'"],
                'attr'   =>['value'=>isset($props['tab'])?$props['tab']:'']],
            'group'              => ['label'=>$this->lang['xf_lbl_group'],'classes'=>['easyui-combobox'],'position'=>'after','attr'=>['value'=>isset($props['group'])?$props['group']:'']],
            'order'              => ['label'=>$this->lang['xf_lbl_order'],'position'=>'after','attr'=>['value'=>isset($props['order'])?$props['order']:'']],
            'type'              => ['position'=>'after', 'attr'=>['type'=>'radio','value'=>$type]],
            'text_length'      => ['label'=>$this->lang['xf_lbl_text_length']."<br />"],
            'text_default'      => ['label'=>lang('default')."<br />", 'attr'=>  ['type'=>'textarea']],
            'link_default'      => ['label'=>lang('default')."<br />"],
            'int_select'      => ['label'=>lang('range')."<br />", 'values'=>$ints, 'attr'=>['type'=>'select']],
            'int_default'      => ['label'=>lang('default')."<br />", 'attr'=>['value'=>isset($props['attr']['value'])?$props['attr']['value']:'0']],
            'float_select'      => ['label'=>lang('precision')."<br />",'values'=>$floats, 'attr'=>['type'=>'select']],
            'float_default'      => ['label'=>lang('default')."<br />", 'attr'=>['value'=>isset($props['attr']['value'])?$props['attr']['value']:'0']],
            'radio_default'      => ['label'=>$this->lang['xf_lbl_radio_default']."<br />", 'attr'=>['type'=>'textarea']],
            'checkbox_default'=> ['label'=>lang('default')."<br />",'values'=>$checks,'attr'=>['type'=>'select']]];
        switch ($props['attr']['type']) {
            case 'varchar':
            case 'text':
            case 'textarea': $fields['type']['attr']['value'] = 'text';
            case 'html':
                $fields['text_length']['attr']['value'] = isset($props['attr']['maxlength']) ? $props['attr']['maxlength'] : 32;
                // continue like link, which is just text
            case 'link_url':
            case 'link_image':
            case 'link_inventory':
//                $fields['type']['attr']['value'] = $props['attr']['type'];
                $fields['text_default']['attr']['value'] = isset($props['default']) ? $props['default'] : '';
                break;
            case 'integer':
                $data_type = (strpos($props['dbType'],'(') === false) ? strtolower($props['dbType']) : strtolower(substr($props['dbType'],0,strpos($props['dbType'],'(')));
                $fields['int_select']['attr']['value'] = $data_type;
                break;
            case 'float':
                $data_type = (strpos($props['dbType'],'(') === false) ? strtolower($props['dbType']) : strtolower(substr($props['dbType'],0,strpos($props['dbType'],'(')));
                $fields['float_select']['attr']['value'] = $data_type;
                break;
            case 'radio':
            case 'select':
            case 'checkbox_multi':
            case 'enum':
                $tmp = [];
                if (isset($props['opts'])) { foreach ($props['opts'] as $row) { $tmp[] = $row['id'].":".$row['text']; } }
                $fields['radio_default']['attr']['value'] = implode(';', $tmp);
                break;
            case 'checkbox':
                $fields['checkbox_default']['attr']['value'] = $props['default'];
                break;
            default:
        }
        $output  = html5('module',$fields['module']);
        $output .= html5('table', $fields['table']);
        $output .= html5('id',    $fields['id']);
        $output .= '<table>
         <tbody>
          <tr><td colspan="2">'.$this->lang['xf_lbl_field_tip'] .'</td></tr>
          <tr><td colspan="2">'.html5('field', $fields['field']).'</td></tr>
          <tr><td colspan="2">'.html5('label', $fields['label']).'</td></tr>
          <tr><td colspan="2">'.html5('tag',   $fields['tag'])  .'</td></tr>
          <tr><td colspan="2">'.html5('tab',   $fields['tab'])  .'</td></tr>
          <tr><td colspan="2">'.html5('group', $fields['group']).'</td></tr>
          <tr><td colspan="2">'.html5('order', $fields['order'])."</td></tr>";
// @todo ************************************* THIS NEEDS FIXIN ***********************************************//
        if (isset($viewData['options']) && is_array($viewData['options'])){
            $output .= '  <tr class="panel-header"><th colspan="2">'.lang('options')."</th></tr>";
            $output .= '  <tr><td colspan="2">'.$viewData['options']['description']."</td></tr>";
            foreach ($viewData['options']['values'] as $key => $settings) { $output .= "  <tr><td>".html5($key, $settings)."</td></tr>"; }
        }

        $output .= '  <tr class="panel-header"><th colspan="2">'.lang('attributes')."</th></tr>";

        $output .= "  <tr><td>";
        $fields['type']['label'] = $this->lang['xf_lbl_text'];
        $fields['type']['attr']['checked'] = $type=='text' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'text'; // radio button type
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_html'];
        $fields['type']['attr']['checked'] = $type=='html' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'html';
        $output .= html5('type', $fields['type'])."<br />";
        $output .= "  </td><td>";
        $output .= html5('text_length', $fields['text_length'])."<br />";
        $output .= html5('text_default', $fields['text_default']);
        $output .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';
        $output .= "  <tr><td>";
        $fields['type']['label'] = $this->lang['xf_lbl_link_url'];
        $fields['type']['attr']['checked'] = $type=='link_url' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'link_url';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_link_image'];
        $fields['type']['attr']['checked'] = $type=='link_image' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'link_image';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_link_inventory'];
        $fields['type']['attr']['checked'] = $type=='link_inventory' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'link_inventory';
        $output .= html5('type', $fields['type']);
        $output .= "  </td><td>";
        $output .= html5('link_default', $fields['link_default']);
        $output .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';
        $output .= "  <tr><td>";
        $fields['type']['label'] = $this->lang['xf_lbl_int'];
        $fields['type']['attr']['checked'] = $type=='integer' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'integer';
        $output .= html5('type', $fields['type']);
        $output .= "  </td><td>";
        $output .= html5('int_select',  $fields['int_select'])."<br />";
        $output .= html5('int_default', $fields['int_default']);
        $output .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';
        $output .= "  <tr><td>";
        $fields['type']['label'] = $this->lang['xf_lbl_float'];
        $fields['type']['attr']['checked'] = $type=='float' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'float';
        $output .= html5('type', $fields['type']);
        $output .= "  </td><td>";
        $output .= html5('float_select',  $fields['float_select'])."<br />";
        //$output .= html5('float_format',  $fields['float_format'])."<br />"; // for decimal type
        $output .= html5('float_default', $fields['float_default']);
        $output .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';
        $output .= "  <tr><td>";
        $fields['type']['label'] = $this->lang['xf_lbl_checkbox_multi'];
        $fields['type']['attr']['checked'] = $type=='checkbox_multi' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'checkbox_multi';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_select'];
        $fields['type']['attr']['checked'] = $type=='select' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'select';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_radio'];
        $fields['type']['attr']['checked'] = $type=='radio' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'radio';
        $output .= html5('type', $fields['type'])."</td>";
        $output .= "  </td><td>";
        $output .= html5('radio_default', $fields['radio_default']);
        $output .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';
        $output .= "  <tr><td>";
        $fields['type']['label'] = $this->lang['xf_lbl_checkbox'];
        $fields['type']['attr']['checked'] = $type=='checkbox' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'checkbox';
        $output .= html5('type', $fields['type']);
        $output .= "  </td><td>";
        $output .= html5('checkbox_default', $fields['checkbox_default']);
        $output .= '  </td></tr><tr><td colspan="2"><hr /></td></tr>';
        $output .= "  <tr><td>";
        $fields['type']['label'] = lang('date');
        $fields['type']['attr']['checked'] = $type=='date' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'date';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = lang('time');
        $fields['type']['attr']['checked'] = $type=='time' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'time';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_datetime'];
        $fields['type']['attr']['checked'] = $type=='datetime' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'datetime';
        $output .= html5('type', $fields['type'])."<br />";
        $fields['type']['label'] = $this->lang['xf_lbl_timestamp'];
        $fields['type']['attr']['checked'] = $type=='timestamp' ? 'checked' : false;
        $fields['type']['attr']['value'] = 'timestamp';
        $output .= html5('type', $fields['type']);
        $output .= '  </td></tr>';
        $output .= " </tbody>";
        $output .= "</table>";
        return $output;
    }

    /**
     * Method to save a new/modified custom field
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function save(&$layout=[])
    {
        $request = $_POST;
        $rID   = isset($request['id'])    ? clean($request['id'],    'integer'): 0;
        $module= isset($request['module'])? clean($request['module'],'text')   : '';
        $table = isset($request['table']) ? clean($request['table'], 'text')   : '';
        if (!$module || !$table) { return msgAdd("Module and/or table information missing!"); }
        if (!validateSecurity('bizuno', 'admin', $rID?3:2)) { return; }
        // clean out all non-allowed values and then check if we have a empty string
        $old_field = clean($request['id'], 'text'); // get the old field name
        $new_field = preg_replace("[^A-Za-z0-9_]", "", clean($request['field'], 'text'));
        if ($new_field == '') { return msgAdd(lang('err_field_empty')); }
        if (!$old_field || $old_field <> $new_field) { // changed field name
            $exists = dbFieldExists(BIZUNO_DB_PREFIX.$table, $new_field);
            if ($exists) { return msgAdd(lang('xf_err_field_exists')); }
        }
        $type   = clean('type', ['format'=>'text', 'default'=>'text'], 'post');
        $comment= [];
        switch ($type) {
            default:
            case 'html':
            case 'text':
                $length = clean('text_length', 'integer', 'post');
                if ($length < 1) { $length = '32'; }
                $default = clean('text_default', 'text', 'post');
                if ($length < 256)          { $fieldType = "VARCHAR($length) DEFAULT '".addslashes($default)."'"; }
                elseif ($length < 65536)    { $fieldType = 'TEXT'; }
                elseif ($length < 16777216) { $fieldType = 'MEDIUMTEXT'; }
                else                        { $fieldType = 'LONGTEXT'; }
                if     ($type=='html') { $comment[] = 'type:html'; }
                elseif ($length > 256) { $comment[] = 'type:textarea'; }
                break;
            case 'link_url':       $comment[] ='type:linkurl';
            case 'link_image':     if (sizeof($comment)==0) { $comment[] ='type:linkimg'; }
            case 'link_inventory': if (sizeof($comment)==0) { $comment[] ='type:linkinv'; }
                $default   = clean('link_default', 'text', 'post');
                $fieldType = "VARCHAR(255) DEFAULT '".addslashes($default)."'";
                break;
            case 'integer': $comment[] ='type:integer';
                $select  = clean('int_select',  'text', 'post');
                $default = clean('int_default','integer', 'post');
                switch ($select) {
                    case 'tinyint':   $fieldType = "TINYINT DEFAULT '$default'";  break;
                    case 'smallint':  $fieldType = "SMALLINT DEFAULT '$default'"; break;
                    case 'mediumint': $fieldType = "MEDIUMINT DEFAULT '$default'";break;
                    default:
                    case 'int':       $fieldType = "INT DEFAULT '$default'";      break;
                    case 'bigint':    $fieldType = "BIGINT DEFAULT '$default'";   break;
                }
                break;
            case 'float': $comment[] ='type:float';
                $select  = clean('float_select', 'text', 'post');
//                $format  = clean($request['float_format'], 'text');
                $default = clean('float_default','float', 'post');
                switch ($select) {
                    default:
                    case 'float':  $fieldType = "FLOAT DEFAULT '$default'"; break;
                    case 'double': $fieldType = "DOUBLE DEFAULT '$default'";break;
//                    case 2: $fieldType = "DECIMAL($format) DEFAULT '$default'";break;
                }
                break;
            case 'select':         $comment[] ='type:select'; // default selection is asssumed to be listed first
            case 'radio':          if (sizeof($comment)==0) { $comment[] ='type:radio'; }
            case 'checkbox_multi': if (sizeof($comment)==0) { $comment[] ='type:checkbox_multi'; }
                $default = clean('radio_default', 'text', 'post');
                $choices = explode(';', $default);
                $keys    = [];
                $values  = [];
                foreach ($choices as $choice) {
                    $pairs = explode(':', $choice, 2);
                    $keys[]   = trim($pairs[0]);
                    $values[] = isset($pairs[1]) ? trim($pairs[1]) : trim($pairs[0]);
                }
                $fieldType = "ENUM('".implode("','", $keys)."') DEFAULT '{$keys[0]}'";
                $comment[] = "opts:".implode(":", $values);
                break;
            case 'checkbox': $comment[] ='type:checkbox';
                $select    = clean('checkbox_default', 'char', 'post');
                $fieldType = "ENUM('0','1') DEFAULT '$select'";
                break;
            case 'date':      $fieldType = "DATE";     break;
            case 'time':      $fieldType = "TIME";     break;
            case 'datetime':  $fieldType = "DATETIME"; break;
            case 'timestamp': $fieldType = "TIMESTAMP";break;
        }
        $order= clean('order','integer', 'post');
        $tab  = clean('tab',  'integer', 'post');
        if ($order) { $comment[] = "order:$order"; }
        if ($tab)   { $comment[] = "tab:$tab"; }
        if (isset($request['label']) && strlen($request['label']) > 0) {
            $label = str_replace([':',';'], ['.',','], clean($request['label'],'text'));
            $comment[] = "label:$label";
        }
        if (isset($request['tag']) && strlen($request['tag']) > 0) {
            $tag = str_replace([':',';'], ['.',','], clean($request['tag'],'text'));
            $comment[] = "tag:$tag";
        }
        if (isset($request['group']) && strlen($request['group']) > 0) {
            $group = str_replace([':',';'], ['.',','], clean($request['group'],'text'));
            $comment[] = "group:$group";
        }
        $cmt = addslashes(implode(";",$comment));
        if ($old_field) { $sql = "ALTER TABLE `".BIZUNO_DB_PREFIX."$table` CHANGE `$old_field` `$new_field` $fieldType COMMENT '$cmt'"; }
        else      { $sql = "ALTER TABLE `".BIZUNO_DB_PREFIX."$table` ADD COLUMN `$new_field` $fieldType COMMENT '$cmt'"; }
        dbGetResult($sql);
        msgAdd(lang('extra_fields')." (".($old_field?lang('update'):lang('add')).") ".lang('msg_database_write'), 'success');
        msgLog(lang('extra_fields')." (".($old_field?lang('update'):lang('add')).") $table.$new_field");
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#accFields').accordion('select', 0); jq('#dgFields').datagrid('reload'); jq('#detail').html('&nbsp;');"]]);
    }

    /**
     * Method to delete a custom field
     * @param array $layout - structure coming in
     * @return modified $structure
     */
    public function delete(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'admin', 4)) { return; }
        $table = clean('table','text', 'get');
        $field = clean('data', 'text', 'get');
        if (!$table || !$field) { return msgAdd("Table and/of field information missing!"); }
        if ($field) {
            msgLog(lang('extra_tabs')." (".lang('delete').") $table.$field");
            $layout = array_replace_recursive($layout, ['content' => ['action'=>'eval','actionData'=>"jq('#dgFields').datagrid('reload');"],
                'dbAction'=> [BIZUNO_DB_PREFIX.'TBD' => "ALTER TABLE `".BIZUNO_DB_PREFIX."$table` DROP COLUMN `$field`"]]);
        }
    }
}
