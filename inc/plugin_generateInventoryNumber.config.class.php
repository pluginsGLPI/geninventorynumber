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
// Original Author of file: Béchu Philippe
// Purpose of file:
// ----------------------------------------------------------------------

if (!defined('GLPI_ROOT')) {
	die("Sorry. You can't access directly to this file");
}

class plugin_GenerateInventoryNumberConfig extends CommonDBTM {

	function plugin_GenerateInventoryNumberConfig() {
		$this->table = "glpi_plugin_generateinventorynumber_config";
	}

	function showForm($target) {
		global $LANG, $LANGGENINVENTORY, $CFG_GLPI, $DB;

		$this->getFromDB(1);

		echo "<form name='form' method='post' action=\"$target\">";
		echo "<div align='center'>";
		echo "<table class='tab_cadre' cellpadding='5'>";
		echo "<tr><th colspan='2'>" . $LANGGENINVENTORY["setup"][0]."</th></tr>";
		
		echo "<input type='hidden' name='ID' value='1'>";
		
		echo "<tr>";
		echo "<td class='tab_bg_1' align='center'>" . $LANGGENINVENTORY["config"][0] . "</td>";
		echo "<td class='tab_bg_1'>";
		dropdownYesNo("active",$this->fields["active"]);
		echo "</td>";
		echo "</td></tr>";

		echo "<tr><th colspan='2'>" . $LANGGENINVENTORY["config"][1]."</th></tr>";
/*
		echo "<tr><td class='tab_bg_1' align='center'>" . $LANGGENINVENTORY["config"][2] . "</td>";
		echo "<td class='tab_bg_1'>";
		dropdownYesNo("generate_ocs",$this->fields["generate_ocs"]);
		echo "</td>";
		echo "</td></tr>";
*/		
		echo "<tr><td class='tab_bg_1' align='center'>" . $LANGGENINVENTORY["config"][3] . "</td>";
		echo "<td class='tab_bg_1'>";
		dropdownYesNo("generate_internal",$this->fields["generate_internal"]);
		echo "</td>";
		echo "</td></tr>";

		echo "<tr><td class='tab_bg_1' align='center'>" . $LANGGENINVENTORY["config"][4] . "</td>";
		echo "<td class='tab_bg_1'>";
		dropdownYesNo("generate_data_injection",$this->fields["generate_data_injection"]);
		echo "</td>";
		echo "</td></tr>";


		echo "<tr><th colspan='2'>" . $LANGGENINVENTORY["config"][10]."</th></tr>";
		
		$types = array("computer"=>0,"networking"=>1,"printer"=>2,"monitor"=>3,"peripheral"=>16,"phone"=>34);
		foreach($types as $type => $value)
		{
			echo "<td class='tab_bg_1' align='center'>" . $LANG["Menu"][$value] . "</td>";
			echo "<td class='tab_bg_1'>";
			echo "<input type='text' name='template_$type' value=\"".$this->fields["template_$type"]."\">";
			echo "</td>";
			echo "</td></tr>";
		}

		echo "<tr class='tab_bg_1'><td align='center' colspan='2'>";
		echo "<input type='submit' name='update' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
		echo "</td></tr>";

		echo "</table></form>";
		if ($_SESSION["glpiactive_entity"] == 0) {
			echo "<table class='tab_cadre' cellpadding='5'>";
			echo "<tr class='tab_bg_1'><td><a href='plugin_generateInventoryNumber.uninstall.php'>" . $LANGGENINVENTORY["setup"][2] . "</a>";
			echo " <img src='" . $CFG_GLPI["root_doc"] . "/pics/aide.png' alt=\"\" onmouseout=\"setdisplay(getElementById('commentsup'),'none')\" onmouseover=\"setdisplay(getElementById('commentsup'),'block')\">";
			echo "<span class='over_link' id='commentsup'>" . $LANGGENINVENTORY["setup"][2] . "</span>";
			echo "</td></tr>";
		}


		echo "</table></div>";

	}


}
?>