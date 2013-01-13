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


/**
* Calculates generated field
* 
* @param	string	field format (model)
* @param	string	type of the context object
* @param	int		ID of the entity for context object
* @param	array	Field list from object
* @param 	array	List of fields configured in plugin for the context object (configfields table)
* @return	string	generated value
*/
function plugin_geninventorynumber_autoName($objectName, $type, $FK_entities = 0, $fields, $field_params = array ()) {
	global $DB;

	$len = strlen($objectName);
	if ($len > 8 && substr($objectName, 0, 4) === '&lt;' && substr($objectName, $len -4, 4) === '&gt;') {

		$autoNum = substr($objectName, 4, $len -8);
		$mask = '';
		if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
			$serial = (isset ($fields['serial']) ? $fields['serial'] : '');

			$global = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
			$autoNum = str_replace(array (
				'\\y',
				'\\Y',
				'\\m',
				'\\d',
				'_',
				'%',
				'\\g',
				'\\s'
			), array (
				date('y'),
				date('Y'),
				date('m'),
				date('d'),
				'\\_',
				'\\%',
				'',
				$serial
			), $autoNum);
			$mask = $mask[0];
			$pos = strpos($autoNum, $mask) + 1;
			$len = strlen($mask);
			$like = str_replace('#', '_', $autoNum);

			if ($field_params[$type]['use_index']) {
				$sql = "SELECT next_number FROM glpi_plugin_geninventorynumber_configs WHERE FK_entities=$FK_entities";
			} else {
				$sql = "SELECT next_number FROM glpi_plugin_geninventorynumber_indexes WHERE FK_entities=$FK_entities AND field='otherserial' AND type='$type'";
			}
			$result = $DB->query($sql);

			$objectName = str_replace(array (
				$mask,
				'\\_',
				'\\%'
			), array (
				str_pad($DB->result($result, 0, "next_number"), $len, '0', STR_PAD_LEFT),
				'_',
				'%'
			), $autoNum);
		}
	}
	return $objectName;
}

/**
* Returns current index for a given object type
* 
* @param	string	type of the context
* @return	int		0 or the current index
*
* NOTE: Utilis� uniquement pour l'affichage dans l'�dition de la configuration (ShowCoreConfig)
*/
function plugin_geninventorynumber_getIndexByTypeName($type) {
	global $DB;

	$query = "SELECT next_number FROM `glpi_plugin_geninventorynumber_indexes` WHERE type='$type'";
	$result = $DB->query($query);
	if (!$DB->numrows($result)) {
		return 0;
	} else {
		return $DB->result($result, 0, "next_number");
	}
}

/**
* Get all configfields for a given field (ex : 'otherserial')
*
*	Return array format: $item[type] = line
*		where type is object type (ex. 'Computer') 
*		and line issued from table plugin_geninventorynumber_configfields
*
* @param	string	Name of the field(ex : 'otherserial')	
* @return	array	List of configfields
*/
function plugin_geninventorynumber_getFieldInfos($field) {
	global $DB;
   $query = "SELECT fields.* FROM `glpi_plugin_geninventorynumber_configfields` as fields,
               `glpi_plugin_geninventorynumber_configs` as config
                  WHERE config.field='$field' AND config.id=fields.config_id
                     ORDER BY fields.device_type";
   $result = $DB->query($query);
   $fields = array();
   while ($datas = $DB->fetch_array($result)) {
   	$fields[$datas['device_type']] = $datas;
   }        
   return $fields;
}

/**
* Dropdown of fields the plugin can manage (generate)
* 
* @param	string	Name of the dropdown
* @param	string	??? should be an array of options ?
* @return	null
*/
function plugin_geninventorynumber_dropdownFields($name,$value) {
   global $LANG;
   $fields['otherserial'] = $LANG['common'][20];
   Dropdown::showFromArray($name,$fields,$value);	
}

/**
* Show edition form for ConfigFields associated with a Config
* 	Each line represents an entry in plugin_geninventorynumber_configfields 
* 
* @param	string 	"POST" path for form
* @param	int		ID of parent configuration
* @return	null
*
* TODO : Show "locale" name instead of value ('Computer')
*/
function plugin_geninventorynumber_showCoreConfig($target,$id) {
	global $LANG, $CFG_GLPI, $DB;
	
	$config = new PluginGeninventorynumberConfig;
	$config->getFromDB($id);
	$fields = plugin_geninventorynumber_getFieldInfos($config->fields['field']);
   
	echo "<form name='form_core_config' method='post' action=\"$target\">";
	echo "<div align='center'>";
	echo "<table class='tab_cadre_fixe' cellpadding='5'>";
	echo "<tr><th colspan='5'>" . $LANG["plugin_geninventorynumber"]["config"][9] . "</th></tr>";

	echo "<input type='hidden' name='id' value='$id'>";
	echo "<input type='hidden' name='FK_entities' value='0'>";

	echo "<tr><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][10] . "</th><th>" . $LANG["common"][60] . "</th>";
	echo "<th>" . $LANG["plugin_geninventorynumber"]["config"][5] . "</th><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][6] . "</th></tr>";

	foreach ($fields as $type => $value) {
		echo "<td class='tab_bg_1' align='center'>" . $type. "</td>";
		echo "<td class='tab_bg_1'>";
      echo "<input type='hidden' name='ids[$type][id]' value='".$value["id"]."'>";
      echo "<input type='hidden' name='ids[$type][device_type]' value='$type'>";
      echo "<input type='text' name='ids[$type][template]' value=\"" . $value["template"] . "\">";
		echo "</td>";
		echo "<td class='tab_bg_1' align='center'>";
		Dropdown::showYesNo("ids[$type][enabled]", $value["enabled"]);
		echo "</td>";
		echo "<td class='tab_bg_1' align='center'>";
		Dropdown::showYesNo("ids[$type][use_index]", $value["use_index"]);
		echo "</td>";
		echo "<td class='tab_bg_1'>";
		if ($value["enabled"] && !$value["use_index"])
			$disabled = "";
		else
			$disabled = "disabled";

		echo "<input type='text' name='ids[$type][index]' value='" . plugin_geninventorynumber_getIndexByTypeName($type) . "' size='12' " . $disabled . ">";
		echo "</td>";
		echo "</tr>";
	}

	echo "<tr class='tab_bg_1'><td align='center' colspan='5'>";
	echo "<input type='submit' name='update_fields' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
	echo "</td></tr>";

	echo "</table></form>";
}

/**
* Replace plugin rights in Session var when switching Profile
* 
* @return	null
*/
function plugin_geninventorynumber_changeprofile(){
   $prof=new PluginGeninventorynumberProfile();
   if($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
      $_SESSION["glpi_plugin_geninventorynumber_profile"]=$prof->fields;
   }
   else {
      unset($_SESSION["glpi_plugin_geninventorynumber_profile"]);
   }
}

/**
* Check if users has a right for a module
* 
* @param	string	Module name to check (ex 'generate')
* @param	string	Expected right(ex 'w')
* @return	bool 	True if user has right
*/
function plugin_geninventorynumber_Session::haveRight($module, $right) {
   $matches = array (
      "" => array (
         "",
         "r",
         "w"
      ), // should not happen
   "r" => array (
         "r",
         "w"
      ),
      "w" => array (
         "w"
      ),
      "1" => array (
         "1"
      ),
      "0" => array (
         "0",
         "1"
      ), // should not happen
	);
	if (isset ($_SESSION["glpi_plugin_geninventorynumber_profile"][$module]) 
			&& in_array($_SESSION["glpi_plugin_geninventorynumber_profile"][$module], $matches[$right]))
		return true;
	else
		return false;
}

?>