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

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS = array (
	"setup"
);
if (!defined('GLPI_ROOT')) {
	define('GLPI_ROOT', '../../..');
}
include (GLPI_ROOT . "/inc/includes.php");
checkRight("config", "w");

if (!isGenerateInventoryNumberPluginInstalled()) {

	commonHeader($LANGGENINVENTORY["title"][1], $_SERVER['PHP_SELF'], "config", "plugins");

	if ($_SESSION["glpiactive_entity"] == 0) {

		if (!TableExists("glpi_plugin_generateinventorynumber")) {

			echo "<div align='center'>";
			echo "<table class='tab_cadre' cellpadding='5'>";
			echo "<tr><th>" . $LANGGENINVENTORY["setup"][0];
			echo "</th></tr>";
			echo "<tr class='tab_bg_1'><td>";
			echo "<a href='plugin_generateInventoryNumber.install.php'>" . $LANGGENINVENTORY["setup"][1] . "</a></td></tr>";
			echo "</table></div>";
		}
	} else {
		echo "<div align='center'><br><br><img src=\"" . $CFG_GLPI["root_doc"] . "/pics/warning.png\" alt=\"warning\"><br><br>";
		echo "<b>" . $LANGGENINVENTORY["setup"][10] . "</b></div>";
	}
} else {
	$config = new plugin_GenerateInventoryNumberConfig;
	plugin_generateInventoryNumber_updateIndexes(array_merge($_GET,$_POST));
	
	commonHeader($LANGGENINVENTORY["title"][1], $_SERVER["PHP_SELF"], "config", "plugins");
	$config->showForm($_SERVER["PHP_SELF"]);
}

commonFooter();

function plugin_generateInventoryNumber_updateIndexes($params) {
	global $DB, $INVENTORY_TYPES;

	if (isset ($params["update"])) {
		$config = new plugin_GenerateInventoryNumberConfig;
		$config->update($params);

		//Update each type's index
		foreach ($INVENTORY_TYPES as $type => $type_name)
		{
			if (isset($params["next_number_$type_name"]))
				glpi_plugin_generateinventorynumber_updateIndexByType($type, $params["next_number_$type_name"]);
		}
	}

	//glpi_header($_SERVER["PHP_SELF"]);
}
?>