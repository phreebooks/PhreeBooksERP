<?php
/*
 * Bizuno dashboard - New User Portal
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
 * @filesource /lib/controller/module/bizuno/dashboards/new_user/new_user.php
 */

namespace bizuno;

class new_user
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'new_user';
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
        $portal= explode('.', $_SERVER['SERVER_ADDR']);
        $email = clean('bizunoUser', ['format'=>'email','default'=>''], 'cookie');
        $js    = "ajaxForm('userNewForm');
jq('#userNewForm').keypress(function(event){
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') if (jq('#userNewForm').form('validate')) { jq('body').addClass('loading'); jq('#userNewForm').submit(); }
});
bizFocus('pass');";
        $layout = array_merge_recursive($layout, [
            'divs'  => [
                'divLogin' =>['order'=>30,'type'=>'divs','attr'=>['id'=>'divLogin'],'divs'=>[
                    'head'    => ['order'=>10,'type'=>'html',  'html'=>"<p>&nbsp;</p>"],
                    'formBOF' => ['order'=>20,'type'=>'form',  'key' =>'userNewForm'],
                    'email'   => ['order'=>50,'type'=>'fields','keys'=>['email']],
                    'br1'     => ['order'=>51,'type'=>'html',  'html'=>"<br />"],
                    'pass'    => ['order'=>52,'type'=>'fields','keys'=>['pass']],
                    'br2'     => ['order'=>53,'type'=>'html',  'html'=>"<br />"],
                    'NewPW'   => ['order'=>54,'type'=>'fields','keys'=>['NewPW']],
                    'br3'     => ['order'=>55,'type'=>'html',  'html'=>"<br />"],
                    'NewPWRP' => ['order'=>56,'type'=>'fields','keys'=>['NewPWRP']],
                    'br4'     => ['order'=>57,'type'=>'html',  'html'=>"<br />"],
                    'UserLang'=> ['order'=>58,'type'=>'fields','keys'=>['UserLang']],
                    'btnStrt' => ['order'=>59,'type'=>'html',  'html'=>'<div style="text-align:right">'],
                    'btnLogin'=> ['order'=>60,'type'=>'fields','keys'=>['btnLogin']],
                    'btnEnd'  => ['order'=>61,'type'=>'html',  'html'=>"</div>"],
                    'formEOF' => ['order'=>90,'type'=>'html',  'html'=>"</form>"]]],
                'divSrv'   => ['order'=>99,'type'=>'html','html'=>'('.$portal[3].')']],
            'forms' => [
                'userNewForm'=> ['attr'=>['type'=>'form','action'=>BIZUNO_AJAX."&p=bizuno/portal/bizunoNewUser"]]],
            'fields'=> [
                'email'   => ['order'=>10,'label'=>lang('email'),            'options'=>['width'=>300,'height'=>30,'value'=>"'$email'",'validType'=>"'email'"],'attr'=>['value'=>$email,'required'=>true,'size'=>40]],
                'pass'    => ['order'=>20,'label'=>$this->lang['reset_code'],'attr'=>['type'=>'password','required'=>true]],
                'NewPW'   => ['order'=>30,'label'=>lang('password_new'),     'attr'=>['type'=>'password','required'=>true]],
                'NewPWRP' => ['order'=>40,'label'=>lang('password_confirm'), 'attr'=>['type'=>'password','required'=>true]],
                'UserLang'=> ['order'=>50,'label'=>lang('language'),'values'=>viewLanguages(),'attr'=>['type'=>'select','value'=>clean('bizunoLang', 'text', 'cookie')]],
                'btnLogin'=> ['order'=>60,'attr'=>['type'=>'button','value'=>$this->lang['btn_create_account']],'events'=>['onClick'=>"jq('#userNewForm').submit();"]]],
            'jsReady'=> ['init'=>$js]]);
    }
}
