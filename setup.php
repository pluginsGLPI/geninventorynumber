<?php
/*
 * @version $Id: bill.tabs.php 530 2011-06-30 11:30:17Z walid $
LICENSE

This file is part of the geninventorynumber plugin.

geninventorynumber plugin is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

geninventorynumber plugin is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with GLPI; along with geninventorynumber. If not, see <http://www.gnu.org/licenses/>.
--------------------------------------------------------------------------
@package   geninventorynumber
@author    the geninventorynumber plugin team
@copyright Copyright (c) 2008-2013 geninventorynumber plugin team
@license   GPLv2+
http://www.gnu.org/licenses/gpl.txt
@link      https://forge.indepnet.net/projects/geninventorynumber
@link      http://www.glpi-project.org/
@since     2008
---------------------------------------------------------------------- */

function plugin_init_geninventorynumber() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $LANG, $GENINVENTORYNUMBER_TYPES;

   $PLUGIN_HOOKS['csrf_compliant']['geninventorynumber'] = true;
   $PLUGIN_HOOKS['change_profile']['geninventorynumber']
      = array('PluginGeninventorynumberProfile', 'changeProfile');
   $PLUGIN_HOOKS['post_init']['geninventorynumber'] = 'plugin_geninventorynumber_postinit';
       
   $GENINVENTORYNUMBER_TYPES = array ('Computer', 'Monitor', 'Printer', 'NetworkEquipment',
                                       'Peripheral', 'Phone', 'SoftwareLicense');
   
   $plugin = new Plugin();
   if ($plugin->isInstalled('geninventorynumber') && $plugin->isActivated('geninventorynumber')) {
      $PLUGIN_HOOKS['use_massive_action']['geninventorynumber'] = 1;

      Plugin::registerClass('PluginGeninventorynumberProfile',
                            array('addtabon' => array('Profile')));
      Plugin::registerClass('PluginGeninventorynumberConfigField');
      
      $PLUGIN_HOOKS['pre_item_purge']['geninventorynumber']
      = array('Profile' => array('PluginGeninventorynumberProfile', 'purgeProfiles'));

      if (Session::haveRight("config", "w")) {
         $PLUGIN_HOOKS['config_page']['geninventorynumber'] = 'front/config.form.php';
      }
   }
}

function plugin_version_geninventorynumber() {
   global $LANG;
   return array ('name'           => $LANG["plugin_geninventorynumber"]["title"][1],
                   'minGlpiVersion' => '0.83.3',
                   'version'        => '2.0',
                   'author'         => "<a href='http://www.teclib.com'>TECLIB'</a>",
                   'homepage'       => 'https://forge.indepnet.net/project/show/Geninventorynumber');
}

function plugin_geninventorynumber_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.83.3','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI 0.83.3 or higher";
   } else {
      return true;
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
