<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
  <title><?php echo HEADING_TITLE; ?></title>
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

<frameset rows="*" cols="300,*">
  <frameset rows="40,*" cols="*">
    <frame name="topFrame" src="<?php echo $frame_url . '&amp;fID=top'; ?>" scrolling="NO">
    <frame name="leftFrame" src="<?php echo $frame_url . '&amp;fID=left'; ?>">
  </frameset>
  <frame name="mainFrame" src="<?php echo $start_page; ?>">
</frameset>
</html>
