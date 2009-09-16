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
include_once ("config/plugin_geninventorynumber.define.php");
foreach (glob(GLPI_ROOT . '/plugins/geninventorynumber/inc/*.php') as $file)
	include_once ($file);

function plugin_init_geninventorynumber() {
	global $PLUGIN_HOOKS, $CFG_GLPI,$LANG;

	$PLUGIN_HOOKS['change_profile']['geninventorynumber'] = 'plugin_geninventorynumber_changeprofile';

   $plugin = new Plugin;
	if ($plugin->isActivated('geninventorynumber'))
	{
      registerPluginType('geninventorynumber', 'PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE', 1561, array (
         'classname' => 'PluginGenInventoryNumberConfig',
         'tablename' => 'glpi_plugin_geninventorynumber_config',
         'formpage' => 'front/plugin_geninventorynumber.config.form.php',
         'searchpage' => 'front/plugin_geninventorynumber.config.php',
         'typename' => 'config',
      ));


		$PLUGIN_HOOKS['use_massive_action']['geninventorynumber'] = 1;
		$PLUGIN_HOOKS['pre_item_update']['geninventorynumber'] = 'plugin_pre_item_update_geninventorynumber';
	  	$PLUGIN_HOOKS['item_add']['geninventorynumber'] = 'plugin_item_add_geninventorynumber';

		$PLUGIN_HOOKS['headings']['geninventorynumber'] = 'plugin_get_headings_geninventorynumber';
		$PLUGIN_HOOKS['headings_action']['geninventorynumber'] = 'plugin_headings_actions_geninventorynumber';

      if (haveRight("config", "w")) {
            $PLUGIN_HOOKS['config_page']['geninventorynumber'] = 'front/plugin_geninventorynumber.config.form.php';
      }
	}
		
}

function plugin_version_geninventorynumber() {
	global $LANG;

	return array (
		'name' => $LANG["plugin_geninventorynumber"]["title"][1],
		'minGlpiVersion' => '0.72',
		'version' => '1.3.0',
		'author' => 'Walid Nouh',
      'homepage'=>'https://forge.indepnet.net/wiki/geninventorynumber'
	);
}

function plugin_geninventorynumber_check_prerequisites() {
	if (GLPI_VERSION >= 0.72) {
		return true;
	} else {
		echo "GLPI version not compatible need 0.72";
	}
}

function plugin_geninventorynumber_check_config() {
	return true;
}

function plugin_geninventorynumber_getSearchOption() {
   global $LANG;
   $sopt = array ();
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE]['common'] = $LANG["plugin_geninventorynumber"]["title"][1];

   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][1]['table'] = 'glpi_plugin_geninventorynumber_config';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][1]['field'] = 'name';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][1]['linkfield'] = '';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][1]['name'] = $LANG['common'][16];
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][1]['datatype'] = 'itemlink';

   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][2]['table'] = 'glpi_plugin_geninventorynumber_config';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][2]['field'] = 'active';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][2]['linkfield'] = '';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][2]['name'] = $LANG['common'][60];
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][2]['datatype'] = 'bool';

   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][3]['table'] = 'glpi_plugin_geninventorynumber_config';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][3]['field'] = 'comments';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][3]['linkfield'] = '';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][3]['name'] = $LANG['common'][25];

   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][30]['table'] = 'glpi_plugin_geninventorynumber_config';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][30]['field'] = 'ID';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][30]['linkfield'] = '';
   $sopt[PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE][30]['name'] = $LANG["common"][2];


   return $sopt;

}

?>