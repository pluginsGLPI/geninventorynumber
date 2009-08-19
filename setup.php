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
include_once ("config/plugin_generateinventorynumber.define.php");
foreach (glob(GLPI_ROOT . '/plugins/generateinventorynumber/inc/*.php') as $file)
	include_once ($file);

function plugin_init_generateinventorynumber() {
	global $PLUGIN_HOOKS, $CFG_GLPI;

	$PLUGIN_HOOKS['change_profile']['generateinventorynumber'] = 'plugin_generateinventorynumber_changeprofile';

   $plugin = new Plugin;
	if ($plugin->isActivated("generateinventorynumber"))
	{
		$PLUGIN_HOOKS['use_massive_action']['generateinventorynumber'] = 1;
		$PLUGIN_HOOKS['pre_item_update']['generateinventorynumber'] = 'plugin_pre_item_update_generateinventorynumber';
	  	$PLUGIN_HOOKS['item_add']['generateinventorynumber'] = 'plugin_item_add_generateinventorynumber';

		$PLUGIN_HOOKS['headings']['generateinventorynumber'] = 'plugin_get_headings_generateinventorynumber';
		$PLUGIN_HOOKS['headings_action']['generateinventorynumber'] = 'plugin_headings_actions_generateinventorynumber';

      if (haveRight("config", "w")) {
            $PLUGIN_HOOKS['config_page']['generateinventorynumber'] = 'front/plugin_generateinventorynumber.config.form.php';
      }
	}
		
}

function plugin_version_generateinventorynumber() {
	global $LANG;

	return array (
		'name' => $LANG["plugin_generateinventorynumber"]["title"][1],
		'minGlpiVersion' => '0.72',
		'version' => '1.2.0',
		'author' => 'Walid Nouh',
	);
}

function plugin_generateinventorynumber_check_prerequisites() {
	if (GLPI_VERSION >= 0.72) {
		return true;
	} else {
		echo "GLPI version not compatible need 0.72";
	}
}

function plugin_generateinventorynumber_check_config() {
	return true;
}

?>