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
	global $LANG, $CFG_GLPI, $DB, $ALLOWED_TYPES;

	$config = new PluginGenInventoryNumberConfig;
	$config->getFromDB($ID);

	echo "<form name='form_core_config' method='post' action=\"$target\">";
	echo "<div align='center'>";
	echo "<table class='tab_cadre_fixe' cellpadding='5'>";
	echo "<tr><th colspan='5'>" . $LANG["plugin_geninventorynumber"]["config"][9] . "</th></tr>";

	echo "<input type='hidden' name='ID' value='1'>";
	echo "<input type='hidden' name='FK_entities' value='0'>";

	echo "<tr><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][10] . "</th><th>" . $LANG["common"][60] . "</th>";
	echo "<th>" . $LANG["plugin_geninventorynumber"]["config"][5] . "</th><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][6] . "</th></tr>";

	foreach ($ALLOWED_TYPES as $type => $value) {
		echo "<td class='tab_bg_1' align='center'>" . $value . "</td>";
		echo "<td class='tab_bg_1'>";
		echo "<input type='text' name='template_$type' value=\"" . $config->fields["template_$type"] . "\" " . (!$config->fields[$type . "_gen_enabled"] ? "disabled" : "") . ">";
		echo "</td>";
		echo "<td class='tab_bg_1' align='center'>";
		dropdownYesNo($type . "_gen_enabled", $config->fields[$type . "_gen_enabled"]);
		echo "</td>";
		echo "<td class='tab_bg_1' align='center'>";
		dropdownYesNo($type . "_global_index", $config->fields[$type . "_global_index"]);
		echo "</td>";
		echo "<td class='tab_bg_1'>";
		if ($config->fields[$type . "_gen_enabled"] && !$config->fields[$type . "_global_index"])
			$disabled = "";
		else
			$disabled = "disabled";

		echo "<input type='text' name='next_number_$type' value='" . plugin_geninventorynumber_getIndexByTypeName($type) . "' size='12' " . $disabled . ">";
		echo "</td>";
		echo "</tr>";
	}

	echo "<tr class='tab_bg_1'><td align='center' colspan='5'>";
	echo "<input type='submit' name='update' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
	echo "</td></tr>";

	echo "</table></form>";
	//echo "</div>";

}
?>