<?php
/*
  ----------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2008 by the INDEPNET Development Team.
  
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
// Original Author of file: Dévi Balpe
// Purpose of file:
// ----------------------------------------------------------------------

class geninventorynumberProfile extends CommonDBTM {
	
	function geninventorynumberProfile()
	{
		$this->table="glpi_plugin_geninventorynumber_profiles";
		$this->type=-1;
	}

	//if profile deleted
	function cleanProfiles($ID) {
	
		global $DB;
		$query = "DELETE FROM glpi_plugin_geninventorynumber_profiles WHERE ID='$ID' ";
		$DB->query($query);
	}
	
	function showForm($target,$ID){
		global $LANG;
		
		if (!Session::haveRight("profile","r")) return false;
		$canedit=Session::haveRight("profile","w");

		if ($ID){
			$this->getFromDB($ID);
		} else {
			$this->getEmpty();
		}
		
		echo "<form id='geninventorynumber_profile' method='post' action='$target'>";
		echo "<table class='tab_cadre_fixe'><tr>";

		echo "<tr><th colspan='2' align='center'><strong>".$LANG["plugin_geninventorynumber"]["profiles"][0]." ".$this->fields["name"]."</strong></th></tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANG["plugin_geninventorynumber"]["profiles"][1].":</td><td>";
		dropdownNoneReadWrite("generate",$this->fields["generate"],1,0,1);
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANG["plugin_geninventorynumber"]["massiveaction"][1].":</td><td>";
		dropdownNoneReadWrite("generate_overwrite",$this->fields["generate_overwrite"],1,0,1);
		echo "</td>";
		echo "</tr>";

		if ($canedit){
			echo "<tr class='tab_bg_1'>";
			echo "<td colspan='2' align='center'>";
			echo "<input type='hidden' name='ID' value=$ID>";
			echo "<input type='submit' name='update_user_profile' value=\"".$LANG["buttons"][7]."\" class='submit'>";
			echo "</td></tr>";
		}
		echo "</table></form>";
	}
}
?>