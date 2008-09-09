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

class GenerateInventoryNumberProfile extends CommonDBTM {
	
	function GenerateInventoryNumberProfile()
	{
		$this->table="glpi_plugin_generateinventorynumber_profiles";
		$this->type=-1;
	}

	//if profile deleted
	function cleanProfiles($ID) {
	
		global $DB;
		$query = "DELETE FROM glpi_plugin_generateinventorynumber_profiles WHERE ID='$ID' ";
		$DB->query($query);
	}
	
	function showProfileForm($target,$ID){
		global $LANG,$CFG_GLPI,$LANGGENINVENTORY;

		if (!haveRight("profile","r")) return false;

		$onfocus="";
		if ($ID){
			$this->getFromDB($ID);
		} else {
			$this->getEmpty();
			$onfocus="onfocus=\"this.value=''\"";
		}

		if (empty($this->fields["interface"])) $this->fields["interface"]="generateinventorynumber";
		if (empty($this->fields["name"])) $this->fields["name"]=$LANG["common"][0];


		echo "<form name='form' method='post' action=\"$target\">";
		echo "<div align='center'>";
		echo "<table class='tab_cadre'><tr>";
		echo "<th>".$LANG["common"][16].":</th>";
		echo "<th><input type='text' name='name' value=\"".$this->fields["name"]."\" $onfocus></th>";
		echo "<th>".$LANG["profiles"][2].":</th>";
		echo "<th><select name='interface' id='profile_interface'>";
		echo "<option value='generateinventorynumber' ".($this->fields["interface"]!="generateinventorynumber"?"selected":"").">".$LANGGENINVENTORY["title"][1]."</option>";

		echo "</select></th>";
		echo "</tr></table>";
		echo "</div>";
		
		$params=array('interface'=>'__VALUE__',
				'ID'=>$ID,
			);
		ajaxUpdateItemOnSelectEvent("profile_interface","profile_form",$CFG_GLPI["root_doc"]."/plugins/generateInventoryNumber/ajax/profiles.php",$params,false);
		ajaxUpdateItem("profile_form",$CFG_GLPI["root_doc"]."/plugins/generateInventoryNumber/ajax/profiles.php",$params,false,'profile_interface');
		echo "<br>";

		echo "<div align='center' id='profile_form'>";
		echo "</div>";

		echo "</form>";

	}

	function showGenerateinventorynumberForm($ID){
		global $LANG,$LANGGENINVENTORY;
		
		if (!haveRight("profile","r")) return false;
		$canedit=haveRight("profile","w");

		if ($ID){
			$this->getFromDB($ID);
		} else {
			$this->getEmpty();
		}

		echo "<table class='tab_cadre'><tr>";

		echo "<tr><th colspan='2' align='center'><strong>".$LANGGENINVENTORY["setup"][5]."</strong></td></tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANGGENINVENTORY["profiles"][1].":</td><td>";
		dropdownNoneReadWrite("generate",$this->fields["generate"],1,0,1);
		echo "</td>";
		echo "</tr>";

		echo "<tr class='tab_bg_2'>";
		echo "<td>".$LANGGENINVENTORY["massiveaction"][1].":</td><td>";
		dropdownNoneReadWrite("generate_overwrite",$this->fields["generate_overwrite"],1,0,1);
		echo "</td>";
		echo "</tr>";

		if ($canedit){
			echo "<tr class='tab_bg_1'>";
			if ($ID){
				echo "<td  align='center'>";
				echo "<input type='hidden' name='ID' value=$ID>";
				echo "<input type='submit' name='update' value=\"".$LANG["buttons"][7]."\" class='submit'>";
				echo "</td><td  align='center'>";
				echo "<input type='submit' name='delete' value=\"".$LANG["buttons"][6]."\" class='submit'>";
			} else {
				echo "<td colspan='2' align='center'>";
				echo "<input type='submit' name='add' value=\"".$LANG["buttons"][8]."\" class='submit'>";
			}
			echo "</td></tr>";
		}
		echo "</table>";

	}
}
	
?>