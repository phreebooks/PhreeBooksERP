<?php
/*
 * Functions to support user operations
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
 * @version    2.x Last Update: 2018-02-18
 * @filesource lib/controller/module/bizuno/users.php
 */

namespace bizuno;

require_once(BIZUNO_LIB."controller/module/bizuno/functions.php");
require_once(BIZUNO_ROOT."portal/guest.php");

class bizunoUsers
{
	public $moduleID = 'bizuno';

	function __construct()
    {
        $this->lang = getLang($this->moduleID);
    }
    
	/**
     * Main entry point structure for Bizuno users
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function manager(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'users', 1)) { return; }
		$title = lang('bizuno_users');
		$layout = array_replace_recursive($layout, viewMain(), [
            'pageTitle'=> $title,
			'divs' => [
                'heading'=> ['order'=>30, 'type'=>'html',     'html'=>"<h1>$title</h1>\n"],
				'roles'  => ['order'=>60, 'type'=>'accordion','key' =>'accUsers']],
			'accordion'=> ['accUsers'=>  ['divs'=>  [
                'divUsersManager'=> ['order'=>30,'label'=>lang('manager'),'type'=>'datagrid','key'=>'dgUsers'],
				'divUsersDetail' => ['order'=>70,'label'=>lang('details'),'type'=>'html','html'=>'&nbsp;']]]],
			'datagrid'=> ['dgUsers' => $this->dgUsers('dgUsers', $security)]]);
	}

	/**
     * Lists the users with applied filters from user
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function managerRows(&$layout)
    {
        if (!$security = validateSecurity('bizuno', 'users', 1)) { return; }
		$structure = $this->dgUsers('dgUsers', $security);
		$data = ['type'=>'datagrid', 'structure'=>$structure];
        $layout = array_replace_recursive($layout, $data);
	}
    
    /**
     * saves user selections in cache for page re-entry
     */
    private function managerSettings()
    {
		$data = ['path'=>'bizunoUsers','values' => [
            ['index'=>'rows',  'clean'=>'integer','default'=>getModuleCache('bizuno', 'settings', 'general', 'max_rows')],
            ['index'=>'page',  'clean'=>'integer','default'=>'1'],
            ['index'=>'sort',  'clean'=>'text',   'default'=>BIZUNO_DB_PREFIX."users.title"],
            ['index'=>'order', 'clean'=>'text',   'default'=>'ASC'],
            ['index'=>'f0',    'clean'=>'char',   'default'=>'y'],
            ['index'=>'search','clean'=>'text',   'default'=>'']]];
		$this->defaults = updateSelection($data);
	}

	/**
     * Datagrid structure for Bizuno users
     * @param string $name - DOM field name
     * @param integer $security - users defined security level
     * @return array - datagrid structure
     */
    private function dgUsers($name, $security=0) 
    {
		$this->managerSettings();
		$yes_no_choices = [['id'=>'a','text'=>lang('all')], ['id'=>'y','text'=>lang('active')], ['id'=>'n','text'=>lang('inactive')]];
		// clean up the filter sqls
        if (!isset($this->defaults['f0'])) { $this->defaults['f0'] = 'y'; }
		switch ($this->defaults['f0']) {
			default:
			case 'a': $f0_value = ""; break;
			case 'y': $f0_value = BIZUNO_DB_PREFIX."users.inactive<>'1'"; break;
			case 'n': $f0_value = BIZUNO_DB_PREFIX."users.inactive='1'";  break;
		}
        return [
            'id'    => $name,
			'strict'=> true, // forces limit of the fields read to columns listed, roles inactive is overwriting users inactive
			'rows'  => $this->defaults['rows'],
			'page'  => $this->defaults['page'],
			'attr'  => [
                'url'     => BIZUNO_AJAX."&p=bizuno/users/managerRows",
				'toolbar' => '#'.$name.'Toolbar',
				'pageSize'=> getModuleCache('bizuno', 'settings', 'general', 'max_rows'),
				'idField' => 'admin_id'],
			'events' => [
                'rowStyler'    => "function(index, row) { if (row.inactive == '1') { return {class:'row-inactive'}; }}",
				'onDblClickRow'=> "function(rowIndex, rowData){ accordionEdit('accUsers', 'dgUsers', 'divUsersDetail', '".lang('details')."', 'bizuno/users/edit', rowData.admin_id); }"],
			'source' => [
                'tables' => [
                    'users' => ['table'=>BIZUNO_DB_PREFIX."users", 'join'=>'',    'links'=>''],
					'roles' => ['table'=>BIZUNO_DB_PREFIX."roles", 'join'=>'join','links'=>BIZUNO_DB_PREFIX."roles.id=".BIZUNO_DB_PREFIX."users.role_id"]],
				'actions' => [
                    'newUser'  => ['order'=>10, 'html'=>  ['icon'=>'new',  'events'=>  ['onClick'=>"accordionEdit('accUsers', 'dgUsers', 'divUsersDetail', '".lang('details')."', 'bizuno/users/edit', 0);"]]],
					'clrSearch'=> ['order'=>50, 'html'=>  ['icon'=>'clear','events'=>  ['onClick'=>"jq('#search').val(''); ".$name."Reload();"]]]],
				'search' => [BIZUNO_DB_PREFIX."users.email", BIZUNO_DB_PREFIX."roles".'.title'],
				'sort'   => ['s0'=>  ['order'=>10, 'field'=>($this->defaults['sort'].' '.$this->defaults['order'])]],
				'filters'=> [
                    'f0' => ['order'=>10,'sql'=>$f0_value,'html' => ['label'=>lang('status'), 'values'=>$yes_no_choices, 'attr'=>  ['type'=>'select', 'value'=>$this->defaults['f0']]]],
                    'search' => ['order'=>90, 'html'=>  ['label'=>lang('search'), 'attr'=>  ['value'=>$this->defaults['search']]]]]],
			'columns' => [
                'admin_id'=> ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."users.admin_id",'attr'=>  ['hidden'=>true]],
				'inactive'=> ['order'=>0, 'field'=>BIZUNO_DB_PREFIX."users.inactive",'attr'=>  ['hidden'=>true]],
				'action'  => ['order'=>1, 'label'=>lang('action'), 'attr'=>  ['width'=>100], 'events'=>  ['formatter'=>$name.'Formatter'],
					'actions'=> [
						'edit' => ['icon'=>'edit', 'size'=>'small', 'order'=>20,
							'events'=>  ['onClick'=>"accordionEdit('accUsers', 'dgUsers', 'divUsersDetail', '".lang('details')."', 'bizuno/users/edit', idTBD);"]],
						'copy' => ['icon'=>'copy', 'size'=>'small', 'order'=>40,
							'events'=> ['onClick'=>"var title=prompt('".lang('msg_copy_name_prompt')."'); jsonAction('bizuno/users/copy', idTBD, title);"]],
						'delete' => ['icon'=>'trash', 'size'=>'small', 'order'=>90, 'hidden'=>$security>3?false:true,
							'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/users/delete', idTBD);"]],
                            ],
                    ],
				'email'   => ['order'=>10, 'field' => BIZUNO_DB_PREFIX."users.email", 'label'=>pullTableLabel(BIZUNO_DB_PREFIX."users", 'email'),
					'attr'=> ['width'=>120, 'sortable'=>true, 'resizable'=>true]],
				'title'   => ['order'=>20, 'field' => BIZUNO_DB_PREFIX."users.title", 'label'=>lang('title'),
					'attr'=> ['width'=>120, 'sortable'=>true, 'resizable'=>true]],
				'role_id' => ['order'=>30, 'field' => BIZUNO_DB_PREFIX."roles.title", 'label'=>lang('role'),
					'attr'=> ['width'=>120, 'sortable'=>true, 'resizable'=>true]]]];
	}

    /**
     * structure to edit a user
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function edit(&$layout)
    {
		$rID = clean('rID', 'integer', 'get');
        if (!$security = validateSecurity('bizuno', 'users', $rID?3:2)) { return; }
		$positions = [['id'=>'top','text'=>lang('top')], ['id'=>'left','text'=>lang('left')]];
        $stores = getModuleCache('bizuno', 'stores');
        array_unshift($stores, ['id'=>-1, 'text'=>lang('all')]);
		$data = ['type'=>'divHTML',
			'divs'    => ['detail'=> ['order'=>10, 'src'=>BIZUNO_LIB."view/module/bizuno/accUsersDetail.php"]],
			'toolbar' => ['tbUsers'=>  ['icons' => [
				'save'=> ['order'=>20,'hidden'=>$security>1?'0':'1',   'events'=>  ['onClick'=>"jq('#frmUsers').submit();"]],
                'new' => ['order'=>40,'hidden'=>$security>1?false:true,'events'=>  ['onClick'=>"accordionEdit('accUsers', 'dgUsers', 'divUsersDetail', '".jsLang('details')."', 'bizuno/users/edit', 0);"]],
				'help'=> ['order'=>99,'index' =>'']]]],
			'tabs'=> ['tabUsers'=>  ['divs'=>  [
                'general' => ['order'=>10,'label'=>lang('general'), 'src'=>BIZUNO_LIB."view/module/bizuno/tabUsersGeneral.php"]]]],
			'form'  => ['frmUsers'=>  ['attr'=>  ['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/users/save"]]],
			'fields'=> dbLoadStructure(BIZUNO_DB_PREFIX."users"),
			'text'  => ['pw_title' => $rID ? lang('password_lost') : lang('password')]];
		// merge data with structure
		$dbData = $rID ? dbGetRow(BIZUNO_DB_PREFIX."users", "admin_id='$rID'") : ['settings'=>''];
		$usrSettings = json_decode($dbData['settings'], true);
        $settings = isset($usrSettings['profile']) ? $usrSettings['profile'] : [];
        msgDebug("\nSettings decoded is: ".print_r($settings, true));
		unset($dbData['settings']);
		dbStructureFill($data['fields'], $dbData);
		$data['pageTitle'] = lang('bizuno_users').' - '.($rID ? $dbData['email'] : lang('new'));
		// set some special cases
		$data['fields']['contact_id']['label'] = lang('contacts_rep_id_i');
		$data['fields']['password_new']     = ['label'=>lang('password_new'),    'attr'=>  ['type'=>'password']];
		$data['fields']['password_confirm'] = ['label'=>lang('password_confirm'),'attr'=>  ['type'=>'password']];
		$data['fields']['role_id']['attr']['type']= 'select';
        $data['fields']['role_id']['values']      = listRoles(true, false);
		$data['fields']['theme']   = ['label'=>lang('theme'),   'values'=>viewKeyDropdown(adminThemes()),'position'=>'after','attr'=>['type'=>'select', 'value'=>isset($settings['theme'] )?$settings['theme'] :'default']];
		$data['fields']['colors']  = ['label'=>lang('style'),   'values'=>viewKeyDropdown(themeColors()),'position'=>'after','attr'=>['type'=>'select', 'value'=>isset($settings['colors'])?$settings['colors']:'default']];
		$data['fields']['menu']    = ['label'=>lang('menu_pos'),'values'=>$positions,				     'position'=>'after','attr'=>['type'=>'select', 'value'=>isset($settings['menu'])  ?$settings['menu']  :'default']];
		$data['fields']['cols']    = ['label'=>$this->lang['dashboard_columns'],					     'position'=>'after','attr'=>['value'=>isset($settings['cols'])  ?$settings['cols']  :'3']];
		$data['fields']['store_id']= ['label'=>$this->lang['store_id'],'values'=>getModuleCache('bizuno', 'stores'),'position'=>'after','attr'=>['type'=>'select', 'value'=>isset($settings['store_id'])?$settings['store_id'] :'0']];
		$data['fields']['restrict_store']= ['label'=>$this->lang['restrict_store'],'values'=>$stores,'position'=>'after','attr'=>['type'=>'select', 'value'=>isset($settings['restrict_store'])?$settings['restrict_store'] :'-1']];
		$data['fields']['restrict_user'] = ['label'=>$this->lang['restrict_user'], 'position'=>'after','attr'=>['type'=>'selNoYes', 'value'=>isset($settings['restrict_user'])?$settings['restrict_user'] :'0']];
        $data['attachPath']        = getModuleCache('bizuno', 'properties', 'usersAttachPath');
		$data['attachPrefix']      = $rID.'-';
		$data['settings']          = $usrSettings; // pass for customization
        $layout = array_replace_recursive($layout, $data);
	}

	/**
	 * This method saves the users data and updates the portal if required.
	 * @return Post save action, refresh datagrid, clear form
	 */
	public function save(&$layout=[])
    {
        $rID  = clean('admin_id','integer','post');
        $email= clean('email',   'email',  'post');
        if (!$security = validateSecurity('bizuno', 'users', $rID?3:2)) { return; }
		$values = requestData(dbLoadStructure(BIZUNO_DB_PREFIX."users"));
		if (!$rID) {
			$dup = dbGetValue(BIZUNO_DB_PREFIX."users", 'admin_id', "email='".addslashes($email)."' AND admin_id<>$rID");
            if ($dup) { return msgAdd(lang('error_duplicate_id')); }
            $oldEmail = false;
		} else {
            $oldEmail = dbGetValue(BIZUNO_DB_PREFIX."users", 'email', "admin_id=$rID");
        }
        if (!isset($values['role_id']) || !$values['role_id']) { return msgAdd($this->lang['err_role_undefined']); }
		// build the users default settings
		$settings = $rID ? json_decode(dbGetValue(BIZUNO_DB_PREFIX."users", 'settings', "admin_id=$rID"), true) : [];
		$settings['profile']['theme']         = clean('theme', 'text',   'post');
		$settings['profile']['colors']        = clean('colors','text',   'post'); // theme colors scheme
		$settings['profile']['menu']          = clean('menu',  'text',   'post'); // menu position
		$settings['profile']['cols']          = clean('cols',  'integer','post'); // dashboard columns
		$settings['profile']['store_id']      = clean('store_id','integer','post'); // home store for granularity
		$settings['profile']['restrict_store']= clean('restrict_store','integer','post'); // restrict to store
		$settings['profile']['restrict_user'] = clean('restrict_user', 'integer','post'); // restrict user
		$values['settings']  = json_encode($settings);
		// update the local users table with details
		$newID = dbWrite(BIZUNO_DB_PREFIX."users", $values, $rID?'update':'insert', "admin_id='$rID'");
        // check role attributes for more processing
        $role = dbGetRow(BIZUNO_DB_PREFIX."roles", "id={$values['role_id']}");
        msgDebug("\nRead role = ".print_r($role, true));
        $role['settings'] = json_decode($role['settings'], true);
        if (!$values['inactive'] && (!isset($role['settings']['restrict']) || !$role['settings']['restrict'])) {
            msgDebug("\nGoing to save user on portal.");
            $portal = new guest();
            $portal->portalSaveUser($values['email'], $values['title'], $rID?false:true);
            if ($oldEmail <> $values['email']) { portalDelete($oldEmail); }
        } elseif ($values['inactive']) {
            msgAdd("\nThis users account will be denied access through the portal.", 'caution');
            portalDelete($email); // disable access through the portal
        }
        if (!$rID) { $rID = $_POST['admin_id'] = $newID; }
		msgDebug("\nrID = $rID and session admin_id = ".getUserCache('profile', 'admin_id', false, 0)."");
        $io = new io();
        if ($io->uploadSave('file_attach', getModuleCache('bizuno', 'properties', 'usersAttachPath')."rID_{$rID}_")) {
            dbWrite(BIZUNO_DB_PREFIX.'users', ['attach'=>'1'], 'update', "id=$rID");
        }
		msgLog(lang('table')." users - ".lang('save')." {$values['email']} ($rID)");
		msgAdd(lang('msg_database_write'), 'success');
		$data = ['content'=>  ['action'=>'eval','actionData'=>"jq('#accUsers').accordion('select',0); jq('#dgUsers').datagrid('reload');"]];
        $layout = array_replace_recursive($layout, $data);
	}

	/**
     * Copies a user to a new username
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function copy(&$layout=[])
    {
		$this->security = getUserCache('security');
		$rID   = clean('rID',  'integer', 'get');
		$email = clean('data', 'email', 'get');
        if (!$rID || !$email) { return msgAdd(lang('err_copy_name_prompt')); }
		$user = dbGetRow(BIZUNO_DB_PREFIX."users", "admin_id='$rID'");
		// copy user at the portal
		$pData = portalRead('users', "biz_user='{$user['email']}'");
		unset($pData['id']);
		unset($pData['date_updated']);
		$pData['date_created']= date('Y-m-d H:i:s');
		$pData['biz_user']    = $email;
		portalWrite('users', $pData);
		unset($user['admin_id']);
		$user['email'] = $email;
		$nID = $_GET['rID'] = dbWrite(BIZUNO_DB_PREFIX."users", $user);
        if ($nID) { msgLog(lang('table')." users-".lang('copy').": $email ($rID => $nID)"); }
		$data = ['content'=>['action'=>'eval','actionData'=>"jq('#dgUsers').datagrid('reload'); accordionEdit('accUsers', 'dgUsers', 'divUsersDetail', '".jsLang('details')."', 'bizuno/users/edit', $nID);"]];
        $layout = array_replace_recursive($layout, $data);
	}

	/**
     * Deletes a user and removes them from the portal
     * @param array $layout - structure coming in
     * @return modified structure
     */
    public function delete(&$layout=[])
    {
		$this->security = getUserCache('security');
		$rID = clean('rID', 'integer', 'get');
        if (!$rID) { return msgAdd(lang('err_copy_name_prompt')); }
        if (getUserCache('profile', 'admin_id', false, 0) == $rID) { return msgAdd($this->lang['err_delete_user']); }
		$email = dbGetValue(BIZUNO_DB_PREFIX."users", 'email', "admin_id='$rID'");
		$data = ['content' => ['action'=>'eval', 'actionData'=>"jq('#dgUsers').datagrid('reload');"],
			'dbAction' => [BIZUNO_DB_PREFIX."users"         => "DELETE FROM ".BIZUNO_DB_PREFIX."users WHERE admin_id='$rID'",
                           BIZUNO_DB_PREFIX."users_profiles"=> "DELETE FROM ".BIZUNO_DB_PREFIX."users_profiles WHERE user_id='$rID'"]];
		portalDelete($email);
        $io = new \bizuno\io();
        $io->fileDelete(getModuleCache('bizuno', 'properties', 'usersAttachPath')."rID_{$rID}_*");
		msgLog(lang('table')." users-".lang('delete')." $email ($rID)");
		$layout = array_replace_recursive($layout, $data);
	}
}
