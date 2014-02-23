<?php
// +-----------------------------------------------------------------+
// |                   PhreeBooks Open Source ERP                    |
// +-----------------------------------------------------------------+
// | Copyright(c) 2008-2013 PhreeSoft, LLC (www.PhreeSoft.com)       |

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
//  Path: /modules/phreebooks/pages/admin/template_tab_stats.php
//
?>
<div title="<?php echo TEXT_STATISTICS;?>" id="tab_stats">
<?php 
  if (sizeof($admin_classes['phreebooks']->tables) > 0) {
    echo "  <fieldset><!-- db table stats -->\n";
    echo "    <legend>" . TEXT_TABLE_STATS . "</legend>\n";
    echo "    <table class=\"ui-widget\" style=\"border-collapse:collapse;width:100%\">\n";
    echo "      <thead class=\"ui-widget-header\">\n";
    echo "        <tr>\n";
	echo "          <th>" . TEXT_TABLE . "</th>\n";
	echo "          <th>" . TEXT_ENGINE . "</th>\n";
	echo "          <th>" . TEXT_NUM_ROWS . "</th>\n";
	echo "          <th>" . TEXT_COLLATION . "</th>\n";
	echo "          <th>" . TEXT_SIZE . "</th>\n";
	echo "          <th>" . TEXT_NEXT_ID . "</th>\n";
    echo "        </tr>\n";
    echo "      </thead>\n";
    echo "      <tbody class=\"ui-widget-content\">\n";
    foreach ($admin_classes['phreebooks']->tables as $tablename => $tablesql) {
	  $result = $db->Execute("SHOW TABLE STATUS LIKE '" . $tablename ."'");
	  echo "         <tr>\n";
	  echo "          <td>" . $result->fields['Name'] . "</td>\n";
	  echo "          <td align=\"center\">" . $result->fields['Engine'] . "</td>\n";
	  echo "          <td align=\"center\">" . $result->fields['Rows'] . "</td>\n";
	  echo "          <td align=\"center\">" . $result->fields['Collation'] . "</td>\n";
	  echo "          <td align=\"center\">" .($result->fields['Data_length'] + $result->fields['Index_length']) . "</td>\n";
	  echo "          <td align=\"center\">" . $result->fields['Auto_increment'] . "</td>\n";
	  echo "        </tr>\n";
    }
    echo "      </tbody>\n";
	echo "    </table>\n";
	echo "  </fieldset>\n";
  }
?>
</div>
