<?php
/*
 * Handles business messages from PhreeSoft
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
 * @version    3.x Last Update: 2018-10-30
 * @filesource /lib/controller/module/bizuno/messages.php
 */

namespace bizuno;

class bizunoMessages
{
    public $moduleID = 'bizuno';

    function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
    /**
     * Message manager main entry point
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function manager(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'message', 1)) { return; }
        $title = lang('messages');
        $layout = array_replace_recursive($layout, viewMain(), [
            'title'=> $title,
            'divs'     => [
                'heading' => ['order'=>30,'type'=>'html',     'html'=>"<h1>$title</h1>\n"],
                'phreemsg'=> ['order'=>60,'type'=>'accordion','key'=>'accMessage']],
            'accordion'=> ['accMessage'=>['divs'=>[
                'divMessageManager'=>['order'=>30,'label'=>lang('manager'),'type'=>'datagrid','key'=>'dgMessage'],
//                'divMessageDetail' =>['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;'],
                ]]],
            'datagrid' => ['dgMessage' => $this->dgMessage('dgMessage', $security)]]);
    }

    /**
     * List messages currently stored in the db
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function managerRows(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'message', 1)) { return; }
        $structure = $this->dgMessage('dgMessage', $security);
        $data = ['type'=>'datagrid', 'structure'=>$structure];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Datagrid structure for messages manager
     * @param string $name - DOM id of the datagrid
     * @param integer $security - security setting for the user
     * @return array - datagrid structure
     */
    private function dgMessage($name, $security=0)
    {
        return ['id' =>$name,
            'attr'   =>['type'=>'table','idField'=>'id','url'=>BIZUNO_AJAX."&p=bizuno/messages/managerRows"],
            'events' =>[
                'rowStyler'    => "function(index, row) { if (row.status=='0') { return {class:'row-default'}; }}",
                'onDblClickRow'=> "function(rowIndex, rowData) { jsonAction('bizuno/messages/read&host=".BIZUNO_HOST."', rowData.id); }"],
            'source' =>['tables'=>['phreemsg'=>['table'=>BIZUNO_DB_PREFIX."phreemsg"]]],
            'columns'=>['id'=>['order'=>0,'field'=>BIZUNO_DB_PREFIX."phreemsg.id",    'attr'=>  ['hidden'=>true]],
                'status' => ['order'=>0,'field'=>BIZUNO_DB_PREFIX."phreemsg.status",'attr'=>  ['hidden'=>true]],
                'action' => ['order'=>1,'label'=>lang('action'),'events'=>['formatter'=>$name.'Formatter'],
                    'actions'=> [
                        'read'  => ['icon'=>'email', 'size'=>'small', 'order'=>20,'events'=>['onClick'=>"jsonAction('bizuno/messages/read&host=".BIZUNO_HOST."', idTBD);"]],
                        'delete'=> ['icon'=>'trash', 'size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
                            'events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/messages/delete', idTBD);"]]]],
                'post_date'=> ['order'=>10, 'field'=>BIZUNO_DB_PREFIX."phreemsg.post_date", 'label'=>lang('date'),
                    'attr' => ['width'=>60, 'sortable'=>true, 'resizable'=>true]],
                'subject'  => ['order'=>20, 'field'=>BIZUNO_DB_PREFIX."phreemsg.subject", 'label'=>lang('subject'),
                    'attr' => ['width'=>200,'sortable'=>true, 'resizable'=>true]]]];
    }

    /**
     * Structure to handle editing messages
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function read(&$layout=[])
    {
        global $io;
        if (!$security = validateSecurity('bizuno', 'message', 1)) { return; }
        $rID = clean('rID', 'integer', 'get');
        $row = dbGetRow(BIZUNO_DB_PREFIX.'phreemsg', "id=$rID");
        if (!$row) { return msgAdd('Bad data!'); }
        if (!empty($row['body'])) { // message is here, just show it
            $response['sysMsg'] = $row['body'];
        } else { // go the PhreeSoft servers to try to retrieve it
            $response = $io->apiPhreeSoft('getSysMessage', ['msgID'=>$row['msg_id']]);
            if (!$response || !isset($response['sysMsg'])) { $response = ['sysMsg'=>"Error communicating with PhreeSoft servers! Please check your account settings."]; }            
        }
        msgDebug("\nReady to generate window with SysMessage: ".print_r($response, true));
        dbWrite(BIZUNO_DB_PREFIX.'phreemsg', ['status'=>'1'], 'update', "id=$rID"); // set status to read, lower new message count
        $quickBar = getUserCache('quickBar');
        if (!empty($quickBar['child']['sysMsg']['attr']['value'])) { 
            $quickBar['child']['sysMsg']['attr']['value']--;
            if ($quickBar['child']['sysMsg']['attr']['value'] < 1) { // prevents showing 0
                unset($quickBar['child']['sysMsg']['attr']['value']);
                $newCnt = '';
            } else {
                $newCnt = $quickBar['child']['sysMsg']['attr']['value'];
            }
            setUserCache('quickBar', false, $quickBar);
        }
        $data = ['type'=>'popup','title'=>$row['subject'],'attr'=>['id'=>'winSysMsg'],
            'divs'   =>['body'=>['order'=>50,'type'=>'html','html'=>$response['sysMsg']]],
            'jsReady'=>['init'=>"jq('#sysMsg').html('$newCnt'); jq('#dgMessage').datagrid('reload');"]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Structure to delete a message
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function delete(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'message', 4)) { return; }
        $rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('err_delete_name_prompt')); }
        $msgCnt = '';
        $row    = dbGetRow(BIZUNO_DB_PREFIX."phreemsg", "id=$rID");
        $quickBar = getUserCache('quickBar');
        if (!$row['status'] && !empty($quickBar['child']['sysMsg']['attr']['value'])) { 
            $quickBar['child']['sysMsg']['attr']['value']--;
            if ($quickBar['child']['sysMsg']['attr']['value'] < 1) { // prevents showing 0
                unset($quickBar['child']['sysMsg']['attr']['value']);
                $msgCnt = "jq('#sysMsg').html(''); ";
            } else {
                $msgCnt = "jq('#sysMsg').html('{$quickBar['child']['sysMsg']['attr']['value']}'); ";
            }
            setUserCache('quickBar', false, $quickBar);
        }
        $title = $row['msg_id'];
        dbGetResult("DELETE FROM ".BIZUNO_DB_PREFIX."phreemsg"." WHERE id=$rID");
        msgLog(lang('message').' - '.lang('delete')." $title ($rID)");
        $data  = ['content'=>['action'=>'eval','actionData'=>$msgCnt."jq('#dgMessage').datagrid('reload');"]];
        $layout= array_replace_recursive($layout, $data);
    }
}
