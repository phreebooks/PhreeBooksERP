<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<title><?php echo TITLE_TOP_FRAME; ?></title>
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/stylesheet.css'; ?>">
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/jquery-ui.css'; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/'.MY_COLORS.'/easyui.css'; ?>" />
  <link rel="stylesheet" type="text/css" href="<?php echo DIR_WS_THEMES.'css/icon.css'; ?>" />	
  <link rel="shortcut icon" type="image/ico" href="favicon.ico">
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
  <div>
    <?php echo html_icon('actions/go-home.png',           TEXT_HOME,    'large', 'style="cursor:pointer;" onclick="parent.mainFrame.location.href=\'' . DOC_ROOT_URL . '\'"') . "\n"; ?>
    <?php echo html_icon('actions/go-previous.png',       TEXT_BACK,    'large', 'style="cursor:pointer;" onclick="parent.mainFrame.history.back()"') . "\n"; ?>
    <?php echo html_icon('actions/go-next.png',           TEXT_FORWARD, 'large', 'style="cursor:pointer;" onclick="parent.mainFrame.history.forward()"') . "\n"; ?>
    <?php echo html_icon('devices/printer.png',           TEXT_PRINT,   'large', 'style="cursor:pointer;" onclick="parent.mainFrame.focus(); parent.mainFrame.print()"') . "\n"; ?>
    <?php echo html_icon('apps/internet-web-browser.png', TEXT_SUPPORT, 'large', 'style="cursor:pointer;" onclick="parent.mainFrame.location.href=\'http://www.phreesoft.com\'"') . "\n"; ?>
    <?php echo html_icon('actions/system-log-out.png',    TEXT_EXIT,    'large', 'style="cursor:pointer;" onclick="parent.window.close()"') . "\n"; ?>
  </div>
</body>
</html>