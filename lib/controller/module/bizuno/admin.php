<?php
/*
 * Module Bizuno admin functions
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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-09-03
 * @filesource /lib/controller/module/bizuno/admin.php
 */

namespace bizuno;

class bizunoAdmin
{
    public  $moduleID = 'bizuno';
    private $update_queue = [];

    function __construct()
    {
        $this->lang     = getLang($this->moduleID);
        $this->settings = array_replace_recursive(getStructureValues($this->settingsStructure()), getModuleCache($this->moduleID, 'settings', false, false, []));
        $this->structure= [
            'url'            => BIZUNO_URL."controller/module/$this->moduleID/",
            'version'        => MODULE_BIZUNO_VERSION,
            'category'       => 'bizuno',
            'required'       => true,
            'usersAttachPath'=> "data/$this->moduleID/users/uploads",
            'quickBar'       => ['styles'=>['float'=>'right','padding'=>'1px'],'child'=>[
                'settings' => ['order'=>50,'label'=>lang('bizuno_company'),'icon'=>'settings','child'=>[
                    'admin'  => ['order'=>10,'label'=>lang('settings'),'icon'=>'settings','events'=>['onClick'=>"hrefClick('bizuno/settings/manager');"]],
                    'encrypt'=> ['order'=>20,'label'=>lang('bizuno_encrypt_enable'),'icon'=>'encrypt-off','required'=>true,'attr'=>['id'=>'ql_encrypt'],
                        'events' => ['onClick'=>"windowEdit('bizuno/main/encryptionForm','winEncrypt','".jsLang('bizuno_encrypt_enable')."', 400, 150)"]],
                    'profile'=> ['order'=>30,'label'=>lang('profile'), 'icon'=>'profile', 'events'=>['onClick'=>"hrefClick('bizuno/profile/edit');"]],
                    'roles'  => ['order'=>40,'label'=>lang('roles'),   'icon'=>'roles',   'events'=>['onClick'=>"hrefClick('bizuno/roles/manager');"]],
                    'users'  => ['order'=>50,'label'=>lang('users'),   'icon'=>'users',   'events'=>['onClick'=>"hrefClick('bizuno/users/manager');"]],
                    'help'   => ['order'=>60,'label'=>lang('help'),    'icon'=>'help',    'required'=>true,'events'=>['onClick'=>"winHref('https://www.phreesoft.com/biz-school');"]],
                    'sysMsg' => ['order'=>70,'label'=>lang('messages'),'icon'=>'email',   'required'=>true,'events'=>['onClick'=>"hrefClick('bizuno/messages/manager');"]],
                    'ticket' => ['order'=>80,'label'=>lang('support'), 'icon'=>'support', 'required'=>true,'events'=>['onClick'=>"hrefClick('bizuno/tools/ticketMain');"],'hidden'=>defined('BIZUNO_SUPPORT_EMAIL')?false:true],
                    'newTab' => ['order'=>95,'label'=>lang('new_tab'), 'icon'=>'add',     'required'=>true,'events'=>['onClick'=>"winHref(bizunoHome);"]],
                    'logout' => ['order'=>99,'label'=>lang('logout'),  'icon'=>'logout',  'required'=>true,'events'=>['onClick'=>"jsonAction('bizuno/portal/logout');"]]]]]],
            'menuBar' => ['child'=>[
                'tools' => ['order'=>70,'label'=>lang('tools'),'icon'=>'tools','group'=>'tool','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=tools');"],'child'=>[
                    'imgmgr' => ['order'=>75,'label'=>lang('image_manager'),'icon'=>'mimeImg', 'events'=>['onClick'=>"jsonAction('bizuno/image/manager');"]],
                    'impexp' => ['order'=>85,'label'=>lang('bizuno_impexp'),'icon'=>'refresh', 'events'=>['onClick'=>"hrefClick('bizuno/tools/impExpMain');"]],
                    'backup' => ['order'=>90,'label'=>lang('backup'),       'icon'=>'backup',  'events'=>['onClick'=>"hrefClick('bizuno/backup/manager');"]]]]]],
            'hooks' => ['phreebooks'=>  ['tools'=>  [
                'fyCloseHome'=> ['page'=>'tools','class'=>'bizunoTools','order'=>50],
                'fyClose'    => ['page'=>'tools','class'=>'bizunoTools','order'=>50]]]]];
        if (strpos(getUserCache('profile', 'admin_encrypt', false, ''), ':')) {
//            $this->structure['quickBar']['child']['settings']['child']['encrypt'] = ['tip'=>lang('encrypt_enabled'),'order'=>60,'icon'=>'icon-encrypt-on'];
        }
        $this->dirlist = ['backups','data','images','temp'];
        $this->reportStructure = [
            'misc' => ['title'=>'misc', 'folders'=>  [
                'misc:rpt' => ['type'=>'dir', 'title'=>'reports'],
                'misc:misc'=> ['type'=>'dir', 'title'=>'forms']]],
            'bnk'  => ['title'=>'banking', 'folders'=>  [
                'bnk:rpt'  => ['type'=>'dir', 'title'=>'reports'],
                'bnk:j18'  => ['type'=>'dir', 'title'=>'bank_deposit'],
                'bnk:j20'  => ['type'=>'dir', 'title'=>'bank_check']]],
            'cust' => ['title'=>'customers', 'folders'=>  [
                'cust:rpt' => ['type'=>'dir', 'title'=>'reports'],
                'cust:j9'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_9'],
                'cust:j10' => ['type'=>'dir', 'title'=>'journal_main_journal_id_10'],
                'cust:j12' => ['type'=>'dir', 'title'=>'journal_main_journal_id_12'],
                'cust:j13' => ['type'=>'dir', 'title'=>'journal_main_journal_id_13'],
                'cust:j19' => ['type'=>'dir', 'title'=>'sales_receipt'],
                'cust:lblc'=> ['type'=>'dir', 'title'=>'label'],
                'cust:ltr' => ['type'=>'dir', 'title'=>'letter'],
                'cust:stmt'=> ['type'=>'dir', 'title'=>'statement']]],
            'gl'   => ['title'=>'general_ledger', 'folders'=>  [
                'gl:rpt'   => ['type'=>'dir', 'title'=>'reports', 'type'=>'dir']]],
            'hr'   => ['title'=>'employees', 'folders'=> [
                'hr:rpt'   => ['type'=>'dir', 'title'=>'reports']]],
            'inv'  => ['title'=>'inventory', 'folders'=>  [
                'inv:rpt'  => ['type'=>'dir', 'title'=>'reports']]],
            'vend' => ['title'=>'vendors', 'folders'=>  [
                'vend:rpt' => ['type'=>'dir', 'title'=>'reports'],
                'vend:j3'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_3'],
                'vend:j4'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_4'],
                'vend:j7'  => ['type'=>'dir', 'title'=>'journal_main_journal_id_7'],
                'vend:lblv'=> ['type'=>'dir', 'title'=>'label'],
                'vend:stmt'=> ['type'=>'dir', 'title'=>'statement']]]];
        $this->phreeformProcessing = [
            'json'    => ['text'=>$this->lang['pf_proc_json'],    'group'=>lang('tools')],
            'today'   => ['text'=>lang('today'),                  'group'=>lang('date')]];
        setProcessingDefaults($this->phreeformProcessing, $this->moduleID, $this->lang['title']);
        $this->phreeformFormatting = [
            'uc'      => ['text'=>$this->lang['pf_proc_uc'],      'group'=>lang('text')],
            'lc'      => ['text'=>$this->lang['pf_proc_lc'],      'group'=>lang('text')],
            'yesBno'  => ['text'=>$this->lang['pf_proc_yesbno'],  'group'=>lang('text')],
            'blank'   => ['text'=>$this->lang['pf_proc_blank'],   'group'=>lang('text')],
            'printed' => ['text'=>$this->lang['pf_proc_printed'], 'group'=>lang('text')],
            'neg'     => ['text'=>$this->lang['pf_proc_neg'],     'group'=>lang('numeric')],
            'n2wrd'   => ['text'=>$this->lang['pf_proc_n2wrd'],   'group'=>lang('numeric')],
            'null0'   => ['text'=>$this->lang['pf_proc_null0'],   'group'=>lang('numeric')],
            'rnd0d'   => ['text'=>$this->lang['pf_proc_rnd0d'],   'group'=>lang('numeric')],
            'rnd2d'   => ['text'=>$this->lang['pf_proc_rnd2d'],   'group'=>lang('numeric')],
            'currency'=> ['text'=>lang('currency'),               'group'=>lang('numeric')],
            'curLong' => ['text'=>lang('currency_long'),          'group'=>lang('numeric')],
            'curNull0'=> ['text'=>$this->lang['pf_cur_null_zero'],'group'=>lang('numeric')],
            'percent' => ['text'=>lang('percent'),                'group'=>lang('numeric')],
            'precise' => ['text'=>$this->lang['pf_proc_precise'], 'group'=>lang('numeric')],
            'date'    => ['text'=>$this->lang['pf_proc_date'],    'group'=>lang('date')],
            'dateLong'=> ['text'=>$this->lang['pf_proc_datelong'],'group'=>lang('date')]];
        setProcessingDefaults($this->phreeformFormatting, $this->moduleID, $this->lang['title']);
        $this->phreeformSeparators = [
            'sp'     => ['text'=>$this->lang['pf_sep_space1'], 'module'=>$this->moduleID,'function'=>'viewSeparator'],
            '2sp'    => ['text'=>$this->lang['pf_sep_space2'], 'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'comma'  => ['text'=>$this->lang['pf_sep_comma'],  'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'com-sp' => ['text'=>$this->lang['pf_sep_commasp'],'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'dash-sp'=> ['text'=>$this->lang['pf_sep_dashsp'], 'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'sep-sp' => ['text'=>$this->lang['pf_sep_sepsp'],  'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'nl'     => ['text'=>$this->lang['pf_sep_newline'],'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'semi-sp'=> ['text'=>$this->lang['pf_sep_semisp'], 'module'=>$this->moduleID,'function'=>'viewSeparator'],
            'del-nl' => ['text'=>$this->lang['pf_sep_delnl'],  'module'=>$this->moduleID,'function'=>'viewSeparator']];
        $this->notes = [$this->lang['note_bizuno_install_1']];
    }

    /**
     * User configurable settings structure
     * @return array structure for settings forms
     */
    private function settingsStructure()
    {
        foreach ([0,1,2,3,4] as $value) { $selPrec[] = ['id'=>$value, 'text'=>$value]; }
        $selAPI = [['id'=>'','text'=>$this->lang['auto_detect']],['id'=>'10','text'=>lang('journal_main_journal_id_10')],['id'=>'12','text'=>lang('journal_main_journal_id_12')]];
        $selSep = [['id'=>'.','text'=>'Dot (.)'],['id'=>',','text'=>'Comma (,)'],['id'=>' ','text'=>'Space ( )'],['id'=>"'",'text'=>"Apostrophe (')"]];
        $locale = localeLoadDB();
        $zones  = viewTimeZoneSel($locale);
        $selDate= [
            ['id'=>'m/d/Y','text'=>'mm/dd/yyyy'],
            ['id'=>'d/m/Y','text'=>'dd/mm/yyyy'],
            ['id'=>'Y/m/d','text'=>'yyyy/mm/dd'],
            ['id'=>'d.m.Y','text'=>'dd.mm.yyyy'],
            ['id'=>'Y.m.d','text'=>'yyyy.mm.dd'],
            ['id'=>'dmY',  'text'=>'ddmmyyyy'],
            ['id'=>'Ymd',  'text'=>'yyyymmdd'],
            ['id'=>'Y-m-d','text'=>'yyyy-mm-dd']];
        $data  = [
            'general' => ['order'=>10,'label'=>lang('general'),'fields'=>[
                'password_min'    => ['options'=>['min'=> 8],           'attr'=>['type'=>'integer','value'=> 8]],
                'max_rows'        => ['options'=>['min'=>10,'max'=>100],'attr'=>['type'=>'integer','value'=>20]],
                'session_max'     => ['options'=>['min'=> 0,'max'=>300],'attr'=>['type'=>'integer','value'=> 0]], // min zero for auto refresh
                'hide_filters'    => ['attr'=>['type'=>'selNoYes']]]],
            'company' => ['order'=>20,'label'=>lang('company'),'fields'=>[
                'id'              => ['label'=>pullTableLabel('contacts',     'short_name', 'b'),'attr'=>['value'=>getUserCache('profile', 'biz_title')]],
                'primary_name'    => ['label'=>pullTableLabel('address_book', 'primary_name'),   'attr'=>['value'=>getUserCache('profile', 'biz_title')]],
                'contact'         => ['label'=>pullTableLabel('address_book', 'contact', 'm')],
                'email'           => ['label'=>pullTableLabel('address_book', 'email', 'm')],
                'contact_ap'      => ['label'=>pullTableLabel('address_book', 'contact', 'p')],
                'email_ap'        => ['label'=>pullTableLabel('address_book', 'email', 'p')],
                'contact_ar'      => ['label'=>pullTableLabel('address_book', 'contact', 'r')],
                'email_ar'        => ['label'=>pullTableLabel('address_book', 'email', 'r')],
                'address1'        => ['label'=>pullTableLabel('address_book', 'address1')],
                'address2'        => ['label'=>pullTableLabel('address_book', 'address2')],
                'city'            => ['label'=>pullTableLabel('address_book', 'city')],
                'state'           => ['label'=>pullTableLabel('address_book', 'state')],
                'postal_code'     => ['label'=>pullTableLabel('address_book', 'postal_code')],
                'country'         => ['label'=>pullTableLabel('address_book', 'country'),'attr'=>['type'=>'country']],
                'telephone1'      => ['label'=>pullTableLabel('address_book', 'telephone1')],
                'telephone2'      => ['label'=>pullTableLabel('address_book', 'telephone2')],
                'telephone3'      => ['label'=>pullTableLabel('address_book', 'telephone3')],
                'telephone4'      => ['label'=>pullTableLabel('address_book', 'telephone4')],
                'website'         => ['label'=>pullTableLabel('address_book', 'website')],
                'gov_id_number'   => ['label'=>pullTableLabel('contacts','gov_id_number')],
                'logo'            => ['attr'=>['type'=>'hidden']]]],
            'my_phreesoft_account' => ['order'=>30,'label'=>lang('my_phreesoft_account'),'fields'=>[
                'phreesoft_user'  => ['label'=>lang('username')],
                'phreesoft_key'   => ['options'=>['width'=>400]]]],
//              'phreesoft_pass'  => ['label'=>lang('password'),'attr'=>['type'=>'password']],
//              'test_con'        => ['label'=>lang('test'),'icon'=>'checkin','attr'=>['type'=>'hidden'],'events'=>['onClick'=>"jsonAction('bizuno/admin/testAccount', 0, jq('#my_phreesoft_account_phreesoft_user').val()+';'+jq('#my_phreesoft_account_phreesoft_pass').val());"]]]],
            'mail' => ['order'=>40,'label'=>lang('mail'),'fields'=>[
                'smtp_enable'     => ['attr'=>['type'=>'selNoYes']],
                'smtp_host'       => ['attr'=>['value'=>'smtp.gmail.com']],
                'smtp_port'       => ['attr'=>['type'=>'integer', 'value'=>587]],
                'smtp_user'       => ['attr'=>['value'=>'']],
                'smtp_pass'       => ['attr'=>['type'=>'password','value'=>'']]]],
            'bizuno_api' => ['order'=>50,'label'=>lang('bizuno_api'),'fields'=>[
                'auto_detect'     => ['values'=>$selAPI,'attr'=>['type'=>'select']],
                'gl_receivables'  => ['attr'=>['type'=>'ledger','id'=>'bizuno_api_gl_receivables','value'=>getModuleCache('phreebooks','settings','customers','gl_receivables')]],
                'gl_sales'        => ['attr'=>['type'=>'ledger','id'=>'bizuno_api_gl_sales',      'value'=>getModuleCache('phreebooks','settings','customers','gl_sales')]],
                'gl_discount'     => ['attr'=>['type'=>'ledger','id'=>'bizuno_api_gl_discount',   'value'=>getModuleCache('phreebooks','settings','customers','gl_discount')]],
                'gl_tax'          => ['attr'=>['type'=>'ledger','id'=>'bizuno_api_gl_tax',        'value'=>getModuleCache('phreebooks','settings','customers','gl_liability')]],
                'tax_rate_id'     => ['values'=>viewSalesTaxDropdown('c'),'attr'=>['type'=>'select','value'=>0]]]],
            'locale' => ['order'=>60,'label'=>lang('locale'),'fields'=>[
                'timezone'        => ['values'=>$zones,  'options'=>['width'=>400],'attr'=>['type'=>'select','value'=>'America/New_York']],
                'number_precision'=> ['values'=>$selPrec,'attr'=>['type'=>'select','value'=>'2']],
                'number_decimal'  => ['values'=>$selSep, 'attr'=>['type'=>'select','value'=>'.']],
                'number_thousand' => ['values'=>$selSep, 'attr'=>['type'=>'select','value'=>',']],
                'number_prefix'   => ['attr'=>['value'=>'']],
                'number_suffix'   => ['attr'=>['value'=>'']],
                'number_neg_pfx'  => ['attr'=>['value'=>'-']],
                'number_neg_sfx'  => ['attr'=>['value'=>'']],
                'date_short'      => ['values'=>$selDate,'attr'=>['type'=>'select','value'=>'m/d/Y']]]]];
        settingsFill($data, $this->moduleID);
        return $data;
    }

    /**
     * Tests the entered credentials for API transactions
     * @param array $layout - Structure coming in
     * @return modified $layout
     */
    public function testAccount()
    {
        global $io;
        $parts  = explode(';', clean('data', 'text', 'get'), 2);
        $creds  = ['UserID'=>!empty($parts[0]) ? $parts[0] : '', 'UserPW'=>!empty($parts[1]) ? $parts[1] : ''];
        msgDebug("\nSending creds = ".print_r($creds, true));
        $success= $io->apiPhreeSoft('testAccount', $creds, 'post');
        if (!empty($success['success'])) { msgAdd($this->lang['account_verified'], 'success'); }
    }

    /**
     * Special initialization methods for this module
     * @return boolean - true on success, false on error
     */
    function initialize()
    {
        return true;
    }

    /**
     * Structure for Settings main page for module Bizuno
     * @param array $layout - structure coming in
     * @return array - modified $layout
     */
    public function adminHome(&$layout=[])
    {
        if (!$security = validateSecurity('bizuno', 'admin', 1)) { return; }
        msgDebug("\nEditing with settings = ".print_r(getModuleCache('bizuno', 'settings'), true));
        $output= $this->getAdminFields();
        $imgSrc= getModuleCache('bizuno', 'settings', 'company', 'logo');
        $imgDir= dirname($imgSrc) == '/' ? '/' : dirname($imgSrc).'/';
        $stmt  = dbGetResult("SHOW TABLE STATUS");
        $stats = $stmt->fetchAll(\PDO::FETCH_ASSOC);
        $data  = [
            'tabs'    => ['tabAdmin'=>['divs'=>[
//              'mail' => ['order'=>30,'label'=>lang('mail_accounts'),'type'=>'html','html'=>'','options'=>['href'=>"'".BIZUNO_AJAX."&bizRt=bizuno/admin/mailMgr'"]],
                'tabs' => ['order'=>40,'label'=>lang('extra_tabs'),'type'=>'html','html'=>'','options'=>['href'=>"'".BIZUNO_AJAX."&bizRt=bizuno/tabs/manager'"]],
                'tools'=> ['order'=>80,'label'=>lang('tools'),'type'=>'divs','divs'=>[
                    'status' => ['order'=>20,'type'=>'divs','classes'=>['areaView'],'divs'=>[
                        'stsSet' => ['order'=>10,'type'=>'panel','classes'=>['block66'],'key'=>'stsSet'],
                        'encSet' => ['order'=>20,'type'=>'panel','classes'=>['block33'],'key'=>'encSet'],
                        'encDel' => ['order'=>30,'type'=>'panel','classes'=>['block33'],'key'=>'encDel'],
                        'fixCmt' => ['order'=>40,'type'=>'panel','classes'=>['block33'],'key'=>'fixCmt']]]]],
                'stats'=> ['order'=>90,'label'=>lang('statistics'),'styles'=>['width'=>'700px;','height'=>'250px'],'type'=>'datagrid','key'=>'bizStats']]]],
            'panels'  => [
                'stsSet' => ['label'=>$this->lang['admin_status_update'],'type'=>'divs','divs'=>[
                    'formBOF'=> ['order'=>10,'type'=>'form',  'key' =>'frmStatus'],
                    'body'   => ['order'=>20,'type'=>'fields','keys'=>$output['keys']['keys0']],
                    'formEOF'=> ['order'=>30,'type'=>'html',  'html'=>"</form>"]]],
                'encSet' => ['label'=>$this->lang['admin_encrypt_update'],'type'=>'fields','keys'=>$output['keys']['keys1']],
                'encDel' => ['label'=>$this->lang['btn_security_clean'],  'type'=>'fields','keys'=>$output['keys']['keys2']],
                'fixCmt' => ['label'=>$this->lang['admin_fix_comments'],  'type'=>'fields','keys'=>$output['keys']['keys3']]],
            'datagrid'=> ['bizStats'=>$this->dgStats('bizStats')],
            'forms'   => ['frmStatus'=>['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&bizRt=bizuno/settings/statusSave"]]],
            'fields'  => $output['fields'],
            'jsHead'  => [$this->moduleID=>"var bizStatsData = ".json_encode($stats).";"],
            'jsBody'  => ['company_logo'=>"imgManagerInit('company_logo', '$imgSrc', '$imgDir', 'images/');"],
            'jsReady' => [$this->moduleID=>"ajaxForm('frmStatus'); jq('#bizStats').datagrid({data:bizStatsData}).datagrid('clientPaging');"]];
        $layout = array_replace_recursive($layout, adminStructure($this->moduleID, $this->settingsStructure(), $this->lang), $data);
    }

    private function getAdminFields()
    {
        $status = dbLoadStructure(BIZUNO_DB_PREFIX."current_status");
        $result = dbGetRow(BIZUNO_DB_PREFIX."current_status", "id=1");
        foreach ($result as $key => $value) {
            if       (isset($this->lang[$key]))     { $label = $this->lang[$key]; }
            elseif (strpos($key, 'next_ref_j')===0) { $label = sprintf(lang('next_ref'), lang('journal_main_journal_id_'.substr($key, 10))); }
            else                                    { $label = sprintf(lang('next_ref'), lang($key)); }
            $status[$key]['position']= 'after';
            $status[$key]['label']   = $label;
            $status[$key]['attr']['value'] = $value;
        }
        ksort($status);
        $output = [
            'keys'  =>[
                'keys0' => ['btnStatus'],
                'keys1' => ['descEncrypt','encrypt_key_orig','encrypt_key_new','encrypt_key_dup','encrypt_key_btn'],
                'keys2' => ['descEncDel','encrypt_clean_date','encrypt_clean_btn'],
                'keys3' => ['descFixComment','fix_cmt_btn']],
            'fields'=>[
                'btnStatus'         => ['order'=>99,'icon'=>'save','label'=>'save','events'=>['onClick'=>"jq('#frmStatus').submit();"]],
                'descEncrypt'       => ['order'=>10,'html'=>"<p>{$this->lang['desc_encrypt_config']}</p>",       'attr'=>['type'=>'raw']],
                'encrypt_key_orig'  => ['order'=>20,'break'=>true,'label' =>$this->lang['admin_encrypt_old'],    'attr'=>['type'=>'password']],
                'encrypt_key_new'   => ['order'=>30,'break'=>true,'label' =>$this->lang['admin_encrypt_new'],    'attr'=>['type'=>'password']],
                'encrypt_key_dup'   => ['order'=>40,'break'=>true,'label' =>$this->lang['admin_encrypt_confirm'],'attr'=>['type'=>'password']],
                'encrypt_key_btn'   => ['order'=>50,'break'=>true,'events'=>['onClick'=>"encryptChange();"],     'attr'=>['type'=>'button','value'=>lang('change')]],
                'descEncDel'        => ['order'=>10,'html'=>"<p>{$this->lang['desc_security_clean']}</p>",'attr'=>['type'=>'raw']],
                'encrypt_clean_date'=> ['order'=>20,'break'=>true,'label' =>$this->lang['desc_security_clean_date'],'attr'=>['type'=>'date','value'=>date('Y-m-d')]],
                'encrypt_clean_btn' => ['order'=>30,'break'=>true,'events'=>['onClick'=>"jq('body').addClass('loading'); jsonAction('bizuno/tools/encryptionClean', 0, jq('#encrypt_clean_date').datebox('getValue'));"],
                    'attr'=>['type'=>'button','value'=>lang('start')]],
                'descFixComment'    => ['order'=>10,'html'=>$this->lang['desc_update_comments'],'attr'=>['type'=>'raw']],
                'fix_cmt_btn'       => ['order'=>20,'events'=>['onClick'=>"jq('body').addClass('loading'); jsonAction('bizuno/tools/repairComments');"],'attr'=>['type'=>'button','value'=>lang('go')]]]];
        foreach ($status as $key => $settings) {
            if ($key == 'id') { continue; }
            $output['fields']["stat_$key"] = $settings;
            $output['keys']['keys0'][] = "stat_$key";
        }
        return $output;
    }

    /**
     * Special operations to save settings page beyond core settings
     * Check for company name change and update portal
     */
    public function adminSave()
    {
        $newTitle = clean('company_primary_name', 'text', 'post');
        $timezone = clean('locale_timezone', 'text', 'post');
        portalUpdateBiz($newTitle, $timezone);
        readModuleSettings($this->moduleID, $this->settingsStructure());
    }
    /**
     * This method pulls common data and uploads to browser to speed up page updates. It should be extended by every module that wants to upload static data for a browser session
     */
    public function loadBrowserSession(&$layout=[])
    {
        // load the default currency, locale
        $locale       = getModuleCache('bizuno', 'settings', 'locale');
        $dateDelimiter= substr(preg_replace("/[a-zA-Z]/", "", $locale['date_short']), 0, 1);
        $locales      = localeLoadDB(); // load countries
        $countries    = [];
        $defISO       = getModuleCache('bizuno', 'settings', 'company', 'country');
        $defTitle     = $defISO;
        foreach ($locales->Locale as $value) {
            $countries[] = ['iso3'=>$value->Country->ISO3, 'iso2'=>$value->Country->ISO2, 'title'=>$value->Country->Title];
            if ($defISO == $value->Country->ISO3) { $defTitle = $value->Country->Title; }
        }
        $ISOCurrency = getDefaultCurrency();
        $data = [
            'calendar'  => ['format'=>$locale['date_short'], 'delimiter'=>$dateDelimiter],
            'country'   => ['iso'=>$defISO,'title'=>$defTitle],
            'currency'  => ['defaultCur'=>$ISOCurrency, 'currencies'=>getModuleCache('phreebooks', 'currency', 'iso')],
            'language'  => substr(getUserCache('profile', 'language', false, 'en_US'), 0, 2),
            'timezone'  => $locale['timezone'],
            'locale'    => [
                'precision'=> isset($locale['number_precision'])? $locale['number_precision']: '2',
                'decimal'  => isset($locale['number_decimal'])  ? $locale['number_decimal']  : '.',
                'thousand' => isset($locale['number_thousand']) ? $locale['number_thousand'] : ',',
                'prefix'   => isset($locale['number_prefix'])   ? $locale['number_prefix']   : '',
                'suffix'   => isset($locale['number_suffix'])   ? $locale['number_suffix']   : '',
                'neg_pfx'  => isset($locale['number_neg_pfx'])  ? $locale['number_neg_pfx']  : '-',
                'neg_sfx'  => isset($locale['number_neg_sfx'])  ? $locale['number_neg_sfx']  : ''],
            'dictionary'=> $this->getBrowserLang(),
            'countries' => ['total'=>sizeof($countries), 'rows'=>$countries]];
        $layout = array_replace_recursive($layout, ['content'=>$data]);
    }

    private function getBrowserLang()
    {
        return ['ACCOUNT'=>lang('account'),
            'CITY'       =>lang('address_book_city'),
            'CLOSE'      =>lang('close'),
            'CONTACT_ID' =>lang('contacts_short_name'),
            'EDIT'       =>lang('edit'),
            'FINISHED'   =>lang('finished'),
            'FROM'       =>lang('from'),
            'INFORMATION'=>lang('information'),
            'MESSAGE'    =>lang('message'),
            'NAME'       =>lang('address_book_primary_name'),
            'PLEASE_WAIT'=>lang('please_wait'),
            'PROPERTIES' =>lang('properties'),
            'SETTINGS'   =>lang('settings'),
            'SHIPPING_ESTIMATOR'=>lang('shipping_estimator'),
            'STATE'      =>lang('address_book_state'),
            'TITLE'      =>lang('title'),
            'TO'         =>lang('to'),
            'TOTAL'      =>lang('total'),
            'TRASH'      =>lang('trash'),
            'TYPE'       =>lang('type'),
            'VALUE'      =>lang('value'),
            'VIEW'       =>lang('view')];
    }

    /**
     * Sets the admin account and database credentials, portal specific
     * @param type $layout
     */
    public function installPreFlight(&$layout=[])
    {
        $success = true;
        bizAutoLoad(BIZUNO_ROOT."portal/guest.php", 'guest');
        $guest = new guest();
        if (method_exists($guest, 'installPreFlight')) { $success = $guest->installPreFlight($layout); }
        $bID = clean('bID', 'integer', 'get');
        if ($success) { $layout = ['content'=>['action'=>'eval', 'actionData'=>"jsonAction('bizuno/admin/installForm', $bID);"]]; }
    }

    /**
     * Sets the popup form to load db and set starting settings
     * @param arrray $layout
     */
    public function installForm(&$layout=[])
    {
        $bID   = clean('rID', 'integer', 'get');
        if (!$bID) { return msgAdd("bad business ID: $bID"); }
        $locale= localeLoadDB();
        $year  = date('Y');
        for ($i=2; $i>=0; $i--) { $years[] = ['id'=>$year - $i, 'text'=>$year - $i]; }
        $fields= [
            'biz_title'   => ['order'=>10,'label'=>$this->lang['biz_title'],   'attr'=>['value'=>getUserCache('profile', 'biz_title'),'maxlength'=>'16']],
            'biz_lang'    => ['order'=>20,'label'=>$this->lang['biz_lang'],    'values'=>viewLanguages(true),     'attr'=>['type'=>'select','value'=>'en_US']],
            'biz_timezone'=> ['order'=>30,'label'=>$this->lang['biz_timezone'],'values'=>viewTimeZoneSel($locale),'attr'=>['type'=>'select','value'=>$this->guessTimeZone($locale)]],
            'biz_currency'=> ['order'=>40,'label'=>$this->lang['biz_currency'],'values'=>viewCurrencySel($locale),'attr'=>['type'=>'select','value'=>'USD']],
            'biz_chart'   => ['order'=>50,'label'=>$this->lang['biz_chart'],   'values'=>localeLoadCharts(),      'attr'=>['type'=>'select','value'=>"locale/en_US/charts/retailCorp.xml"]],
            'biz_fy'      => ['order'=>60,'label'=>$this->lang['biz_fy'],      'values'=>$years, 'attr'=>['type'=>'select','value'=>date('Y')]]];
        $data = ['type'=>'popup','title'=>$this->lang['bizuno_install_title'],'attr'=>['id'=>'bizInstall','wClosable'=>false],
            'toolbars'=> ['tbInstall'=>['icons'=> [
                'instBack'=> ['order'=>10,'icon'=>'close','label'=>lang('cancel'),'events'=>['onClick'=>"bizWindowClose('bizInstall');"]],
                'instNext'=> ['order'=>20,'icon'=>'next', 'label'=>lang('next'),  'events'=>['onClick'=>"installSave($bID);"]]]]],
            'divs'    => [
                'toolbar'=> ['order'=>10,'type'=>'toolbar','key' =>'tbInstall'],
                'divBOF' => ['order'=>15,'type'=>'html',   'html'=>'<div id="divInstall"><p>'.$this->lang['intro'].'</p>'],
                'body'   => ['order'=>50,'type'=>'fields', 'keys'=>array_keys($fields)],
                'divEOF' => ['order'=>85,'type'=>'html',   'html'=>"</div>"]],
            'fields'  => $fields,
           'jsBody'   => ['init'=>$this->getViewInstallJS()]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     *
     * @return type
     */
    private function getViewInstallJS()
    {
        return "function installSave(bizID) {
    jq('#instNext').linkbutton({ iconCls:'iconL-loading',text:'' });
    divData = jq('#divInstall :input').serializeObject();
    jq.ajax({
        url:     '".BIZUNO_AJAX."&bizRt=bizuno/admin/installBizuno&bID='+bizID,
        type:    'post',
        data:    divData,
        async:   false,
        success: function (data) { processJson(data); }
    });
    jq('#bizInstall').window('destroy');
}";
    }

    /**
     * try to guess time zone by client ip
     * @return string
     */
    private function guessTimeZone($locale=[])
    {
        if (empty($locale)) { $locale= localeLoadDB(); }
        $ipInfo= file_get_contents('http://ip-api.com/json/'.$_SERVER['REMOTE_ADDR']);
        $data  = json_decode($ipInfo);
        $output= 'America/New_York';
        if (empty($data->timezone)) { return $output; }
//date_default_timezone_set($data->timezone);
        foreach ($locale->Timezone as $value) {
            if ($data->timezone == $value->Code) { $output = $value->Code;  break; }
        }
        return $output;
    }

    public function installBizuno(&$layout=[])
    {
        global $bizunoUser, $io;
        bizAutoLoad(BIZUNO_LIB ."controller/module/bizuno/settings.php",    'bizunoSettings');
        bizAutoLoad(BIZUNO_LIB ."controller/module/phreebooks/admin.php",   'phreebooksAdmin');
        bizAutoLoad(BIZUNO_LIB ."controller/module/phreebooks/currency.php",'phreebooksCurrency');
        bizAutoLoad(BIZUNO_ROOT."portal/guest.php",                         'guest');
        bizAutoLoad(BIZUNO_LIB ."model/registry.php",                       'bizRegistry');
        ini_set('memory_limit','1024M'); // temporary
        $guest = new guest();
        if (method_exists($guest, 'installBizunoPre')) { if (!$guest->installBizunoPre()) { return; } } // pre-install for portal
        $usrEmail = biz_validate_user()[0];
        if (!$usrEmail) { return msgAdd('User is not logged in!'); }
        setUserCache('profile', 'biz_id',   clean('bID',      'text',   'get'));
        setUserCache('profile', 'biz_title',clean('biz_title','text',   'post'));
        setUserCache('profile', 'language', clean('biz_lang', 'text',   'post'));
        setUserCache('profile', 'chart',    clean('biz_chart','text',   'post'));
        setUserCache('profile', 'first_fy', clean('biz_fy',   'integer','post'));
        $curISO = clean('biz_currency', 'text', 'post');
        setModuleCache('phreebooks', 'currency', 'defISO', $curISO); // temp set currency for table loading and PhreeBooks intialization
        // error check title
        if (strlen(getUserCache('profile', 'biz_title')) < 3) { return msgAdd('Your business name needs to be from 3 to 15 characters!'); }
        // Here we go, ready to install
        $bAdmin = new bizunoSettings();
        msgDebug("\n  Creating the company directory");
        $io->validatePath("index.php"); // create the data folder
        // ready to install, tables first
        if (dbTableExists(BIZUNO_DB_PREFIX.'journal_main')) { return msgAdd("Cannot install, the database has tables present. Aborting!"); }
        $tables = [];
        require(BIZUNO_LIB."controller/module/bizuno/install/tables.php"); // get the tables
        $bAdmin->adminInstTables($tables);
        // Set the current_status to defaults for module install to work properly
        dbWrite(BIZUNO_DB_PREFIX."current_status", ['id'=>1]);
        // Load PhreeBooks defaults
        $pbAdmin = new phreebooksAdmin();
        $pbAdmin->installFirst(); // load the chart and initialize PhreeBooks stuff
        // now Modules
        setUserCache('security', 'admin',   4);
        setUserCache('profile',  'role_id', 1);
        msgDebug("\nModule list to install = ".print_r($guest->getModuleList(true), true));
        foreach ($guest->getModuleList(true) as $module => $path) { $bAdmin->moduleInstall($layout, $module, $path); }
        // create the admin user account
        setUserCache('profile', 'email', $usrEmail);
        $admin_id = isset($GLOBALS['bizuno_install_admin_id']) ? $GLOBALS['bizuno_install_admin_id'] : 1;
        setUserCache('profile', 'admin_id', $admin_id); // since first record in db
        $role_id  = dbWrite(BIZUNO_DB_PREFIX."roles", ['title'=>'admin','settings'=>'']);
        $userData = ['email'=>$usrEmail, 'title'=>'Admin', 'role_id'=>$role_id, 'settings'=>json_encode($bizunoUser)];
        dbWrite(BIZUNO_DB_PREFIX."users", $userData);
        $bAdmin->adminFillSecurity($role_id, 4);
        $this->initDashboards($admin_id, $bAdmin->notes); // create some starting dashboards
        if (method_exists($guest, 'installBizuno')) { $guest->installBizuno(); } // hook for after db set up for portal
        // build the registry, i.e. set the module cache
        $cur     = new phreebooksCurrency();
        $registry= new bizRegistry();
        $registry->initRegistry(getUserCache('profile', 'email'), getUserCache('profile', 'biz_id'));
        setModuleCache('phreebooks', 'currency', false, ['defISO'=>$curISO, 'iso'=>[$curISO =>$cur->currencySettings($curISO)]]);
        $company = getModuleCache('bizuno', 'settings', 'company'); // set the business title and id
        $company['id'] = $company['primary_name'] = getUserCache('profile', 'biz_title');
        setModuleCache('bizuno', 'settings', 'company', $company);
        $locale  = getModuleCache('bizuno', 'settings', 'locale'); // set the timezone
        $locale['timezone'] = clean('biz_timezone', 'text', 'post');
        setModuleCache('bizuno', 'settings', 'locale', $locale);
        portalWrite('business', ['title'=>$company['id'],'currency'=>getDefaultCurrency(),'time_zone'=>$locale['timezone'],'date_last_visit'=>date('Y-m-d h:i:s')], 'update', "id='".getUserCache('profile', 'biz_id')."'");
        msgLog(lang('user_login')." ".getUserCache('profile', 'email'));
        $this->secureMyFiles();
        $layout = ['content'=>['action'=>'eval','actionData'=>"loadSessionStorage();"]];
    }

    /**
     * Populates the home page dashboard for the admin
     * @param integer $admin_id - current install user database record id
     * @param array $my_to_do - list of action items that is generated during the install
     */
    private function initDashboards($admin_id=1, $my_to_do=[])
    {
        $setCLink = ['data'=>['PhreeSoft Biz School'=>'https://www.phreesoft.com/biz-school/']]; // company links presets
        $setLnch  = ['inv_mgr','mgr_c','mgr_v','j6_mgr', 'j12_mgr', 'admin']; // launchpad link presets
        $panels[] = ['column_id'=>0,'row_id'=>0,'dashboard_id'=>'quick_start',     'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'bizuno'];
        $panels[] = ['column_id'=>0,'row_id'=>1,'dashboard_id'=>'todays_j12',      'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'phreebooks','settings'=>json_encode([])];
        $panels[] = ['column_id'=>0,'row_id'=>2,'dashboard_id'=>'todays_j06',      'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'phreebooks','settings'=>json_encode([])];
        $panels[] = ['column_id'=>1,'row_id'=>0,'dashboard_id'=>'my_to_do',        'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'bizuno',    'settings'=>json_encode(['data'=>$my_to_do])];
        $panels[] = ['column_id'=>1,'row_id'=>1,'dashboard_id'=>'favorite_reports','user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'phreeform', 'settings'=>json_encode(['data'=>[]])];
        $panels[] = ['column_id'=>1,'row_id'=>2,'dashboard_id'=>'daily_tip',       'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'bizuno'];
        $panels[] = ['column_id'=>2,'row_id'=>0,'dashboard_id'=>'launchpad',       'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'bizuno',    'settings'=>json_encode($setLnch)];
        $panels[] = ['column_id'=>2,'row_id'=>1,'dashboard_id'=>'company_links',   'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'bizuno',    'settings'=>json_encode($setCLink)];
        $panels[] = ['column_id'=>2,'row_id'=>2,'dashboard_id'=>'todays_audit',    'user_id'=>$admin_id,'menu_id'=>'home','module_id'=>'bizuno',    'settings'=>json_encode([])];
        foreach ($panels as $panel) { dbWrite(BIZUNO_DB_PREFIX."users_profiles", $panel); }
    }

    /**
     * Secures the root folder with .htaccess, and BIZUNO_DATA folder with access to images only
     */
    public function secureMyFiles()
    {
        global $io;
        if (file_exists('dist.htaccess') && !file_exists('.htaccess')) { rename('dist.htaccess', '.htaccess'); }
        $htaccess = '# secure uploads directory
<Files ~ ".*\..*">
	Order Allow,Deny
	Deny from all
</Files>
<FilesMatch "\.(jpg|jpeg|jpe|gif|png|tif|tiff)$">
	Order Deny,Allow
	Allow from all
</FilesMatch>';
        $io->fileWrite($htaccess, '.htaccess', false);
    }

    /**
     * NOT USED - Displays a tab with grid to add email addresses - replaced by settings in users to build drop down with Bizuno company settings
     * @param array $layout
     */
    public function mailMgr(&$layout=[])
    {
        // convert from old ay (single setting) to the first entry

        // get the mail datagrid data
        $rows  = getModuleCache('bizuno', 'mail', 'accounts', false, []);
        $dgData= "var dgMailData = ".json_encode(['total'=>sizeof($rows), 'rows'=>$rows]). ";";
        $data  = ['type'=>'divHTML',
            'divs'    => [
                'tbMail'=> ['order'=> 1,'type'=>'toolbar', 'key'=>'tbMail'],
                'body'  => ['order'=>50,'type'=>'datagrid','key'=>'dgMail']],
            'toolbars'=> ['tbMail'=>['icons'=>['save'=>['order'=>40,'events'=>['onClick'=>"divSubmit('bizuno/admin/mailSave');"]]]]],
            'datagrid'=> ['dgMail'=>$this->dgMail('dgMail')],
            'jsHead'  => ['dgMailData'=>$dgData]];
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * Saves the mail addresses from grid to Bizuno module settings
     * @param array $layout - Working structure
     */
    public function mailSave(&$layout=[])
    {
//      msgLog(lang('mail_accounts')." - ".lang('update'));
        msgAdd(lang('save_success'));
    }

    /**
     * Grid structure for mail list
     * @param string $name - DOM field name
     * @return array - grid structure
     */
    protected function dgMail($name)
    {
        return ['id' => $name, 'type'=>'edatagrid',
            'attr'   => ['toolbar'=>"#{$name}Toolbar", 'rownumbers'=>true],
            'events' => ['data'=> $name.'Data',
                'onClickRow'=> "function(rowIndex) { jq('#$name').edatagrid('editRow', rowIndex); }",
                'onAdd'     => "function(index,row){
    var ed1 = jq('#$name').edatagrid('getEditor', {index:index,field:'host'}); jq(ed1.target).val('smtp.gmail.com');
    var ed2 = jq('#$name').edatagrid('getEditor', {index:index,field:'port'}); jq(ed2.target).numberbox('setValue', 587); }"],
            'source' => ['actions'=>['new'=>['order'=>10,'icon'=>'add','size'=>'large','events'=>['onClick'=>"jq('#$name').edatagrid('addRow');"]]]],
            'columns'=> [
                'action'=> ['order'=> 1,'label'=>lang('action'),   'attr'=>['width'=>80],
                    'actions'=> ['trash'=>['icon'=>'trash','order'=>20,'events'=>['onClick'=>"jq('#$name').edatagrid('destroyRow');"]]],
                    'events' => ['formatter'=>"function(value,row,index){ return ".$name."Formatter(value,row,index); }"]],
                'name'   => ['order'=>10,'label'=>lang('name'),       'attr'=>['width'=>200, 'editor'=>'text']],
                'user'   => ['order'=>20,'label'=>lang('email'),      'attr'=>['width'=>200, 'editor'=>'text']],
                'chkSmtp'=> ['order'=>30,'label'=>lang('enable_smtp'),'attr'=>['width'=>100],'events'=>['editor'=>"{type:'switchbutton'}"]],
                'host'   => ['order'=>40,'label'=>lang('host'),       'attr'=>['width'=>200, 'editor'=>'text']],
                'port'   => ['order'=>50,'label'=>lang('port'),       'attr'=>['width'=>200],'events'=>['editor'=>"{type:'numberbox',options:{precision:0}}"]],
                'pass'   => ['order'=>60,'label'=>lang('password'),   'attr'=>['width'=>200],'events'=>['editor'=>"{type:'textbox',options:{type:'password'}}"]]]];
    }

    private function dgStats($name='bizStats')
    {
        return ['id'=>$name, 'columns'=> [
            'Name'          => ['order'=>10,'label'=>lang('table'),              'attr'=>['width'=>200]],
            'Engine'        => ['order'=>20,'label'=>$this->lang['db_engine'],   'attr'=>['width'=>100]],
            'Rows'          => ['order'=>30,'label'=>$this->lang['db_rows'],     'attr'=>['width'=>100]],
            'Collation'     => ['order'=>40,'label'=>$this->lang['db_collation'],'attr'=>['width'=>200]],
            'Data_length'   => ['order'=>50,'label'=>$this->lang['db_data_size'],'attr'=>['width'=>100]],
            'Index_length'  => ['order'=>60,'label'=>$this->lang['db_idx_size'], 'attr'=>['width'=>100]],
            'Auto_increment'=> ['order'=>70,'label'=>$this->lang['db_next_id'],  'attr'=>['width'=>100]]]];
    }

}
