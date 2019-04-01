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
 * @copyright  2008-2019, PhreeSoft, Inc.
 * @license    http://opensource.org/licenses/OSL-3.0  Open Software License (OSL 3.0)
 * @version    3.x Last Update: 2019-03-22
 * @filesource /view/easyUI/html5.php
 */

namespace bizuno;

final class html5 {

    const htmlEditor = 'https://cdn.tinymce.com/4/tinymce.min.js';
    const bizunoHelp = 'https://www.bizuno.com?p=bizuno/portal/helpMain';

    private $pageT  = '50';  // page layout minimum pixel height
    private $pageB  = '35';
    private $pageL  = '175';
    private $pageR  = '175';
    public  $jsHead  = [];
    public  $jsBody  = [];
    public  $jsReady = [];
    public  $jsResize= [];

    function __construct() {

    }

    /**
     * This function builds an array of div elements based on a type and structure
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
        msgDebug("\nEntering buildDiv of type {$prop['type']}");
        switch ($prop['type']) {
            case 'accordion':
                if (isset($prop['key'])) {
                    $prop = array_merge($viewData['accordion'][$prop['key']], ['id'=>$prop['key']]);
                }
                $this->layoutAccordion($output, $prop);
                break;
            case 'address':
                if (!empty($prop['label'])) { $output['body'] .= $this->layoutFieldset($prop); }
                $this->layoutAddress($output, $prop);
                if (!empty($prop['label'])) { $output['body'] .= '</fieldset>'; }
                break;
            case 'attach':   $this->layoutAttach($output, $prop); break;
            case 'datagrid': $this->layoutDatagrid($output, $prop, $prop['key']); break;
            case 'divs':
                if (!empty($prop['label'])) { $output['body'] .= $this->layoutFieldset($prop); }
                $this->buildDivs($output, $prop);
                if (!empty($prop['label'])) { $output['body'] .= '</fieldset>'; }
                break;
            case 'fields': $output['body'] .= $this->layoutFields($viewData, $prop); break;
            case 'form':   $output['body'] .= $this->render($prop['key'], $viewData['forms'][$prop['key']]); break;
            case 'html':   $output['body'] .= $prop['html']."\n"; break;
            case 'list':   $output['body'] .= $this->layoutList($viewData, $prop); break;
            case 'menu':   $this->menu($output, $prop); break;
//          case 'panel':  $this->layoutPanel($output, $prop); break; // no longer used?
            case 'panel':
                if (isset($prop['key'])) { $prop = array_merge($viewData['panels'][$prop['key']], ['id'=>$prop['key']]); }
                $this->layoutPanel($output, $prop);
                break;
            case 'payment':$this->layoutPayment($output, $prop); break;
            case 'payments':
                foreach ($viewData['payments'] as $methID) {
                    $fqcn   = "\\bizuno\\$methID";
                    bizAutoLoad(BIZUNO_LIB."controller/module/payment/methods/$methID/$methID.php", $fqcn);
                    $totSet = getModuleCache('payment','methods',$methID,'settings');
                    $totals = new $fqcn($totSet);
                    $totals->render($output, $viewData);
                }
                break;
            case 'table':
                if (isset($prop['key'])) {
                    $prop = array_merge($prop, $viewData['tables'][$prop['key']]);
                    $prop['attr']['id'] = $prop['key'];
                }
                $this->layoutTable($output, $prop);
                break;
            case 'tabs':
                if (isset($prop['key'])) {
                    $prop = array_merge($viewData['tabs'][$prop['key']], ['id'=>$prop['key']]);
                }
                $this->layoutTab($output, $prop);
                break;
            case 'toolbar':
                if (isset($prop['key'])) {
                    $prop = array_merge($viewData['toolbars'][$prop['key']], ['id' => $prop['key']]);
                }
                $this->layoutToolbar($output, $prop);
                break;
            case 'totals':
                if (!empty($prop['label'])) { $output['body'] .= $this->layoutFieldset($prop); }
                foreach ($viewData['totals'] as $methID) {
                    $path = getModuleCache('phreebooks', 'totals', $methID, 'path');
                    $fqcn = "\\bizuno\\$methID";
                    bizAutoLoad("{$path}$methID.php", $fqcn);
                    $totSet = getModuleCache('phreebooks','totals',$methID,'settings');
                    $totals = new $fqcn($totSet);
                    $totals->render($output, $viewData);
                }
                if (!empty($prop['label'])) { $output['body'] .= '</fieldset>'; }
                break;
            case 'tree':
                if (isset($prop['key'])) {
                    $prop['el'] = array_merge($viewData['tree'][$prop['key']], ['id' => $prop['key']]);
                }
                $this->layoutTree($output, $prop['el']);
                break;
            default:
            case 'template': // set some variables if needed
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
            case 'u':           return $this->htmlElBoth($id, $prop);
            case 'br':
            case 'hr':
            case 'img':         return $this->htmlElEmpty($id, $prop);
            case 'div':
            case 'form':
            case 'section':
            case 'header':
            case 'li':
            case 'ol':
            case 'td':
            case 'th':
            case 'tr':
            case 'thead':
            case 'tbody':
            case 'tfoot':
            case 'ul':          return $this->htmlElOpen($id, $prop);
            case 'button':      return $this->inputButton($id, $prop);
//          case 'iframe':      return $this->layoutIframe($output, $id, $prop);
            case 'checkbox':    return $this->inputCheckbox($id, $prop);
            case 'color':       return $this->inputColor($id, $prop);
            case 'contact':     return $this->inputContact($id, $prop);
            case 'country':     return $this->inputCountry($id, $prop);
            case 'currency':    return $this->inputCurrency($id, $prop);
            case 'date':
            case 'datetime-local':
            case 'datetime':    return $this->inputDate($id, $prop);
            case 'email':       return $this->inputEmail($id, $prop);
            case 'decimal':
            case 'float':
            case 'number':      return $this->inputNumber($id, $prop);
            case 'file':        return $this->inputFile($id, $prop);
            case 'hidden':
            case 'linkimg': // used in custom fields contacts/inventory
            case 'phone':
            case 'tel':
            case 'text':
            case 'time':        return $this->inputText($id, $prop);
            case 'integer':
            case 'month':
            case 'week':
            case 'year':        return $this->inputNumber($id, $prop, 0);
            case 'inventory':   return $this->inputInventory($id, $prop);
            case 'password':    return $this->inputPassword($id, $prop);
            case 'ledger':      return $this->inputGL($id, $prop);
            case 'selCurrency': return $this->selCurrency($id, $prop);
            case 'selNoYes':    return $this->selNoYes($id, $prop);
            case 'radio':       return $this->inputRadio($id, $prop);
            case 'raw':         return $this->inputRaw($id, $prop);
            case 'select':      return $this->inputSelect($id, $prop);
            case 'html':
            case 'htmlarea': // @todo - need to deprecate htmlarea, replace with either html or textarea
            case 'textarea':    return $this->inputTextarea($id, $prop);
            case 'table':       return $this->layoutTable($id, $prop);
            case 'tax':         return $this->inputTax($id, $prop);


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
            case 'link': //Defines the relationship between a document and an external resource (most used to link to style sheets)
            case 'main': //Specifies the main content of a document
            case 'map': //Defines a client-side image-map
            case 'mark': //Defines marked/highlighted text
            case 'menu': //Defines a list/menu of commands
            case 'menuitem': //Defines a command/menu item that the user can invoke from a popup menu
            case 'meta': //Defines metadata about an HTML document
            case 'meter': //Defines a scalar measurement within a known range (a gauge)
            case 'object': //Defines an embedded object
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
            case 'url': // Bizuno Added
            case 'var': //Defines a variable
            case 'video': //Defines a video or movie
            case 'wbr': //Defines a possible line-break
            // special cases and adjustments
            default:
                msgDebug("\nUndefined Element type: {$prop['attr']['type']} with properties: " . print_r($prop, true));
                msgAdd("Undefined element type: {$prop['attr']['type']}", 'trap');
        }
        if (isset($prop['break']) && $prop['break'] && $prop['attr']['type'] <> 'hidden') { $field .= "<br />\n"; }
        if (isset($prop['js'])) { $this->jsBody[] = $prop['js']; }
        return $field;
    }

    /*     * *************************** Elements ***************** */

    /**
     * Creates an full html element with separate opening and closing tags, i.e. <...> something </...>
     * @param string $id - element id
     * @param array $prop - element properties
     * @return string - html element ready to send to browser
     */
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

    /**
     * Creates an element that is self closing, i.e. <... />
     * @param string $id - element id
     * @param array $prop - element properties
     * @return string - html element ready to send to browser
     */
    public function htmlElEmpty($id, $prop) {
        return "<{$prop['attr']['type']}" . $this->addAttrs($prop) . " />";
    }

    /**
     * Builds a HTML open element, i.e no closing tag </...>
     * @param string $id - element id
     * @param array $prop - element properties
     * @return string - html element ready to send to browser
     */
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
        if (!empty($prop['thead'])) { $this->tableRows($output, $prop['thead']) . "</thead>\n"; }
        if (!empty($prop['tbody'])) { $this->tableRows($output, $prop['tbody']) . "</tbody>\n"; }
        if (!empty($prop['tfoot'])) { $this->tableRows($output, $prop['tfoot']) . "</tfoot>\n"; }
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

    /***************************** Navigation ******************/
    /**
     * This function takes the menu structure and builds the easyUI HTML markup
     * @param string $output - The running HTML output
     * @return string - HTML formatted EasyUI menu appended to $output
     */
    function menu(&$output, $prop) {
        if (empty($prop['data']['child'])) { return; }
        $prop['attr']['type'] = 'div';
        $prop['data']['attr']['type'] = 'div';
        $orient    = in_array($prop['region'], ['east', 'left', 'right', 'west']) ? 'v' : 'h';
        $hideLabel = !empty($prop['hideLabels']) ? $prop['hideLabels'] : false;
        $hideBorder= !empty($prop['hideBorder']) ? $prop['hideBorder'] : false;
        if ($orient == 'v') {
            if (empty($prop['size'])) { $prop['size'] = 'small'; }
            $prop['classes'][] = 'easyui-menu';
            $prop['options']['inline'] = 'true';
        } else {
            if (empty($prop['size'])) { $prop['size'] = 'large'; }
        }
        $output['body'] .= $this->htmlElOpen('', $prop)."\n";
        $output['body'] .= $this->menuChild($prop['data']['child'], $prop['size'], $orient, $hideLabel, $hideBorder);
        $output['body'] .= "</div>\n";
    }

    /**
     * This function takes a menu child structure and builds the easyUI HTML markup, it is recursive for multi-level menus
     * @param string $id - root ID for the parent element
     * @param string $struc - The menu structure
     * @return string - HTML formatted EasyUI menu (child) appended to parent menu $output
     */
    public function menuChild($struc=[], $size='small', $orient='v', $hideLabel=false) {
        $output = '';
        $subQueue = [];
        if (empty($struc)) { return; }
        $structure = sortOrder($struc);
        foreach ($structure as $subid => $submenu) {
            $options = [];
            if (!isset($submenu['security'])|| !empty($submenu['child']))    { $submenu['security'] = 1; }
            if (!empty($submenu['hidden'])  ||  empty($submenu['security'])) { continue; }
            if ( empty($submenu['child'])   && !empty($submenu['icon']) && $orient=='h' && ($hideLabel || !empty($submenu['hideLabel']))) { // just an icon
                $output .= $this->menuIcon($subid, $submenu);
                continue;
            }
            if (!empty($submenu['type']) && $submenu['type'] == 'field') {
                $output .= $this->render($subid, $submenu);
                continue;
            }
            if (empty($submenu['attr']['id'])) { $submenu['attr']['id'] = $subid; }
            if ($orient == 'h') {
                if (empty($submenu['child'])) { $submenu['classes'][] = 'easyui-linkbutton'; }
                else                          { $submenu['classes'][] = 'easyui-splitbutton'; }
                $options['plain'] = 'false';
            }
            if (isset($submenu['popup'])) { $submenu['events']['onClick'] = "winOpen('$subid','{$submenu['popup']}');"; }
            if     (isset($submenu['icon']) && $size == 'small') { $options['iconCls']="'icon-{$submenu['icon']}'"; $options['size']="'small'"; }
            elseif (isset($submenu['icon']))                     { $options['iconCls']="'iconL-{$submenu['icon']}'";$options['size']="'large'"; }
            if ($orient == 'h' && !empty($submenu['child'])) { $options['menu'] = "'#sub_{$subid}'"; }
            if (!empty($submenu['disabled'])) { $options['disabled'] = 'true'; }
            $label = !empty($submenu['label']) ? $submenu['label'] : lang($subid);
            $submenu['attr']['title'] = !empty($submenu['tip']) ? $submenu['tip'] : $label;
            $submenu['options'] = $options;
            if ($orient == 'h') {
                if (isset($submenu['child'])) { $subQueue[] = ['id' => "sub_{$subid}", 'menu' => $submenu['child']]; }
                $submenu['attr']['type'] = 'a';
                $output .= "  ".$this->htmlElOpen($subid, $submenu) . ($hideLabel ? '' : $label) . "</a>\n";
            } else {
                $submenu['attr']['type'] = 'div';
                $output .= "  ".$this->htmlElOpen($subid, $submenu) . "<span>".($hideLabel ? '' : $label)."</span>"; // <div>
                if (isset($submenu['child'])) { $output .= "\n<div>" . $this->menuChild($submenu['child'], 'small', 'v') . "</div>\n"; }
                $output .= '  </div>' . "\n";
            }
        }
        if ($orient == 'h') { foreach ($subQueue as $child) { // process the submenu queue
                $output .= '  <div id="' . $child['id'] . '">' . $this->menuChild($child['menu'], 'small', 'v') . "</div>\n";
        } }
        return $output;
    }

    /**
     *
     * @param type $id
     * @param type $prop
     * @return type
     */
    public function menuIcon($id, $prop) {
        if (empty($prop['size'])) { $prop['size'] = 'large'; } // default to large icons
        $prop['attr']['type'] = 'span';
        switch ($prop['size']) {
            case 'small': $prefix = "icon";  $size = '16px'; break;
            case 'meduim':$prefix = "iconM"; $size = '24px'; break;
            case 'large':
            default:      $prefix = "iconL"; $size = '32px'; break;
        }
        $prop['classes'][]          = "{$prefix}-{$prop['icon']}";
        $prop['styles']['border']   = '0';
        $prop['styles']['display']  = 'inline-block;vertical-align:middle'; // added float:left for mobile lists
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

    /***************************** Mobile *****************************/
    /**
     * Creates the body list if present
     * @param array $theList - list of menu items to be displayed in the body of the screen
     * @return string
     */
    private function mobileBodyList($theList=[])
    {
        if (empty($theList)) { return ''; }
        $output = '<ul id="list" class="m-list">'."\n";
        $items  = sortOrder($theList);
        foreach ($items as $menuID => $item) {
            $output .= $this->htmlElOpen('', ['events'=>!empty($item['events']) ? $item['events'] : [],'attr'=>['type'=>'li']]);
            unset($item['events']);
            $item['classes']['image'] = "list-image";
            $output .= html5('', $item);
            $output .= '<div class="list-header">'.$item['label'].'</div>';
            if (!empty(getUserCache('menuBar')['child'][$menuID]['desc'])) { $output .= '<div>'.getUserCache('menuBar')['child'][$menuID]['desc'].'</div>'; }
            $output .= "</li>\n";
        }
        return $output."</ul>\n";
    }

    /**
     * Takes an array of icons and creates a tiled list of large icons, like mobile and tablet
     * @param array $content - list of icons to build menu with
     * @param string $location - [default: header] Placement of menu, choices are header or footer
     * @return array - HTML for the tiled menu
     */
    public function mobileMenu($content=[], $location='header')
    {
        $hasDropMenu = false;
        if (empty($content)) { return ''; }
        $menuID = clean('menuID', ['format'=>'cmd', 'default'=>'home'], 'get');
        $output = '<'.$location.'><div class="m-toolbar">'."\n";
        if (!empty($content['title'])){ $output .= '<div class="m-title">'.$content['title'].'</div>'; }
        if (!empty($content['left'])) {
            $output .= '<div class="m-left">';
            foreach($content['left'] as $choice) {
                $output .= $this->mobileMenuAdd($choice, $menuID);
                if ($choice == 'more') { $hasDropMenu = true; }
            }
            $output .= '</div>';
        }
        if (!empty($content['right'])) {
            $output .= '<div class="m-right">';
            foreach($content['right'] as $choice) {
                $output .= $this->mobileMenuAdd($choice, $menuID);
                if ($choice == 'more') { $hasDropMenu = true; }
            }
            $output .= "</div>\n";
        }
        $output .= '</div></'.$location.'>'."\n";
        if ($hasDropMenu) { $output .= $this->mobileMenuChild('more'.$menuID, $this->mobileMenuMore()); }
        return $output;
    }

    /**
     * Creates the HTML element for the menu at the specified position
     * @param string $choice - determines the type of menu item to display
     * @param string $menuID - jQuery id for access
     * @return string - HTML element to be placed in the menu
     */
    private function mobileMenuAdd($choice='home', $menuID='')
    {
        switch ($choice) {
            case 'add':   return html5('', ['order'=>10,'icon'=>'add','options'=>['menuAlign'=>"'right'"],
                'classes'=>['easyui-linkbutton'],'events'=>['onClick'=>"hrefClick('".BIZUNO_HOME."&p=bizuno/dashboard/manager&menuID=$menuID');"]]);
            case 'back':  return html5('', ['order'=>10,'icon'=>'back','options'=>['menuAlign'=>"'left'"],
                'classes'=>['easyui-linkbutton'],'events'=>['onClick'=>"jq.mobile.back();"]]);
            case 'close': return html5('', ['order'=>10,'icon'=>'close','options'=>['menuAlign'=>"'left'"],
                'classes'=>['easyui-linkbutton'],'events'=>['onClick'=>"jq.mobile.back();"]]);
            case 'home':  return html5('', ['order'=>10,'label'=>lang('home'),'icon'=>'home','events'=>['onClick'=>"hrefClick('');"]]);
            case 'more':  return html5('', ['order'=>10,'icon'=>'more','options'=>['hasDownArrow'=>'false','menu'=>"'#more$menuID'",'menuAlign'=>"'right'"],
                'classes'=>['easyui-menubutton']]);
        }
    }

    /**
     * This function takes a menu child structure and builds the easyUI HTML markup, it is recursive for multi-level menus
     * @param string $id - root ID for the parent element
     * @param string $props - The menu structure
     * @return string - HTML formatted EasyUI menu (child) appended to parent menu $output
     */
    public function mobileMenuChild($id, $props) {
        $output = '<div id="'.$id.'" class="easyui-menu" style="width:150px;">';
        if (empty($props)) { return; }
        $structure = sortOrder($props);
        foreach ($structure as $subid => $submenu) {
            if (!isset($submenu['hidden']))    { $submenu['hidden'] = false; }
            if (!isset($submenu['security']) || isset($submenu['child'])) { $submenu['security'] = 1; }
            if ($submenu['hidden'] || empty($submenu['security'])) { continue; }
            if ( empty($submenu['attr']['id'])){ $submenu['attr']['id'] = $subid; }
            if ( isset($submenu['popup']))     { $submenu['events']['onClick'] = "winOpen('$subid','{$submenu['popup']}');"; }
            $options = [];
            if ( isset($submenu['icon']))      { $options['iconCls'] = "'icon-{$submenu['icon']}'"; $options['size'] = "'small'"; }
            if (!empty($submenu['disabled']))  { $options['disabled']= 'true'; }
            $submenu['options'] = $options;
            $submenu['attr']['type'] = 'div';
            $label = !empty($submenu['label']) ? $submenu['label'] : lang($subid);
            $output .= $this->htmlElOpen($subid, $submenu) . $label . "</div>\n";
        }
        $output .= "</div>\n";
        return $output;
    }

    /**
     * builds the mobile menu for the 'more' icon with links to the major menus and settings
     * @param type $output
     * @param type $data
     */
    private function mobileMenuMore()
    {
        $main = getUserCache('menuBar');
        if (empty($main['child'])) { return; }
        foreach ($main['child'] as $menuID => $menu) {
            unset($menu['child']);
            $menu['events'] = ['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=$menuID');"];
            $output[$menuID] = $menu;
        }
        $output['settings']= ['order'=>90,'label'=>lang('bizuno_company'),'icon'=>'settings','events'=>['onClick'=>"hrefClick('bizuno/main/bizunoHome&menuID=settings');"]];
        $output['help']    = ['order'=>98,'label'=>lang('help'),  'icon'=>'help'];
        $output['logout']  = ['order'=>99,'label'=>lang('logout'),'icon'=>'logout','events'=>['onClick'=>"jsonAction('bizuno/portal/logout');"]];
        return $output;
    }

    /**
     * Determines the type of page to render to build the correct menu
     * @param type $data
     * @return string $type - Choices are home, menu, dash, or target
     */
    private function mobileMenuType()
    {
        if (!getUserCache('profile', 'admin_id', false, 0)) { return 'portal'; } // not logged in
        $path = clean('p', 'filename', 'get');
        if (empty($path) || in_array($path, ['bizuno/main/bizunoHome'])) {
            $menuID = clean('menuID', 'cmd', 'get');
            return !empty($menuID) ? 'menu' : 'home';
        }
        if (empty($path) || in_array($path, ['bizuno/main/dashboard'])) { return 'dash'; }
        return 'target';
    }

    /***************************** Layout ******************/
    public function layoutMobile(&$output, $data) {
        $output['head']['meta'][]= ['order'=>70,'html'=>'<meta name="mobile-web-app-capable" content="yes" />'];
        $output['head']['css'][] = ['order'=>20,'html'=>'<link type="text/css" rel="stylesheet" href="'.BIZUNO_URL.'view/easyUI/jquery-easyui/themes/mobile.css" />'];
        $output['head']['js'][]  = ['order'=>20,'html'=>'<script type="text/javascript" src="'.BIZUNO_URL.'view/easyUI/jquery-easyui/jquery.easyui.mobile.js?ver='.MODULE_BIZUNO_VERSION.'"></script>'];
        $output['jsResize'][]    = "jq('#navMobile').navpanel('resize', windowWidth);";
        if (empty($data['divs'])) { return msgAdd('No divs were found, the page does not contain any content!'); }

        $output['body'] .= '<div id="navMobile" class="easyui-navpanel">'."\n";
        $menuID = clean('menuID', ['format'=>'cmd', 'default'=>'home'], 'get');
        $theList= $footer = $header = [];
        $title  = !empty($data['title']) ? $data['title'] : 'No Title';
        switch($this->mobileMenuType()) {
            case 'home': // Home screen
                $header = ['title'=>$title,'left'=>[],'right'=>[]];
                $theList= $this->mobileMenuMore();
                $theList['dashboard'] = ['order'=>1,'label'=>lang('dashboard'),'icon'=>'dashboard','events'=>['onClick'=>"hrefClick('bizuno/main/dashboard');"]];
                break;
            case 'menu': // Menu screen for main menu or My Business
                $title  = lang($menuID);
                $header = ['title'=>$title,'left'=>['home'],'right'=>['more']];
                if ($menuID=='settings') {
                    $theList= getUserCache('quickBar')['child']['home']['child'];
                    unset($theList['logout']);
                } else {
                    $theList= getUserCache('menuBar')['child'][$menuID]['child'];
                    $theList['dashboard'] = ['order'=>1,'label'=>lang('dashboard'),'icon'=>'dashboard','events'=>['onClick'=>"hrefClick('bizuno/main/dashboard&menuID=$menuID');"]];
                }
                break;
            case 'dash': // Dashboard manager screen
                $title  = in_array($menuID, ['settings','home']) ? lang('bizuno_company') : getUserCache('menuBar')['child'][$menuID]['label'];
                $header = ['title'=>$title,'left'=>['back'],'right'=>['add']];
                break;
            case 'target':
                $header = ['title'=>$title,'left'=>['back'],'right'=>['more']];
                break;
            case 'portal':
                $header = ['title'=>'Welcome to Bizuno','left'=>[],'right'=>[]];
        }
        $output['body'] .= $this->mobileMenu($header, 'header');
        $output['body'] .= $this->mobileMenu($footer, 'footer');
        if (!empty($theList)) { $output['body'] .= $this->mobileBodyList($theList); }
//      $output['body'] .= "</div>\n"; // THIS NEEDS TO BE ADDED AT THE END OF THE HTML TO KEEP WITHIN THE CONFINES OF THE MOBILE DEVICE
        // remove standard header and footer content
        unset($data['divs']['qlinks']);
        unset($data['divs']['menu']);
        unset($data['divs']['footer']);
        unset($data['divs']['imgHome']);
        // form toolbars need to be placed at bottom of form as it is a more sequential, autosave??
        // hover pull down menus will need to be click operated instead, no more dashboards on menus
        // sub menus will need to be a dashboard when menu is clicked to show then click to get to function
        $data['divs'] = sortOrder($data['divs']);
        foreach ($data['divs'] as $props) { $this->buildDiv($output, $props); }
        $output['body'] .= '<iframe id="attachIFrame" src="" style="display:none;visibility:hidden;"></iframe>'."\n"; // For file downloads
        $output['body'] .= '<div class="modal"></div><div id="divChart"></div>'."\n";
    }

    /**
     * Tablet layout is the same as desktop except if dashboard only 2 columns are shown
     */
    public function layoutTablet(&$output, $data) {
        return $this->layoutMobile($output, $data);
    }

    public function layoutDesktop(&$output, $data) {
        if (empty($data['divs'])) { return msgAdd('No divs were found, the page does not contain any content!'); }
//        $output['jsResize'][] = "jq(body).layout('resize');"; // in WordPress version it's a div
        $data['divs'] = sortOrder($data['divs']);
        $regions = $dim = [];
        foreach ($data['divs'] as $id => $settings) {
            $curRgn = isset($settings['region']) ? $settings['region'] : 'center'; // default to center region
            $region = str_replace(["top", "bottom", "right", "left"], ["north", "south", "east", "west"], $curRgn); // map to codex
            $regions[$region][$id] = $settings;
            if (!isset($dim[$region]['height'])){ $dim[$region]['height'] = 32; }
            if (!isset($dim[$region]['width'])) { $dim[$region]['width']  = 32; }
            if (!empty($settings['height']))    { $dim[$region]['height'] = max($dim[$region]['height'],$settings['height']); }
            if (!empty($settings['width']))     { $dim[$region]['width']  = max($dim[$region]['width'], $settings['width']); }
        }
        $compass = ['north', 'west', 'center', 'east', 'south'];
        foreach ($compass as $region) {
            if (empty($dim[$region])) { continue; }
            switch ($region) {
                case 'north': $divID = 'bodyNorth';
                    $opts = "region:'north',border:false,style:{borderWidth:0},minHeight:".max($dim[$region]['height'],$this->pageT);
                    break;
                case 'south': $divID = 'bodySouth';
                    $opts = "region:'south',border:false,style:{borderWidth:0},minHeight:".max($dim[$region]['height'],$this->pageB);
                    break;
                case 'west': $divID = 'bodyWest';
                    $opts = "region:'west',border:false,style:{borderWidth:0},width:"     .max($dim[$region]['width'], $this->pageL);
                    break;
                case 'east': $divID = 'bodyEast';
                    $opts = "region:'east',border:false,style:{borderWidth:0},width:"     .max($dim[$region]['width'], $this->pageR);
                    break;
                default:
                case 'center':$divID = 'bodyCenter';
                    $opts = "region:'center',border:false,style:{borderWidth:0},minHeight:10,minWidth:200";
            }
            $output['body'] .= '<div id="'.$divID.'" data-options="'.$opts.',split:true">' . "\n";
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
        $output['jsResize'][] = "jq('#{$prop['id']}').accordion('resize',{width:jq(this).parent().width()});";
        $prop['attr']['type'] = 'div';
        $prop['classes'][] = 'easyui-accordion';
        if (empty($prop['styles']['width'])) { $prop['styles']['width'] = 'auto'; }
        if (empty($prop['styles']['height'])){ $prop['styles']['height']= 'auto'; }
        $output['body'] .= $this->htmlElOpen($prop['id'], $prop);
        $output['body'] .= "\n<!-- Begin accordion group {$prop['id']} -->\n";
        foreach ($prop['divs'] as $accID => $accContents) {
            $output['body'] .= '     <div id="' . $accID . '" title="' . $accContents['label'] . '" style="padding:10px;"';
            if (isset($accContents['options'])) {
                $temp = [];
                foreach ($accContents['options'] as $key => $value) { $temp[] = "$key:$value"; } // was "$key:".encodeType($value);
                $output['body'] .= ' data-options="'.implode(',', $temp).'"';
            }
            $output['body'] .= "><!-- BOF accordion ".$accID." -->\n";
            unset($accContents['label']);
            $this->buildDiv($output, $accContents);
            $output['body'] .= "     </div><!-- EOF accordion ".$accID." -->\n";
        }
        $output['body'] .= "  </div>\n";
        if (isset($prop['select'])) {
            $output['jsBody'][] = "jq('#{$prop['id']}').accordion('select','{$prop['select']}');";
        }
    }

    /**
     * Handles the layout of an address block. Send defaults to override default configuration settings
     * @param type $output
     * @param type $props
     */
    public function layoutAddress(&$output, $props) {
        $defaults = ['type'=>'c','format'=>'short','suffix' =>'','search'=>false,'props'=>true,'clear'=>true,'copy'=>false,'cols'=>true,
            'update'=>false,'validate'=>false,'required'=>false,'store'=>true,'drop' =>false,'fill'=>'none','notes'=>false];
        $attr     = array_replace($defaults, $props['settings']);
        $structure= $props['content'];
        $structure['country']['attr']['type'] = 'country'; // triggers the combogrid
        if ($attr['format'] != 'long') { unset($structure['country']['label']); }
        $structure['email']['attr']['size'] = 32; // keep this from overlapping with other divs
        if (!empty($attr['required'])) { foreach (array_keys($structure) as $field) {
            if (getModuleCache('contacts', 'settings', 'address_book', $field)) { $structure[$field]['options']['required'] = 'true'; }
        } }
        // Tool bar
        $toolbar  = [];
        if ($attr['clear']) { $toolbar[] = html5('', ['icon'=>'clear','events'=>['onClick'=>"addressClear('{$attr['suffix']}')"]]); }
        if ($attr['validate'] && getModuleCache('extShipping', 'properties', 'status')) {
            $toolbar[] = ' '.html5('', ['icon'=>'truck','label'=>lang('validate_address'),'events'=>['onClick'=>"shippingValidate('{$attr['suffix']}');"]]);
        }
        if ($attr['copy']) {
            $src = explode(':', $attr['copy']);
            if (empty($src[1])) { $src = ['_b', '_s']; } // defaults
            $toolbar[] = ' '.html5('',['icon'=>'copy','events'=>['onClick'=>"addressCopy('{$src[0]}', '{$src[1]}')"]]);
        }
        if ($attr['props']) { $toolbar[] = '<span id="spanContactProps'.$attr['suffix'].'" style="display:none">'.html5('contactProps'.$attr['suffix'], ['icon'=>'settings',
            'events' => ['onClick'=>"windowEdit('contacts/main/properties&rID='+jq('#contact_id{$attr['suffix']}').val(), 'winContactProps', '".jsLang('details')."', 1000, 600);"]]).'</span>';
        }
        if (sizeof($toolbar)) { $output['body'] .= implode(" ", $toolbar)."<br />"; }
        // Options bar
        $options = [];
        if ($attr['update']) { $options[] = html5('AddUpdate'.$attr['suffix'], ['label'=>lang('add_update'),'attr'=>['type'=>'checkbox']]); }
        if ($attr['drop']) {
            $drop_attr = ['type'=>'checkbox'];
            if (isset($structure['drop_ship']['attr']['checked'])) { $drop_attr['checked'] = 'checked'; }
            $options[] = html5('drop_ship'.$attr['suffix'], ['label'=>lang('drop_ship'), 'attr'=>$drop_attr,
                'events' => ['onChange'=>"jq('#contactDiv{$attr['suffix']}').toggle();"]]);
        }
        if (sizeof($options)) { $output['body'] .= implode("<br />", $options)."<br />"; }

        $output['body'] .= '<div>';
        if ($attr['search']) {
            $structure['contactSel'] = ['defaults'=>['type'=>$attr['type'],'drop'=>$attr['drop'],'callback'=>"contactsDetail(row.id, '{$attr['suffix']}', '{$attr['fill']}');"],'attr'=>['type'=>'contact']];
            $output['body'] .= '<div id="contactDiv'.$attr['suffix'].'"'.($attr['drop']?' style="display:none"':'').'>';
            $output['body'] .= html5('contactSel'.$attr['suffix'], $structure['contactSel']).'</div>';
            // Address select (hidden by default)
            $output['body'] .= '<div id="addressDiv'.$attr['suffix'].'" style="display:none">'.html5('addressSel'.$attr['suffix'], ['attr'=>['type'=>'text']])."</div>";
            $output['jsBody']['addrCombo'.$attr['suffix']] = "var addressVals{$attr['suffix']} = [];
jq('#addressSel{$attr['suffix']}').combogrid({width:150, panelWidth:750, idField:'id', textField:'primary_name', data:addressVals{$attr['suffix']},
    onSelect: function (id, data){ addressFill(data, '{$attr['suffix']}'); },
    columns:  [[
        {field:'address_id', hidden:true},
        {field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
        {field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
        {field:'city',        title:'".jsLang('address_book_city')."', width:100},
        {field:'state',       title:'".jsLang('address_book_state')."', width: 50},
        {field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
        {field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}]] });";
            // show the address drop down if values are present
//          if (isset($data['address'][$attr['suffix']])) { $output['jsReady'][] = "jq('#addressDiv{$attr['suffix']}').show();"; }
        } else {
            $output['body'] .= html5('contactSel'.$attr['suffix'], ['attr'=>['type'=>'hidden']]);
        }
        if ($attr['format'] == 'short') { foreach ($structure as $key => $value) {
            if (!empty($value['label'])) { $structure[$key]['options']['prompt'] = "'".jsLang($value['label'])."'"; }
            unset($structure[$key]['label']);
        } }
        $output['body'] .= "</div>\n";

        if (isset($structure['email'])) { $structure['email'] = array_merge_recursive($structure['email'], ['options'=>['multiline'=>true,'width'=>250,'height'=>60]]); }
        $data = [];
        // Address block
        $col1 = ['contact_id','address_id','primary_name','contact','address1','address2','city','state','postal_code','country'];
        foreach ($col1 as $idx) { if (isset($structure[$idx])) {
            $data['fields'][$idx.$attr['suffix']] = $structure[$idx];
        } }
        // Contact block
        $col2 = ['telephone1','telephone2','telephone3','telephone4','email','website'];
        foreach ($col2 as $idx) { if (isset($structure[$idx])) {
            $data['fields'][$idx.$attr['suffix']] = $attr['cols'] ? array_merge($structure[$idx], ['col'=>2]) : array_merge($structure[$idx], ['col'=>1]);
        } }
        $output['body'] .= $this->layoutFields([], $data);
        // @todo is the below line ever used?
//      if ($attr['notes']) { $output['body'] .= html5('notes'.$attr['suffix'],$structure['notes']); }
    }

    /**
     *
     * @param type $output
     * @param type $prop
     */
    public function layoutAttach(&$output, $prop) {
        global $viewData;
        $upload_mb= min((int)(ini_get('upload_max_filesize')), (int)(ini_get('post_max_size')), (int)(ini_get('memory_limit')));
        $path     = $prop['defaults']['path'].$prop['defaults']['prefix'];
        $io       = new \bizuno\io();
        msgDebug("\nbefore read rows");
        $rows     = $io->fileReadGlob($path);
        msgDebug("\nafter read rows: ".print_r($rows, true));
        $attr     = ['delPath'=>'bizuno/main/fileDelete','getPath'=>'bizuno/main/fileDownload','dgName'=>'dgAttachment'];
        $datagrid = ['id'=>$attr['dgName'],'title'=>lang('attachments').' '.sprintf(lang('max_upload'), $upload_mb),
            'attr'   => ['toolbar'=>"#{$attr['dgName']}Toolbar", 'pagination'=>false, 'idField'=>'title'],
            'events' => ['data'   => json_encode(['total'=>sizeof($rows),'rows'=>$rows])],
            'source' => ['actions'=> ['file_attach'=>['order'=>10,'attr'=>['type'=>'file','name'=>'file_attach']]]],
            'columns'=> [
                'action' => ['order'=>1,'label'=>lang('action'),'attr'=>['width'=>100],
                    'events' => ['formatter'=>"function(value,row,index) { return {$attr['dgName']}Formatter(value,row,index); }"],
                    'actions'=> [
                        'download'=>['order'=>30,'icon'=>'download','events'=>['onClick'=>"jq('#attachIFrame').attr('src','".BIZUNO_AJAX."&p={$attr['getPath']}&pathID=$path&fileID=idTBD');"]],
                        'trash'   =>['order'=>70,'icon'=>'trash',   'events'=>['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('{$attr['delPath']}','{$attr['dgName']}','{$path}idTBD');"]]]],
                'title'=> ['order'=>10,'label'=>lang('filename'),'attr'=>['width'=>300,'resizable'=>true]],
                'size' => ['order'=>20,'label'=>lang('size'),    'attr'=>['width'=>100,'resizable'=>true,'align'=>'center']],
                'date' => ['order'=>30,'label'=>lang('date'),    'attr'=>['width'=>100,'resizable'=>true,'align'=>'center']]]];
        $viewData['datagrid'][$attr['dgName']] = $datagrid;
        $this->layoutDatagrid($output, ['key'=>$attr['dgName'],'classes'=>['block50']]);
    }

    /**
     * This function builds the HTML (and JavaScript) content to render a jQuery easyUI datagrid
     * @param array $output - running HTML string to render the page
     * @param string $prop - The structure source data to pull from, if key is present then it's viewData, else it's the prop of the div for the datagrid
     * @param array $idx - The index in $data to grab the structure to build
     * @return string - HTML formatted EasyUI datagrid appended to $output
     */
    public function layoutDatagrid(&$output, $prop, $key=false) {
        global $viewData;
        if (empty($prop['key'])) { // prop contains the datagrid structure, this will go away after function htmlDatagrid is fully removed with new structure
            $output['body'] .= $this->htmlElOpen('', ['attr'=>['type'=>'div']]);
        } else { // prop contains the div properties, extract the datagrid structure
            $output['body'] .= $this->htmlElOpen('', array_merge($prop, ['attr'=>['type'=>'div']]));
            $prop = $viewData['datagrid'][$prop['key']];
        }
        $output['jsReady'][] = "jq('#{$prop['id']}').datagrid('resize');";
        $output['jsResize'][]= "jq('#{$prop['id']}').datagrid('resize',{width:jq(this).parent().width()});";
        $id = $prop['id'];
        $dgType = (isset($prop['type']) && $prop['type']) ? $prop['type'] : 'datagrid';
        $output['body'] .= "<!-- $dgType {$prop['id']} -->\n";
        if (isset($prop['attr']['toolbar'])) { // start the toolbar div
            $output['body'] .= '<div id="'.str_replace('#', '', $prop['attr']['toolbar']).'" style="padding:5px;height:auto">'."\n";
            $output['body'] .= "  <div>\n";
            if (isset($prop['source']['filters'])) {
                $prop['source']['filters'] = sortOrder($prop['source']['filters']);
                $temp = $dgGet = [];
                foreach ($prop['source']['filters'] as $key => $value) {
                    if (!empty($value['hidden']) || (!empty($value['attr']['type']) && $value['attr']['type']=='label')) { continue; }
                    $id = isset($value['attr']['id']) ? $value['attr']['id'] : $key; // override id, for dups on multi datagrid page
                    $temp[] = $id.":jq('#$id').val()";
                }
                $output['jsBody'][] = "function {$prop['id']}Reload() {\n  jq('#{$prop['id']}').$dgType('load',{".implode(',', $temp)."});\n}";
            }
            if (isset($prop['source']['fields'])) {
                $prop['source']['fields'] = sortOrder($prop['source']['fields']);
                $output['body'] .= '<div style="float:right">';
                foreach ($prop['source']['fields'] as $key => $value) {
                    if (!isset($value['hidden']) || !$value['hidden']) { $output['body'] .= $this->render($key, $value); }
                }
                $output['body'] .= '</div>';
            }
            if (isset($prop['source']['actions'])) {
                $prop['source']['actions'] = sortOrder($prop['source']['actions']);
                // handle the right aligned toolbar elements
                $right = '';
                foreach ($prop['source']['actions'] as $key => $value) {
                    if (isset($value['align']) && $value['align'] == 'right') { $right .= $this->render($key, $value); }
                }
                if ($right) { $output['body'] .= '<div style="float:right;">' . $right . "</div>\n"; }
                // now the left aligned
                foreach ($prop['source']['actions'] as $key => $value) {
                    if (empty($value['hidden']) && (!isset($value['align']) || $value['align']=='left')) {
                        $output['body'] .= $this->render($key, $value);
                    }
                }
            }
            if (isset($prop['source']['filters'])) {
                if (!empty($prop['source']['filters']['search'])) {
                    $output['body'] .= $this->render('search', $prop['source']['filters']['search']);
                    unset($prop['source']['filters']['search']);
                }
                $output['body'] .= '<a onClick="' . $prop['id'] . 'Reload();" class="easyui-linkbutton" data-options="iconCls:\'icon-search\'">' . lang('search') . "</a><br />\n";
                foreach ($prop['source']['filters'] as $key => $value) {
                    if (!empty($value['hidden'])) { continue; }
                    $output['body'] .= $this->render($key, $value);
                }
            }
            $output['body'] .= "  </div>\n";
            $output['body'] .= "</div>\n";
        }
        if (isset($prop['columns']) && is_array($prop['columns'])) { // build the formatter for the action column
            $prop['columns'] = sortOrder($prop['columns']);
            if (!empty($prop['columns']['action']['actions'])) {
                $actions = sortOrder($prop['columns']['action']['actions']);
                $jsBody = "  var text = '';";
                foreach ($actions as $id => $event) {
                    if (!isset($event['hidden'])) { $event['hidden'] = false; }
                    if (!$event['hidden']) {
                        if (isset($event['display'])) { $jsBody .= "  if ({$event['display']})"; }
                        unset($event['size']); // make all icons large
                        $temp = $this->render('', $event) . "&nbsp;";
                        $jsBody .= "  text += '" . str_replace(["\n", "\r", "'"], ["", "", "\'"], $temp) . "';\n";
                    }
                }
                $jsBody .= "  text = text.replace(/indexTBD/g, index);\n";
                if (isset($prop['attr']['idField'])) { // for sending db ID's versus row index ID's
                    $jsBody .= "  text = text.replace(/idTBD/g, row.{$prop['attr']['idField']});\n";
                }
                if (isset($prop['attr']['xtraField'])) { // for replacing row.values
                    foreach ($prop['attr']['xtraField'] as $rField) {
                        $jsBody .= "  text = text.replace(/{$rField['key']}/g, row.{$rField['value']});\n";
                    }
                }
                $btnMore  = trim($this->render("more_{$prop['id']}_indexTBD", ['icon'=>'more','size'=>'small','label'=>lang('more'),'events'=>['onClick'=>"myMenu{$prop['id']}(event, indexTBD)"],'attr'=>['type'=>'button']]));
                $funcMore = "function {$prop['id']}Formatter(value, row, index) {\n  var text='$btnMore';\n";
                $funcMore.= "  text = text.replace(/indexTBD/g, index);\n  return text;\n}\n";
                $output['jsBody'][] = $funcMore."function myMenu{$prop['id']}(e, index) {
    e.preventDefault();
    jq('#{$prop['id']}').datagrid('unselectAll');
    jq('#{$prop['id']}').datagrid('selectRow', index);
    var row = jq('#{$prop['id']}').datagrid('getRows')[index];
    if (typeof row == 'undefined') {  }
    jq('#tmenu').remove();
    var tmenu = jq('<div id=\"tmenu\"></div>').appendTo('body');
    $jsBody
    jq('<div />').html(text).appendTo(tmenu);
    tmenu.menu({ minWidth:30,itemHeight:44,onClick:function(item) {  } });
    jq('#more_{$prop['id']}_'+index).removeClass('icon-more');
    jq('#more_{$prop['id']}_'+index).menubutton({ iconCls: 'icon-more', menu:'#tmenu',hasDownArrow: false });
    jq('#tmenu').menu('show',{left:e.pageX, top:e.pageY} );
}";
                $prop['events']['onRowContextMenu'] = "function (e, index, row) { myMenu{$prop['id']}(e, index); }";
            }
        }
        $output['body'] .= '<table id="' . $prop['id'] . '"';
        if (isset($prop['title'])) { $output['body'] .= ' title="' . $prop['title'] . '"'; }
        $output['body'] .= "></table>";
        if (isset($prop['footnotes'])) {
            $output['body'] .= '<b>' . lang('notes') . ":</b><br />\n";
            foreach ($prop['footnotes'] as $note) { $output['body'] .= $note . "<br />\n"; }
        }
        if (isset($prop['tip'])) { $output['body'] .= "<div>\n  " . $prop['tip'] . "\n</div>\n"; }
        $js = "jq('#{$prop['id']}').$dgType({\n";
        $options = [];
        if (!empty($prop['rows'])) { $options[] = "  pageSize:{$prop['rows']}"; }
        if (!empty($prop['attr'])) { foreach ($prop['attr'] as $key => $value) { $options[] = "  $key:" . encodeType($value); } }
        if (isset($prop['events'])) {
            foreach ($prop['events'] as $key => $value) { $options[] = " $key:$value"; }
        }
        // build the columns
        $cols = [];
        foreach ($prop['columns'] as $col => $settings) {
            $settings['attr']['field'] = $col;
            $settings['attr']['title'] = isset($settings['label']) ? $settings['label'] : $col;
            $temp = [];
            foreach ($settings['attr'] as $key => $value) { $temp[] = "$key:" . encodeType($value); }
            if (!empty($settings['events']) && empty($settings['attr']['hidden'])) {
                foreach ($settings['events'] as $key => $value) { $temp[] = "$key:$value"; }
            }
            $cols[] = "    { " . implode(",", $temp) . " }";
        }
        $options[] = "  columns:[[\n" . implode(",\n", $cols) . "\n]]";
        $js .= implode(",\n", $options) . "\n});";
        $output['jsBody'][] = $js;
        $output['body'] .= "</div><!-- EOF Datagrid -->\n";
    }

    /**
     * Build the view for a list of fields
     * OLD WAY - index fields contained the entire structure, build from there
     * NEW WAY - index keys contains the keys to $data['fields'] array containing structure.
     * The new way will allow easier customization as the structure location is fixed from page to page and groups can be used to form fieldsets
     * @param array $prop
     * @return string - HTML markup
     */
    public function layoutFields($data=[], $prop=[]) {
        if (!empty($prop['keys'])) { // pull from $data
            foreach ($prop['keys'] as $key) { $prop['fields'][$key] = $data['fields'][$key]; }
        }
        $tmp1  = sortOrder($prop['fields']); //sort order first
        $fields= [];
        foreach ($tmp1 as $key => $value) {
            if (empty($value['col'])) { $value['col'] = 1; }
            $fields[$value['col']][$key] = $value;
        }
        ksort($fields);
//      msgDebug("after sort layoutFields with prop = ".print_r($fields, true));
        $output = '';
        if (!empty($prop['label'])) { $output .= $this->layoutFieldset($prop); }
        foreach ($fields as $column) {
            $output .= '<div class="blockView">';
            foreach ($column as $key => $attr) { $output .= $this->render($key, $attr); }
            $output .= "</div>\n";
        }
        if (!empty($prop['label'])) { $output .= '</fieldset>'; }
        $output .= '<div>&nbsp;</div>';
        return $output;
    }

    public function layoutFieldset($props) {
        switch ($GLOBALS['myDevice']) {
            case 'mobile':
            case 'tablet': // was style="width:100%;min-width:0;"
                $props['styles']['width']    = "100%";
                $props['styles']['min-width']= "0";
            default:
        }
        return '<fieldset'.$this->addAttrs($props).'><legend>'.$props['label'].'</legend>';
    }

    public function layoutList($layout, $props) {
        $format = !empty($props['ui']) && $props['ui']=='none' ? 'none' : '';
        if ($format == 'none') {
            $output = "<p>";
        } else {
            $props['attr']['type'] = 'ul';
            $props['classes'][] = 'easyui-datalist';
            $output = "\n<!-- BOF list -->\n".$this->htmlElOpen(!empty($props['id']) ? $props['id'] : '', $props)."\n";
        }
        foreach ($layout['lists'][$props['key']] as $fld => $li) {
            $fldID = !empty($li['attr']['id']) ? $li['attr']['id'] : $fld;
            if ($format=='none') {
                $output .= (is_array($li) ? $this->render($fldID, $li) : $li);
                if (!isset($li['break']) || $li['break']) { $output .= "</p>\n<p>"; }
            } else {
                $output .= "<li>". (is_array($li) ? $this->render($fldID, $li) : $li) . "</li>\n";
            }
        }
        if ($format=='none') { $output .= "</p>\n"; }
        else                 { $output .= "</ul><!-- EOF List -->\n"; }
        return $output;
    }

    /**
     * This function builds the HTML output render a jQuery easyUI panel feature
     * @param string $output - running string of them HTML output to be add to
     * @param array $data - complete data array containing structure of entire page, only JavaScript part is used to force load from JavaScript data
     * @param string $id - accordion DOM ID
     * @param array $settings - the structure of the accordions (i.e. div structure for each accordion)
     */
    public function layoutPanel(&$output, $prop) {
        $prop['attr']['type'] = 'div';
        $prop['classes'][] = 'easyui-panel';
        if (empty($prop['html'])) { $prop['html'] = '&nbsp;'; }
        $output['body'] .= $this->htmlElOpen($prop['id'], $prop);
        $output['body'] .= "<p>{$prop['html']}</p></div>";
    }

/*    public function layoutPanel?(&$otuput, $prop) { // don't think this is used anymore
        switch ($GLOBALS['myDevice']) {
            case 'mobile': // need to build as a new div and attach to page
            case 'tablet':
                if (empty($prop['attr']['id'])) { $prop['attr']['id'] = randomValue(); }
                $output['body'] .= '<div id="'.$prop['attr']['id'].'" class="easyui-navpanel">';
                $title  = !empty($prop['title']) ? $prop['title'] : 'No Title';
                $header = ['left'=>['back'],'title'=>$title,'right'=>[]];
                $footer = [];
                $output['body'] .= $this->mobileMenu($header, 'header');
                $output['body'] .= $this->mobileMenu($footer, 'footer');
                $output['body'] .= '<div>'.$this->buildDivs($prop)."</div>\n"; // build the body
                break;
            case 'desktop':
            default:
        }
    } */

     public function layoutPayment(&$output, $prop) {
        global $viewData;
        $output['body'] .= '<div class="blockView">'."<fieldset><legend>".lang('payment_method')."</legend>\n";
        // if we have data, see what the stored values are to set defaults
        $viewDataValues= [];
        $dispFirst = $viewData['fields']['selMethod']['attr']['value'] = $viewData['fields']['method_code']['attr']['value'];

        if (isset($prop['settings']['items'])) { foreach ($prop['settings']['items'] as $row) { // fill in the data if available
            $props = explode(";", $row['description']);
            foreach ($props as $val) {
                $tmp = explode(":", $val);
                $viewDataValues[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
            }
            if (empty($row['item_ref_id'])) { continue; }
            $txID = dbGetValue(BIZUNO_DB_PREFIX."journal_item", array('description','trans_code'), "ref_id='{$prop['settings']['items'][0]['item_ref_id']}' AND gl_type='ttl'");
            $props1 = !empty($txID['description']) ? explode(";", $txID['description']) : [];
            foreach ($props1 as $val) {
                $tmp = explode(":", $val);
                $viewDataValues[$tmp[0]] = isset($tmp[1]) ? $tmp[1] : '';
            }
            $viewDataValues['id']        = $row['item_ref_id'];
            $viewDataValues['trans_code']= !empty($txID['trans_code']) ? $txID['trans_code'] : '';
            $viewDataValues['total']     = !empty($row['total']) ? $row['total'] : 0;
        } }
        // set the pull down for which method, onChange execute javascript function to load defaults
        $output['body'] .= html5('method_code', $viewData['fields']['selMethod']);
        $methods = sortOrder(getModuleCache('payment', 'methods'));
        foreach ($methods as $method => $settings) {
            if (isset($settings['status']) && $settings['status']) { // load the div for each method
                if (!is_file($settings['path']."$method.php")) {
                    msgAdd("I cannot find the method $method to load! Skipping.");
                    continue;
                }
                if (!$dispFirst) { $dispFirst = $method; }
                $style = $dispFirst == $method ? '' : ' style="display:none;"';
                $output['body'] .= '<div id="div_'.$method.'" class="layout-expand-over"'.$style.'>'."\n";
                $fqcn  = "\\bizuno\\$method";
                bizAutoLoad($settings['path']."$method.php", $fqcn);
                $pmtSet= getModuleCache('payment','methods',$method,'settings');
                $temp  = new $fqcn($pmtSet);
                $temp->render($output, $viewData, $viewDataValues, $dispFirst);
                $output['body'] .= "</div>\n";
            }
        }
        $output['body'] .= "</fieldset></div>\n";
        $this->jsReady[] = "payment_$dispFirst();"; // force loading of defaults for displayed payment method
    }

    /**
     * This function generates the tabs pulled from the current structure, position: $data['tabs'][$idx]
     * @param array $output - running HTML string to render the page
     * @param string $prop - The structure source data to pull from
     * @param array $idx - The index in $prop to grab the structure to build
     * @return string - HTML formatted EasyUI tabs appended to $output
     */
    public function layoutTab(&$output, $prop) {
        $output['jsResize'][] = "jq('#{$prop['id']}').tabs('resize',{width:jq(this).parent().width()});";
        $divs = sortOrder($prop['divs']);
        if (isset($prop['focus'])) {
            $indices = array_keys($divs);
            foreach ($indices as $key => $tabID) { if ($prop['focus'] == $tabID) { $prop['options']['selected'] = $key; } }
        }
        $prop['attr']['type'] = 'div';
        $prop['classes']['ui']= "easyui-tabs";
        $output['body'] .= $this->htmlElOpen($prop['id'], $prop)."\n<!-- Begin tab group {$prop['id']} -->\n";
        foreach ($divs as $tabID => $tabDiv) {
            $tabDiv['attr']['id'] = $tabID;
            $tabDiv['attr']['title'] = !empty($tabDiv['label']) ? $tabDiv['label'] : $tabID;
            $tabDiv['classes']['display']= 'menuHide';
            $tabDiv['styles']['padding'] = '5px';
            if (!empty($tabDiv['icon'])) { $tabDiv['attr']['iconCls'] = "icon-{$tabDiv['icon']}"; }
            $output['body'] .= "<!-- Begin tab $tabID -->\n";
            unset($tabDiv['label']); // clear the label or it will be create a duplicate fieldset
            $this->buildDiv($output, $tabDiv);
            $output['body'] .= "<!-- End tab $tabID -->\n";
        }
        $output['body'] .= "</div><!-- End tab group {$prop['id']} -->\n";
        $this->jsReady[] = "jq('.menuHide').show();";
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
     * This function generates a html toolbar pulled from the current structure
     * @param array $output - running HTML string to render the page
     * @param array $id - The index in $prop to grab the structure to build
     * @param string $prop - The structure source data to pull from
     * @return string - HTML formatted EasyUI toolbar appended to $output
     */
    public function layoutToolbar(&$output, $prop) {
        if (!empty($prop['hidden'])) { return; } // toolbar is hidden
        foreach ($prop['icons'] as $name => $struc) {
            if (!isset($struc['type'])) { $prop['icons'][$name]['type'] = 'icon'; }
            if (!isset($struc['icon']) && $prop['icons'][$name]['type'] == 'icon') { $prop['icons'][$name]['icon'] = $name; }
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
        $output['jsResize'][] = "jq('#{$prop['id']}').tree('resize',{width:jq(this).parent().width()});";
        $temp = [];
        $output['body'] .= '<ul id="' . $prop['id'] . '"></ul>' . "\n";
        if (isset($prop['menu'])) {
            $output['body'] .= "<div";
            foreach ($prop['menu']['attr'] as $key => $value) { $output['body'] .= ' ' . $key . '="' . str_replace('"', '\"', $value) . '"'; }
            $output['body'] .= ">\n";
            foreach ($prop['menu']['actions'] as $key => $value) {
                $output['body'] .= '  <div id="' . $key . '"';
                foreach ($value['attr'] as $key => $val) { $output['body'] .= ' ' . $key . '="' . str_replace('"', '\"', $val) . '"'; }
                $output['body'] .= ">" . (isset($value['label']) ? $value['label'] : '') . "</div>\n";
            }
            $output['body'] .= "</div>\n";
        }
        if (isset($prop['footnotes'])) {
            $output['body'] .= '<b>' . lang('notes') . ":</b><br />\n";
            foreach ($prop['footnotes'] as $note) { $output['body'] .= $note . "\n"; }
        }
        foreach ($prop['attr'] as $key => $value) {
            $val = is_bool($value) ? ($value ? 'true' : 'false') : "'$value'";
            $temp[] = "$key: $val";
        }
        if (isset($prop['events'])) {
            foreach ($prop['events'] as $key => $value) { $temp[] = "      $key: $value"; }
        }
        $output['jsBody'][] = "jq('#".$prop['id']."').tree({\n".implode(",\n", $temp)."\n});\n";
    }

    /***************************** Forms ******************/

    public function input($id, $prop) {
        $this->addID($id, $prop);
        $field = '<input';
        if (isset($prop['attr']['value'])) {
            $value = isset($prop['format']) ? viewFormat($prop['attr']['value'], $prop['format']) : $prop['attr']['value'];
            $field .= ' value="' . str_replace('"', '&quot;', $value) . '"';
            unset($prop['attr']['value']);
        }
        if (!empty($prop['js']))     { $this->jsBody[] = $prop['js']; } // old way
        if (!empty($prop['jsBody'])) { $this->jsBody[] = $prop['jsBody']; } // new way
        $output = $this->addLabelFirst($id, $prop) . $field . $this->addAttrs($prop) . " />" . $this->addLabelLast($id, $prop);
        $hidden = isset($prop['attr']['type']) && $prop['attr']['type']=='hidden' ? true : false;
        return $output . (!empty($prop['break']) && !$hidden ? '<br />' : '');
    }

    public function inputButton($id, $prop) {
        $this->addID($id, $prop);
        $prop['attr']['type'] = 'a';
        $prop['classes'][] = 'easyui-linkbutton';
//        $prop['styles']['cursor'] = 'pointer';
        if (!isset($prop['attr']['href'])) { $prop['attr']['href'] = '#'; }
        return $this->htmlElBoth($id, $prop);
    }

    public function inputCheckbox($id, $prop) {
        if (empty($prop['attr']['checked'])) { unset($prop['attr']['checked']); }
        $prop['classes'][] = 'easyui-switchbutton';
        $prop['options']['value'] = !empty($prop['attr']['value']) ? "'".$prop['attr']['value']."'" : 1;
        $prop['options']['onText']  = "'".jsLang('yes')."'";
        $prop['options']['offText'] = "'".jsLang('no')."'";
        $prop['attr']['type'] = 'text';
        unset($prop['attr']['value']);
        $this->mapEvents($prop);
        return $this->input($id, $prop);
    }

    public function inputColor($id, $prop) {
        $prop['classes'][] = 'easyui-color';
        return $this->input($id, $prop);
    }

    public function inputContact($id, $prop) {
        $defs = ['type'=>'c','value'=>0,'suffix'=>'','store'=>0,'drop'=>false,'fill'=>false,'data'=>false,'callback'=>"contactsDetail(row.id, '', false);"];
        $attr = array_merge($defs, $prop['defaults']);
        $url  = "'".BIZUNO_AJAX."&p=contacts/main/managerRows&clr=1";
        $url .= "&type=" .(!empty($attr['drop'])  ? 'c' : $attr['type']);
        $url .= "&store=".(!empty($attr['store']) ? '1' : '0');
        $url .= "'";
        $prop['classes'][] = 'easyui-combogrid';
        $prop['options']['width']       = "250,panelWidth:750,delay:900,idField:'id',textField:'primary_name',mode:'remote',iconCls:'icon-search',hasDownArrow:false,selectOnNavigation:false";
        $prop['options']['url']         = $url;
        $prop['options']['onBeforeLoad']= "function (param) { var newValue=jq('#$id').combogrid('getValue'); if (newValue.length < 3) return false; }";
        if (!empty($attr['data']))     { $prop['options']['data']      = $attr['data']; }
        if (!empty($attr['callback'])) { $prop['options']['onClickRow']= "function (idx, row) { {$attr['callback']} }"; }
        $prop['options']['columns']       = "[[{field:'id', hidden:true},
    {field:'short_name',  title:'".jsLang('contacts_short_name')."', width:100},
    {field:'type',        hidden:".(strlen($attr['type'])>1?'false':'true').",title:'".jsLang('contacts_type')."', width:100},
    {field:'primary_name',title:'".jsLang('address_book_primary_name')."', width:200},
    {field:'address1',    title:'".jsLang('address_book_address1')."', width:100},
    {field:'city',        title:'".jsLang('address_book_city')."', width:100},
    {field:'state',       title:'".jsLang('address_book_state')."', width: 50},
    {field:'postal_code', title:'".jsLang('address_book_postal_code')."', width:100},
    {field:'telephone1',  title:'".jsLang('address_book_telephone1')."', width:100}]]";
        unset($prop['attr']['type'], $prop['defaults']);
        return $this->input($id, $prop);
    }

    public function inputCountry($id, $prop) {
        $value = !empty($prop['attr']['value']) ? $prop['attr']['value'] : getModuleCache('bizuno','settings','company','country','USA');
        $prop['classes'] = ['easyui-combogrid'];
        $prop['options']['data'] = "bizDefaults.countries";
        $prop['options']['width'] = 150;
        $prop['options']['panelWidth'] = 300;
        $prop['options']['value'] =  "'$value'" ;
        $prop['options']['idField'] = "'iso3'";
        $prop['options']['textField'] = "'title'";
        $prop['options']['columns'] = "[[{field:'iso3',title:'".jsLang('code')."',width:60},{field:'title',title:'".jsLang('title')."',width:200}]]";
        return $this->input($id, $prop);
    }

    public function inputCurrency($id, $prop) {
        $cur = !empty($GLOBALS['bizunoCurrency']) ? $GLOBALS['bizunoCurrency'] : getUserCache('profile','currency',false,'USD');
        $iso = getModuleCache('phreebooks', 'currency', 'iso', $cur);
        if (empty($iso)) { $iso = getModuleCache('phreebooks', 'currency', 'iso', getUserCache('profile', 'currency', false, 'USD')); }
        $prop['classes'][] = 'easyui-numberbox';
        $prop['options']['decimalSeparator']= "'".addslashes($iso['dec_pt'])."'";
        $prop['options']['groupSeparator']  = "'".addslashes($iso['sep'])."'";
        $prop['options']['precision']       = intval($iso['dec_len']);
        if (!empty($iso['prefix'])) { $prop['options']['prefix'] = "'" .addslashes($iso['prefix'])." '"; }
        if (!empty($iso['suffix'])) { $prop['options']['suffix'] = "' ".addslashes($iso['suffix'])."'"; }
        $prop['styles']['text-align'] = 'right';
        if (empty($prop['options']['width'])) { $prop['options']['width'] = 125; }
        unset($prop['attr']['type'], $prop['attr']['size']);
        $this->mapEvents($prop);
        return $this->input($id, $prop);
    }

    public function inputDate($id, $prop) {
        if (empty($prop['styles']['width'])) { $prop['styles']['width']  = '150px'; }
        $prop['classes'][] = 'easyui-datebox';
        $prop['attr']['type'] = 'text'; // needed to turn off browser takeover of date box (Chrome)
        if (!empty($prop['attr']['value'])) {
            $prop['options']['value'] = "'".viewDate($prop['attr']['value'])."'";
            unset($prop['attr']['value']);
        }
        return $this->input($id, $prop);
    }

    public function inputEmail($id, $prop) {
        $prop['classes'][] = 'easyui-textbox easyui-validatebox';
        $defaults = ['options'=>['multiline'=>true,'width'=>275,'height'=>60,'prompt'=>"'".jsLang('email')."'",'iconCls'=>"'icon-email'"]];
        $prop1 = array_replace_recursive($defaults, $prop);
        $prop1['attr']['type'] = 'text';
        return $this->input($id, $prop1);
    }

    public function inputFile($id, $prop) {
        unset($prop['break'], $prop['attr']['type']);
        $prop['classes'][] = 'easyui-filebox';
        if (empty($prop['options']['width'])) { $prop['options']['width'] = 350; }
        $this->mapEvents($prop);
        return $this->input($id, $prop);
    }

    /**
     * This function builds the combo box editor HTML for a datagrid to view GL Accounts
     * @return string set the editor structure
     */
    public function inputGL($id, $prop)
    {
        if (!empty($prop['types'])) {
            $js = "var {$id}Data = [];
    var types= [".implode(',',$prop['types'])."];
    for (i=0; i<bizDefaults.glAccounts.length; i++) {
        for (j=0; j<types.length; j++) { if (bizDefaults.glAccounts[i].type == type[j] && bizDefaults.glAccounts[i].inactive != '1') {$id}Data[] = bizDefaults.glAccounts[i]; }
    }";
        } elseif (!empty($prop['heading'])) { // just heading accounts
            $assets= [0, 2, 4, 6, 8, 12, 32, 34]; // gl_account types that are assets
            $accts = []; // load gl Accounts
            foreach (getModuleCache('phreebooks', 'chart', 'accounts') as $row) {
                if (empty($row['heading'])) { continue; }
                $row['asset'] = in_array($row['type'], $assets) ? 1 : 0;
                $row['type'] = viewFormat($row['type'], 'glType');
                $accts[] = $row; // need to remove keys
            }
            if (empty($accts)) { $accts = [['id'=>'','type'=>'','title'=>'No Primaries Defined']]; }
            $js = "var {$id}Data = ".json_encode($accts).";";
        } else {
            $js = "var {$id}Data=[];\njq.each(bizDefaults.glAccounts.rows, function( key, value ) { if (value['inactive'] != '1') { {$id}Data.push(value); } });";
        }
        $this->jsHead[] = $js;
        $prop['classes'][]         = 'easyui-combogrid';
        $prop['options']['width']  = "250,rows:100,panelWidth:490,idField:'id',textField:'title',selectOnNavigation:false";
        $prop['options']['data']   = "{$id}Data";
//        $prop['options']['onShowPanel'] = "function(){ alert('show panel'); }";
        if (!empty($prop['attr']['value'])) { $prop['options']['value'] = "'".$prop['attr']['value']."'"; }
        $this->mapEvents($prop);
        $prop['options']['columns']= "[[{field:'id',title:'".jsLang('gl_account')."',width:60},{field:'title',title:'".jsLang('title')."',width:200},{field:'type',title:'".jsLang('type')."',width:180}]]";
        unset($prop['attr']['type'],$prop['attr']['value']);
//        $this->jsReady[] = "jq('#$id').combogrid('reload');";
        return  $this->input($id, $prop);
    }

    public function inputInventory($id, $prop) {
        $defaults = ['width'=>250, 'panelWidth'=>350, 'delay'=>500, //'iconCls'=>"'icon-search'", 'hasDownArrow'=>'false',
            'idField'=>"'id'", 'textField'=>"'description_short'", 'mode'=>"'remote'"];
        $defaults['url']     = "'".BIZUNO_AJAX."&p=inventory/main/managerRows&clr=1'";
        $defaults['callback']= "jq('#item_cost').val(data.item_cost); jq('#full_price').val(data.full_price);";
        $defaults['columns'] = "[[{field:'id',hidden:true},{field:'sku',title:'".jsLang('sku')."',width:100},{field:'description_short',title:'".jsLang('description')."',width:200}]]";
        // override defaults
        $prop['options'] = !empty($prop['defaults']) ? array_merge($defaults, $prop['defaults']) : $defaults;
        $prop['classes'][]              = 'easyui-combogrid';
        $prop['options']['onBeforeLoad']= "function () { var newValue=jq('#$id').combogrid('getValue'); if (newValue.length < 2) return false; }";
        $prop['options']['onClickRow']  = "function (id, data) { {$prop['options']['callback']} }";
        if (isset($prop['attr']['value'])) { $prop['options']['value'] = "'".$prop['attr']['value']."'"; }
        unset($prop['options']['callback'], $prop['attr']['type']);
        return $this->input($id, $prop);
    }

    public function inputMonth($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputNumber($id, $prop, $precision=false) {
        $prop['classes'][] = 'easyui-numberbox';
        $prop['styles']['text-align'] = 'right';
        if ($precision !== false) { $prop['options']['precision'] = 0; }
        if (empty($prop['options']['width'])) { $prop['options']['width'] = 100; }
        $this->mapEvents($prop);
        return $this->input($id, $prop);
    }

    public function inputPassword($id, $prop) {
        if (empty($prop['styles']['width']))  { $prop['styles']['width']  = '300px'; }
//      if (empty($prop['styles']['height'])) { $prop['styles']['height'] = '38px'; }
        $prop['classes'][] = 'easyui-passwordbox';
        $prop['options']['prompt'] = "'".jsLang('password')."'";
        return $this->input($id, $prop);
    }

    public function inputRadio($id, $prop) {
        if (empty($prop['attr']['checked'])) { unset($prop['attr']['checked']);  }
        if (empty($prop['attr']['selected'])){ unset($prop['attr']['selected']); }
        $prop['position'] = 'after';
        return $this->input($id, $prop);
    }

    public function inputRange($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputRaw($id, $prop) {
        $output = $this->addLabelFirst($id, $prop) . $prop['html'] . $this->addLabelLast($id, $prop);
        return $output . (!empty($prop['break']) ? '<br />' : '');
    }

    public function inputSearch($id, $prop) {
        return $this->input($id, $prop);
    }

    public function inputSelect($id, $prop) {
        $this->addID($id, $prop);
        $first = $this->addLabelFirst($id, $prop);
        $last  = $this->addLabelLast($id, $prop);
        unset($prop['attr']['type']);
        if (!empty($prop['jsBody'])) { $this->jsBody[] = $prop['jsBody']; } // new way
        // style it
        $prop['classes']['ui'] = "easyui-combobox";
        if (!empty($prop['attr']['value']) && !is_array($prop['attr']['value'])) {
            $prop['options']['value'] = "'".str_replace("'", "\\'", $prop['attr']['value'])."'"; // handle single quote in value variable
        }
        if (empty($prop['options']['editable'])) { $prop['options']['editable'] = 'false'; }
        if (empty($prop['options']['width'])) { $prop['options']['width'] = 200; }
        // fix the events
        $this->mapEvents($prop);
        // value need to be an array to accomodate multiple select comboboxes
        $values = isset($prop['attr']['value']) ? (array)$prop['attr']['value'] : []; // needs to be isset, cannot be empty
        if (sizeof($values)) {
            $optField = '<option value="'.$values[0].'">'.htmlspecialchars($values[0]).'</option>';
        } else { $optField = ''; }
        unset($prop['attr']['value']);
        $field = '<select' . $this->addAttrs($prop) . '>';
        if (isset($prop['values']) && is_array($prop['values']) && sizeof($prop['values']) > 0) {
            foreach ($prop['values'] as $choice) {
                if (isset($choice['hidden']) && $choice['hidden']) { continue; }
                $field .= '<option value="'.$choice['id'].'"';
                if (in_array($choice['id'], $values)) { $field .= ' selected="selected"'; }
                $field .= '>' . htmlspecialchars(isset($choice['title']) ? $choice['title'] : $choice['text']) . '</option>';
            }
        } else {
            $field .= $optField;
        }
        $field .= "</select>\n";
        $output = $first . $field . $last;
        return $output . (!empty($prop['break']) ? '<br />' : '');
    }

    public function inputTax($id, $prop) {
        $defaults = ['value'=>'', 'type'=>'c', 'callback'=>false];
        if (!empty($prop['defaults']))    { $defaults = array_merge($defaults, $prop['defaults']); }
        if ( empty($defaults['callback'])){ $defaults['callback'] = "totalUpdate('inputTax');"; }
        $prop['classes'][]          = 'easyui-combogrid';
        $prop['options']['data']    = "bizDefaults.taxRates.{$defaults['type']}.rows";
        $prop['options']['value']   = "'{$defaults['value']}'";
        $prop['options']['width']   = "120,panelWidth:210,delay:500,idField:'id',textField:'text'";
        if (!empty($defaults['data'])) { $prop['options']['data'] = $defaults['data']; }
        $prop['options']['onSelect']= "function (id, data) { {$defaults['callback']} }";
        $prop['options']['columns'] = "[[{field:'id',hidden:true},{field:'text',title:'".jsLang('journal_main_tax_rate_id')."',width:120},{field:'tax_rate',title:'".jsLang('amount')."',align:'center',width:70}]]";
        unset($prop['attr']['type']);
        return $this->input($id, $prop);
    }

    public function inputTel($id, $prop) {
        // restrict to numbers, dots or dashes
        return $this->input($id, $prop);
    }

    public function inputText($id, $prop) {
        if (!empty($prop['inner']) && !empty($prop['label'])) { $prop['options']['prompt'] = jsLang('email'); }
        if (in_array($prop['attr']['type'], ['hidden'])) { unset($prop['break']); return $this->input($id, $prop); }
        $prop['classes'][] = 'easyui-textbox';
        if (empty($prop['options']['width'])) { $prop['options']['width'] = 200; }
        $this->mapEvents($prop);
        return $this->input($id, $prop);
    }

    public function inputTextarea($id, $prop) {
        $this->addID($id, $prop);
        if (empty($prop['attr']['rows'])) { $prop['attr']['rows'] = 20; }
        if (empty($prop['attr']['cols'])) { $prop['attr']['cols'] = 60; }
        $content = '';
        $field  = $this->addLabelFirst($id, $prop);
        $field .= '<textarea';
        foreach ($prop['attr'] as $key => $value) {
            if (in_array($key, ['type', 'maxlength'])) { continue; }
            if ($key == 'value') { $content = $value; continue; }
            $field .= ' ' . $key . '="' . str_replace('"', '&quot;', $value) . '"';
        }
        $field .= ">" . htmlspecialchars($content) . "</textarea>\n";
        $field .= $this->render('', ['icon'=>'edit','size'=>'small','label'=>lang('edit'),'events'=>['onClick'=>"tinymceInit('$id');"]]);
        $field .= $this->addLabelLast($id, $prop);
        $this->jsBody['tinyMCE'] = "jq.cachedScript('" . self::htmlEditor . "');";
        if (!empty($prop['break'])) { $field .= '<br />'; }
        return $field;
    }

    public function inputTime($id, $prop) {
        // time spinner
        return $this->input($id, $prop);
    }

    public function inputUrl($id, $prop) {
        return $this->input($id, $prop);
    }

    public function selCurrency($id, $prop) {
        if (empty($prop['attr']['value'])) { $prop['attr']['value'] = getUserCache('profile', 'currency', false, 'USD'); }
        if (sizeof(getModuleCache('phreebooks', 'currency', 'iso')) > 1) {
            $prop['attr']['type'] = 'select';
            $prop['values']       = viewDropdown(getModuleCache('phreebooks', 'currency', 'iso'), "code", "title");
            unset($prop['attr']['size']);
            $onChange = !empty($prop['callback']) ? " {$prop['callback']}(newVal, oldVal);" : '';
//          $onChange = "setCurrency(newVal);" . (!empty($prop['callback']) ? " {$prop['callback']}(newVal, oldVal);" : '');
            $this->jsReady[] = "jq('#$id').combobox({editable:false, onChange:function(newVal, oldVal){ $onChange } });";
        } else {
            $prop['attr']['type'] = 'hidden';
        }
        return $this->render($id, $prop) ;
    }

    public function selNoYes($id, $prop) {
        if (!empty($prop['attr']['value'])) { $prop['attr']['checked'] = true; }
        $prop['attr']['type'] = 'checkbox';
        return $this->render($id, $prop);
    }

    /***************************** Media ******************/

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
        if (!empty($prop['options'])) {
            $tmp = [];
            foreach ($prop['options'] as $key => $value) { $tmp[] = "$key:$value"; }
            $prop['attr']['data-options'] = "{".implode(',', $tmp)."}";
        }
        if (!empty($prop['attr'])) { foreach ($prop['attr'] as $key => $value) {
            $field .= ' '.$key.'="'.str_replace('"', '\"', $value).'"'; // was str_replace('"', '&quot;', $value)
        } }
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

    private function mapEvents(&$prop) {
        if (empty($prop['events'])) { return; }
        foreach ($prop['events'] as $key => $event) {
            //msgDebug("\nmapEvents with key = $key and event = $event");
            $action = false;
            switch($key) {
                case 'onBlur':   $newKey = 'onChange'; $action = "function (newVal, oldVal) { ".$event." }"; break;
                case 'onChange': $newKey = 'onChange'; $action = "function (newVal, oldVal) { ".$event." }"; break;
                case 'onSelect': $newKey = 'onSelect'; $action = "function (index, row) { ".$event." }";     break;
                default: // do nothing
            }
            if ($action) {
                msgDebug("\nadding to options array with key = $key and action = $action");
                $prop['options'][$newKey] = $action;
                unset($prop['events'][$key]);
            }
        }
    }

    private function addID($id = '', &$prop = []) {
        if ($id && !isset($prop['attr']['name'])) { $prop['attr']['name'] = $id; }
        if (isset($prop['attr']['id'])) { } // use it
        elseif (strpos($id, '[]'))      { unset($prop['attr']['id']); } // don't show id attribute if generic array
        elseif ($id) {
            $prop['attr']['id'] = str_replace('[', '_', $id); // clean up for array inputs causing html errors
            $prop['attr']['id'] = str_replace(']', '', $prop['attr']['id']);
        }
        if (isset($prop['attr']['required']) && $prop['attr']['required']) { $prop['classes'][] = 'easyui-validatebox'; unset($prop['attr']['required']); }
    }

    private function addLabelFirst($id, $prop) {
        $field = '';
        if ( empty($prop['label'])) { return $field; }
        if (!empty($prop['attr']['type']) && $prop['attr']['type'] == 'hidden') { return $field; }
        if ( empty($prop['position'])) {
            $el = ['styles'=>['vertical-align'=>'top'],'attr'=>['type'=>'label','for'=>$id]];
            if (!empty($prop['lblStyle'])) { $el['styles'] = array_merge($el['styles'], $prop['lblStyle']); }
            $field .= $this->htmlElOpen('', $el) . $prop['label'].'</label>&nbsp;';
            if (!empty($prop['tip'])) { $field .= $this->addToolTip($id, $prop['tip']); }
        }
        return $field;
    }

    private function addLabelLast($id, $prop) {
        $field = '';
        if ( empty($prop['label'])) { return $field; }
        if (!empty($prop['attr']['type']) && $prop['attr']['type'] == 'hidden') { return $field; }
        if (!empty($prop['position'])     && $prop['position'] == 'after') {
            $mins  = !empty($prop['attr']['type']) && in_array($prop['attr']['type'], ['checkbox','radio'])? 'min-width:60px;' : '';
            $styles= "vertical-align:top;$mins";
            if (!empty($prop['tip'])) { $field .= $this->addToolTip($id, $prop['tip']); }
            $field .= '<label for="'.$id.'" class="fldLabel" style="'.$styles.'">'.$prop['label'].'</label>';
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

    private function addToolTip($id, $tip='') {
        $opts = ['showEvent'=>"'click'",'position'=>"'bottom'",'onShow'=>"function(){ jq(this).tooltip('tip').css({width:'450px'}); }"];
        $prop = ['classes'=>["icon-help", "easyui-tooltip"],'styles'=>['border'=>0,'display'=>'inline-block;vertical-align:middle','height'=>'16px','min-width'=>'16px','cursor'=>'pointer'],
            'options'=>$opts,'attr'=>['type'=>'span','id'=>"tip_$id",'title'=>$tip]];
//        $this->jsReady[] = "jq('#tip_$id').tooltip({ $opts,content:'".addslashes($tip)."'});";
        return $this->render('', $prop);
    }
}

/** *************************** Datagrid Editors ***************** */
/**
 * Creates the datagrid editor for a currency numberbox
 * @param string $onChange - JavaScript to run on change event
 * @param boolean $precision - set to true for number precision; false for currency precision
 * @return string - grid editor JSON
 */
function dgEditCurrency($onChange='', $precision=false) {
    $iso  = getUserCache('profile', 'currency', false, 'USD');
    $props= getModuleCache('phreebooks', 'currency', 'iso', $iso, 'USD');
    $prec = $precision ? getModuleCache('bizuno','settings','locale','number_precision','2') : $props['dec_len'];;
    $dec  = str_replace("'", "\\'", $props['dec_pt']);
    $tsnd = str_replace("'", "\\'", $props['sep']);
    $pfx  = !empty($props['prefix']) ? str_replace("'", "\\'", $props['prefix'])." " : '';
    $sfx  = !empty($props['suffix']) ? " ".str_replace("'", "\\'", $props['suffix']) : '';
    return "{type:'numberbox',options:{precision:$prec,decimalSeparator:'$dec',groupSeparator:'$tsnd',prefix:'$pfx',suffix:'$sfx',onChange:function(newValue, oldValue){ $onChange } } }";
}

/**
 * This function builds the combo box editor HTML for a datagrid to view GL Accounts
 * @param string $onClick - JavaScript to run on click event
 * @return string set for the editor structure
 */
function dgEditGL($onClick='') {
    return "{type:'combogrid',options:{ data:pbChart, mode:'local', width:300, panelWidth:450, idField:'id', textField:'title',onClickRow:function(index, row){ $onClick },
inputEvents:jq.extend({},jq.fn.combogrid.defaults.inputEvents,{ keyup:function(e){ glComboSearch(jq(this).val()); } }),
rowStyler:  function(index,row){ if (row.inactive=='1') { return { class:'row-inactive' }; } },
columns:    [[{field:'id',title:'".jsLang('gl_account')."',width:80},{field:'title',title:'".jsLang('title')."',width:200},{field:'type',title:'".jsLang('type')."',width:160}]]}}";
}

/**
 * Creates the datagrid editor for a numberbox
 * @param string $onChange - JavaScript to run on change event
 * @return string set for the editor structure
 */
function dgEditNumber($onChange='') {
    $prec  = getModuleCache('bizuno','settings','locale','number_precision','2');
    $tsnd  = str_replace("'", "\\'", getModuleCache('bizuno','settings','locale','number_thousand',','));
    $dec   = str_replace("'", "\\'", getModuleCache('bizuno','settings','locale','number_decimal', '.'));
    $prefix= getModuleCache('bizuno','settings','locale','number_prefix', '');
    $pfx   = !empty($prefix) ? str_replace("'", "\\'", $prefix)." " : '';
    $suffix= getModuleCache('bizuno','settings','locale','number_suffix', '');
    $sfx   = !empty($suffix) ? " ".str_replace("'", "\\'", $suffix) : '';
    return "{type:'numberbox',options:{precision:$prec,decimalSeparator:'$dec',groupSeparator:'$tsnd',prefix:'$pfx',suffix:'$sfx',onChange:function(newValue, oldValue){ $onChange } } }";
}

/**
 * Creates the datagrid editor for a tax combogrid
 * @param string $id - datagrid ID
 * @param string $field - datagrid field ID to set
 * @param char $type - c for customers or v for vendors
 * @param string $xClicks - callback JavaScript, if any
 * @return string set for the editor structure
 */
function dgEditTax($id, $field, $type='c', $xClicks='') {
    return "{type:'combogrid',options:{data: bizDefaults.taxRates.$type.rows,width:120,panelWidth:210,idField:'id',textField:'text',
        onClickRow:function (index, row) { jq('#$id').edatagrid('getRows')[curIndex]['$field'] = row.id; $xClicks },
        rowStyler:function(idx, row) { if (row.status==1) { return {class:'journal-waiting'}; } else if (row.status==2) { return {class:'row-inactive'}; }  },
        columns: [[{field:'id',hidden:true},{field:'text',width:120,title:'".jsLang('journal_main_tax_rate_id')."'},{field:'tax_rate',width:70,title:'".jsLang('amount')."',align:'center'}]]
    }}";
}

function dgEditText() {
    return 'text';
}
