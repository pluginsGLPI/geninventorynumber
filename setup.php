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
foreach (glob(GLPI_ROOT . '/plugins/GenInventoryNumber/inc/*.php') as $file)
	include_once ($file);

/**
* Plugin initialization
*
* @return	null
*/
function plugin_init_geninventorynumber() {
	global $PLUGIN_HOOKS, $GENINVENTORYNUMBER_INVENTORY_TYPES, $CFG_GLPI, $LANG;

   $GENINVENTORYNUMBER_INVENTORY_TYPES = array (
      COMPUTER_TYPE,
      MONITOR_TYPE,
      PRINTER_TYPE,
      NETWORKING_TYPE,
      PERIPHERAL_TYPE,
      PHONE_TYPE
   );

	//TODO: How are other types defined ?

	Plugin::registerClass('PluginGeninventorynumberConfig',
		array (
		'classname' => 'PluginGeninventorynumberConfig',
		'tablename' => 'glpi_plugin_geninventorynumber_configs',
		'formpage' => 'front/config.form.php',
		'searchpage' => 'front/config.php',
		'typename' => 'configs',
		)
	);

	$pre_item_update_actions = array();
	$item_add_actions = array();
	foreach ($GENINVENTORYNUMBER_INVENTORY_TYPES as $type) {
		$item_add_actions[$type] = 'plugin_item_add_geninventorynumber';
		$pre_item_update_actions[$type] = 'plugin_pre_item_update_geninventorynumber';
	}

	$plugin = new Plugin;
	if ($plugin->isInstalled('geninventorynumber') && $plugin->isActivated('geninventorynumber')) {
		$PLUGIN_HOOKS['change_profile']['geninventorynumber'] = 'plugin_geninventorynumber_changeprofile';

		$PLUGIN_HOOKS['use_massive_action']['geninventorynumber'] = 1;
		$PLUGIN_HOOKS['item_add']['geninventorynumber'] = $item_add_actions;
		$PLUGIN_HOOKS['pre_item_update']['geninventorynumber'] = $pre_item_update_actions;

		$PLUGIN_HOOKS['headings']['geninventorynumber'] = 'plugin_get_headings_geninventorynumber';
		$PLUGIN_HOOKS['headings_action']['geninventorynumber'] = 'plugin_headings_actions_geninventorynumber';

		$PLUGIN_HOOKS['pre_item_purge']['geninventorynumber'] = array("Profile"=>'plugin_pre_item_purge_geninventorynumber');

		if (haveRight("config", "w")) {
			$PLUGIN_HOOKS['config_page']['geninventorynumber'] = 'front/config.php';
		}
	}
}

/**
* Definition of plugin
*
* @return	array	Array on informations about plugin
*/
function plugin_version_geninventorynumber() {
	global $LANG;
	return array (
		'name' => $LANG["plugin_geninventorynumber"]["title"][1],
		'minGlpiVersion' => '0.78',
		'version' => '1.4.0',
		'author' => 'Walid Nouh & Dévi Balpe',
		'homepage' => 'https://forge.indepnet.net/project/show/Geninventorynumber'
	);
}

/**
* Prerequisites check
*
* @return	bool	True if plugin can be installed
*/
function plugin_geninventorynumber_check_prerequisites() {
	if (GLPI_VERSION >= 0.78) {
		return true;
	} else {
		echo "GLPI version not compatible need 0.78";
	}
}

/**
* Compatibility check
*
* @return	bool	True if plugin compatible with configuration
*/
function plugin_geninventorynumber_check_config() {
	return true;
}

?>