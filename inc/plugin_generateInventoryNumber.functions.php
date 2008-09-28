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

function plugin_generateInventoryNumber_getConfig($FK_entities = 0) {
	$config = new plugin_GenerateInventoryNumberConfig;
	$config->getFromDB(1);
	return $config;
}

function plugin_generateInventoryNumber_canGenerate($parm, $config) {
	global $ALLOWED_TYPES;
	//If object injected from OCS and 
	if (isset ($parm["_from_ocs"]) && $parm["_from_ocs"] == 1 && $config->fields["generate_ocs"] == 1)
		return true;

	//If object is injected from data_injection
	if (isset ($parm["_from_data_injection"]) && $parm["_from_data_injection"] && $config->fields["generate_data_injection"] == 1)
		return true;

	//If object is entered manually in GLPI	
	if (!isset ($parm["_from_data_injection"]) && !isset ($parm["_from_ocs"]) && $config->fields["generate_internal"] == 1)
		return true;

	$type_str = array_keys($ALLOWED_TYPES, $parm["type"]);
	if ($config->fields[$type_str . "_gen_enabled"])
		return true;

	return false;
}

function plugin_item_add_generateInventoryNumber($parm) {
	global $INVENTORY_TYPES, $DB;
	if (isset ($parm["type"]) && isset($INVENTORY_TYPES[$parm["type"]])) {
		$config = plugin_generateInventoryNumber_getConfig(0);

		//Globally check if auto generation is on
		if ($config->fields["active"]) {

			if (plugin_generateInventoryNumber_canGenerate($parm, $config)) {
				$template = addslashes_deep($config->fields[plugin_generateInventoryNumber_getTemplateFieldByType($parm["type"])]);

				$commonitem = new CommonItem;
				$commonitem->setType($parm["type"], true);
				$fields = $commonitem->obj->fields;

				//Cannot use update() because it'll launch pre_item_update and clean the inventory number...
				$sql = "UPDATE " . $commonitem->obj->table . " SET otherserial='" . plugin_generateInventoryNumber_autoName($template, $parm["type"], 0) . "' WHERE ID=" . $parm["ID"];
				$DB->query($sql);

				plugin_generateInventoryNumber_incrementNumber(0, $parm["type"]);
			}
		}
	}

	return $parm;
}

function plugin_pre_item_update_generateInventoryNumber($parm) {
	global $INVENTORY_TYPES;

	if (isset ($parm["_item_type_"]) && isset($INVENTORY_TYPES[$parm["_item_type_"]])) {

		$config = plugin_generateInventoryNumber_getConfig(0);
		$template = addslashes_deep($config->fields[plugin_generateInventoryNumber_getTemplateFieldByType($parm["_item_type_"])]);

		if ($config->fields["active"] && $template != '') {
			if (isset ($parm["otherserial"]))
				unset ($parm["otherserial"]);
		}
	}

	return $parm;
}

function plugin_generateInventoryNumber_getTemplateFieldByType($type) {
	switch ($type) {
		case COMPUTER_TYPE :
			return "template_computer";
		case MONITOR_TYPE :
			return "template_monitor";
		case PRINTER_TYPE :
			return "template_printer";
		case PERIPHERAL_TYPE :
			return "template_peripheral";
		case NETWORKING_TYPE :
			return "template_networking";
		case PHONE_TYPE :
			return "template_phone";
	}
}

function plugin_generateInventoryNumber_autoName($objectName, $type, $FK_entities = 0) {
	global $DB;

	$len = strlen($objectName);
	if ($len > 8 && substr($objectName, 0, 4) === '&lt;' && substr($objectName, $len -4, 4) === '&gt;') {
		$autoNum = substr($objectName, 4, $len -8);
		$mask = '';
		if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
			$global = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
			$autoNum = str_replace(array (
				'\\y',
				'\\Y',
				'\\m',
				'\\d',
				'_',
				'%',
				'\\g'
			), array (
				date('y'),
				date('Y'),
				date('m'),
				date('d'),
				'\\_',
				'\\%',
				''
			), $autoNum);
			$mask = $mask[0];
			$pos = strpos($autoNum, $mask) + 1;
			$len = strlen($mask);
			$like = str_replace('#', '_', $autoNum);

			if (plugin_generateInventoryNumber_isGlobalIndexByType($type))
				$sql = "SELECT next_number FROM glpi_plugin_generateinventorynumber_config WHERE FK_entities=$FK_entities";
			else
				$sql = "SELECT next_number FROM glpi_plugin_generateinventorynumber_indexes WHERE FK_entities=$FK_entities AND field='otherserial' AND type=$type";

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

// Define rights for the plugin types
function plugin_generateInventoryNumber_haveTypeRight($type, $right) {
	return plugin_generateInventoryNumber_haveRight($type, $right);
}

function plugin_generateInventoryNumber_incrementNumber($FK_entities = 0, $type) {
	global $DB;

	if (plugin_generateInventoryNumber_isGlobalIndexByType($type))
		$sql = "UPDATE glpi_plugin_generateinventorynumber_config SET next_number=next_number+1 WHERE FK_entities=$FK_entities";
	else
		$sql = "UPDATE glpi_plugin_generateinventorynumber_indexes SET next_number=next_number+1 WHERE FK_entities=$FK_entities AND type=$type AND field='otherserial'";
	$DB->query($sql);
}

function plugin_generateInventoryNumber_MassiveActions($type) {
	global $LANGGENINVENTORY, $INVENTORY_TYPES;

	if (isset($INVENTORY_TYPES[$type])) {
		if (plugin_generateInventoryNumber_haveRight("generate", "w"))
			$values["plugin_generateInventoryNumbe_generate"] = $LANGGENINVENTORY["massiveaction"][0];

		if (plugin_generateInventoryNumber_haveRight("generate_overwrite", "w"))
			$values["plugin_generateInventoryNumber_generate_overwrite"] = $LANGGENINVENTORY["massiveaction"][1];
		return $values;
	} else
		return array ();
}

function plugin_generateInventoryNumber_MassiveActionsDisplay($type, $action) {
	global $LANG, $INVENTORY_TYPES;

	if (isset($INVENTORY_TYPES[$type])) {
		switch ($action) {
			case "plugin_generateInventoryNumber_generate" :
			case "plugin_generateInventoryNumber_generate_overwrite" :
				echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG["buttons"][2] . "\" >";
				break;
			default :
				break;
		}
	}

	return "";
}

function plugin_generateInventoryNumber_MassiveActionsProcess($data) {
	global $DB, $INVENTORY_TYPES;

	switch ($data['action']) {
		case "plugin_generateInventoryNumber_generate" :
		case "plugin_generateInventoryNumber_generate_overwrite" :
			foreach ($data["item"] as $key => $val) {
				if ($val == 1) {

					$commonitem = new CommonItem;
					$commonitem->getFromDB($data['device_type'], $key);
					if (//Only generates inventory number for object without it !
					 (($data["action"] == "plugin_generateInventoryNumber_generate") && isset ($commonitem->obj->fields["otherserial"]) && $commonitem->obj->fields["otherserial"] == "") //Or is overwrite action is selected
					|| ($data["action"] == "plugin_generateInventoryNumber_generate_overwrite")) {
						$parm["ID"] = $key;
						$parm["type"] = $data['device_type'];
						plugin_item_add_generateInventoryNumber($parm);
					}
				}
			}
			break;
		default :
			break;
	}
}

function plugin_generateInventoryNumber_isGlobalIndexByType($type) {
	global $INVENTORY_TYPES;

	if (isset($INVENTORY_TYPES[$type]))
	{
		$config = plugin_generateInventoryNumber_getConfig(0);
		return $config->fields[$INVENTORY_TYPES[$type] . "_global_index"];
	}

	return null;
}
?>