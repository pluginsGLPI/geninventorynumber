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
* Define list of massive actions available through this plugin
*
* @param string   Type of object currently displayed
* @return   array list of key(action name) and values (displayed string) for massive actions
*/
function plugin_geninventorynumber_MassiveActions($type) {
   global $LANG, $GENINVENTORYNUMBER_TYPES;

   $values = array ();
   if (in_array($type, $GENINVENTORYNUMBER_TYPES)) {
      $fields = plugin_geninventorynumber_getFieldInfos('otherserial');
      if ($fields[$type]['enabled']) {
         if (Session::haveRight("plugin_geninventorynumber_generate", "w")) {
            $values["plugin_geninventorynumber_generate"] 
               = $LANG["plugin_geninventorynumber"]["massiveaction"][0];
         }
         if (Session::haveRight("plugin_geninventorynumber_generate_overwrite", "w")) {
            $values["plugin_geninventorynumber_generate_overwrite"] 
               = $LANG["plugin_geninventorynumber"]["massiveaction"][1];
         }
         return $values;
      }
      else {
         return array ();
      }
   } else {
      return array ();
   }
}

/**
* Shows validate button when plugin massive action selected in dropdown
*
* @param array Options as designed for MassiveActionsDisplay hook
* @return   string   HTML code for button or empty string
*/
function plugin_geninventorynumber_MassiveActionsDisplay($options = array()) {
   global $LANG, $GENINVENTORYNUMBER_TYPES;
   
   if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($options['action']) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_generate_overwrite" :
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" " .
                  "class=\"submit\" value=\"" . $LANG["buttons"][2] . "\" >";
            break;
         default :
            break;
      }
   }
   return "";
}

/**
* Executes massive actions for this plugin
*
*  $data structure : array('item'=>array('ID', 'ID2', ...),'itemtype'=>'TypeObjets', 'action'=>'NomAction')
*
* @param array Massive Actions Parameters (as designed for hook)
* @return   null
*/
function plugin_geninventorynumber_MassiveActionsProcess($data) {
   global $DB;

   switch ($data['action']) {
      case "plugin_geninventorynumber_generate" :
      case "plugin_geninventorynumber_generate_overwrite" :
         foreach ($data["item"] as $key => $val) {
            if ($val == 1) {
               $commonitem = new $data['itemtype'];
               $commonitem->getFromDB($key);
               if (//Only generates inventory number for object without it !
                (($data["action"] == "plugin_geninventorynumber_generate") 
                  && isset ($commonitem->fields["otherserial"]) 
                     && $commonitem->fields["otherserial"] == "") //Or is overwrite action is selected
               || ($data["action"] == "plugin_geninventorynumber_generate_overwrite")) {
                  plugin_item_add_geninventorynumber($commonitem);
               }
            }
         }
         break;
      default :
         break;
   }
}

/**
* Create database tables for this plugin and updates from older versions
*
* @return   true
*
*/
function plugin_geninventorynumber_Install() {;
   global $CFG_GLPI;
   
   $migration = new Migration('1.5.0');
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/profile.class.php");
   PluginGeninventorynumberProfile::install($migration);
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/config.class.php");
   PluginGeninventorynumberConfig::install($migration);
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/configfield.class.php");
   PluginGeninventorynumberConfigField::install($migration);
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/index.class.php");
   PluginGeninventorynumberIndex::install($migration);
   
   return true;
}

/**
* Destroys database tables on uninstall
*
* @return   true
*/
function plugin_geninventorynumber_Uninstall() {
   
   $migration = new Migration('1.5.0');
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/profile.class.php");
   PluginGeninventorynumberProfile::uninstall($migration);
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/config.class.php");
   PluginGeninventorynumberConfig::uninstall($migration);
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/configfield.class.php");
   PluginGeninventorynumberConfigField::uninstall($migration);
   include_once(GLPI_ROOT."/plugins/geninventorynumber/inc/index.class.php");
   PluginGeninventorynumberIndex::uninstall($migration);
 
   return true;
}

?>