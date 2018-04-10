<?php
/*
 * Module Bizuno main methods
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
 * @filesource /lib/controller/module/bizuno/main.php
 * 
 * @todo BUG - 
 * when lowering number of columns, dashboards in removed columns no longer show, need to add to last current column
 * context sensitive help, look at current module, page, method to send to Phreehelp
 */

namespace bizuno;

class bizunoMain
{
	public $moduleID = 'bizuno';

	function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
	/**
     * generates the structure for the home page and any main menu dashboard page
     * @param array $layout - structure coming in
     */
    public function BizunoHome(&$layout) 
    {
        $defMenu= getUserCache('profile', 'admin_id', false, 0) ? 'home' : 'portal';
        $menu_id= clean('menuID', ['format'=>'text','default'=>$defMenu], 'get');
		$cols   = getUserCache('profile', 'columns', false, 3);
		$title  = getModuleCache('bizuno', 'settings', 'company', 'primary_name', getUserCache('profile', 'biz_title'));
		$layout = array_replace_recursive(viewMain(), [
            'pageTitle'=> "$title - ".getModuleCache('bizuno', 'properties', 'title'),
			'menu_id'  => $menu_id,
			'divs'     => ['dashboard'=>['order'=>50,'type'=>'template','src'=>BIZUNO_LIB."view/module/bizuno/divDashboard.php"]],
            'lang'     =>['msg_add_dashboards'=>$this->lang['msg_add_dashboards']]]);
	}
	
	/**
     * Used to refresh session timer to keep log in alive. Forces log out after 8 hours if no user actions are detected.
     */
    public function sessionRefresh(&$layout) {
    } // nothing to do, just reset session clock

	/**
     * Loads the countries from the locales.xml file into an array to use on processing
     * @param array $layout - structure coming in
     */
    public function countriesLoad(&$layout) {
        $temp = localeLoadDB();
		$output = [];
        foreach ($temp->country as $value) { $output[] = ['id' => $value->iso3, 'text'=> $value->name]; }
		$layout = array_replace_recursive($layout, ['type'=>'raw', 'content'=>json_encode($output)]);
	}

	/**
     * generates the pop up encryption form
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function encryptionForm(&$layout) {
        if (!validateSecurity('bizuno', 'profile', 1)) { return; }
		$icnSave= ['icon'=>'save', 'label'=>lang('save'), 'events'=>  ['onClick'=>"jsonAction('bizuno/main/encryptionSet', 0, jq('#pwEncrypt').val());"]];
		$inpEncr= ['attr'=>['type'=>'password']];
		$html   = lang('msg_enter_encrypt_key').'<br />'.html5('pwEncrypt', $inpEncr).html5('', $icnSave);
		$js     = "jq('#pwEncrypt').focus();
jq('#winEncrypt').keypress(function(event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode=='13') jsonAction('bizuno/main/encryptionSet', 0, jq('#pwEncrypt').val());
});";
        $html .= htmlJS($js);
		$layout = array_replace_recursive($layout, ['type'=>'divHTML','divs'=>  ['divEncrypt'=>  ['order'=>50,'type'=>'html','html'=>$html]]]);
	}
	
	/**
     * Validates and sets the encryption key, if successful
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function encryptionSet(&$layout)
    {
        if (!validateSecurity('bizuno', 'profile', 1)) { return; }
		$error  = false;
		$key    = clean('data', 'password', 'get');
		$encKey = getModuleCache('bizuno', 'encKey', false, '');
        if (!$encKey) { msgAdd($this->lang['err_encryption_not_set']); }
		if ($key && $encKey) {
			$stack = explode(':', $encKey);
            if (sizeof($stack) != 2) { $error = true; }
            if (md5($stack[1] . $key) <> $stack[0]) { $error = true; }
        } else { $error = true; }
        if ($error) { return msgAdd(lang('err_login_failed')); }
		setUserCache('profile', 'admin_encrypt', $key);
        $qlinks = getUserCache('quickBar');
        unset($qlinks['child']['encrypt']);
        setUserCache('quickBar', false, $qlinks);
		$layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 
            'actionData'=>"jq('#winEncrypt').window('close'); jq('#ql_encrypt').hide();"]]);
	}
	
	/*
     * Downloads a file to the user
     */
    public function fileDownload()
    {
        $path = clean('pathID', 'path', 'get');
		$file = clean('fileID', 'file', 'get');
		$parts = explode(":", $file, 2);
		if (sizeof($parts) > 1) { // if file contains a prefix the format will be: prefix:prefixFilename
			$dir  = $path.$parts[0];
			$file = str_replace($parts[0], '', $parts[1]);
		} else {
			$dir  = $path;
			$file = $file;
		}
		msgLog(lang('download').' - '.$file);
		msgDebug("\n".lang('download').' - '.$file);
		$output = new io();
		$output->download('file', $dir, $file);
	}

	/**
     * Deletes a file from the myBiz folder
     * @param array $layout - structure coming in
     */
    public function fileDelete(&$layout=[])
    {
        $dgID = clean('rID', 'text', 'get');
		$file = clean('data','text', 'get');
		$output = new io();
        msgDebug("\nDeleting dgID = $dgID and file = $file");
		$output->fileDelete($file);
		msgLog(lang('delete').' - '.$file);
		msgDebug("\n".lang('delete').' - '.$file);
		$layout = array_replace_recursive($layout, ['content'=>['action'=>'eval', 
			'actionData'=>"var row=jq('#$dgID').datagrid('getSelected'); 
var idx=jq('#$dgID').datagrid('getRowIndex', row);
jq('#$dgID').datagrid('deleteRow', idx);"]]);
	}
}
