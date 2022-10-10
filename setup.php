<?php

/**
 * -------------------------------------------------------------------------
 * GenInventoryNumber plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenInventoryNumber.
 *
 * GenInventoryNumber is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GenInventoryNumber is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenInventoryNumber. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2008-2022 by GenInventoryNumber plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/geninventorynumber
 * -------------------------------------------------------------------------
 */

define ('PLUGIN_GENINVENTORYNUMBER_VERSION', '2.8.3');

// Minimal GLPI version, inclusive
define("PLUGIN_GENINVENTORYNUMBER_MIN_GLPI", "10.0.0");
// Maximum GLPI version, exclusive
define("PLUGIN_GENINVENTORYNUMBER_MAX_GLPI", "10.0.99");

function plugin_init_geninventorynumber() {
   global $PLUGIN_HOOKS, $CFG_GLPI, $GENINVENTORYNUMBER_TYPES;

   $PLUGIN_HOOKS['csrf_compliant']['geninventorynumber'] = true;
   $PLUGIN_HOOKS['post_init']['geninventorynumber'] = 'plugin_geninventorynumber_postinit';

   $GENINVENTORYNUMBER_TYPES = ['Computer', 'Monitor', 'Printer', 'NetworkEquipment',
                                 'Peripheral', 'Phone', 'SoftwareLicense', 'Cable',
                                 'Appliance', 'Certificate', 'ConsumableItem', 'Enclosure',
                                 'PassiveDCEquipment', 'PDU', 'Rack'];

   $plugin = new Plugin();
   if ($plugin->isActivated('geninventorynumber')) {
      $PLUGIN_HOOKS['use_massive_action']['geninventorynumber'] = 1;

      Plugin::registerClass('PluginGeninventorynumberProfile',
                            ['addtabon' => ['Profile']]);
      Plugin::registerClass('PluginGeninventorynumberConfig');
      Plugin::registerClass('PluginGeninventorynumberConfigField');

      if (Session::haveRight('config', UPDATE)) {
         $PLUGIN_HOOKS["menu_toadd"]['geninventorynumber']
            = ['tools' => 'PluginGeninventorynumberConfig'];
      }
   }
}

function plugin_version_geninventorynumber() {
   return [
      'name'         => __('Inventory number generation', 'geninventorynumber'),
      'version'      => PLUGIN_GENINVENTORYNUMBER_VERSION,
      'author'       => "<a href='http://www.teclib.com'>TECLIB'</a> + KK",
      'homepage'     => 'https://github.com/pluginsGLPI/geninventorynumber',
      'license'      => 'GPLv2+',
      'requirements' => [
         'glpi' => [
            'min' => PLUGIN_GENINVENTORYNUMBER_MIN_GLPI,
            'max' => PLUGIN_GENINVENTORYNUMBER_MAX_GLPI,
          ]
       ]
   ];
}
