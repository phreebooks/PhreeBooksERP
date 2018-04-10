<?php
/*
 * Administration methods for the contacts module
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
 * @version    2.x Last Update: 2018-02-09
 * @filesource /lib/controller/module/contacts/admin.php
 */

namespace bizuno;

class contactsAdmin
{
	public $moduleID = 'contacts';

	function __construct()
    {
		$this->lang     = getLang($this->moduleID);
		$this->settings = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
		$this->structure= [
            'url'        => BIZUNO_URL."controller/module/$this->moduleID/",
            'version'    => MODULE_BIZUNO_VERSION,
			'category'   => 'bizuno',
			'required'   => '1',
			'attachPath' => 'data/contacts/uploads/',
			'quickBar'   => ['child'=>['home'=>['child'=>[
                'mgr_e'    => ['order'=>45,'label'=>lang('employees'),'icon'=>'employee','events'=>['onClick'=>"hrefClick('contacts/main/manager&type=e');"]]]]]],
			'menuBar'    => ['child'=>[
                'customers'=> ['order'=>10,'label'=>lang('customers'),'group'=>'cust','icon'=>'sales','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=customers');"],'child'=>[
                    'mgr_c'=> ['order'=>10,'label'=>lang('contacts_type_c_mgr'),'icon'=>'users',  'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=c');"]],
                    'mgr_i'=> ['order'=>20,'label'=>lang('contacts_type_i_mgr'),'icon'=>'chat',   'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=i');"]],
                    'rpt_c'=> ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc','events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=cust');"]]]],
				'vendors'  => ['order'=>20,'label'=>lang('vendors'),'group'=>'vend','icon'=>'purchase','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=vendors');"],'child'=>[
                    'mgr_v'=> ['order'=>20,'label'=>lang('contacts_type_v_mgr'),'icon'=>'users',  'events'=>['onClick'=>"hrefClick('contacts/main/manager&type=v');"]],
                    'rpt_v'=> ['order'=>99,'label'=>lang('reports'),            'icon'=>'mimeDoc','events'=>['onClick'=>"hrefClick('phreeform/main/manager&gID=vend');"]]]]]],
			'crm_actions'=> [
                'new' =>$this->lang['contacts_crm_new_call'],
				'ret' =>$this->lang['contacts_crm_call_back'],
				'flw' =>$this->lang['contacts_crm_follow_up'],
				'lead'=>$this->lang['contacts_crm_new_lead'],
				'inac'=>lang('inactive')],
			'hooks' => ['phreebooks'=>['tools'=>[
                'fyCloseHome'=> ['page'=>'tools', 'class'=>'contactsTools', 'order'=>50],
                'fyClose'    => ['page'=>'tools', 'class'=>'contactsTools', 'order'=>50]]]],
			'api' => ['path'=>'contacts/api/contactsAPI']];
		$this->phreeformProcessing = [
            'contactID'  => ['text'=>lang('contacts_short_name'),      'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat'],
			'contactName'=> ['text'=>lang('address_book_primary_name'),'group'=>$this->lang['title'],'module'=>'bizuno','function'=>'viewFormat']];
	}

	/**
     * Sets the structure of the user settings for the contacts module
     * @return array - user settings 
     */
    public function settingsStructure()
    {
		$data = [
            'general' => ['auto_add'=>['attr'=>['type'=>'selNoYes', 'value'=>'0']]],
            'address_book' => [
                'primary_name'=> ['attr'=>['type'=>'selNoYes', 'value'=>'1']],
                'address1'    => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'city'        => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'state'       => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'postal_code' => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'telephone1'  => ['attr'=>['type'=>'selNoYes', 'value'=>'0']],
                'email'       => ['attr'=>['type'=>'selNoYes', 'value'=>'0']]]];
		settingsFill($data, $this->moduleID);
		return $data;
	}

	/**
     * Builds the home menu for settings of the contacts module
     * @param array $layout - current working structure
     * @return modified $layout
     */
    public function adminHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        $clnDefault = viewFormat(localeCalculateDate(date('Y-m-d'), 0, -1), 'date');
        $data = [
            'tabs' => ['tabAdmin'=> ['divs'=>  [
                'settings'=> ['order'=>20,'label'=>lang('settings'),'src'=>BIZUNO_LIB."view/module/bizuno/tabAdminSettings.php"],
				'fields'  => ['order'=>50,'label'=>lang('extra_fields'),'type'=>'html', 'html'=>'',
                    'attr'=> ["data-options"=>"href:'".BIZUNO_AJAX."&p=bizuno/fields/manager&module=$this->moduleID&table=contacts'"]],
                'tabDBs'  => ['order'=>70,'label'=>lang('dashboards'),'attr'=>['module'=>$this->moduleID,'type'=>'dashboards'],'src'=>BIZUNO_LIB."view/module/bizuno/tabAdminMethods.php"],
				'tools'   => ['order'=>80,'label'=>lang('tools'),   'src'=>BIZUNO_LIB."view/module/contacts/tabSettingsTools.php"]]]],
			'fields' => [
                'dateJ9Close'=> ['label'=>'','classes'=>['easyui-datebox'], 'attr'=>['data-options'=>"{value:'$clnDefault'}"]],
                'btnJ9Close' => ['label'=>'', 'events'=> ['onClick' => "jq('body').addClass('loading'); jsonAction('contacts/tools/j9Close', 0, jq('#dateJ9Close').datebox('getValue'));"],
                    'attr'=>  ['type'=>'button', 'value'=>lang('start')]],
                'btnSyncAttach' => ['label'=>'', 'events'=> ['onClick' => "jq('body').addClass('loading'); jsonAction('contacts/tools/syncAttachments&verbose=1');"],
                    'attr'=>  ['type'=>'button', 'value'=>lang('go')]]],
            'lang' => $this->lang];
		$layout = array_replace_recursive($layout, adminStructure($this->moduleID, $this->settingsStructure(), $this->lang), $data);
	}

	/**
     * Saves the users settings 
     */
    public function adminSave()
    {
		readModuleSettings($this->moduleID, $this->settings);
	}
}
