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

class PluginGenInventoryNumberConfig extends CommonDBTM {

	function PluginGenInventoryNumberConfig() {
		$this->table = "glpi_plugin_geninventorynumber_config";
      $this->type = PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE;
	}

   function defineTabs($ID, $withtemplate) {
      global $LANG;
      $ong[1] = $LANG["plugin_geninventorynumber"]["config"][7];
      $ong[2] = $LANG["plugin_geninventorynumber"]["config"][11];
      return $ong;
   }

	function showForm($target,$ID) {
		global $LANG, $CFG_GLPI, $DB;

		$this->getFromDB($ID);
      $this->showTabs($ID,'',$_SESSION['glpi_tab']);
          
		echo "<form name='form' method='post' action=\"$target\">";
      echo "<div class='center' id='tabsbody'>";
      echo "<table class='tab_cadre_fixe'>";
		$this->showFormHeader($ID,'',2);

		echo "<input type='hidden' name='ID' value='1'>";
		echo "<input type='hidden' name='FK_entities' value='0'>";

      echo "<tr>";
      echo "<td class='tab_bg_1'>";
      echo "<table>";
      echo "<tr>";
         echo "<td class='tab_bg_1' align='center'>" . $LANG['common'][16] . "</td>";
         echo "<td class='tab_bg_1'>";
         autocompletionTextField('name',$this->table,'name',$this->fields['name'],40);
         echo "</td></tr>";
   
         echo "<tr>";
         echo "<td class='tab_bg_1' align='center'>" . $LANG["plugin_geninventorynumber"]["config"][8] . "</td>";
         echo "<td class='tab_bg_1'>";
         plugin_geninventorynumber_dropdownFields('field',$this->fields['field']);
         echo "</td></tr>";
   
   		echo "<tr>";
   		echo "<td class='tab_bg_1' align='center'>" . $LANG["plugin_geninventorynumber"]["config"][0] . "</td>";
   		echo "<td class='tab_bg_1'>";
   		dropdownYesNo("active", $this->fields["active"]);
   		echo "</td></tr>";
   
         echo "<tr>";
   		echo "<td class='tab_bg_1' align='center'>".$LANG["plugin_geninventorynumber"]["config"][6]." ".$LANG["common"][59]."</td>";
   		echo "<td class='tab_bg_1'>";
   		echo "<input type='text' name='next_number' value='".$this->fields["next_number"]."' size='12'>&nbsp;";
 		echo "</td>";
      echo "</tr>";
      echo "</table>";
      echo "</td>";
      echo "<td class='tab_bg_1'>";
      echo "<table>";
         echo "<tr>";
         echo "<td align='center'>";
         echo "<td class='tab_bg_1'>".$LANG['common'][25]."</td><td>";
         echo "<textarea cols='60' rows='4' name='comments' >".$this->fields["comments"]."</textarea>";
         echo "</td>"; 
      echo "</tr></table>";
      echo "</tr>";
      
      echo "<tr class='tab_bg_1'><td align='center' colspan='4'>";
      echo "<input type='submit' name='update' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
      echo "</td></tr>";

      
      echo "</table>";
      echo "</div>";
      echo "</form>";
      echo "<div id='tabcontent'></div>";
      echo "<script type='text/javascript'>loadDefaultTab();</script>";
	}

}
?>