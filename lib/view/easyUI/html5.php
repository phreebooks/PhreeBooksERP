<?php

/*
 * This class maps the structure to HTML syntax using the jQuery easyUI UI
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
 * @copyright  2008-2018, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-06-19
 * @filesource /view/easyUI/html5.php
 */

namespace bizuno;

final class html5 {

    const htmlEditor = 'https://cdn.tinymce.com/4/tinymce.min.js';
    const bizunoHelp = 'https://www.bizuno.com?p=bizuno/portal/helpMain';

    private $pageT = '50';  // page layout minimum pixel height
    private $pageB = '35';
    private $pageL = '175';
    private $pageR = '175';
    public $jsHead = [];
    public $jsBody = [];
    public $jsReady= [];

    function __construct() {
        
    }

    /**
     * This function builds a div element based on a type and structure
     * @param string $output - running output buffer
     * @param array $data - data structure to be processed (typically within the div)
     */
    public function buildDivs(&$output, $data) {
        $data['divs'] = sortOrder($data['divs']);
        foreach ($data['divs'] as $prop) { $this->buildDiv($output, $prop); }
    }

    /**
     * This function builds a div element based on a type and structure
     * @param string $output - running output buffer
     * @param array $data - data structure to be processed (typically within the div)
     * @param array $prop - type of div to build and structure
     */
    public function buildDiv(&$output, $prop) {
        global $viewData;
        $closeDiv = false;
        if (!empty($prop['hidden'])) { return '';}
        if ( empty($prop['type']))   { $prop['type'] = 'template'; } // default
        if (!empty($prop['attr'])) { 
            $prop['attr']['type'] = 'div';
            $output['body'] .= $this->render(!empty($prop['attr']['id'])?$prop['attr']['id']:'', $prop);
            $closeDiv = true;
        }
        switch ($prop['type']) {
            case 'accordion':
                if (isset($prop['key'])) {  // legacy to old style
                    $prop = array_merge($viewData['accordion'][$prop['key']], ['id'=>$prop['key']]);
                }
                $this->layoutAccordion($output, $prop);
                break;
            case 'address':
                $this->layoutAddress($output, $prop); break;
            case 'datagrid':
                if (isset($prop['key'])) { $prop['el'] = $viewData['datagrid'][$prop['key']]; } // legacy to old style
                $this->layoutDatagrid($output, $prop['el']);
                break;
            case 'divs': $this->buildDivs($output, $prop); break;
            case 'fields':
                if (!empty($prop['label'])) { $output['body'] .= $prop['label']."<br />"; }
                sortOrder($prop);
                foreach ($prop['fields'] as $key => $attr) { $output['body'] .= $this->render($key, $attr); }
                break;
            case 'form': $output['body'] .= $this->render($prop['key'], $viewData['forms'][$prop['key']]); break;
            case 'html': $output['body'] .= $prop['html']."\n"; break;
            case 'menu': $this->menu($output, $prop); break;
            case 'payments':
                foreach ($viewData['payment_methods'] as $methID) {
                    require_once(BIZUNO_LIB."controller/module/payment/methods/$methID/$methID.php");
                    $totSet = getModuleCache('payment','methods',$methID,'settings');
                    $fqcn = "\\bizuno\\$methID";
                    $totals = new $fqcn($totSet);
                    $totals->render($output, $viewData);
                }
                break;
            case 'table':
                if (isset($prop['key'])) {  // legacy to old style
                    $prop = array_merge($prop, $viewData['tables'][$prop['key']]);
                    $prop['attr']['id'] = $prop['key'];
                }
                $this->layoutTable($output, $prop);
                break;
            case 'tabs':
                if (isset($prop['key'])) {  // legacy to old style
                    $prop = array_merge($viewData['tabs'][$prop['key']], ['id'=>$prop['key']]);
                }
                $this->layoutTab($output, $prop);
                break;
            case 'toolbar':
                if (isset($prop['key'])) {  // legacy to old style
                    $prop = array_merge($viewData['toolbars'][$prop['key']], ['id' => $prop['key']]);
                }
                $this->layoutToolbar($output, $prop);
                break;
            case 'totals':
                foreach ($viewData['totals_methods'] as $methID) {
                    require_once(BIZUNO_LIB."controller/module/phreebooks/totals/$methID/$methID.php");
                    $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
                    $fqcn = "\\bizuno\\$methID";
                    $totals = new $fqcn($totSet);
                    $totals->render($output, $viewData);
                }
                break;
            case 'tree':
                if (isset($prop['key'])) {  // legacy to old style
                    $prop['el'] = array_merge($viewData['tree'][$prop['key']], ['id' => $prop['key']]);
                }
                $this->layoutTree($output, $prop['el']);
                break;
            default:
            case 'template': // set some variables if needed
//                msgDebug("\ntemplate for buildDiv = ".print_r($prop, true));
                if (!isset($prop['settings']) && isset($prop['attr'])) {
                    $prop['settings'] = $prop['attr'];
                } // for legacy
                if (isset($prop['src']) && file_exists($prop['src'])) {
                    require ($prop['src']);
                }
                break;
        }
        if ($closeDiv) { $output['body'] .= "</div>"; }
    }

    public function render($id = '', $prop = []) {
    if (!is_array($prop)) { return msgAdd("received string as array for field $id"); }
        if (empty($prop['attr']['type'])) { $prop['attr']['type'] = 'text'; } // assume text if no type
        $field = '';
        if (isset($prop['hidden']) && $prop['hidden']) { return $field; }
        if (isset($prop['icon'])) { return $this->menuIcon($id, $prop); }
        switch ($prop['attr']['type']) {
            case 'a':
            case 'address':
            case 'article':
            case 'aside':
            case 'b':
            case 'em':
            case 'fieldset':
            case 'footer':
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
            case 'label':
            case 'p':
            case 'span':
            case 'u': return $this->htmlElBoth($id, $prop);
            case 'br':
            case 'hr':
            case 'img': return $this->htmlElEmpty($id, $prop);
            case 'div':
            case 'form':
            case 'section':
            case 'header':
            case 'td':
            case 'th':
            case 'tr':
            case 'thead':
            case 'tbody':
            case 'tfoot':
                return $this->htmlElOpen($id, $prop);
            case 'button': return $this->inputButton($id, $prop);
//            case 'iframe':          return $this->layoutIframe($output, $id, $prop);
            case 'checkbox': return $this->inputCheckbox($id, $prop);
            case 'color': return $this->inputColor($id, $prop);
            case 'currency': return $this->inputCurrency($id, $prop);
            case 'date':
            case 'datetime-local':
            case 'datetime': return $this->inputDate($id, $prop);
            case 'email': return $this->inputEmail($id, $prop);
            case 'decimal':
            case 'float':
            case 'integer': return $this->inputNumber($id, $prop);
            case 'file':
            case 'hidden':
            case 'input':
            case 'month':
            case 'number':
            case 'password':
            case 'phone':
            case 'tel':
            case 'text':
            case 'time':
            case 'week':
            case 'year': return $this->input($id, $prop);
            case 'selNoYes': return $this->selNoYes($id, $prop);
            case 'radio': return $this->inputRadio($id, $prop);
            case 'raw': return $this->inputRaw($id, $prop);
            case 'select': return $this->inputSelect($id, $prop);
            case 'html':
            case 'htmlarea': // @todo - need to deprecate htmlarea, replace with either html or textarea
            case 'textarea': return $this->inputTextarea($id, $prop);
            case 'table': return $this->layoutTable($id, $prop);


            case 'abbr': //Defines an abbreviation or an acronym
            case 'area': //Defines an area inside an image-map
            case 'audio': //Defines sound content
            case 'base': //Specifies the base URL/target for all relative URLs in a document
            case 'bdi': //Isolates a part of text that might be formatted in a different direction from other text outside it
            case 'bdo': //Overrides the current text direction
            case 'blockquote': //Defines a section that is quoted from another source
            case 'canvas': //Used to draw graphics, on the fly, via scripting (usually JavaScript)
            case 'caption': //Defines a table caption
            case 'cite': //Defines the title of a work
            case 'code': //Defines a piece of computer code
            case 'col': //Specifies column properties for each column within a case 'colgroup': element 
            case 'colgroup': //Specifies a group of one or more columns in a table for formatting
            case 'datalist': //Specifies a list of pre-defined options for input controls
            case 'dd': //Defines a description/value of a term in a description list
            case 'del': //Defines text that has been deleted from a document
            case 'details': //Defines additional details that the user can view or hide
            case 'dfn': //Represents the defining instance of a term
            case 'dialog': //Defines a dialog box or window
            case 'dl': //Defines a description list
            case 'dt': //Defines a term/name in a description list
            case 'embed': //Defines a container for an external (non-HTML) application
            case 'figcaption': //Defines a caption for a case 'figure': element
            case 'figure': //Specifies self-contained content
            case 'i': //Defines a part of text in an alternate voice or mood
            case 'ins': //Defines a text that has been inserted into a document
            case 'kbd': //Defines keyboard input
            case 'keygen': //Defines a key-pair generator field (for forms)
            case 'label': //Defines a label for an case 'input': element
            case 'legend': //Defines a caption for a case 'fieldset': element
            case 'li': //Defines a list item
            case 'link': //Defines the relationship between a document and an external resource (most used to link to style sheets)
            case 'main': //Specifies the main content of a document
            case 'map': //Defines a client-side image-map
            case 'mark': //Defines marked/highlighted text
            case 'menu': //Defines a list/menu of commands
            case 'menuitem': //Defines a command/menu item that the user can invoke from a popup menu
            case 'meta': //Defines metadata about an HTML document
            case 'meter': //Defines a scalar measurement within a known range (a gauge)
            case 'object': //Defines an embedded object
            case 'ol': //Defines an ordered list
            case 'output': //Defines the result of a calculation
            case 'param': //Defines a parameter for an object
            case 'picture': //Defines a container for multiple image resources
            case 'pre': //Defines preformatted text
            case 'progress': //Represents the progress of a task
            case 'q': //Defines a short quotation
            case 'range': // Bizuno added
            case 'reset': // Bizuno Added
            case 'rp': //Defines what to show in browsers that do not support ruby annotations
            case 'rt': //Defines an explanation/pronunciation of characters (for East Asian typography)
            case 'ruby': //Defines a ruby annotation (for East Asian typography)
            case 's': //Defines text that is no longer correct
            case 'samp': //Defines sample output from a computer program
            case 'script': //Defines a client-side script
            case 'search': // Bizuno Added
            case 'small': //Defines smaller text
            case 'source': //Defines multiple media resources for media elements (case 'video': and case 'audio':)
            case 'strong': //Defines important text
            case 'style': //Defines style information for a document
            case 'sub': //Defines subscripted text
            case 'summary': //Defines a visible heading for a case 'details': element
            case 'sup': //Defines superscripted text
            case 'time': //Defines a date/time
            case 'title': //Defines a title for the document
            case 'track': //Defines text tracks for media elements (case 'video': and case 'audio':)
            case 'ul': //Defines an unordered list
            case 'url': // Bizuno Added
            case 'var': //Defines a variable
            case 'video': //Defines a video or movie
            case 'wbr': //Defines a possible line-break
            // special cases and adjustments
            default:
                msgDebug("\nUndefined Element type {$prop['attr']['type']} with properties: " . print_r($prop, true));
                msgAdd("Undefined element type {$prop['attr']['type']}", 'trap');
        }
        if (isset($prop['break']) && $prop['break'] && $prop['attr']['type'] <> 'hidden') {
            $field .= "<br />\n";
        }
        if (isset($prop['js'])) {
            $this->jsBody[] = $prop['js'];
        }
        return $field;
    }

    /*     * *************************** Elements ***************** */

    private function htmlElBoth($id, $prop) {
        if (isset($prop['attr']['value'])) {
            $tmp = isset($prop['format']) ? viewFormat($prop['attr']['value'], $prop['format']) : $prop['attr']['value'];
            $value = str_replace('"', '&quot;', $tmp);
            unset($prop['attr']['value']);
        } else {
            $value = '&nbsp;';
        }
        $type = $prop['attr']['type'];
        unset($prop['attr']['type']);
        $output = "<$type" . $this->addAttrs($prop) . ">" . $value . "</$type>\n";
        return $output . (!empty($prop['break']) ? '<br />' : '');
    }

    public function htmlElEmpty($id, $prop) {
        return "<{$prop['attr']['type']}" . $this->addAttrs($prop) . " />";
    }

    private function htmlElOpen($id, $prop) {
        $this->addID($id, $prop);
        $type = $prop['attr']['type'];
        unset($prop['attr']['type']);
        return "<$type" . $this->addAttrs($prop) . ">";
    }

    /*     * *************************** Headings ***************** */

    // H1-H6

    /*     * *************************** Tables ***************** */
    public function div() {
        $field .= '<' . $prop['attr']['type'];
        foreach ($prop['attr'] as $key => $value) {
            if ($key <> 'type') {
                $field .= ' ' . $key . '="' . str_replace('"', '\"', $value) . '"';
            } // was str_replace('"', '&quot;', $value)
        }
        $field .= ">";
    }

    /**
     * This function generates the tables pulled from the current structure, position: $data['tables'][$idx]
     * @param array $output - running HTML string to render the page
     * @param string $data - The structure source data to pull from
     * @param array $idx - The index in $data to grab the structure to build
     * @return string - HTML formatted EasyUI tables appended to $output
     */
    function table(&$output, $id = '', $prop = []) {
        $output['body'] .= $this->render($id, $prop) . "\n";
        if (!empty($prop['thead'])) {
            $this->tableRows($output, $prop['thead']) . "</thead>\n";
        }
        if (!empty($prop['tbody'])) {
            $this->tableRows($output, $prop['tbody']) . "</tbody>\n";
        }
        if (!empty($prop['tfoot'])) {
            $this->tableRows($output, $prop['tfoot']) . "</tfoot>\n";
        }
        $output['body'] .= "</table><!-- End table $id -->\n";
    }

    function tableRows(&$output, $prop) {
        $output['body'] = $this->render('', $prop) . "\n";
        foreach ($prop['tr'] as $tr) {
            $output['body'] .= $this->render('', $tr);
            foreach ($tr['td'] as $td) {
                $value = $td['attr']['value'];
                unset($td['attr']['value']);
                $output['body'] .= $this->render('', $td) . $value . "</" . $td['attr']['type'] . ">";
            }
            $output['body'] .= "</tr>\n";
        }
    }

    /*     * *************************** Lists ***************** */
    /*     * *************************** Navigation ***************** */

    /**
     * This function takes the menu structure and builds the easyUI HTML markup
     * @param string $output - The running HTML output
     * @return string - HTML formatted EasyUI menu appended to $output
     */
    function menu(&$output, $prop) {
        if (empty($prop['data']['child'])) { return; }
        $prop['attr']['type'] = 'div';
        $prop['data']['attr']['type'] = 'div';
//        $prop['classes'][] = 'menuHide';
        if (empty($prop['size'])) { $prop['size'] = 'small'; }
        $orient    = in_array($prop['region'], ['east', 'left', 'right', 'west']) ? 'v' : 'h';
        $hideLabel = !empty($prop['hideLabels']) ? true : false;
        $hideBorder= !empty($prop['hideBorder']) ? true : false;
        if ($orient == 'v') { 
            $prop['classes'][] = 'easyui-menu';
            $prop['attr']['data-options'] = 'inline:true';
        } //" data-options="inline:true">'; }
        $output['body'] .= $this->htmlElOpen('', $prop)."\n";
        $output['body'] .= $this->menuChild($prop['data']['child'], $prop['size'], $orient, $hideLabel, $hideBorder);
//        if ($orient == 'v') { $output['body'] .= "  </div>\n"; }
        $output['body'] .= "</div>\n";
    }

    /**
     * This function takes a menu child structure and builds the easyUI HTML markup, it is recursive for multi-level menus
     * @param string $id - root ID for the parent element
     * @param string $struc - The menu structure
     * @return string - HTML formatted EasyUI menu (child) appended to parent menu $output
     */
    public function menuChild($struc=[], $size='small', $orient='v', $hideLabel=false, $hideBorder=false) {
        $output = '';
        $subQueue = [];
        $structure = sortOrder($struc);
        foreach ($structure as $subid => $submenu) {
            $options = [];
            if (!isset($submenu['hidden'])) { $submenu['hidden'] = false; }
            if (!isset($submenu['security']) || isset($submenu['child'])) { $submenu['security'] = 1; }
            if ($submenu['hidden'] || empty($submenu['security'])) { continue; }
            if (empty($submenu['child']) && !empty($submenu['icon']) && $orient=='h' && ($hideLabel || !empty($submenu['hideLabel']))) { // just an icon
                $output .= $this->menuIcon($subid, $submenu);
                continue;
            }
            if (!empty($submenu['type']) && $submenu['type'] == 'field') {
                $output .= $this->render($subid, $submenu);
                continue;
            }
            if (empty($submenu['attr']['id'])) { $submenu['attr']['id'] = $subid; }
            if ($orient == 'h') {
                if (empty($submenu['child'])) {
                    $submenu['classes'][] = 'easyui-linkbutton';
                } else {
                    $submenu['classes'][] = 'easyui-splitbutton';
                }
                $options[] = "plain:false";
            }
            if (isset($submenu['popup'])) {
                $submenu['events']['onClick'] = "winOpen('$subid','{$submenu['popup']}');";
            }
            if (isset($submenu['icon']) && $size == 'small') { $options[] = "iconCls:'icon-{$submenu['icon']}', size:'small'"; }
            if (isset($submenu['icon']) && $size == 'large') { $options[] = "iconCls:'iconL-{$submenu['icon']}',size:'large'"; }
            if ($orient == 'h' && !empty($submenu['child'])) { $options[] = "menu:'#sub_{$subid}'"; }
            if (!empty($submenu['disabled'])) { $options[] = "disabled:true"; }
            $label = !empty($submenu['label']) ? $submenu['label'] : lang($subid);
            $submenu['attr']['title'] = !empty($submenu['tip']) ? $submenu['tip'] : $label;
            if (sizeof($options)) { $submenu['attr']['data-options'] = implode(',', $options); }
            if ($orient == 'h') {
                if (isset($submenu['child'])) { $subQueue[] = ['id' => "sub_{$subid}", 'menu' => $submenu['child']]; }
                $submenu['attr']['type'] = 'a';
                $output .= "  " . $this->htmlElOpen($subid, $submenu) . ($hideLabel ? '' : $label) . "</a>\n";
            } else {
                $submenu['attr']['type'] = 'div';
                $output .= "  " . $this->htmlElOpen($subid, $submenu) . "<span>".($hideLabel ? '' : $label)."</span>"; // <div>
                if (isset($submenu['child'])) { $output .= "\n<div>" . $this->menuChild($submenu['child'], 'small', 'v') . "</div>\n"; }
                $output .= '  </div>' . "\n";
            }
        }
        if ($orient == 'h') {
            foreach ($subQueue as $child) { // process the submenu queue
                $output .= '  <div id="' . $child['id'] . '">' . $this->menuChild($child['menu'], 'small', 'v') . "</div>\n";
            }
        }
        return $output;
    }

    /**
     * 
     * @param type $id
     * @param type $prop
     * @return type
     */
    public function menuIcon($id, $prop) {
        if (!isset($prop['size'])) { $prop['size'] = 'large'; } // default to large icons
        $prop['attr']['type'] = 'span';
        switch ($prop['size']) {
            case 'small': $prefix = "icon";  $size = '16px'; break;
            case 'meduim':$prefix = "iconM"; $size = '24px'; break;
            case 'large':
            default:      $prefix = "iconL"; $size = '32px'; break;
        }
        $prop['classes'][]          = "{$prefix}-{$prop['icon']}";
        $prop['styles']['border']   = '0';
        $prop['styles']['display']  = 'inline-block;vertical-align:middle';
        $prop['styles']['height']   = $size;
        $prop['styles']['min-width']= $size;
        $prop['styles']['cursor']   = 'pointer';
        $prop['attr']['title']      = isset($prop['label']) ? $prop['label'] : lang($prop['icon']);
        if ($prop['icon'] == 'help') {
            if (!isset($prop['index'])) { $prop['index'] = ''; }
            $prop['events']['onClick'] = "window.open('" . self::bizunoHelp . "&amp;idx={$prop['index']}','help','width=800,height=600,resizable=1,scrollbars=1,top=100,left=100')";
        }
        unset($prop['icon']);
        return $this->render($id, $prop);
    }

    /***************************** Layout ******************/
    public function layoutMobile() {
        msgAdd('layoutMobile: This functionality is not available yet.');
        // shrink menu to just icons, 2 or 3 elements, rest to More..., only display menu elements that are allowed
        // remove footer
        // set global screen size so div builder knows what to do
        // allowed menus, customers, vendors, sales, CRM, purchases, cash receipts, bank register, inventory view, dashboards (which ones), profile, roles, users, extensions (???)
        // not allowed: reports except email only, pay bills, pricing, general journal, settings, tools, 
        // how to handle regions? standardize? menu bottom, quickBar rolled inot menu, ??
        // all view divs need to be divisible by 3, 4 (for orders) to accordion style input
        // toolbars need to be placed at bottom of form as it is a more sequential, autosave??
        // hover pull down menus will need to be click operated instead, no more dashboards on menus
        // sub menus will need to be a dashboard when menu is clicked to show then click to get to function
    }

    public function layoutTablet() {
        msgAdd('layoutTablet: This functionality is not available yet.');
        // will look a lot like mobile except 2 columns instead of 1, allow all features
    }

    public function layoutDesktop(&$output, $data) {
//        $output['body'] .= '<body class="easyui-layout">' . "\n";
        if (empty($data['divs'])) { return msgAdd('No divs were found, the page does not contain any content!'); }
        $data['divs'] = sortOrder($data['divs']);
        // now separate by region to take advantage of layout
        $regions = [];
        $dim = [];
        foreach ($data['divs'] as $id => $settings) {
            $curRgn = isset($settings['region']) ? $settings['region'] : 'center'; // default to center region
            $region = str_replace(["top", "bottom", "right", "left"], ["north", "south", "east", "west"], $curRgn); // map to codex
            $regions[$region][$id] = $settings;
            $dim[$region]['height'] = isset($settings['height']) ? $settings['height'] : false;
            $dim[$region]['width'] = isset($settings['width']) ? $settings['width'] : false;
        }
        $compass = ['north', 'west', 'center', 'east', 'south'];
        foreach ($compass as $region) {
            if (empty($dim[$region])) { continue; }
            switch ($region) {
                case 'north': $divID = 'bodyNorth';
                    $opts = "region:'north',border:false,style:{borderWidth:0},minHeight:" . ($dim[$region]['height'] ? $dim[$region]['height'] : $this->pageT);
                    break;
                case 'south': $divID = 'bodySouth';
                    $opts = "region:'south',border:false,style:{borderWidth:0},minHeight:" . ($dim[$region]['height'] ? $dim[$region]['height'] : $this->pageB);
                    break;
                case 'west': $divID = 'bodyWest';
                    $opts = "region:'west',border:false,style:{borderWidth:0},width:" . ($dim[$region]['width'] ? $dim[$region]['width'] : $this->pageL);
                    break;
                case 'east': $divID = 'bodyEast';
                    $opts = "region:'east',border:false,style:{borderWidth:0},width:" . ($dim[$region]['width'] ? $dim[$region]['width'] : $this->pageR);
                    break;
                default:
                case 'center':$divID = 'bodyCenter';
                    $opts = "region:'center',border:false,style:{borderWidth:0},minHeight:10,minWidth:200";
            }
            $output['body'] .= '<div id="' . $divID . '" data-options="' . $opts . ',split:true">' . "\n";
            foreach ($regions[$region] as $settings) {
                $this->buildDiv($output, $settings);
            }
            $output['body'] .= "</div>\n";
        }
        $output['body'] .= '<iframe id="attachIFrame" src="" style="display:none;visibility:hidden;"></iframe>' . "\n"; // For file downloads
        $output['body'] .= '<div class="modal"></div><div id="divChart"></div>' . "\n"; // Place at bottom of page
    }

    /**
     * This function builds the HTML output render a jQuery easyUI accordion feature
     * @param string $output - running string of them HTML output to be add to
     * @param array $data - complete data array containing structure of entire page, only JavaScript part is used to force load from JavaScript data
     * @param string $id - accordion DOM ID
     * @param array $settings - the structure of the accordions (i.e. div structure for each accordion)
     */
    public function layoutAccordion(&$output, $prop) {
        $prop['divs'] = sortOrder($prop['divs']);
        $output['body'] .= '  <div id="' . $prop['id'] . '" class="easyui-accordion" style="width:auto;height:auto;"';
        if (isset($prop['attr'])) {
            $temp = [];
            foreach ($prop['attr'] as $key => $value) {
                $temp[] = "$key:$value";
            }
            $output['body'] .= ' data-options="' . implode(',', $temp) . '"';
        }
        $output['body'] .= "><!-- Begin accordion group {$prop['id']} -->\n";
        foreach ($prop['divs'] as $accID => $accContents) {
            $output['body'] .= '     <div id="' . $accID . '" title="' . $accContents['label'] . '" style="padding:10px;"';
            if (isset($accContents['attr'])) {
                $temp = [];
                foreach ($accContents['attr'] as $key => $value) {
                    $temp[] = "$key:" . encodeType($value);
                }
                $output['body'] .= ' data-options="' . implode(',', $temp) . '"';
            }
            $output['body'] .= "><!-- BOF accordion " . $accContents['label'] . " -->\n";
            $this->buildDiv($output, $accContents);
            $output['body'] .= "     </div> <!-- EOF accordion " . $accContents['label'] . " -->\n";
        }
        $output['body'] .= "  </div>\n";
        if (isset($prop['select'])) {
            $output['jsBody'][] = "jq('#{$prop['id']}').accordion('select','{$prop['select']}');";
        }
    }

    /**
     * 
     * @param type $output
     * @param type $props
     */
    public function layoutAddress(&$output, $props) {
        $defaults = ['type'=>defined('CONTACT_TYPE') ? constant('CONTACT_TYPE') : 'c',
            'format'=>'short','suffix'  =>'',   'search'=>false,  'props'=>true,'clear'=>true, 'copy'=>false,
            'update'=>false,  'validate'=>false,'required'=>false,'store'=>true,'drop' =>false,'fill'=>'none','notes'=>false];
        $attr      = array_replace($defaults, $props['settings']);
        $structure = $props['content'];
        if ($attr['format'] != 'long') { unset($structure['country']['label']); }
        $structure['email']['attr']['size'] = 32; // keep this from overlapping with other divs
        if (!empty($attr['required'])) { foreach (array_keys($structure) as $field) {
            if (getModuleCache('contacts', 'settings', 'address_book', $field)) { $structure[$field]['attr']['required'] = 1; }
        } }
        if (!$attr['search']) { $structure['contactSel'] = ['attr'=>['type'=>'hidden']]; }
        else {
            $structure['contactSel'] = ['classes'=>['easyui-combogrid'],'attr'=>['data-options'=>"
                width:130, panelWidth:750, delay:900, idField:'id', textField:'primary_name', mode: 'remote',iconCls: 'icon-search', hasDownArrow:false,
                url:'".BIZUNO_AJAX."&p=contacts/main/managerRows&clr=1&type=".($attr['drop']?'c':$attr['type'])."&store=".($attr['store']?'1':'0')."',
                onBeforeLoad:function (param) { var newValue = jq('#contactSel{$attr['suffix']}').combogrid('getValue'); if (newValue.length < 3) return false; },
                selectOnNavigation:false,
                onClickRow:  function (idx, row){ contactsDetail(row.id, '{$attr['suffix']}', '{$attr['fill']}'); },
                columns: [[{field:'id', hidden:true},
                    {field:'short_name',  title:'".jsLang('contacts_short_name')."', width:100},
                    {field:'type',        hidden:".(strlen($attr['type'])>1?'false':'true').",title:'".jsLang('contacts_type')."', width:100},
                    {field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
                    {field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
                    {field:'city',        title:'".jsLang('address_book_city')."', width:100},
                    {field:'state',       title:'".jsLang('address_book_state')."', width: 50},
                    {field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
                    {field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}]]"]];
        }
        // build pull down selection
        if (!empty($props['label'])) { $output['body'] .= "<label>".$props['label']."</label><br />\n"; }
        if ($attr['clear']) { $output['body'] .= ' '.html5('', ['icon'=>'clear','size'=>'small','events'=>['onClick'=>"addressClear('{$attr['suffix']}')"]])."\n"; }
        if ($attr['validate'] && getModuleCache('extShipping', 'properties', 'status')) {
            $output['body'] .= ' '.html5('', ['icon'=>'truck','size'=>'small','label'=>lang('validate_address'),'events'=>['onClick'=>"shippingValidate('{$attr['suffix']}');"]])."\n";
        }
        if ($attr['copy']) {
            $src = explode(':', $attr['copy']);
            if (empty($src[1])) { $src = ['_b', '_s']; } // defaults
            $output['body'] .= ' '.html5('',['icon'=>'copy','size'=>'small','events'=>['onClick'=>"addressCopy('{$src[0]}', '{$src[1]}')"]])."\n";
        }
        $output['body'] .= "<br />\n";

        $output['body'] .= '<div id="contactDiv'.$attr['suffix'].'"'.($attr['drop']?' style="display:none"':'').'>'."\n";
        $output['body'] .= html5('contactSel'.$attr['suffix'], $structure['contactSel']);
        $output['body'] .= '<span id="spanContactProps'.$attr['suffix'].'" style="display:none">&nbsp;';
        if ($attr['props']) { $output['body'] .= html5('contactProps'.$attr['suffix'], ['icon'=>'settings', 'size'=>'small',
            'events' => ['onClick'=>"windowEdit('contacts/main/properties&rID='+jq('#contact_id{$attr['suffix']}').val(), 'winContactProps', '".jsLang('details')."', 1000, 600);"]]);
        }
        $output['body'] .= '</span></div>';

        // Address select (hidden by default)
        $output['body'] .= '  <div id="addressDiv'.$attr['suffix'].'" style="display:none">'.html5('addressSel'.$attr['suffix'], ['attr'=>['type'=>'text']])."</div>\n";
        $output['jsBody']['addrCombo'.$attr['suffix']] = "var addressVals{$attr['suffix']} = ".(isset($data['address'][$attr['suffix']]) ? $data['address'][$attr['suffix']] : "[]").";";
        $output['jsReady']['addrCombo'.$attr['suffix']] = "jq('#addressSel{$attr['suffix']}').combogrid({
            width:     150,
            panelWidth:750,
            data:      addressVals{$attr['suffix']},
            idField:   'id',
            textField: 'primary_name',
            onSelect:  function (id, data){ addressFill(data, '{$attr['suffix']}'); },
            columns:   [[
                {field:'address_id', hidden:true},
                {field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
                {field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
                {field:'city',        title:'".jsLang('address_book_city')."', width:100},
                {field:'state',       title:'".jsLang('address_book_state')."', width: 50},
                {field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
                {field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}]]
        });";
        // show the address drop down if values are present
        if (isset($data['address'][$attr['suffix']])) { $output['jsReady'][] = "jq('#addressDiv{$attr['suffix']}').show();"; }
        // Options Bar
        if ($attr['update']) { $output['body'] .= html5('AddUpdate'.$attr['suffix'], ['label'=>lang('add_update'),'attr'=>['type'=>'checkbox']])."<br />\n"; }
        if ($attr['drop']) {
            $drop_attr = ['type'=>'checkbox'];
            if (isset($structure['drop_ship']['attr']['checked'])) { $drop_attr['checked'] = 'checked'; }
            $output['body'] .= html5('drop_ship'.$attr['suffix'], ['label'=>lang('drop_ship'), 'attr'=>$drop_attr,
                'events' => ['onClick'=>"jq('#contactDiv{$attr['suffix']}').toggle();"]])."\n";
        }
        $output['body'] .= "<br />\n";
        // Address fields
        $output['body'] .= '  <div class="inner-labels">'."\n";
        if (isset($structure['contact_id'])) { $output['body'] .= html5('contact_id'.$attr['suffix'], $structure['contact_id'])."\n"; }
        $output['body'] .= html5('address_id'.$attr['suffix'],  $structure['address_id'])."\n";
        $output['body'] .= html5('primary_name'.$attr['suffix'],$structure['primary_name'])."<br />\n";
        $output['body'] .= html5('contact'.$attr['suffix'],     $structure['contact'])."<br />\n";
        $output['body'] .= html5('address1'.$attr['suffix'],    $structure['address1'])."<br />\n";
        $output['body'] .= html5('address2'.$attr['suffix'],    $structure['address2'])."<br />\n";
        $output['body'] .= html5('city'.$attr['suffix'],        $structure['city'])."<br />\n";
        $output['body'] .= html5('state'.$attr['suffix'],       $structure['state'])."<br />\n";
        $output['body'] .= html5('postal_code'.$attr['suffix'], $structure['postal_code'])."<br />\n";
        $output['body'] .= htmlComboCountry('country'.$attr['suffix'],$structure['country']['attr']['value'])."<br />\n";
        $output['body'] .= html5('telephone1'.$attr['suffix'],  $structure['telephone1'])."<br />\n";
        if (!empty($structure['telephone2'])) { $output['body'] .= html5('telephone2'.$attr['suffix'],  $structure['telephone2'])."<br />\n"; }
        if (!empty($structure['telephone3'])) { $output['body'] .= html5('telephone3'.$attr['suffix'],  $structure['telephone3'])."<br />\n"; }
        if (!empty($structure['telephone4'])) { $output['body'] .= html5('telephone4'.$attr['suffix'],  $structure['telephone4'])."<br />\n"; }
        $output['body'] .= html5('email'.$attr['suffix'],       $structure['email'])."<br />\n";
        if (!empty($structure['website'])) { $output['body'] .= html5('website'.$attr['suffix'],  $structure['website'])."<br />\n"; }
        if ($attr['notes']) { $output['body'] .= html5('email'.$attr['suffix'],$structure['email']); }
        $output['body'] .= "  </div>\n";
        $output['jsReady']['addrDiv'.$attr['suffix']] = "setInnerLabels(addressFields, '".$attr['suffix']."');";
    }

    /**
     * This function builds the HTML (and JavaScript) content to render a jQuery easyUI datagrid
     * @param array $output - running HTML string to render the page
     * @param string $data - The structure source data to pull from
     * @param array $idx - The index in $data to grab the structure to build
     * @return string - HTML formatted EasyUI datagrid appended to $output
     */
    public function layoutDatagrid(&$output, $prop) {
//        msgDebug("\nprop = ".print_r($prop, true));
        $id = $prop['id'];
        $dgType = (isset($prop['type']) && $prop['type']) ? $prop['type'] : 'datagrid';
        $output['body'] .= "\n<!-- $dgType {$prop['id']} -->\n";
        if (isset($prop['attr']['toolbar'])) { // start the toolbar div
            $output['body'] .= '<div id="' . str_replace('#', '', $prop['attr']['toolbar']) . '" style="padding:5px;height:auto">' . "\n";
            $output['body'] .= "  <div>\n";
            if (isset($prop['source']['filters'])) {
                $prop['source']['filters'] = sortOrder($prop['source']['filters']);
                $js = "function {$prop['id']}Reload() {\n";
                $js .= "  jq('#{$prop['id']}').$dgType('reload', { ";
                $temp = [];
                foreach ($prop['source']['filters'] as $key => $value) {
                    if (!isset($value['hidden'])) {
                        $value['hidden'] = false;
                    }
                    $id = isset($value['html']['attr']['id']) ? $value['html']['attr']['id'] : $key; // override id, for dups on multi datagrid page
                    if (!$value['hidden']) {
                        $temp[] = $id . ':jq("#' . $id . '").val()';
                    }
                }
                $js .= implode(',', $temp) . ' });' . "\n}";
                $output['jsBody'][] = $js;
            }
            if (isset($prop['source']['fields'])) {
                $temp = [];
                foreach ($prop['source']['fields'] as $key => $value) {
                    $temp[$key] = isset($value['order']) ? $value['order'] : 0;
                }
                array_multisort($temp, SORT_ASC, $prop['source']['fields']);
                $output['body'] .= '<div style="float:right">';
                foreach ($prop['source']['fields'] as $key => $value)
                    if (!isset($value['hidden']) || !$value['hidden'])
                        $output['body'] .= $this->render($key, $value['html']);
                $output['body'] .= '</div>';
            }
            if (isset($prop['source']['actions'])) {
                $temp = [];
                foreach ($prop['source']['actions'] as $key => $value) {
                    $temp[$key] = isset($value['order']) ? $value['order'] : 0;
                }
                array_multisort($temp, SORT_ASC, $prop['source']['actions']);
                // handle the right aligned toolbar elements
                $right = '';
                foreach ($prop['source']['actions'] as $key => $value) {
                    if (isset($value['align']) && $value['align'] == 'right') {
                        $right .= $this->render($key, $value['html']);
                    }
                }
                if ($right) { $output['body'] .= '<div style="float:right;">' . $right . "</div>\n"; }
                // now the left aligned
                foreach ($prop['source']['actions'] as $key => $value) {
                    if ((!isset($value['hidden']) || !$value['hidden']) && (!isset($value['align']) || $value['align'] == 'left')) {
                        $output['body'] .= $this->render($key, $value['html']);
                    }
                }
            }
            if (isset($prop['source']['filters'])) {
                if (!empty($prop['source']['filters']['search'])) {
                    $output['body'] .= $this->render('search', $prop['source']['filters']['search']['html']);
                    unset($prop['source']['filters']['search']);                    
                }
                $output['body'] .= '      <a href="#" onClick="' . $prop['id'] . 'Reload();" class="easyui-linkbutton" data-options="iconCls:\'icon-search\'">' . lang('search') . "</a>\n";
                foreach ($prop['source']['filters'] as $key => $value) {
                    if (!isset($value['hidden']) || !$value['hidden']) {
                        if (!isset($value['break']) || $value['break']) { $output['body'] .= "<br />"; }
                        $output['body'] .= $this->render($key, $value['html']);
                    }
                }
            }
            $output['body'] .= "  </div>\n";
            $output['body'] .= "</div>\n";
        }
        if (isset($prop['columns']) && is_array($prop['columns'])) { // build the formatter for the action column
            $temp = [];
            foreach ($prop['columns'] as $key => $value) {
                $temp[$key] = $value['order'];
            }
            array_multisort($temp, SORT_ASC, $prop['columns']);
            if (isset($prop['columns']['action']['actions'])) {
                $js = "function {$prop['id']}Formatter(value, row, index) {\n";
                $js .= "  var text = '';\n";
                foreach ($prop['columns']['action']['actions'] as $id => $event) {
                    if (!isset($event['hidden'])) {
                        $event['hidden'] = false;
                    }
                    if (!$event['hidden']) {
                        if (isset($event['display'])) {
                            $js .= "  if ({$event['display']})";
                        }
                        $temp = $this->render('', $event) . "&nbsp;";
                        $js .= "  text += '" . str_replace(["\n", "\r", "'"], ["", "", "\'"], $temp) . "';\n";
                    }
                }
                $js .= "  text = text.replace(/indexTBD/g, index);\n";
                if (isset($prop['attr']['idField'])) { // for sending db ID's versus row index ID's
                    $js .= "  text = text.replace(/idTBD/g, row.{$prop['attr']['idField']});\n";
                }
                if (isset($prop['attr']['xtraField'])) { // for replacing row.values
                    foreach ($prop['attr']['xtraField'] as $rField) {
                        $js .= "  text = text.replace(/{$rField['key']}/g, row.{$rField['value']});\n";
                    }
                }
                $js .= "  return text;\n}";
                $output['jsBody'][] = $js;
            }
        }
        $output['body'] .= '<div><table id="' . $prop['id'] . '"';
        if (isset($prop['title'])) {
            $output['body'] .= ' title="' . $prop['title'] . '"';
        }
        $output['body'] .= "></table>\n";
        if (isset($prop['footnotes'])) {
            $output['body'] .= '<b>' . lang('notes') . ":</b><br />\n";
            foreach ($prop['footnotes'] as $note) {
                $output['body'] .= $note . "<br />\n";
            }
        }
        $output['body'] .= "</div>\n";
        if (isset($prop['tip'])) {
            $output['body'] .= "<div>\n  " . $prop['tip'] . "\n</div>\n";
        }
        $js = "jq('#{$prop['id']}').$dgType({\n";
        $options = [];
        foreach ($prop['attr'] as $key => $value) {
            $options[] = "  $key:" . encodeType($value);
        }
        if (isset($prop['events'])) {
            foreach ($prop['events'] as $key => $value) {
                $options[] = " $key:$value";
            }
        }
        // build the columns
        $cols = [];
        foreach ($prop['columns'] as $col => $settings) {
            $settings['attr']['field'] = $col;
            $settings['attr']['title'] = isset($settings['label']) ? $settings['label'] : $col;
            $temp = [];
            foreach ($settings['attr'] as $key => $value) {
                $temp[] = "$key:" . encodeType($value);
            }
            if (isset($settings['events'])) {
                foreach ($settings['events'] as $key => $value) {
                    $temp[] = "$key:$value";
                }
            }
            $cols[] = "    { " . implode(",", $temp) . " }";
        }
        $options[] = "  columns:[[\n" . implode(",\n", $cols) . "\n]]";
        $js .= implode(",\n", $options) . "\n});";
        $output['jsBody'][] = $js;
    }

    public function layoutTable(&$output, $prop) {
        $output['body'] .= $this->htmlElOpen('', $prop) . "\n";
        if (isset($prop['thead'])) {
            $output['body'] .= $this->layoutTableRows($prop['thead']) . "</thead>\n";
        }
        if (isset($prop['tbody'])) {
            $output['body'] .= $this->layoutTableRows($prop['tbody']) . "</tbody>\n";
        }
        if (isset($prop['tfoot'])) {
            $output['body'] .= $this->layoutTableRows($prop['tfoot']) . "</tfoot>\n";
        }
        $output['body'] .= "</table><!-- End table {$prop['attr']['id']} -->\n";
    }

    private function layoutTableRows($region) {
        $output = $this->htmlElOpen('', $region) . "\n";
        foreach ($region['tr'] as $tr) {
            $output .= $this->htmlElOpen('', $tr);
            foreach ($tr['td'] as $td) {
                $value = $td['attr']['value'];
                unset($td['attr']['value']);
                $output .= $this->htmlElOpen('', $td) . $value . "</{$td['attr']['type']}>\n";
            }
            $output .= "</tr>\n";
        }
        return $output;
    }

    /**
     * This function generates the tabs pulled from the current structure, position: $data['tabs'][$idx]
     * @param array $output - running HTML string to render the page
     * @param string $prop - The structure source data to pull from
     * @param array $idx - The index in $prop to grab the structure to build
     * @return string - HTML formatted EasyUI tabs appended to $output
     */
    public function layoutTab(&$output, $prop) {
        $prop['divs'] = sortOrder($prop['divs']);
        $output['body'] .= '     <div id="' . $prop['id'] . '" class="easyui-tabs"';
        if (isset($prop['attr']['focus'])) {
            $indices = array_keys($prop['divs']);
            foreach ($indices as $key => $tabID) {
                if ($prop['attr']['focus'] == $tabID) {
                    $prop['attr']['selected'] = $key;
                    unset($prop['attr']['focus']);
                    break;
                }
            }
        }
        if (!empty($prop['styles'])) {
            $output['body'] .= $this->addStyles($prop['styles']);
        }
        if (!empty($prop['attr'])) {
            $temp = [];
            foreach ($prop['attr'] as $key => $value) {
                $temp[] = "$key:" . encodeType($value);
            }
            $output['body'] .= ' data-options="' . implode(',', $temp) . '"';
        }
        $output['body'] .= "><!-- Begin tab group {$prop['id']} -->\n";
        foreach ($prop['divs'] as $tabID => $tabDiv) {
            $tabDiv['attr']['id'] = $tabID;
            $tabDiv['attr']['title'] = !empty($tabDiv['label']) ? $tabDiv['label'] : $tabID;
            $tabDiv['styles']['padding'] = '20px';
            if (!empty($tabDiv['icon'])) {
                $tabDiv['attr']['iconCls'] = "icon-{$tabDiv['icon']}";
            }
            $output['body'] .= "<!-- Begin tab $tabID -->\n";
            $this->buildDiv($output, $tabDiv);
            $output['body'] .= "<!-- End tab $tabID -->\n";
        }
        $output['body'] .= "</div><!-- End tab group {$prop['id']} -->\n";
    }

    /**
     * This function generates a html toolbar pulled from the current structure
     * @param array $output - running HTML string to render the page
     * @param array $id - The index in $prop to grab the structure to build
     * @param string $prop - The structure source data to pull from
     * @return string - HTML formatted EasyUI toolbar appended to $output
     */
    public function layoutToolbar(&$output, $prop) {
        if (isset($prop['hidden']) && $prop['hidden']) { return; } // toolbar is hidden
        foreach ($prop['icons'] as $name => $struc) {
            if (!isset($struc['type'])) {
                $prop['icons'][$name]['type'] = 'icon';
            }
            if (!isset($struc['icon']) && $prop['icons'][$name]['type'] == 'icon') {
                $prop['icons'][$name]['icon'] = $name;
            }
        }
        $prop['data']['child'] = $prop['icons'];
        unset($prop['icons']);
        $prop['size'] = 'large';
        $prop['region'] = 'center';
        $this->menu($output, $prop);
    }

    /**
     * This functions builds the HTML for a jQuery easyUI tree
     * @param array $output - running HTML string to render the page
     * @param string $prop - The structure source data to pull from
     * @param array $idx - The index in $prop to grab the structure to build
     * @return string - HTML formatted EasyUI tree appended to $output
     */
    public function layoutTree(&$output, $prop) {
        $temp = [];
        $output['body'] .= '<ul id="' . $prop['id'] . '"></ul>' . "\n";
        if (isset($prop['menu'])) {
            $output['body'] .= "<div";
            foreach ($prop['menu']['attr'] as $key => $value) {
                $output['body'] .= ' ' . $key . '="' . str_replace('"', '\"', $value) . '"';
            }
            $output['body'] .= ">\n";
            foreach ($prop['menu']['actions'] as $key => $value) {
                $output['body'] .= '  <div id="' . $key . '"';
                foreach ($value['attr'] as $key => $val) {
                    $output['body'] .= ' ' . $key . '="' . str_replace('"', '\"', $val) . '"';
                }
                $output['body'] .= ">" . (isset($value['label']) ? $value['label'] : '') . "</div>\n";
            }
            $output['body'] .= "</div>\n";
        }
        if (isset($prop['footnotes'])) {
            $output['body'] .= '<b>' . lang('notes') . ":</b><br />\n";
            foreach ($prop['footnotes'] as $note) {
                $output['body'] .= $note . "\n";
            }
        }
        foreach ($prop['attr'] as $key => $value) {
            $val = is_bool($value) ? ($value ? 'true' : 'false') : "'$value'";
            $temp[] = "$key: $val";
        }
        if (isset($prop['events'])) {
            foreach ($prop['events'] as $key => $value) {
                $temp[] = "      $key: $value";
            }
        }
        $output['jsBody'][] = "jq('#" . $prop['id'] . "').tree({\n" . implode(",\n", $temp) . "\n});\n";
    }

    public function layoutIframe() {
        
    }

    public function layoutFieldset() {
        
    }

    public function layoutLegend() {
        
    }

    public function layoutHeader() {
        
    }

    public function layoutNav() {
        
    }

    public function layoutSection() {
        
    }

    public function layoutArticle() {
        
    }

    public function layoutAside() {
        
    }

    public function layoutFooter() {
        
    }

    /*     * *************************** Forms ***************** */

    public function input($id, $prop) {
        $this->addID($id, $prop);
        $field = '<input';
        if (isset($prop['attr']['value'])) {
            $value = isset($prop['format']) ? viewFormat($prop['attr']['value'], $prop['format']) : $prop['attr']['value'];
            $field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
            unset($prop['attr']['value']);
        }
        if (!empty($prop['js'])) {
            $this->jsBody[] = $prop['js'];
        } // old way
        if (!empty($prop['jsBody'])) {
            $this->jsBody[] = $prop['jsBody'];
        } // new way
        $output = $this->addLabelFirst($id, $prop) . $field . $this->addAttrs($prop) . " />" . $this->addLabelLast($id, $prop);
        return $output . (!empty($prop['break']) ? '<br />' : '');
    }

    public function inputTextarea($id, $prop) {
        $this->addID($id, $prop);
        if (empty($prop['attr']['rows'])) { $prop['attr']['rows'] = 20; }
        if (empty($prop['attr']['cols'])) { $prop['attr']['cols'] = 60; }
        $content = '';
        $field = $this->addLabelFirst($id, $prop);
        $field .= '<textarea';
        foreach ($prop['attr'] as $key => $value) {
            if (in_array($key, ['type', 'maxlength'])) { continue; }
            if ($key == 'value') { $content = $value; continue; }
            $field .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
        }
        $field .= ">" . htmlspecialchars($content) . "</textarea>\n";
        $field .= $this->render('', ['icon'=>'edit', 'size'=>'small', 'label'=>lang('edit'), 'events'=>['onClick'=>"tinymceInit('$id');"]]);
        $field .= $this->addLabelLast($id, $prop);
        $this->jsBody['tinyMCE'] = "jq.cachedScript('" . self::htmlEditor . "');";
        if (!empty($prop['break'])) { $field .= '<br />'; }
        return $field;
    }

    public function inputSelect($id, $prop) {
        $this->addID($id, $prop);
        $first= $this->addLabelFirst($id, $prop);
        $last = $this->addLabelLast($id, $prop);
        $value= isset($prop['attr']['value']) ? (array)$prop['attr']['value'] : []; // needs to be isset, cannot be empty
        unset($prop['attr']['type']);
        unset($prop['attr']['value']);
        if (!empty($prop['js']))     { $this->jsBody[] = $prop['js']; } // old way
        if (!empty($prop['jsBody'])) { $this->jsBody[] = $prop['jsBody']; } // new way
        // style it
//        $prop['classes']['combo'] = "easyui-combo";
//        $prop['attr']['data-options'] = "editable:false";
//        convert events to easyUI events, onClick, etc.
        // need to load data values into data-options
        $field = '<select' . $this->addAttrs($prop) . '>';
        if (isset($prop['values']) && is_array($prop['values']) && sizeof($prop['values']) > 0) {
            foreach ($prop['values'] as $choice) {
                if (isset($choice['hidden']) && $choice['hidden']) {
                    continue;
                }
                $field .= '<option value="' . $choice['id'] . '"';
                if (in_array($choice['id'], $value)) {
                    $field .= ' selected="selected"';
                }
                $field .= '>' . htmlspecialchars(isset($choice['title']) ? $choice['title'] : $choice['text']) . '</option>';
            }
        } elseif (sizeof($value)) {
            $field .= '<option value="' . $value[0] . '">' . htmlspecialchars($value[0]) . '</option>';
        }
        $field .= "</select>\n";
        $output = $first . $field . $last;
        return $output . (!empty($prop['break']) ? '<br />' : '');
    }

    public function inputPassword($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputRadio($id, $prop) {
        if (empty($prop['attr']['checked'])) { unset($prop['attr']['checked']);  }
        if (empty($prop['attr']['selected'])){ unset($prop['attr']['selected']); }
        $prop['position'] = 'after';
        return $this->input($id, $prop);
    }

    public function inputRaw($id, $prop) {
        return $this->addLabelFirst($id, $prop) . $prop['html'] . $this->addLabelLast($id, $prop);
    }

    public function inputCheckbox($id, $prop) {
        if (empty($prop['attr']['checked'])) { unset($prop['attr']['checked']); }
//        $prop['classes'][] = 'easyui-switchbutton';
//        $prop['attr']['data-options'] = "onText:'".jsLang('yes')."',offText:'".jsLang('no')."'";
        // convert html events to easyui events
        return $this->input($id, $prop);
    }

    public function inputButton($id, $prop) {
        $this->addID($id, $prop);
        $prop['attr']['type'] = 'a';
        $prop['classes'][] = 'easyui-linkbutton';
//        $prop['styles']['cursor'] = 'pointer';
        if (!isset($prop['attr']['href'])) { $prop['attr']['href'] = '#'; }
        return $this->htmlElBoth($id, $prop);
    }

    public function inputColor($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputCurrency($id, $prop) {
        $rateProp = ['attr' => ['size'=>12]];
		if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1) {
			$prop['attr']['type'] = 'select';
			$prop['values']       = viewDropdown(getModuleCache('phreebooks', 'currency', 'iso'), "code", "title");
			unset($prop['attr']['size']);
            if (!empty($prop['func'])) { 
                $this->jsReady[] = "jq('#$id').combobox({editable:false, onChange:function(newVal, oldVal){ {$prop['func']}(newVal, oldVal); } });";                
            } else {
                $this->jsReady[] = "jq('#$id').combobox({editable:false});";
            }
            $rateProp['attr']['value']= !empty($prop['excRate']) ? $prop['excRate'] : 1;
            $rateProp['break']        = true;
		} else { 
            $prop['attr']['type']     = 'hidden';
            $rateProp['attr']['type'] = 'hidden';
            $rateProp['attr']['value']= 1;
        }
        $output = $this->render($id, $prop) ;
        if (!empty($prop['func'])) { $output .= $this->input('currency_rate', $rateProp); }
        return $output;
    }

    public function inputDate($id, $prop) {
        $prop['classes'][] = 'easyui-datebox';
        $prop['attr']['type'] = 'text'; // needed to turn off browser takeover of date box (Chrome)
        if (!empty($prop['attr']['value'])) {
            $prop['attr']['data-options'] = "value:'" . viewDate($prop['attr']['value']) . "'";
            unset($prop['attr']['value']);
        }
        return $this->input($id, $prop);
    }

    public function inputEmail($id, $prop) {
        // add validate feature and add icon
        return $this->input($id, $prop);
    }

    public function inputMonth($id, $prop) {
        return $this->input($id, $prop);
    }

    public function selNoYes($id, $prop) {
//        $prop['classes'][] = 'easyui-switchbutton';
        // need to set on if checked true or !empty value
        // what does it return?
        $prop['attr']['type'] = 'select';
        $prop['values'] = [['id' => '0', 'text' => lang('no')], ['id' => '1', 'text' => lang('yes')]];
        return $this->render($id, $prop);
    }

    public function inputNumber($id, $prop) {
//        $prop['classes'][] = 'easyui-numberbox';
//      defaults should already be set in .js file
        // need to set on if checked true or !empty value
        // convert events to easyUI events
        return $this->input($id, $prop);
    }

    public function inputRange($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputSearch($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputTel($id, $prop) {
        // restrict to numbers, dots or dashes
        return $this->input($id, $prop);
    }

    public function inputTime($id, $prop) {
        // time spinner
        return $this->input($id, $prop);
    }

    public function inputUrl($id, $prop) {
        return $this->input($id, $prop);
    }

    /*     * *************************** Media ***************** */

    public function media() {
        
    }

    public function mediaVideo() {
        
    }

    public function mediaAudio() {
        
    }

    public function mediaGoogleMaps() {
        
    }

    public function mediaYouTube() {
        
    }

    /*     * *************************** APIs ***************** */

    /*     * *************************** Attributes ***************** */

    private function addAttrs($prop) {
        $field = '';
        foreach ($prop['attr'] as $key => $value) {
            $field .= ' ' . $key . '="' . str_replace('"', '\"', $value) . '"'; // was str_replace('"', '&quot;', $value)
        }
        if (!empty($prop['classes'])) { $field .= $this->addClasses($prop['classes']); }
        if (!empty($prop['styles']))  { $field .= $this->addStyles($prop['styles']); }
        if (!empty($prop['events']))  { $field .= $this->addEvents($prop['events']); }
        return $field;
    }

    private function addClasses($arrClasses = []) {
        if (!is_array($arrClasses)) { $arrClasses = [$arrClasses]; }
        return ' class="' . implode(' ', $arrClasses) . '"';
    }

    private function addEvents($arrEvents = []) {
        if (!is_array($arrEvents)) { $arrEvents = [$arrEvents]; }
        $output = '';
        foreach ($arrEvents as $key => $value) { $output .= ' ' . $key . '="' . str_replace('"', '\"', $value) . '"'; }
        return $output;
    }

    private function addID($id = '', &$prop = []) {
        if ($id && !isset($prop['attr']['name'])) { $prop['attr']['name'] = $id; }
        if (isset($prop['attr']['id'])) { } // use it
        elseif (strpos($id, '[]'))      { unset($prop['attr']['id']); } // don't show id attribute if generic array
        elseif ($id) {
            $prop['attr']['id'] = str_replace('[', '_', $id); // clean up for array inputs causing html errors
            $prop['attr']['id'] = str_replace(']', '', $prop['attr']['id']);
        }
        if (isset($prop['attr']['required']) && $prop['attr']['required']) { $prop['classes'][] = 'easyui-validatebox'; }
    }

    private function addLabelFirst($id, $prop) {
        $field = '';
//      if (!$id)                  { return $field; }
        if (empty($prop['label'])) { return $field; }
        if ($prop['attr']['type'] <> 'hidden' && empty($prop['position'])) {
            return '<label for="'.$id.'" class="fldLabel" style="vertical-align:top">'.$prop['label'].'</label>&nbsp;';
        }
        return $field;
    }

    private function addLabelLast($id, $prop) {
        $field = '';
//      if (!$id)                  { return $field; }
        if (empty($prop['label'])) { return $field; }
        if ($prop['attr']['type']<>'hidden' && isset($prop['position']) && $prop['position'] == 'after') {
            $styles = "vertical-align:top;" . in_array($prop['attr']['type'], ['checkbox','radio'])? 'min-width:60px;' : '';
            $field = '<label for="'.$id.'" class="fldLabel" style="'.$styles.'">'.$prop['label'].'</label>';
        }
        return $field;
    }

    public function addMsgStack() {
        global $msgStack;
        $msgStack->error = array_merge_recursive($msgStack->error, getUserCache('msgStack'));
        clearUserCache('msgStack');
        if (sizeof($msgStack->error)) {
            if (!$msg = json_encode($msgStack->error)) { // msgStack is malformed
                $msg = '[]';
                msgDebug("\nEncoding the messages in json returned with error: " . json_last_error_msg());
            }
            return "var messages = $msg;\ndisplayMessage(messages);";
        }
        return '';
    }

    private function addStyles($arrStyles = []) {
        if (!is_array($arrStyles)) {
            $arrStyles = [$arrStyles];
        }
        $styles = [];
        foreach ($arrStyles as $key => $value) {
            $styles[] = $key . ':' . $value;
        }
        return ' style="' . implode(';', $styles) . ';"';
    }

    private function addToolTip($tooltip = '') {
//      $prop['attr']['missingMessage'] = $prop['tooltip']; // when validating with easyui
        return ' title="' . $tooltip . '"';
    }

}
