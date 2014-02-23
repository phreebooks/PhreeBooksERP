<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
  <title><?php echo TITLE_TOP_FRAME; ?></title>
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/stylesheet.css'; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery-ui.css'; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/easyui.css'; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/icon.css'; ?>" />	
  <link rel="shortcut icon" type="image/ico" href="favicon.ico" />
  <script type="text/javascript">
    var icon_path        = '<?php echo DIR_WS_ICONS; ?>';
    var combo_image_on  = '<?php echo DIR_WS_ICONS . '16x16/phreebooks/pull_down_active.gif';   ?>';
    var combo_image_off = '<?php echo DIR_WS_ICONS . '16x16/phreebooks/pull_down_inactive.gif'; ?>';
    var pbBrowser       = (document.all) ? 'IE' : 'FF';
  </script>
  <script type="text/javascript" src="includes/common.js"></script>
  <script type="text/javascript" src="includes/jquery-1.6.2.min.js"></script>
  <script type="text/javascript" src="includes/jquery-ui-1.8.16.custom.min.js"></script>
  <script type="text/javascript" src="includes/jquery.easyui.min.js"></script>
  <?php require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/config.php'); ?>
  <?php require_once(DIR_FS_WORKING . 'pages/' . $page . '/js_include.php'); ?>
</head>

<body>
<div class="easyui-tabs" id="helptabs">
<div title="<?php echo HEADING_CONTENTS;?>" id="tab_contents">
	<fieldset><?php echo retrieve_toc(); ?></fieldset>
</div>
<div title="<?php echo HEADING_INDEX;?>" id="tab_index">
    <fieldset><?php echo retrieve_index(); ?></fieldset>
</div>
<div title="<?php echo TEXT_SEARCH;?>" id="tab_search">
    <?php echo TEXT_KEYWORD; ?><br />
    <?php echo html_form('search_form', FILENAME_DEFAULT, 'module=phreehelp&amp;page=main&amp;fID=left'); ?>
      <?php echo html_input_field('search_text', $search_text); ?>
      <?php echo html_icon('actions/system-search.png', TEXT_SEARCH, 'small', 'style="cursor:pointer;" onclick="javascript:document.search_form.submit()"') . "\n"; ?>
    </form>
    <br />
    <?php if ($search_text) {
      echo TEXT_SEARCH_RESULTS . '<br />' . chr(10);
      echo '<fieldset>' . chr(10);
	  echo search_results($search_text) . chr(10);
      echo '</fieldset>' . chr(10);
    } ?>
</div>
</div>
</body>
</html>
