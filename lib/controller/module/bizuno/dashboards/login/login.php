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
 * @copyright  2008-2020, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    4.x Last Update: 2020-04-23
 * @filesource /lib/controller/module/bizuno/dashboards/login/login.php
 */

namespace bizuno;

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

    public function render(&$layout=[])
    {
        $portal = explode('.', $_SERVER['SERVER_ADDR']);
        $email  = clean('bizunoUser', ['format'=>'email','default'=>''], 'cookie');
        $divLost= '<span style="text-align:left"><a style="cursor:pointer" onClick="jq(\'#divLogin\').hide(\'slow\'); jq(\'#divLostPW\').show(\'slow\');">'.lang('password_lost').'</a></span><br />';
        $divSrv = '<span style="text-align:right">('.$portal[3].')</span>';
        $js     = "jq('#divLostPW').hide();
jq('#frmLogin').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') if (jq('#frmLogin').form('validate')) { jq('body').addClass('loading'); jq('#frmLogin').submit(); }
});
jq('#frmLostPW').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') jq('#frmLostPW').submit();
});
ajaxForm('frmLogin');
ajaxForm('frmLostPW');
bizFocus('UserPW');";
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'divLogin' =>['order'=>30,'type'=>'divs','attr'=>['id'=>'divLogin'],'divs'=>[
                    'head'    => ['order'=>10,'type'=>'html',  'html'=>"<p>&nbsp;</p>"],
                    'formBOF' => ['order'=>20,'type'=>'form',  'key' =>'frmLogin'],
                    'UserID'  => ['order'=>50,'type'=>'fields','keys'=>['UserID']],
                    'br1'     => ['order'=>51,'type'=>'html',  'html'=>"<br />"],
                    'UserPW'  => ['order'=>52,'type'=>'fields','keys'=>['UserPW']],
                    'br2'     => ['order'=>53,'type'=>'html',  'html'=>"<br />"],
                    'UserLang'=> ['order'=>54,'type'=>'fields','keys'=>['UserLang']],
                    'btnStrt' => ['order'=>55,'type'=>'html',  'html'=>'<div style="text-align:right">'],
                    'btnLogin'=> ['order'=>56,'type'=>'fields','keys'=>['btnLogin']],
                    'btnEnd'  => ['order'=>57,'type'=>'html',  'html'=>"</div>"],
                    'formEOF' => ['order'=>90,'type'=>'html',  'html'=>"</form>"],
                    'divLost' => ['order'=>95,'type'=>'html',  'html'=>$divLost]]],
                'divLostPW'=> ['order'=>70,'type'=>'divs','attr'=>['id'=>'divLostPW'],'divs'=>[
                    'head'    => ['order'=>10,'type'=>'html',  'html'=>"<p>&nbsp;</p>"],
                    'formBOF' => ['order'=>20,'type'=>'form',  'key' =>'frmLostPW'],
                    'tabs'    => ['order'=>50,'type'=>'fields','keys'=>['email','btnLost']],
                    'formEOF' => ['order'=>99,'type'=>'html',  'html'=>"</form>"]]],
                'divSrv'   => ['order'=>99,'type'=>'html',  'html'=>$divSrv]],
            'forms' => [
                'frmLogin' => ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&bizRt=bizuno/portal/login"]],
                'frmLostPW'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&bizRt=bizuno/portal/bizunoLostPW"]]],
            'fields'=> [
                'UserID'   => ['order'=>10,'label'=>lang('email'),   'options'=>['width'=>300,'height'=>30,'value'=>"'$email'",'validType'=>"'email'"],'attr'=>['type'=>'email','value'=>$email]],
                'UserPW'   => ['order'=>20,'label'=>lang('password'),'options'=>['width'=>300,'value'=>"''"],'attr'=>['type'=>'password', 'value'=>'']],
                'UserLang' => ['order'=>30,'label'=>lang('language'),'values'=>viewLanguages(true),'attr'=>['type'=>'select','value'=>clean('bizunoLang', 'text', 'cookie')]],
                'btnLogin' => ['order'=>40,'attr'=>['type'=>'button','value'=>lang('login')],'styles'=>['cursor'=>'pointer'],
                    'events' => ['onClick'=> "if (jq('#frmLogin').form('validate')) { jq('#frmLogin').submit(); }"]],
                'email'    => ['order'=>70,'label'=>lang('email'),'options'=>['width'=>300,'height'=>30,'validType'=>'email'],'attr'=>['type'=>'email']],
                'btnLost'  => ['order'=>80,'attr'=>['type'=>'button','value'=>lang('password_lost')],'styles'=>['cursor'=>'pointer'],
                    'events' => ['onClick'=>"jq('#frmLostPW').submit();"]]],
            'jsReady'=> ['init'=>$js]]);
    }
}
