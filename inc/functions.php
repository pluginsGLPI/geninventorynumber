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
