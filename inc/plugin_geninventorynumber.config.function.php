<?php


/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/
   ----------------------------------------------------------------------

   LICENSE

   This file is part of GLPI.

   GLPI is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 2 of the License, or
   (at your option) any later version.

   GLPI is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   You should have received a copy of the GNU General Public License
   along with GLPI; if not, write to the Free Software
   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
   ------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

function plugin_geninventorynumber_dropdownFields($name,$value) {
   global $LANG;
   $fields['otherserial'] = $LANG['common'][20];
   dropdownArrayValues($name,$fields,$value);	
}

function plugin_geninventorynumber_showCoreConfig($target,$ID) {
	global $LANG, $CFG_GLPI, $DB;

	$config = new PluginGenInventoryNumberConfig;
	$config->getFromDB($ID);
   $fields = plugin_geninventorynumber_getFieldInfos($config->fields['field']);
   
	echo "<form name='form_core_config' method='post' action=\"$target\">";
	echo "<div align='center'>";
	echo "<table class='tab_cadre_fixe' cellpadding='5'>";
	echo "<tr><th colspan='5'>" . $LANG["plugin_geninventorynumber"]["config"][9] . "</th></tr>";

	echo "<input type='hidden' name='ID' value='$ID'>";
	echo "<input type='hidden' name='FK_entities' value='0'>";

	echo "<tr><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][10] . "</th><th>" . $LANG["common"][60] . "</th>";
	echo "<th>" . $LANG["plugin_geninventorynumber"]["config"][5] . "</th><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][6] . "</th></tr>";

   $commonitem = new CommonItem;
   
	foreach ($fields as $type => $value) {
      $commonitem->setType($type,true);
		echo "<td class='tab_bg_1' align='center'>" . $commonitem->getType() . "</td>";
		echo "<td class='tab_bg_1'>";
      echo "<input type='hidden' name='IDS[$type][ID]' value='".$value["ID"]."'>";
      echo "<input type='hidden' name='IDS[$type][device_type]' value='$type'>";
      echo "<input type='text' name='IDS[$type][template]' value=\"" . $value["template"] . "\">";
		echo "</td>";
		echo "<td class='tab_bg_1' align='center'>";
		dropdownYesNo("IDS[$type][enabled]", $value["enabled"]);
		echo "</td>";
		echo "<td class='tab_bg_1' align='center'>";
		dropdownYesNo("IDS[$type][use_index]", $value["use_index"]);
		echo "</td>";
		echo "<td class='tab_bg_1'>";
		if ($value["enabled"] && !$value["use_index"])
			$disabled = "";
		else
			$disabled = "disabled";

		echo "<input type='text' name='IDS[$type][index]' value='" . plugin_geninventorynumber_getIndexByTypeName($type) . "' size='12' " . $disabled . ">";
		echo "</td>";
		echo "</tr>";
	}

	echo "<tr class='tab_bg_1'><td align='center' colspan='5'>";
	echo "<input type='submit' name='update_fields' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
	echo "</td></tr>";

	echo "</table></form>";
	//echo "</div>";

}

function plugin_geninventorynumber_showUnicityConfig($target,$ID) {
   global $LANG, $CFG_GLPI, $DB;

   $config = new PluginGenInventoryNumberConfig;
   $config->getFromDB($ID);
   $fields = plugin_geninventorynumber_getFieldInfos($config->fields['field']);
   
   echo "<form name='form_unicity_config' method='post' action=\"$target\">";
   echo "<div align='center'>";
   echo "<table class='tab_cadre_fixe' cellpadding='2'>";
   echo "<tr><th colspan='5'>" . $LANG["plugin_geninventorynumber"]["config"][9] . "</th></tr>";

   echo "<input type='hidden' name='ID' value='$ID'>";
   echo "<input type='hidden' name='FK_entities' value='0'>";

   echo "<tr><th>" . $LANG["plugin_geninventorynumber"]["config"][12] . "</th><th>" . $LANG["plugin_geninventorynumber"]["config"][11] . "</th></tr>";

   $commonitem = new CommonItem;
   
   foreach ($fields as $type => $value) {
      $commonitem->setType($type,true);
      echo "<tr>";
      echo "<td class='tab_bg_1' align='center'>" . $commonitem->getType();
      echo "<input type='hidden' name='IDS[$type][ID]' value='".$value["ID"]."'>";
      echo "<input type='hidden' name='IDS[$type][device_type]' value='$type'>";
      echo "</td>";
      echo "<td class='tab_bg_1' align='center'>";
      dropdownYesNo("IDS[$type][use_unicity]", $value["use_unicity"]);
      echo "</td></tr>";
   }
   echo "<tr class='tab_bg_1'><td align='center' colspan='2'>";
   echo "<input type='submit' name='update_unicity' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
   echo "</td></tr>";

   echo "</table></form>";
   //echo "</div>";
}
?>