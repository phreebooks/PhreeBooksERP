<?php
/*
 * Bizuno dashboard - Login
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
 * @version    3.x Last Update: 2019-02-04
 * @filesource /lib/controller/module/bizuno/dashboards/login/login.php
 */

namespace bizuno;

define('DASHBOARD_LOGIN_VERSION','2.0');

class login
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'login';
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
        $portal= explode('.', $_SERVER['SERVER_ADDR']);
        $email = clean('bizunoUser', ['format'=>'email','default'=>''], 'cookie');
        $data  = ['type'=>'html', 'title'=>lang('login'),
            'username' => ['options'=>['width'=>300,'height'=>30,'value'=>"'$email'",'validType'=>'email'],'attr'=>['type'=>'email','value'=>clean('bizunoUser', 'text', 'cookie')]],
            'password' => ['options'=>['width'=>300,'height'=>30,'value'=>"''"],'attr'=>['type'=>'password']],
            'language' => ['label'=>lang('language'), 'values'=>viewLanguages(true), 'attr'=>['type'=>'select', 'value'=>clean('bizunoLang', 'text', 'cookie')]],
            'email'    => ['label'=>lang('email'),'options'=>['width'=>300,'height'=>30,'validType'=>'email'],'attr'=>['type'=>'email']],
            'image_title'=> ['label'=>getModuleCache('bizuno', 'properties', 'title'),'attr'=>['type'=>'img','src'=>BIZUNO_LOGO, 'height'=>'50']],
            'btnLogin' => ['attr'=>['type'=>'button','value'=>lang('login')],'styles'=>['cursor'=>'pointer'],
                'events' => ['onClick'=> "if (jq('#userLoginForm').form('validate')) jq('#userLoginForm').submit();"]],
            'btnLost'  => ['attr'=>['type'=>'button','value'=>lang('password_lost')],'styles'=>['cursor'=>'pointer'],
                'events' => ['onClick'=>"jq('#lostPWForm').submit();"]],
            'divs' => ['login'=>  ['order'=>50, 'src'=>BIZUNO_LIB."view/login.php"]],];
        $html = '<div><!-- login section -->
    <div id="divLogin" style="text-align:center">
        <form id="userLoginForm" action="'.BIZUNO_AJAX.'&p=bizuno/portal/login"><br />
            <p>'.html5('UserID',  $data['username']).'</p>
            <p>'.html5('UserPW',  $data['password']).'</p>
            <p>'.html5('UserLang',$data['language']).'</p>
            <div style="text-align:center;margin-top:30px">'.html5('btnLogin', $data['btnLogin']).'</div>
            <div style="text-align:left"><a style="cursor:pointer" onClick="jq(\'#divLogin\').hide(\'slow\'); jq(\'#divLostPW\').show(\'slow\');">'.lang('password_lost').'</a></div>
            <div style="text-align:right">('.$portal[3].')</div>
        </form>
    </div>
    <div id="divLostPW" style="display:none;"><!-- Lost password section -->
        <form id="lostPWForm" action="'.BIZUNO_AJAX.'&p=bizuno/portal/bizunoLostPW">
            <div style="text-align:center"><br />'.html5('email',  $data['email']).'</div>
            <div style="text-align:right">' .html5('btnLost',$data['btnLost'])."</div>
        </form>
    </div>
</div>\n";
        $js = "ajaxForm('userLoginForm');\najaxForm('lostPWForm');
jq('#userLoginForm').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') if (jq('#userLoginForm').form('validate')) { jq('body').addClass('loading'); jq('#userLoginForm').submit(); }
});
jq('#lostPWForm').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') jq('#lostPWForm').submit();
});
bizFocus('UserID');";
        $html .= htmlJS($js);
        return $html;
    }
}
