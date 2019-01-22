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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2018-09-05
 * @filesource /lib/controller/module/bizuno/dashboards/lp_search/lp_search.php
 */

namespace bizuno;

define('DASHBOARD_LP_SEARCH_VERSION','1.0');

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

    public function render()
    {
        $data = [
            'google'   => ['attr'=>['type'=>'text','size'=>'48']],
            'yahoo'    => ['attr'=>['type'=>'text','size'=>'48']],
            'bing'     => ['attr'=>['type'=>'text','size'=>'48']],
            'btnGoogle'=> ['attr'=>['type'=>'button','value'=>$this->lang['google']],'styles'=>['cursor'=>'pointer'],
                'events' => ['onClick'=> "window.open('https://www.google.com/search?q='+jq('#google').val())"]],
            'btnYahoo' => ['attr'=>['type'=>'button','value'=>$this->lang['yahoo']], 'styles'=>['cursor'=>'pointer'],
                'events' => ['onClick'=> "window.open('https://search.yahoo.com?q='+jq('#yahoo').val())"]],
            'btnBing'  => ['attr'=>['type'=>'button','value'=>$this->lang['bing']],  'styles'=>['cursor'=>'pointer'],
                'events' => ['onClick'=> "window.open('https://www.bing.com?q='+jq('#bing').val())"]],
            'imgGoogle'=> ['label'=>$this->lang['google'],'attr'=>['type'=>'img','src'=>BIZUNO_URL.'controller/module/bizuno/dashboards/lp_search/google.png','height'=>'50']],
            'imgYahoo' => ['label'=>$this->lang['yahoo'], 'attr'=>['type'=>'img','src'=>BIZUNO_URL.'controller/module/bizuno/dashboards/lp_search/yahoo.png', 'height'=>'50']],
            'imgBing'  => ['label'=>$this->lang['bing'],  'attr'=>['type'=>'img','src'=>BIZUNO_URL.'controller/module/bizuno/dashboards/lp_search/bing.jpg',  'height'=>'50']],
        ];
        $html = '
<div><!-- lp_search section -->
    <p>'.html5('imgGoogle',$data['imgGoogle']).'<br />'.html5('google',$data['google']).html5('btnGoogle',$data['btnGoogle']).'</p>
    <p>'.html5('imgYahoo', $data['imgYahoo']) .'<br />'.html5('yahoo', $data['yahoo']) .html5('btnYahoo', $data['btnYahoo']) .'</p>
    <p>'.html5('imgBing',  $data['imgBing'])  .'<br />'.html5('bing',  $data['bing'])  .html5('btnBing',  $data['btnBing'])  .'</p>
</div>';
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
        $html .= htmlJS($js);
        return $html;
    }
}
