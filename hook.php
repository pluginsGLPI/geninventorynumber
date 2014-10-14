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
function plugin_geninventorynumber_postinit() {
   global $GENINVENTORYNUMBER_TYPES, $PLUGIN_HOOKS;
   
   foreach ($GENINVENTORYNUMBER_TYPES as $type) {
      $PLUGIN_HOOKS['pre_item_add']['geninventorynumber'][$type]
      = array('PluginGeninventorynumberGeneration', 'preItemAdd');
      $PLUGIN_HOOKS['pre_item_update']['geninventorynumber'][$type]
      = array('PluginGeninventorynumberGeneration', 'preItemUpdate');
   }
}

function plugin_geninventorynumber_MassiveActions($type) {
   global $LANG, $GENINVENTORYNUMBER_TYPES;

   $actions = array ();
   if (in_array($type, $GENINVENTORYNUMBER_TYPES)) {
      $fields = PluginGeninventorynumberConfigField::getConfigFieldByItemType($type);

      if (PluginGeninventorynumberConfigField::isActiveForItemType($type)) {
         if (Session::haveRight("plugin_geninventorynumber_generate", "w")) {
            $actions["plugin_geninventorynumber_generate"]
               = $LANG["plugin_geninventorynumber"]["massiveaction"][0];
         }
         if (Session::haveRight("plugin_geninventorynumber_overwrite", "w")) {
            $actions["plugin_geninventorynumber_overwrite"]
               = $LANG["plugin_geninventorynumber"]["massiveaction"][1];
         }
      }
   }
   return $actions;
}

function plugin_geninventorynumber_MassiveActionsDisplay($options = array()) {
   global $LANG, $GENINVENTORYNUMBER_TYPES;
   if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($options['action']) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
               $LANG["plugin_geninventorynumber"]["buttons"][0] . "\" >";
            break;
         default :
            break;
      }
   }
   return "";
}

function plugin_geninventorynumber_MassiveActionsProcess($data) {
   global $DB;

   switch ($data['action']) {
      case "plugin_geninventorynumber_generate" :
      case "plugin_geninventorynumber_overwrite" :
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $item = new $data['itemtype'];
               $item->getFromDB($key);
               if (//Only generates inventory number for object without it !
                     (($data["action"] == "plugin_geninventorynumber_generate")
                           && isset ($item->fields["otherserial"])
                              && $item->fields["otherserial"] == "") //Or is overwrite action is selected
                     || ($data["action"] == "plugin_geninventorynumber_overwrite")) {
                  PluginGeninventorynumberGeneration::preItemAdd($item, true);
               }
            }
         }
         break;
      default :
         break;
   }
   return true;
}


function plugin_geninventorynumber_install() {
   $migration = new Migration("2.0");
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/config.class.php');
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/profile.class.php');
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/configfield.class.php');
   PluginGeninventorynumberConfig::install($migration);
   PluginGeninventorynumberProfile::install($migration);
   PluginGeninventorynumberConfigField::install($migration);
   return true;
}


function plugin_geninventorynumber_uninstall() {
   $migration = new Migration("2.0");
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/config.class.php');
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/profile.class.php');
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/configfield.class.php');
   PluginGeninventorynumberConfig::uninstall($migration);
   PluginGeninventorynumberProfile::uninstall($migration);
   PluginGeninventorynumberConfigField::uninstall($migration);
   return true;
}
