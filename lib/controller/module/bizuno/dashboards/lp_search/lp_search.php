<?php
/*
 * Bizuno dsahboard - Search engine quicklink with search box
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
 * @version    4.x Last Update: 2020-04-22
 * @filesource /lib/controller/module/bizuno/dashboards/lp_search/lp_search.php
 */

namespace bizuno;

class lp_search
{
    public $moduleID = 'bizuno';
    public $methodDir= 'dashboards';
    public $code     = 'lp_search';
    public $category = 'general';

    function __construct()
    {
        $this->security= getUserCache('security', 'profile', 0);
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render(&$layout=[])
    {
        $js = "
jq('#google').keypress(function(event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') { window.open('https://www.google.com/search?q='+jq('#google').val()); }
});
jq('#yahoo').keypress(function(event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') { window.open('https://search.yahoo.com?q='+jq('#yahoo').val()); }
});
jq('#bing').keypress(function(event) {
    var keycode = (event.keyCode ? event.keyCode : event.which);
    if (keycode == '13') { window.open('https://www.bing.com?q='+jq('#bing').val()); }
});";
        $data = [
            'divs'  => [
                'google'=>['order'=>40,'type'=>'fields','keys'=>['imgGoogle','imgBrk','google','btnGoogle']],
                'yahoo' =>['order'=>50,'type'=>'fields','keys'=>['imgYahoo', 'imgBrk','yahoo', 'btnYahoo']],
                'bing'  =>['order'=>60,'type'=>'fields','keys'=>['imgBing',  'imgBrk','bing',  'btnBing']]],
            'fields'=> [
                'imgBrk'   => ['order'=>15,'html'=>"<br />",'attr'=>['type'=>'raw']],
                'google'   => ['order'=>20,'options'=>['width'=>250],'break'=>false,'attr'=>['type'=>'text']],
                'yahoo'    => ['order'=>20,'options'=>['width'=>250],'break'=>false,'attr'=>['type'=>'text']],
                'bing'     => ['order'=>20,'options'=>['width'=>250],'break'=>false,'attr'=>['type'=>'text']],
                'btnGoogle'=> ['order'=>30,'attr'=>['type'=>'button','value'=>$this->lang['google']],'styles'=>['cursor'=>'pointer'],
                    'events' => ['onClick'=> "window.open('https://www.google.com/search?q='+jq('#google').val())"]],
                'btnYahoo' => ['order'=>30,'attr'=>['type'=>'button','value'=>$this->lang['yahoo']], 'styles'=>['cursor'=>'pointer'],
                    'events' => ['onClick'=> "window.open('https://search.yahoo.com?q='+jq('#yahoo').val())"]],
                'btnBing'  => ['order'=>30,'attr'=>['type'=>'button','value'=>$this->lang['bing']],  'styles'=>['cursor'=>'pointer'],
                    'events' => ['onClick'=> "window.open('https://www.bing.com?q='+jq('#bing').val())"]],
                'imgGoogle'=> ['order'=>10,'label'=>$this->lang['google'],'attr'=>['type'=>'img','src'=>BIZUNO_URL.'controller/module/bizuno/dashboards/lp_search/google.png','height'=>'50']],
                'imgYahoo' => ['order'=>10,'label'=>$this->lang['yahoo'], 'attr'=>['type'=>'img','src'=>BIZUNO_URL.'controller/module/bizuno/dashboards/lp_search/yahoo.png', 'height'=>'50']],
                'imgBing'  => ['order'=>10,'label'=>$this->lang['bing'],  'attr'=>['type'=>'img','src'=>BIZUNO_URL.'controller/module/bizuno/dashboards/lp_search/bing.jpg',  'height'=>'50']]],
            'jsHead'=>['init'=>$js]];
        $layout = array_merge_recursive($layout, $data);
    }
}
