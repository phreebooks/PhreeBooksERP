<?php
/*
 * Bizuno dashboard - PhreeSoft News
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
 * @version    3.x Last Update: 2018-10-21
 * @filesource /lib/controller/module/bizuno/dashboards/ps_news/ps_news.php
 */

namespace bizuno;

define('DASHBOARD_PS_NEWS_VERSION','1.0');

class ps_news
{
    public  $moduleID  = 'bizuno';
    public  $methodDir = 'dashboards';
    public  $code      = 'ps_news';
    public  $category  = 'bizuno';
    public  $noSettings= true;
    private $maxItems  = 4;
    
    function __construct()
    {
        $this->security= 1;
        $this->lang    = getMethLang($this->moduleID, $this->methodDir, $this->code);
    }

    public function render()
    {
        global $io;
        $strXML= $io->cURLGet("https://www.phreesoft.com/feed/");
        $news  = parseXMLstring($strXML);
        msgDebug("\nNews object = ".print_r($news, true));
        $html = '<div><div id="'.$this->code.'_attr" style="display:none"><form id="'.$this->code.'Form" action=""></form></div>';
        $newsCnt = 0;
        if (!empty($news->channel->item)) {
            foreach ($news->channel->item as $entry) { 
                $html .= '<a href="'.$entry->link.'" target="_blank"><h3>'.$entry->title."</h3></a><p>$entry->description</p>";
                if ($newsCnt++ > $this->maxItems) { break; }
            }
            $html .= '</div></div><div style="min-height:4px;"> </div>';
        } else {
            $html .= "Sorry I cannot reach the PhreeSoft.com server. Please try again later.";
        }
        return $html;
    }
}
