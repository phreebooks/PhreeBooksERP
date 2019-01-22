<?php
/*
 * Bizuno dashboard - Reset Password
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
 * @version    3.x Last Update: 2018-05-01
 * @filesource /lib/controller/module/bizuno/dashboards/reset_password/reset_password.php
 */

namespace bizuno;

define('DASHBOARD_RESET_PASSWORD_VERSION','1.0');

class reset_password 
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'reset_password';
    public $noSettings= true;
    public $noCollapse= true;
    public $noClose   = true;

    function __construct()
    {
        $this->security= getUserCache('profile', 'biz_id', false, 0) ? 0 : 1; // only for the portal to log in
        $this->hidden  = true;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render()
    {
        $portal = explode('.', $_SERVER['SERVER_ADDR']);
        $data = ['type'    => 'html',
            'title'    => lang('reset_password'),
            'username'     => ['label'=>lang('email'), 'attr'=>['type'=>'text','required'=>'1','value'=>'','size'=>'40'],'classes'=>['easyui-validatebox']],
            'password'     => ['label'=>$this->lang['reset_code'],'attr'=>['type'=>'password','required'=>'1','size'=>'40'],'classes'=>['easyui-validatebox']],
            'newPass'      => ['label'=>lang('password_new'), 'attr'=>['type'=>'password','required'=>'1','size'=>'40'],'classes'=>['easyui-validatebox']],
            'newPassrepeat'=> ['label'=>lang('password_confirm'),'attr'=>['type'=>'password','required'=>'1','size'=>'40'],'classes'=>['easyui-validatebox']],
            'language'     => ['label'=>lang('language'), 'values'=>viewLanguages(),'attr'=>['type'=>'select']],
            'image_title'  => ['label'=>getModuleCache('bizuno','properties', 'title'),'attr'=>['type'=>'img','src'=>BIZUNO_LOGO,'height'=>'50']],
            'btnReset'     => ['attr'=>['type'=>'button','value'=>lang('reset')],'styles'=>['cursor'=>'pointer'],'events'=>['onClick'=>"jq('#pwResetForm').submit();"]]];
        $data['username']['attr']['value'] = clean('bizuno_user', 'text', 'cookie');
        $data['language']['attr']['value'] = clean('bizuno_lang', 'text', 'cookie');
        $html = '<div><!-- reset_password section -->
    <div id="divLogin" style="text-align:center">
        <form id="pwResetForm" method="post" action="'.BIZUNO_AJAX.'&p=bizuno/portal/bizunoResetPW">
            <p>'.html5('email',  $data['username']).'</p>
            <p>'.html5('pass',  $data['password']).'</p>
            <p>'.html5('NewPW',  $data['newPass']).'</p>
            <p>'.html5('NewPWRP',$data['newPassrepeat']).'</p>
            <div style="text-align:right">'.html5('btnReset', $data['btnReset']).'</div>
            <div style="text-align:right">('.$portal[3].')</div>
        </form>
    </div>
</div>';
        $js = "
ajaxForm('pwResetForm');
jq('#pwResetForm').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') if (jq('#pwResetForm').form('validate')) { jq('body').addClass('loading'); jq('#pwResetForm').submit(); }
});
bizFocus('UserID');";
        $html .= htmlJS($js);
        return $html;
    }
}
