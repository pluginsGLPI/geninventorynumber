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

/**
 * Deletes plugin rights for profile if deleted
*
* @param	object	CommonDBTM object currently purged (only if 'Profile' type)
* @return	object	returns the input
*/
function plugin_pre_item_purge_geninventorynumber($item) {
   switch (get_class($item)) {
      case 'Profile' :
         PluginGeninventorynumberProfile::cleanProfiles($item->fields["id"]);
         break;
   }
   return $item;
}

/**

/**
 * Deletes an inventory number if user-defined when generation is active
 *
 * @param	object CommonDBTM object to be updated
 * @return	null
 */
function plugin_pre_item_update_geninventorynumber($item) {
   global $GENINVENTORYNUMBER_TYPES, $LANG;

   $type = get_class($item);
   if (isset ($type) && in_array ($type,$GENINVENTORYNUMBER_TYPES)) {
      $fields = plugin_geninventorynumber_getFieldInfos('otherserial');
      $template = Toolbox::addslashes_deep($fields[$type]['template']);
      if ($fields[$type]['enabled'] && $fields[$type]['template'] != '') {
         if (isset ($item->input["otherserial"])) {
            unset ($item->input["otherserial"]);
            $_SESSION["MESSAGE_AFTER_REDIRECT"] = $LANG["plugin_geninventorynumber"]["massiveaction"][2];
         }
      }
   }
   return $item;
}

/**
 * Generates a number for the object juste added
 *
 * @param	object	CommonDBTM object just added
 * @return	null
 */
function plugin_item_add_geninventorynumber($item) {
   global $DB, $LANG;

   $massive_action = false;
   $type = get_class($item);
   $fields = plugin_geninventorynumber_getFieldInfos('otherserial');

   if (isset ($fields[$type])) {
      $config = new PluginGeninventorynumberConfig;
      $config->getFromDb(1);

      //Globally check if auto generation is on
      if ($config->fields['active']) {
         if ($fields[$type]['enabled']) {
            $template = addslashes_deep($fields[$type]['template']);

            $commonitem = new $type;
            $commonitem->getFromDB($item->fields["id"]);

            $generated_field = plugin_geninventorynumber_autoName($template, $type, 0, $commonitem->fields, $fields);

            //Cannot use update() because it'll launch pre_item_update and clean the inventory number...
            $sql = "UPDATE " . $commonitem->getTable() . " SET otherserial='" . $generated_field . "' WHERE id=" . $item->fields["id"];
            $DB->query($sql);

            if (!$massive_action && strstr($_SESSION["MESSAGE_AFTER_REDIRECT"], $LANG["plugin_geninventorynumber"]["massiveaction"][3]) === false)
               $_SESSION["MESSAGE_AFTER_REDIRECT"] .= $LANG["plugin_geninventorynumber"]["massiveaction"][3];

            if ($fields[$type]['use_index'])
               $sql = "UPDATE glpi_plugin_geninventorynumber_configs SET next_number=next_number+1 WHERE FK_entities=0";
            else
               $sql = "UPDATE glpi_plugin_geninventorynumber_indexes SET next_number=next_number+1 WHERE FK_entities=0 AND type='".$type."' AND field='otherserial'";
            $DB->query($sql);
         }
      }
   }
   return $item;
}

/**
 * Alias for plugin_geninventorynumber_Session::haveRight
 *
 * @param	string
 * @param	string
 * @return	bool
 */
function plugin_geninventorynumber_haveTypeRight($type, $right) {
   return plugin_geninventorynumber_Session::haveRight($type, $right);
}

/**
 * Define list of massive actions available through this plugin
 *
 * @param	string	Type of object currently displayed
 * @return	array	list of key(action name) and values (displayed string) for massive actions
 */
function plugin_geninventorynumber_MassiveActions($type) {
   global $LANG, $GENINVENTORYNUMBER_TYPES;

   $values = array ();
   if (in_array($type,$GENINVENTORYNUMBER_TYPES)) {
      $fields = plugin_geninventorynumber_getFieldInfos('otherserial');
      if ($fields[$type]['enabled']) {
         if (plugin_geninventorynumber_Session::haveRight("generate", "w")) {
            $values["plugin_geninventorynumber_generate"] = $LANG["plugin_geninventorynumber"]["massiveaction"][0];
         }
         if (plugin_geninventorynumber_Session::haveRight("generate_overwrite", "w")) {
            $values["plugin_geninventorynumber_generate_overwrite"] = $LANG["plugin_geninventorynumber"]["massiveaction"][1];
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
 * @param	array	Options as designed for MassiveActionsDisplay hook
 * @return	string	HTML code for button or empty string
 */
function plugin_geninventorynumber_MassiveActionsDisplay($options = array()) {
   global $LANG, $GENINVENTORYNUMBER_TYPES;
   if (in_array ($options['itemtype'],$GENINVENTORYNUMBER_TYPES)) {
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
 *	$data structure : array('item'=>array('ID', 'ID2', ...),'itemtype'=>'TypeObjets', 'action'=>'NomAction')
 *
 * @param	array	Massive Actions Parameters (as designed for hook)
 * @return	null
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
                     (($data["action"] == "plugin_geninventorynumber_generate") && isset ($commonitem->fields["otherserial"]) && $commonitem->fields["otherserial"] == "") //Or is overwrite action is selected
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

   if (!plugin_plugin_geninventorynumber_haveRight($module, $right)) {
      // Gestion timeout session
      if (!isset ($_SESSION["glpiID"])) {
         glpi_header($CFG_GLPI["root_doc"] . "/index.php");
         exit ();
      }

      displayRightError();
   }
}

function plugin_geninventorynumber_install() {
   $migration = new Migration("2.0");
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/config.class.php');
   PluginGeninventorynumberConfig::install($migration);
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/profile.class.php');
   PluginGeninventorynumberProfile::install($migration);
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/configfield.class.php');
   PluginGeninventorynumberConfigField::install($migration);
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/index.class.php');
   PluginGeninventorynumberIndex::install($migration);
   return true;
}

function plugin_geninventorynumber_uninstall() {
   $migration = new Migration("2.0");
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/config.class.php');
   PluginGeninventorynumberConfig::uninstall($migration);
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/profile.class.php');
   PluginGeninventorynumberProfile::uninstall($migration);
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/configfield.class.php');
   PluginGeninventorynumberConfigField::uninstall($migration);
   include_once(GLPI_ROOT.'/plugins/geninventorynumber/inc/index.class.php');
   PluginGeninventorynumberIndex::uninstall($migration);
   return true;
}

/**
 * Check if a combination of type/field is already registered in the database
 *
 * @param	string	an object type (litteral) (ex : 'Computer')
 * @param	string	the checked field for this type (default:  'otherserial')
 * @return	int ou bool	 ID of the configuration line in the table or false
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
 * @param	string	an object type (litteral) (ex : 'Computer')
 * @param	string	the checked field for this type (default:  'otherserial')
 * @return	null
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
 * @param	string	an object type (litteral) (ex : 'Computer')
 * @param	string	the checked field for this type (default:  'otherserial')
 * @return	null
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