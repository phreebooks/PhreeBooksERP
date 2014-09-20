<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2014 PhreeSoft, LLC (www.PhreeSoft.com)       |
// +-----------------------------------------------------------------+
// | This program is free software: you can redistribute it and/or   |
// | modify it under the terms of the GNU General Public License as  |
// | published by the Free Software Foundation, either version 3 of  |
// | the License, or any later version.                              |
// |                                                                 |
// | This program is distributed in the hope that it will be useful, |
// | but WITHOUT ANY WARRANTY; without even the implied warranty of  |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the   |
// | GNU General Public License for more details.                    |
// +-----------------------------------------------------------------+
//  Path: /admin/phreebooks.php
//
// This file is a modified version of modules.php. Changes could have been made to modules.php to add the 
// phreebooks module but this way is less disruptive when ZenCart is upgraded.

  require('includes/application_top.php');

  $set = (isset($_GET['set']) ? $_GET['set'] : (isset($_POST['set']) ? $_POST['set'] : 'phreebooks'));

  $is_ssl_protected = (substr(HTTP_SERVER, 0, 5) == 'https') ? TRUE : FALSE;

  if (zen_not_null($set)) {
    switch ($set) {
      case 'phreebooks':
      default:
        $module_type      = 'phreebooks';
        $module_directory = DIR_FS_CATALOG_MODULES . 'phreebooks/';
        $module_key       = 'MODULE_PHREEBOOKS_INSTALLED';
        define('HEADING_TITLE', HEADING_TITLE_MODULES_PHREEBOOKS);
        break;
    }
  }

  $action = (isset($_GET['action']) ? $_GET['action'] : '');

  if (zen_not_null($action)) {
    $admname = '{' . preg_replace('/[^\d\w]/', '*', zen_get_admin_name()) . '[' . (int)$_SESSION['admin_id'] . ']}';
    switch ($action) {
      case 'save':
        if (!$is_ssl_protected && in_array($class, array('paypaldp', 'linkpoint_api', 'authorizenet_aim', 'authorizenet_echeck'))) break;
        while (list($key, $value) = each($_POST['configuration'])) {
          if (is_array( $value ) ) {
            $value = implode( ", ", $value);
            $value = preg_replace ("/, --none--/", "", $value);
          }
          $db->Execute("update " . TABLE_CONFIGURATION . "
                        set configuration_value = '" . zen_db_input($value) . "'
                        where configuration_key = '" . zen_db_input($key) . "'");
        }
        $configuration_query = 'select configuration_key as cfgkey, configuration_value as cfgvalue
                                from ' . TABLE_CONFIGURATION;
        $configuration = $db->Execute($configuration_query);
        $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_SETTINGS_CHANGED, preg_replace('/[^\d\w]/', '*', $_GET['module']), $admname);
        zen_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED, $msg, STORE_NAME, EMAIL_FROM, array('EMAIL_MESSAGE_HTML'=>$msg), 'admin_settings_changed');
        zen_redirect(zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'NONSSL'));
        break;
      case 'install':
        $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
        $class = basename($_POST['module']);
        if (!$is_ssl_protected && in_array($class, array('paypaldp', 'linkpoint_api', 'authorizenet_aim', 'authorizenet_echeck'))) break;
        if (file_exists($module_directory . $class . $file_extension)) {
          $configuration_query = 'select configuration_key as cfgkey, configuration_value as cfgvalue
                                  from ' . TABLE_CONFIGURATION;
          $configuration = $db->Execute($configuration_query);
          include($module_directory . $class . $file_extension);
          $module = new $class;
          $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MODULE_INSTALLED, preg_replace('/[^\d\w]/', '*', $_POST['module']), $admname);
          zen_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED, $msg, STORE_NAME, EMAIL_FROM, array('EMAIL_MESSAGE_HTML'=>$msg), 'admin_settings_changed');
          $result = $module->install();
        }
        if ($result != 'failed') {
          zen_redirect(zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . '&module=' . $class . '&action=edit', 'NONSSL'));
        }
       break;
      case 'removeconfirm':
        $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
        $class = basename($_POST['module']);
        if (file_exists($module_directory . $class . $file_extension)) {
          $configuration_query = 'select configuration_key as cfgkey, configuration_value as cfgvalue
                                  from ' . TABLE_CONFIGURATION;
          $configuration = $db->Execute($configuration_query);
          include($module_directory . $class . $file_extension);
          $module = new $class;
          $msg = sprintf(TEXT_EMAIL_MESSAGE_ADMIN_MODULE_REMOVED, preg_replace('/[^\d\w]/', '*', $_POST['module']), $admname);
          zen_mail(STORE_OWNER_EMAIL_ADDRESS, STORE_OWNER_EMAIL_ADDRESS, TEXT_EMAIL_SUBJECT_ADMIN_SETTINGS_CHANGED, $msg, STORE_NAME, EMAIL_FROM, array('EMAIL_MESSAGE_HTML'=>$msg), 'admin_settings_changed');
          $result = $module->remove();
        }
        zen_redirect(zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . '&module=' . $class, 'NONSSL'));
       break;
    }
  }
?>
<!doctype html public "-//W3C//DTD HTML 4.01 Transitional//EN">
<html <?php echo HTML_PARAMS; ?>>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=<?php echo CHARSET; ?>">
<title><?php echo TITLE; ?></title>
<link rel="stylesheet" type="text/css" href="includes/stylesheet.css">
<link rel="stylesheet" type="text/css" href="includes/cssjsmenuhover.css" media="all" id="hoverJS">
<script language="javascript" src="includes/menu.js"></script>
<script language="javascript" src="includes/general.js"></script>
<script type="text/javascript">
  <!--
  function init()
  {
    cssjsmenu('navbar');
    if (document.getElementById)
    {
      var kill = document.getElementById('hoverJS');
      kill.disabled = true;
    }
  }
  // -->
</script>
</head>
<body onLoad="init()">
<!-- header //-->
<?php require(DIR_WS_INCLUDES . 'header.php'); ?>
<!-- header_eof //-->

<!-- body //-->
<table border="0" width="100%" cellspacing="2" cellpadding="2">
  <tr>
<!-- body_text //-->
    <td width="100%" valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
      <tr>
        <td width="100%"><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td class="pageHeading"><?php echo HEADING_TITLE; ?></td>
            <td class="pageHeading" align="right"><?php echo zen_draw_separator('pixel_trans.gif', HEADING_IMAGE_WIDTH, HEADING_IMAGE_HEIGHT); ?></td>
          </tr>
        </table></td>
      </tr>
      <tr>
        <td><table border="0" width="100%" cellspacing="0" cellpadding="0">
          <tr>
            <td valign="top"><table border="0" width="100%" cellspacing="0" cellpadding="2">
              <tr class="dataTableHeadingRow">
                <td class="dataTableHeadingContent"><?php echo TABLE_HEADING_MODULES; ?></td>
                <td class="dataTableHeadingContent">&nbsp;</td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_SORT_ORDER; ?></td>
                <td class="dataTableHeadingContent" align="right"><?php echo TABLE_HEADING_ACTION; ?>&nbsp;</td>
              </tr>
<?php
  $file_extension = substr($PHP_SELF, strrpos($PHP_SELF, '.'));
  $directory_array = array();
  if ($dir = @dir($module_directory)) {
    while ($file = $dir->read()) {
      if (!is_dir($module_directory . $file)) {
        if (substr($file, strrpos($file, '.')) == $file_extension) {
          $directory_array[] = $file;
        }
      }
    }
    sort($directory_array);
    $dir->close();
  }

  $installed_modules = array();
  for ($i=0, $n=sizeof($directory_array); $i<$n; $i++) {
    $file = $directory_array[$i];
    if (file_exists(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file)) {
      include(DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file);
      include($module_directory . $file);
      $class = substr($file, 0, strrpos($file, '.'));
      if (zen_class_exists($class)) {
        $module = new $class;
        if ($module->check() > 0) {
          if ($module->sort_order > 0) {
            if ($installed_modules[$module->sort_order] != '') {
              $zc_valid = false;
            }
            $installed_modules[$module->sort_order] = $file;
          } else {
            $installed_modules[] = $file;
          }
        }
        if ((!isset($_GET['module']) || (isset($_GET['module']) && ($_GET['module'] == $class))) && !isset($mInfo)) {
          $module_info = array('code' => $module->code,
                               'title' => $module->title,
                               'description' => $module->description,
                               'status' => $module->check());
          $module_keys = $module->keys();
          $keys_extra = array();
          for ($j=0, $k=sizeof($module_keys); $j<$k; $j++) {
            $key_value = $db->Execute("select configuration_title, configuration_value, configuration_key,
                                          configuration_description, use_function, set_function
                                          from " . TABLE_CONFIGURATION . "
                                          where configuration_key = '" . zen_db_input($module_keys[$j]) . "'");

            $keys_extra[$module_keys[$j]]['title'] = $key_value->fields['configuration_title'];
            $keys_extra[$module_keys[$j]]['value'] = $key_value->fields['configuration_value'];
            $keys_extra[$module_keys[$j]]['description'] = $key_value->fields['configuration_description'];
            $keys_extra[$module_keys[$j]]['use_function'] = $key_value->fields['use_function'];
            $keys_extra[$module_keys[$j]]['set_function'] = $key_value->fields['set_function'];
          }
          $module_info['keys'] = $keys_extra;
          $mInfo = new objectInfo($module_info);
        }
        if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) {
          if ($module->check() > 0) {
            echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . '&module=' . $class . '&action=edit', 'NONSSL') . '\'">' . "\n";
          } else {
            echo '              <tr id="defaultSelected" class="dataTableRowSelected" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)">' . "\n";
          }
        } else {
          echo '              <tr class="dataTableRow" onmouseover="rowOverEffect(this)" onmouseout="rowOutEffect(this)" onclick="document.location.href=\'' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . '&module=' . $class, 'NONSSL') . '\'">' . "\n";
        }
//print_r($module) . '<br><BR>';
//echo (!empty($module->enabled) ? 'ENABLED' : 'NOT ENABLED') . ' vs ' . (is_numeric($module->sort_order) ? 'ON' : 'OFF') . '<BR><BR>' ;
?>
                <td class="dataTableContent"><?php echo $module->title; ?></td>
                <td class="dataTableContent"><?php echo (strstr($module->code, 'paypal') ? 'PayPal' : $module->code); ?></td>
                <td class="dataTableContent" align="right">
                  <?php if (is_numeric($module->sort_order)) echo $module->sort_order; ?>
                  <?php
                    // show current status
                    if ($set == 'payment' || $set == 'shipping') {
                      echo '&nbsp;' . ((!empty($module->enabled) && is_numeric($module->sort_order)) ? zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') : ((empty($module->enabled) && is_numeric($module->sort_order)) ? zen_image(DIR_WS_IMAGES . 'icon_status_yellow.gif') : zen_image(DIR_WS_IMAGES . 'icon_status_red.gif')));
                    } else {
                      echo '&nbsp;' . (is_numeric($module->sort_order) ? zen_image(DIR_WS_IMAGES . 'icon_status_green.gif') : zen_image(DIR_WS_IMAGES . 'icon_status_red.gif'));
                    }
                  ?>
                </td>
                <td class="dataTableContent" align="right"><?php if (isset($mInfo) && is_object($mInfo) && ($class == $mInfo->code) ) { echo zen_image(DIR_WS_IMAGES . 'icon_arrow_right.gif'); } else { echo '<a href="' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . '&module=' . $class, 'NONSSL') . '">' . zen_image(DIR_WS_IMAGES . 'icon_info.gif', IMAGE_ICON_INFO) . '</a>'; } ?>&nbsp;</td>
              </tr>
<?php
      }
    } else {
      echo ERROR_MODULE_FILE_NOT_FOUND . DIR_FS_CATALOG_LANGUAGES . $_SESSION['language'] . '/modules/' . $module_type . '/' . $file . '<br />';
    }
  }
  ksort($installed_modules);
  $check = $db->Execute("select configuration_value
                         from " . TABLE_CONFIGURATION . "
                         where configuration_key = '" . zen_db_input($module_key) . "'");

  if ($check->RecordCount() > 0) {
    if ($check->fields['configuration_value'] != implode(';', $installed_modules)) {
      $db->Execute("update " . TABLE_CONFIGURATION . "
                    set configuration_value = '" . zen_db_input(implode(';', $installed_modules)) . "', last_modified = now()
                    where configuration_key = '" . zen_db_input($module_key) . "'");
    }
  } else {
    $db->Execute("insert into " . TABLE_CONFIGURATION . "
                (configuration_title, configuration_key, configuration_value,
                 configuration_description, configuration_group_id, sort_order, date_added)
                 values ('Installed Modules', '" . zen_db_input($module_key) . "', '" . zen_db_input(implode(';', $installed_modules)) . "',
                         'This is automatically updated. No need to edit.', '6', '0', now())");
  }
  if (isset($zc_valid) && $zc_valid == false) {
    echo '<span class="alert">' . WARNING_MODULES_SORT_ORDER . '</span>';
  }
?>
              <tr>
                <td colspan="3" class="smallText"><?php echo TEXT_MODULE_DIRECTORY . ' ' . $module_directory; ?></td>
              </tr>
            </table></td>
<?php
  $heading = array();
  $contents = array();
  switch ($action) {
  	case 'remove':
      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');

      $contents = array('form' => zen_draw_form('module_delete', FILENAME_PHREEBOOKS, '&action=removeconfirm'));
      $contents[] = array('text' => '<input type="hidden" name="set" value="' . (isset($_GET['set']) ? $_GET['set'] : "") . '" />');
      $contents[] = array('text' => '<input type="hidden" name="module" value="' . (isset($_GET['module']) ? $_GET['module'] : "") . '"/>');
      $contents[] = array('text' => TEXT_DELETE_INTRO);

      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_remove.gif', IMAGE_DELETE, 'name="removeButton"') . ' <a href="' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL, 'name="cancelButton"') . '</a>');
      break;
    case 'edit':
      if (!$is_ssl_protected && in_array($_GET['module'], array('paypaldp', 'linkpoint_api', 'authorizenet_aim', 'authorizenet_echeck'))) break;
      $keys = '';
      reset($mInfo->keys);
      while (list($key, $value) = each($mInfo->keys)) {
        $keys .= '<b>' . $value['title'] . '</b><br>' . $value['description'] . '<br>';
        if ($value['set_function']) {
          eval('$keys .= ' . $value['set_function'] . "'" . $value['value'] . "', '" . $key . "');");
        } else {
          $keys .= zen_draw_input_field('configuration[' . $key . ']', htmlspecialchars($value['value'], ENT_COMPAT, CHARSET, TRUE));
        }
        $keys .= '<br><br>';
      }
      $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');
      $contents = array('form' => zen_draw_form('modules', FILENAME_PHREEBOOKS, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : '') . '&action=save', 'post', '', true));
      if (ADMIN_CONFIGURATION_KEY_ON == 1) {
        $contents[] = array('text' => '<strong>Key: ' . $mInfo->code . '</strong><br />');
      }
      $contents[] = array('text' => $keys);
      $contents[] = array('align' => 'center', 'text' => '<br>' . zen_image_submit('button_update.gif', IMAGE_UPDATE, 'name="saveButton"') . ' <a href="' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . ($_GET['module'] != '' ? '&module=' . $_GET['module'] : ''), 'NONSSL') . '">' . zen_image_button('button_cancel.gif', IMAGE_CANCEL, 'name="cancelButton"') . '</a>');
      break;
    default:
      $heading[] = array('text' => '<b>' . $mInfo->title . '</b>');

      if ($mInfo->status == '1') {
        $keys = '';
        reset($mInfo->keys);
        while (list(, $value) = each($mInfo->keys)) {
          $keys .= '<b>' . $value['title'] . '</b><br>';
          if ($value['use_function']) {
            $use_function = $value['use_function'];
            if (preg_match('/->/', $use_function)) {
              $class_method = explode('->', $use_function);
              if (!is_object(${$class_method[0]})) {
                include(DIR_WS_CLASSES . $class_method[0] . '.php');
                ${$class_method[0]} = new $class_method[0]();
              }
              $keys .= zen_call_function($class_method[1], $value['value'], ${$class_method[0]});
            } else {
              $keys .= zen_call_function($use_function, $value['value']);
            }
          } else {
            $keys .= $value['value'];
          }
          $keys .= '<br><br>';
        }

        if (ADMIN_CONFIGURATION_KEY_ON == 1) {
          $contents[] = array('text' => '<strong>Key: ' . $mInfo->code . '</strong><br />');
        }
        $keys = substr($keys, 0, strrpos($keys, '<br><br>'));
        if (!(!$is_ssl_protected && in_array($mInfo->code, array('paypaldp', 'linkpoint_api', 'authorizenet_aim', 'authorizenet_echeck')))) {
          $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . (isset($_GET['module']) ? '&module=' . $_GET['module'] : '') . '&action=edit', 'NONSSL') . '">' . zen_image_button('button_edit.gif', IMAGE_EDIT, 'name="editButton"') . '</a>');
        } else {
          $contents[] = array('align' => 'center', 'text' => TEXT_WARNING_SSL_EDIT);
        }
        $contents[] = array('align' => 'center', 'text' => '<a href="' . zen_href_link(FILENAME_PHREEBOOKS, 'set=' . $set . '&module=' . $mInfo->code . '&action=remove', 'NONSSL') . '">' . zen_image_button('button_module_remove.gif', IMAGE_MODULE_REMOVE, 'name="removeButton"') . '</a>');
        $contents[] = array('text' => '<br>' . $mInfo->description);
        $contents[] = array('text' => '<br>' . $keys);
      } else {
        if (!(!$is_ssl_protected && in_array($mInfo->code, array('paypaldp', 'linkpoint_api', 'authorizenet_aim', 'authorizenet_echeck')))) {
          $contents[] = array('align' => 'center', 'text' => zen_draw_form('install_module', FILENAME_PHREEBOOKS, 'set=' . $set . '&action=install') . '<input type="hidden" name="module" value="' . $mInfo->code . '" />' . zen_image_submit('button_module_install.gif', IMAGE_MODULE_INSTALL, 'name="installButton"') . '</form>');
        } else {
          $contents[] = array('align' => 'center', 'text' => TEXT_WARNING_SSL_INSTALL);
        }
        $contents[] = array('text' => '<br>' . $mInfo->description);
      }
      break;
  }
  if ( (zen_not_null($heading)) && (zen_not_null($contents)) ) {
    echo '            <td width="25%" valign="top">' . "\n";
    $box = new box;
    echo $box->infoBox($heading, $contents);
    echo '            </td>' . "\n";
  }
?>
          </tr>
        </table></td>
      </tr>
    </table></td>
<!-- body_text_eof //-->
  </tr>
</table>
<!-- body_eof //-->
<!-- footer //-->
<?php require(DIR_WS_INCLUDES . 'footer.php'); ?>
<!-- footer_eof //-->
<br>
</body>
</html>
<?php require(DIR_WS_INCLUDES . 'application_bottom.php'); ?>