<?php
/*
 * Builds the div to display the attachment structure
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
 * @copyright  2008-2018, PhreeSoft
 * @license    http://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * @version    2.x Last Update: 2018-03-31
 * @filesource /lib/view/module/bizuno/divAttach.php
 */

namespace bizuno;

$defaults = [
    'delPath' => 'bizuno/main/fileDelete',
    'getPath' => 'bizuno/main/fileDownload',
    'dgName'  => 'dgAttachment',
];
$attr = isset($settings['attr']) ? array_replace($defaults, $settings['attr']) : $defaults;

if (isset($viewData['attachPath'])) { // load the file list
	$max_upload  = (int)(ini_get('upload_max_filesize'));
	$max_post    = (int)(ini_get('post_max_size'));
	$memory_limit= (int)(ini_get('memory_limit'));
	$upload_mb   = min($max_upload, $max_post, $memory_limit);
	$path        = $viewData['attachPath'].$viewData['attachPrefix'];
	$io          = new \bizuno\io();
    $rows        = $io->fileReadGlob($path);
	$viewData['datagrid'][$attr['dgName']] = [
        'id'   => $attr['dgName'],
		'title'=> lang('attachments').' '.sprintf(lang('max_upload'), $upload_mb),
		'attr' => [
            'toolbar'   => "#{$attr['dgName']}Toolbar",
			'width'     => 600,
			'pagination'=> false,
			'idField'   => 'title'],
		'events' => ['data'   => json_encode(['total'=>sizeof($rows),'rows'=>$rows])],
		'source' => ['actions'=>['file_attach'=>['order'=>10,'html'=>['attr'=>['type'=>'file','name'=>'file_attach']]]]],
		'columns'=> [
            'action' => ['order'=>1,'label'=>lang('action'),'attr'=>['width'=>100],
				'events' => ['formatter'=>"function(value,row,index) { return {$attr['dgName']}Formatter(value,row,index); }"],
				'actions'=> [
                    'download'=> ['order'=>30,'icon'=>'download','size'=>'small',
						'events'=> ['onClick'=>"jq('#attachIFrame').attr('src','".BIZUNO_AJAX."&p={$attr['getPath']}&pathID=$path&fileID=idTBD');"]],
					'trash'   => ['order'=>70,'icon'=>'trash',   'size'=>'small',
						'events'=> ['onClick'=>"if (confirm('".jsLang('msg_confirm_delete')."')) jsonAction('{$attr['delPath']}','{$attr['dgName']}','{$path}idTBD');"]]]],
			'title'=> ['order'=>10,'label'=>lang('filename'),'attr'=>['width'=>300,'resizable'=>true]],
			'size' => ['order'=>20,'label'=>lang('size'),    'attr'=>['width'=>100,'resizable'=>true,'align'=>'center']],
			'date' => ['order'=>30,'label'=>lang('date'),    'attr'=>['width'=>100,'resizable'=>true,'align'=>'center']]]];
	htmlDatagrid($output, $viewData, $attr['dgName']);
}
