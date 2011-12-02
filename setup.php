<?php

/*
 * @version $Id: soap.php 306 2011-11-08 12:36:05Z remi $
 -------------------------------------------------------------------------
 geninventorynumber - plugin for GLPI
 Copyright (C) 2003-2011 by the geninventorynumber Development Team.

 https://forge.indepnet.net/projects/geninventorynumber
 -------------------------------------------------------------------------

 LICENSE

 This file is part of geninventorynumber plugin.

 geninventorynumber is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with webservices. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 --------------------------------------------------------------------------
 @package   order
 @author    Walid Nouh
 @copyright Copyright (c) 2010-2011 Walid Nouh
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

/**
* Plugin initialization
*
*/
function plugin_init_geninventorynumber() {
   global $PLUGIN_HOOKS, $GENINVENTORYNUMBER_TYPES, $CFG_GLPI, $LANG;

   $GENINVENTORYNUMBER_TYPES = array ('Computer', 'Monitor', 'Printer', 'NetworkEquipment', 
                                      'Peripheral', 'Phone', 'SoftwareLicense');

   $plugin = new Plugin();

   if ($plugin->isInstalled('geninventorynumber')) {
      $PLUGIN_HOOKS['migratetypes']['geninventorynumber'] = 'plugin_geninventorynumber_migratetypes';
   }

   if ($plugin->isInstalled('geninventorynumber') && $plugin->isActivated('geninventorynumber')) {
      Plugin::registerClass('PluginGeninventorynumberConfig', 
                            array('addtabon' => $GENINVENTORYNUMBER_TYPES));

      foreach ($GENINVENTORYNUMBER_TYPES as $type) {
         $PLUGIN_HOOKS['item_add']['geninventorynumber'][$type] 
            =  array('PluginGeninventorynumberCommon', 'addItem');
         $PLUGIN_HOOKS['pre_item_update']['geninventorynumber'][$type] 
            =  array('PluginGeninventorynumberCommon', 'preUpdateItem');
      }

   if (Session::getLoginUserID()) {

         $PLUGIN_HOOKS['submenu_entry']['geninventorynumber']['options']['PluginGeninventoryNumberConfig']['title']
                                                   = $LANG["plugin_geninventorynumber"]["title"][1];
         $PLUGIN_HOOKS['submenu_entry']['geninventorynumber']['options']['PluginGeninventoryNumberConfig']['page']
                                                   = '/plugins/geninventorynumber/front/model.php';
         $PLUGIN_HOOKS['submenu_entry']['geninventorynumber']['options']['PluginGeninventoryNumberConfig']['links']['search']
                                                   = '/plugins/geninventorynumber/front/model.php';
         $PLUGIN_HOOKS['submenu_entry']['geninventorynumber']['options']['PluginGeninventoryNumberConfig']['links']['add']
                                                   = '/plugins/geninventorynumber/front/model.form.php';

         $PLUGIN_HOOKS['change_profile']['geninventorynumber'] 
            = array('PluginGeninventoryNumber', 'changeProfile');
   
         if (Session::haveRight("config", "w")) {
            $PLUGIN_HOOKS['config_page']['geninventorynumber'] = 'front/config.php';
         }
      }
   }
}

/**
* Definition of plugin
*
* @return   array Array on informations about plugin
*/
function plugin_version_geninventorynumber() {
   global $LANG;
   return array ('name'           => $LANG["plugin_geninventorynumber"]["title"][1],
                 'minGlpiVersion' => '0.83',
                 'version'        => '1.5.0',
                 'author'         => 'The geninventorynumber team',
                 'license'        => 'GPLv2+',
                 'homepage'       => 'https://forge.indepnet.net/project/show/geninventorynumber');
}

/**
* Prerequisites check
*
* @return   bool  True if plugin can be installed
*/
function plugin_geninventorynumber_check_prerequisites() {
   if (version_compare(GLPI_VERSION,'0.83','lt') || version_compare(GLPI_VERSION,'0.84','ge')) {
      echo "This plugin requires GLPI >= 0.83";
      return false;
   }
   return true;
}

/**
* Compatibility check
*
* @return   bool  True if plugin compatible with configuration
*/
function plugin_geninventorynumber_check_config() {
   return true;
}

?>