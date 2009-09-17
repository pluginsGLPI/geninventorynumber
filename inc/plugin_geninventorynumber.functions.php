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

function plugin_geninventorynumber_getConfig($FK_entities = 0) {
	$config = new PluginGenInventoryNumberConfig;
	$config->getFromDB(1);
	return $config;
}

function plugin_item_add_geninventorynumber($parm, $massive_action = false, $field = 'otherserial') {
	global $DB, $LANG;

	$fields = plugin_geninventorynumber_getFieldInfos($field);

	if (isset ($parm["type"]) && isset ($fields[$parm["type"]])) {
		$config = plugin_geninventorynumber_getConfig();

		//Globally check if auto generation is on
		if ($config->fields['active']) {

			if ($fields[$parm["type"]]['enabled']) {
				$template = addslashes_deep($fields[$parm["type"]]['template']);

				$commonitem = new CommonItem;
				$commonitem->getFromDB($parm["type"], $parm["ID"]);

				$generated_field = plugin_geninventorynumber_autoName($template, $parm["type"], 0, $commonitem->obj->fields, $fields);

				//Cannot use update() because it'll launch pre_item_update and clean the inventory number...
				$sql = "UPDATE " . $commonitem->obj->table . " SET otherserial='" . $generated_field . "' WHERE ID=" . $parm["ID"];
				$DB->query($sql);

				if (!$massive_action && strstr($_SESSION["MESSAGE_AFTER_REDIRECT"], $LANG["plugin_geninventorynumber"]["massiveaction"][3]) === false)
					$_SESSION["MESSAGE_AFTER_REDIRECT"] .= $LANG["plugin_geninventorynumber"]["massiveaction"][3];

				if ($fields[$parm["type"]]['use_index'])
					$sql = "UPDATE glpi_plugin_geninventorynumber_config SET next_number=next_number+1 WHERE FK_entities=0";
				else
					$sql = "UPDATE glpi_plugin_geninventorynumber_indexes SET next_number=next_number+1 WHERE FK_entities=0 AND type='".$parm["type"]."' AND field='otherserial'";
				$DB->query($sql);

			}
		}
	}

	return $parm;
}

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
				$sql = "SELECT next_number FROM glpi_plugin_geninventorynumber_config WHERE FK_entities=$FK_entities";
			} else {
				$sql = "SELECT next_number FROM glpi_plugin_geninventorynumber_indexes WHERE FK_entities=$FK_entities AND field='otherserial' AND type=$type";
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

function glpi_plugin_geninventorynumber_updateIndexByType($type, $index) {
	global $DB;
	$query = "UPDATE `glpi_plugin_geninventorynumber_indexes` SET next_number='$index' WHERE type='$type'";
	$DB->query($query);
}

function plugin_geninventorynumber_updateIndexes($params) {
	global $DB;

	if (isset ($params["update"])) {
		$config = new PluginGenInventoryNumberConfig;
		$config->update($params);
	}

	if (isset ($params["update_fields"])) {

		//Update each type's index
		foreach ($params["IDS"] as $type => $datas) {
			plugin_geninventorynumber_saveField($datas);
		}
	}
}
?>