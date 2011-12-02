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
   if (in_array($type,$GENINVENTORYNUMBER_TYPES)) {
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
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG["buttons"][2] . "\" >";
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
* ???
*
* @param
* @return
*/
function plugin_geninventorynumber_checkRight($module, $right) {
   global $CFG_GLPI;

   if (!Session::haveRight($module, $right)) {
      // Gestion timeout session
      if (!isset ($_SESSION["glpiID"])) {
         Html::redirect($CFG_GLPI["root_doc"] . "/index.php");
         exit ();
      }

      Html::displayRightError();
   }
}

/**
* Create database tables for this plugin and updates from older versions
*
* @return   null
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
* @return   null
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



/**
* Check if a combination of type/field is already registered in the database
*
* @param string   an object type (litteral) (ex : 'Computer')
* @param string   the checked field for this type (default:  'otherserial')
* @return   int ou bool  ID of the configuration line in the table or false
*
* TODO: check table joins on this request ?
* NOTE : doesn't seem to be used by this version or version 1.30. Used by other plugins ?
*/
function plugin_geninventorynumber_isTypeRegistered($type, $field = 'otherserial') {
   global $DB;
   $query = "SELECT config.id FROM `glpi_plugin_geninventorynumber_configfields` as fields,
                  `glpi_plugin_geninventorynumber_config` as config
                     WHERE config.field='$field' AND config.ID=fields.config_id
                        ORDER BY fields.device_type";
   $result = $DB->query($query);
   if ($DB->numrows($result)) {
      return $DB->result($result, 0, 'ID');
   } else {
      return false;
   }
}

/**
* Register a combination of type/field into database
*
* @param string   an object type (litteral) (ex : 'Computer')
* @param string   the checked field for this type (default:  'otherserial')
* @return   null
*
* NOTE : doesn't seem to be used by this version or version 1.30. Used by other plugins ?
*/
function plugin_geninventorynumber_registerType($type, $field = 'otherserial') {
   global $DB;
   $config_id = plugin_geninventorynumber_isTypeRegistered($type, $field);
   if ($config_id) {
      $sql = "SELECT id FROM `glpi_plugin_geninventorynumber_configfields` WHERE `config_id`='$config_id' AND `device_type`='$type'";
      $result = $DB->query($sql);
      if (!$DB->numrows($result)) {
         $field = new PluginGeninventorynumberConfigField;

         $input["config_id"] = $config_id;
         $input["device_type"] = $type;
         $input["template"] = "&lt;#######&gt;";
         $input["enabled"] = 0;
         $input["index"] = 0;
         $field->add($input);

         $sql = "INSERT INTO `glpi_plugin_geninventorynumber_indexes` (
                     `id` ,`FK_entities` ,`type` ,`field` ,`next_number`) VALUES (NULL , '0', '$type', 'otherserial', '0');";
         $DB->query($sql) or die($DB->error());
      }
   }
}

/**
* Unregister a combination of type/field into database
*
* @param string   an object type (litteral) (ex : 'Computer')
* @param string   the checked field for this type (default:  'otherserial')
* @return   null
*
*  NOTE : doesn't seem to be used by this version or version 1.30. Used by other plugins ?
*/
function plugin_geninventorynumber_unRegisterType($type, $field = 'otherserial') {
   global $DB;
   $query = "DELETE FROM `glpi_plugin_geninventorynumber_configfields` WHERE device_type='$type'";
   $result = $DB->query($query);

   $query = "DELETE FROM `glpi_plugin_geninventorynumber_indexes` WHERE type='$type' AND field='$field'";
   $result = $DB->query($query);
}
?>