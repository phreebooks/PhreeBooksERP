<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2015 PhreeSoft      (www.PhreeSoft.com)       |
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
//  Path: /includes/template_index.php
//
?>

  <!-- end loading includes -->
  <script type="text/javascript">
  var module              = '<?php echo $module; ?>';
  var pbBrowser           = (document.all) ? 'IE' : 'FF';
  var text_search         = '<?php echo TEXT_SEARCH; ?>';
  var date_format         = '<?php echo DATE_FORMAT; ?>';
  var date_delimiter      = '<?php echo DATE_DELIMITER; ?>';
  var inactive_text_color = '#cccccc';
  var form_submitted      = false;
  // Variables for script generated combo boxes
  var icon_path           = '<?php echo DIR_WS_ICONS; ?>';
  var combo_image_on      = '<?php echo DIR_WS_ICONS . '16x16/phreebooks/pull_down_active.gif'; ?>';
  var combo_image_off     = '<?php echo DIR_WS_ICONS . '16x16/phreebooks/pull_down_inactive.gif'; ?>';
<?php if (is_object($admin->currencies)) { // will not be defined unless logged in and db defined ?>
  var decimal_places      = <?php  echo $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_places']; ?>;
  var decimal_precise     = <?php  echo $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_precise']; ?>;
  var decimal_point       = "<?php echo $admin->currencies->currencies[DEFAULT_CURRENCY]['decimal_point']; ?>"; // leave " for ' separator
  var thousands_point     = "<?php echo $admin->currencies->currencies[DEFAULT_CURRENCY]['thousands_point']; ?>";
  var formatted_zero      = "<?php echo $admin->currencies->format(0); ?>";
<?php } ?>
  </script>
<?php
//require_once(DIR_FS_ADMIN . DIR_WS_THEMES . '/config.php');

?>
 </head>
 <body>
  <div id="please_wait"><p><?php echo html_icon('phreebooks/please_wait.gif', TEXT_PLEASE_WAIT, 'large'); ?></p></div>
  <!-- start Menu -->
  <?php $basis->observer()->print_menu($basis);?>
  <!-- end Menu -->
  <!-- Template -->
  <?php require($basis->observer()->get_template());?>
  </div>
  <!-- start Footer -->
  <?php $basis->observer()->print_footer($basis); ?>
  <!-- end Footer -->
</body>
</html>

