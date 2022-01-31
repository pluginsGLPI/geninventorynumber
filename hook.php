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

function plugin_geninventorynumber_postinit() {
   global $GENINVENTORYNUMBER_TYPES, $PLUGIN_HOOKS;

   foreach ($GENINVENTORYNUMBER_TYPES as $type) {
      $PLUGIN_HOOKS['pre_item_add']['geninventorynumber'][$type]
        = ['PluginGeninventorynumberGeneration', 'preItemAdd'];
      $PLUGIN_HOOKS['pre_item_update']['geninventorynumber'][$type]
        = ['PluginGeninventorynumberGeneration', 'preItemUpdate'];
   }
}

function plugin_geninventorynumber_MassiveActions($type) {
   global $GENINVENTORYNUMBER_TYPES;

   $actions = [];
   if (in_array($type, $GENINVENTORYNUMBER_TYPES)) {
      $fields = PluginGeninventorynumberConfigField::getConfigFieldByItemType($type);

      if (PluginGeninventorynumberConfigField::isActiveForItemType($type)) {
         if (Session::haveRight("plugin_geninventorynumber", CREATE)) {
            $actions['PluginGeninventorynumberGeneration'.
               MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_geninventorynumber_generate']
               = __('Generate inventory number', 'geninventorynumber');
         }
         if (Session::haveRight("plugin_geninventorynumber", UPDATE)) {
            $actions['PluginGeninventorynumberGeneration'.
               MassiveAction::CLASS_ACTION_SEPARATOR.'plugin_geninventorynumber_overwrite']
              = __('Regenerate inventory number (overwrite)', 'geninventorynumber');
         }
      }
   }
   return $actions;
}

function plugin_geninventorynumber_install() {
   $php_dir = Plugin::getPhpDir('geninventorynumber');

   $migration = new Migration("0.85+1.0");
   include_once($php_dir . '/inc/config.class.php');
   include_once($php_dir . '/inc/profile.class.php');
   include_once($php_dir . '/inc/configfield.class.php');
   PluginGeninventorynumberConfig::install($migration);
   PluginGeninventorynumberProfile::install($migration);
   PluginGeninventorynumberConfigField::install($migration);
   return true;
}

function plugin_geninventorynumber_uninstall() {
   $php_dir = Plugin::getPhpDir('geninventorynumber');

   $migration = new Migration("0.85+1.0");
   include_once($php_dir . '/inc/config.class.php');
   include_once($php_dir . '/inc/profile.class.php');
   include_once($php_dir . '/inc/configfield.class.php');
   PluginGeninventorynumberConfig::uninstall($migration);
   PluginGeninventorynumberProfile::removeRightsFromSession();
   PluginGeninventorynumberProfile::uninstallProfile();
   PluginGeninventorynumberConfigField::uninstall($migration);
   return true;
}
