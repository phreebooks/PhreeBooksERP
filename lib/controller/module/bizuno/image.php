<?php
/*
 * Image manager popup
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
 * @version    3.x Last Update: 2018-10-30
 * @filesource /lib/controller/module/bizuno/image.php
 */

namespace bizuno;

class bizunoImage
{
    function __construct()
    {
    }

    /**
     * This method generates a popup window for managing images in the images/root folder
     * @param $layout - structure coming in
     */
    public function manager(&$layout=[])
    {
        global $io;
        if (!validateSecurity('bizuno', 'profile', 1)) { return; }
        $search = clean('imgSearch', 'text', 'get');
        $target = clean('imgTarget', 'text', 'get');
        $action = clean('imgAction', ['format'=>'text',    'default'=>''], 'get');
        $path   = clean('imgMgrPath',['format'=>'path_rel','default'=>'images/'], 'get');
        if ($action) {
            switch ($action) {
                case 'parent':  $path   = dirname($path); break;
                case 'refresh': $search = '';             break;
                case 'upload':  return $io->uploadSave('imgFile', "images/$path/");
            }
            if (substr($action, 0, 6) == 'newdir') {
                $parts = explode(":", $action);
                if (isset($parts[1]) && $parts[1]) { // folder name
                    mkdir($path ? BIZUNO_DATA."images/$path/{$parts[1]}" : BIZUNO_DATA."images/{$parts[1]}", 0755);
                    $action = 'refresh';
                } else { return msgAdd("Folder name is required!"); }
            }
        }
        $frmImgFields = html5('imgTarget', ['attr'=>['type'=>'hidden','value'=>$target]]);
        $frmImgFields.= html5('imgAction', ['attr'=>['type'=>'hidden','value'=>'']]);
        $frmImgFields.= html5('imgMgrPath',['attr'=>['type'=>'hidden','value'=>$path]]);
        $data = ['type'=>'popup','title'=>jsLang('image_manager').": $path/",
            'attr'    => ['id'=>'winImgMgr','width'=>860,'height'=>600],
            'divs'    => [
                'frmStart'  => ['order'=>10,'type'=>'html',   'html'=>html5('frmImgMgr',['attr'=>['type'=>'form']])],
                'toolbar'   => ['order'=>20,'type'=>'toolbar','key' =>'tbImgMgr'],
                'frmEnd'    => ['order'=>30,'type'=>'html',   'html'=>'</form>'],
                'frmImgFlds'=> ['order'=>40,'type'=>'html',   'html'=>$frmImgFields],
                'images'    => ['order'=>50,'type'=>'html',   'html'=>$this->managerRows($path, $search, $target)]],
            'toolbars'=> ['tbImgMgr'=>['icons'=>[
                'imgClose'  => ['order'=> 10,'icon'=>'close',  'label'=>lang('close'),  'events'=>['onClick'=>"bizWindowClose('winImgMgr');"]],
                'imgParent' => ['order'=> 20,'icon'=>'up',     'label'=>lang('up'),     'events'=>['onClick'=>"imgAction('parent');"]],
                'imgRefresh'=> ['order'=> 30,'icon'=>'refresh','label'=>lang('refresh'),'events'=>['onClick'=>"imgAction('refresh');"]],
                'imgNewDir' => ['order'=> 40,'icon'=>'dirNew', 'label'=>lang('add_folder'),
                    'events'=> ['onClick'=>"var title=prompt('".lang('msg_new_folder_name')."'); if (title!=null) imgAction('newdir:'+title);"]],
                'imgSearch' => ['order'=> 50,'type'=>'field',  'label'=>lang('search'), 'attr'  =>['value'  =>$search]],
                'imgSrchGo' => ['order'=> 60,'icon'=>'search', 'label'=>lang('go'),     'events'=>['onClick'=>"imgAction('search');"]],
                'imgFile'   => ['order'=> 70,'type'=>'field',  'attr'=>['type'=>'file']],
                'imgUpload' => ['order'=> 80,'icon'=>'import', 'label'=>lang('upload'), 'events'=>['onClick'=>"imgAction('upload');"]]]]]];
        if (in_array($action, ['parent','refresh','search'])) { $data['type'] = 'divHTML'; } // just the window contents
        $layout = array_replace_recursive($layout, $data);
    }

    /**
     * lists the rows to populate the window per users search criteria
     * @param string $path - relative path from images root, must not contain trailing slash (/), empty string for root folder
     * @param string $srch - search string, if any
     * @param string $target - folder to put uploaded images
     * @return string - HTML to render window 
     */
    private function managerRows($path='', $srch, $target)
    {
        msgDebug("\nFinding rows, working with path: $path");
        $output  = '';
        $search  = strtolower($srch);
        if (!is_dir(BIZUNO_DATA."images/$path")) { // if the folder is not there, make it
            mkdir(BIZUNO_DATA."images/$path", 0755, true);
        }
        $theList = scandir(BIZUNO_DATA."images/$path");
        msgDebug("\nWorking path is now: $path");
        foreach ($theList as $fn) {
            if ($fn=='.' || $fn=='..') { continue; }
            if ($search && strpos(strtolower($fn), $search) === false) { continue; }
            $newPath = clean("$path/$fn", 'path_rel'); // remove double slashes, if present
            $isDir   = is_dir(BIZUNO_DATA."images/$newPath");
            $src     = $isDir ? BIZUNO_URL."view/icons/default/32x32/folder.png" : BIZUNO_URL_FS."&src=0/images/$newPath";
            $output .= '<div style="float:left;width:150px;height:150px;border:2px solid #a1a1a1;margin:5px"><div style="float:right">';
            $output .= html5('', ['icon'=>'trash','size'=>'small','label'=>lang('trash'),
                'events'=>  ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('bizuno/image/delete&path=$path&fn=$fn&target=$target&search=$search');"]]);
            $output .= '</div><div>'.html5($fn, ['events'=>['onClick'=>"imgClickImg('$fn".($isDir?"/":'')."');"],'attr'=>['type'=>'img','height'=>125,'width'=>125,'src'=>$src]])."</div>";
            $output .= '<div>'.$fn.'</div></div>';
        }
        $title = jsLang('image_manager').': '.$path. '/';
        $js = "
function imgAction(action) { jq('#imgAction').val(action); imgRefresh(); }
function imgClickImg(strImage) {
    var lastChar = strImage.substr(strImage.length - 1);
    if (lastChar == '/') {
        jq('#imgMgrPath').val(jq('#imgMgrPath').val()+'/'+strImage);
        jq('#imgAction').val('refresh');
        imgRefresh();
    } else if (jq('#imgTarget').val()) {
        var target = jq('#imgTarget').val();
        var path   = jq('#imgMgrPath').val();
        var fullPath= path ? path+'/'+strImage : strImage;
        jq('#imgTarget').val(fullPath);
        jq('#'+target).val(fullPath);
        jq('#img_'+target).attr('src', bizunoAjaxFS+'&src=0/images/'+fullPath);
        bizWindowClose('winImgMgr');
    }
}
function imgRefresh() {
    var target = jq('#imgTarget').val();
    var path   = jq('#imgMgrPath').val();
    var search = jq('#imgSearch').val();
    var action = jq('#imgAction').val();
    var shref  = '".BIZUNO_AJAX."&p=bizuno/image/manager&imgTarget='+target+'&imgMgrPath='+path+'&imgSearch='+search+'&imgAction=';
    if (action == 'upload') {
        jq('#frmImgMgr').submit(function (e) {
            jq.ajax({
                url:        shref+'upload',
                type:       'post',
                data:       new FormData(this),
                mimeType:   'multipart/form-data',
                contentType:false,
                cache:      false,
                processData:false,
                success:    function (data) { processJson(data); jq('#winImgMgr').window('refresh',shref+'refresh'); }
            });
            e.preventDefault();
        });
        jq('#frmImgMgr').submit();
    } else {
        jq('#winImgMgr').window('refresh', shref+action);
    }
}
jq('#winImgMgr').window({'title':'".addslashes($title)."'});";
        $output .= htmlJS($js);
        return $output;
    }

    /**
     * Deletes an image from a folder
     * @param array $layout - structure coming in
     * @return modified $layout
     */
    public function delete(&$layout=[])
    {
        if (!validateSecurity('bizuno', 'profile', 4)) { return; }
        $target  = clean('target','text', 'get');
        $search  = clean('search','text', 'get');
        $path    = clean('path',  'path_rel','get');
        $fn      = clean('fn',    'text', 'get');
        $href    = BIZUNO_AJAX."&p=bizuno/image/manager&imgTarget=$target&imgMgrPath=$path&imgSearch=$search&imgAction=refresh";
        $fullPath= BIZUNO_DATA . clean("images/$path/$fn", 'path_rel'); // remove double slashes, if present
        msgDebug("\nLooking for file to delete file or folder at full path: $fullPath");
        if (file_exists($fullPath)) {
            if (is_dir($fullPath)) {
                $files = scandir($fullPath);
                if (sizeof($files) > 2) { return msgAdd("Folder must be empty before it can be deleted! sizeof = ".sizeof($files)); }
                rmdir($fullPath);
            } else {
                unlink($fullPath);
            }
        }
        $layout = array_replace_recursive($layout, ['content'=>['action'=>'eval','actionData'=>"jq('#winImgMgr').window('refresh','$href');"]]);
    }
    
    public function view(&$layout=[])
    {
        $bID   = clean('rID', 'integer', 'get');
        $img   = clean('data', 'path', 'get');
        $html  = html5('', ['styles'=>['max-width'=>'100%;','max-height'=>'100%;'],'events'=>['onClick'=>"jq('#winImage').window('close');"],
            'attr'=>['type'=>'img','src'=>BIZUNO_URL_FS."src=$bID/images/$img"]]); 
        $data  = ['type'=>'popup','title'=>lang('terms'),'attr'=>['id'=>'winImage'], // ,'width'=>600,'height'=>600
            'divs'=>['winImage'=>['order'=>50,'type'=>'html','html'=>$html]]];
        $layout= array_replace_recursive($layout, $data);
    }
}
