<?php

/*
 * @version $Id: setup.php,v 1.2 2006/04/02 14:45:27 moyo Exp $
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2006 by the INDEPNET Development Team.

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

include_once ("inc/plugin_generateInventoryNumber.auth.functions.php");
include_once ("inc/plugin_generateInventoryNumber.config.class.php");
include_once ("inc/plugin_generateInventoryNumber.setup.functions.php");
include_once ("inc/plugin_generateInventoryNumber.functions.php");

function plugin_init_generateInventoryNumber() {
	global $PLUGIN_HOOKS, $CFG_GLPI, $LANGGENINVENTORY,$INVENTORY_TYPES;

	$PLUGIN_HOOKS['init_session']['generateInventoryNumber'] = 'plugin_generateInventoryNumber_initSession';
	$PLUGIN_HOOKS['change_profile']['generateInventoryNumber'] = 'plugin_generateInventoryNumber_changeprofile';

	if (isGenerateInventoryNumberPluginInstalled())
	{
		$PLUGIN_HOOKS['use_massive_action']['generateInventoryNumber'] = 1;
		$PLUGIN_HOOKS['pre_item_update']['generateInventoryNumber'] = 'plugin_pre_item_update_generateInventoryNumber'; 
	  	$PLUGIN_HOOKS['item_add']['generateInventoryNumber'] = 'plugin_item_add_generateInventoryNumber';
	}
		
	$INVENTORY_TYPES = array(COMPUTER_TYPE,MONITOR_TYPE,PRINTER_TYPE,NETWORKING_TYPE,PERIPHERAL_TYPE,PHONE_TYPE);
	if (isset ($_SESSION["glpiID"])) {

		// Config page
		if (haveRight("config", "w"))
			$PLUGIN_HOOKS['config_page']['generateInventoryNumber'] = 'front/plugin_generateInventoryNumber.config.php';
	}
}

function plugin_version_generateInventoryNumber() {

	global $LANGGENINVENTORY;

	return array (
		'name' => $LANGGENINVENTORY["title"][1],
		'minGlpiVersion' => '0.71',
		'version' => '0.1'
	);
}



?>