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
// Hook done on delete item case
include_once ("config/plugin_generateinventorynumber.define.php");
foreach (glob(GLPI_ROOT . '/plugins/generateinventorynumber/inc/*.php') as $file)
include_once ($file);

function plugin_pre_item_delete_generateinventorynumber($input){
   if (isset($input["_item_type_"]))
   switch ($input["_item_type_"]){
      case PROFILE_TYPE :
         // Manipulate data if needed
         $GenerateInventoryNumberProfile=new GenerateInventoryNumberProfile;
         $GenerateInventoryNumberProfile->cleanProfiles($input["ID"]);
         break;
   }
   return $input;
}

//Define headings added by the plugin
function plugin_get_headings_generateinventorynumber($type,$withtemplate=''){
   global $LANG;

   if (in_array($type,array(PROFILE_TYPE))) {
      // template case
      if ($withtemplate == '') {
         return array();
      }
      // Non template case
      else {
         return array(
            1 => $LANG["plugin_generateinventorynumber"]["title"][1],
         );
      }
   }else {
      return false;
   }


}

// Define headings actions added by the plugin	 
function plugin_headings_actions_generateinventorynumber($type){

   if (in_array($type,array(PROFILE_TYPE))) {
      return array(
         1 => "plugin_headings_generateinventorynumber",
      );
   }else
   {
      return false;
   }

}

// action heading
function plugin_headings_generateinventorynumber($type,$ID,$withtemplate=0){
   global $CFG_GLPI;

   switch ($type) {
      case PROFILE_TYPE :
         $prof=new GenerateInventoryNumberProfile();
         if (!$prof->getFromDB($ID))
         plugin_generateinventorynumber_createaccess($ID);
         $prof->showForm($CFG_GLPI["root_doc"]."/plugins/generateinventorynumber/front/plugin_generateinventorynumber.profile.php",$ID);
         break;
      default :
         break;
   }
}

function plugin_pre_item_update_generateinventorynumber($parm) {
   global $INVENTORY_TYPES,$LANG;

   if (isset ($parm["_item_type_"]) && isset ($INVENTORY_TYPES[$parm["_item_type_"]])) {

      $config = plugin_generateinventorynumber_getConfig(0);
      $template = addslashes_deep($config->fields[plugin_generateinventorynumber_getTemplateFieldByType($parm["_item_type_"])]);

      if (plugin_generateinventorynumber_isActive($parm["_item_type_"]) && $template != '') {
         if (isset ($parm["otherserial"]))
         {
            unset ($parm["otherserial"]);
            $_SESSION["MESSAGE_AFTER_REDIRECT"]=$LANG["plugin_generateinventorynumber"]["massiveaction"][2];
         }

      }
   }

   return $parm;
}


// Define rights for the plugin types
function plugin_generateinventorynumber_haveTypeRight($type, $right) {
   return plugin_generateinventorynumber_haveRight($type, $right);
}

function plugin_generateinventorynumber_MassiveActions($type) {
   global $LANG, $INVENTORY_TYPES;

   $values = array();
   if (isset ($INVENTORY_TYPES[$type]) && plugin_generateinventorynumber_isActive($type)) {
      if (plugin_generateinventorynumber_haveRight("generate", "w")) {
         $values["plugin_generateinventorynumber_generate"] = $LANG["plugin_generateinventorynumber"]["massiveaction"][0];
      }

      if (isset ($INVENTORY_TYPES[$type]) && plugin_generateinventorynumber_haveRight("generate_overwrite", "w")) {
         $values["plugin_generateinventorynumber_generate_overwrite"] = $LANG["plugin_generateinventorynumber"]["massiveaction"][1];
      }

      return $values;
   } else
   return array ();
}

function plugin_generateinventorynumber_MassiveActionsDisplay($type, $action) {
   global $LANG, $INVENTORY_TYPES;

   if (isset ($INVENTORY_TYPES[$type])) {
      switch ($action) {
         case "plugin_generateinventorynumber_generate" :
            case "plugin_generateinventorynumber_generate_overwrite" :
               echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" . $LANG["buttons"][2] . "\" >";
               break;
            default :
               break;
         }
      }
      return "";
   }

function plugin_generateinventorynumber_MassiveActionsProcess($data) {
      global $DB, $INVENTORY_TYPES;

      switch ($data['action']) {
         case "plugin_generateinventorynumber_generate" :
            case "plugin_generateinventorynumber_generate_overwrite" :
               foreach ($data["item"] as $key => $val) {
                  if ($val == 1) {

                     $commonitem = new CommonItem;
                     $commonitem->getFromDB($data['device_type'], $key);
                     if (//Only generates inventory number for object without it !
                        (($data["action"] == "plugin_generateinventorynumber_generate") && isset ($commonitem->obj->fields["otherserial"]) && $commonitem->obj->fields["otherserial"] == "") //Or is overwrite action is selected
                        || ($data["action"] == "plugin_generateinventorynumber_generate_overwrite")) {
                        $parm["ID"] = $key;
                        $parm["type"] = $data['device_type'];
                        plugin_item_add_generateinventorynumber($parm,true);
                     }
                  }
               }
               break;
            default :
               break;
         }
      }

function plugin_generateinventorynumber_checkRight($module, $right) {
         global $CFG_GLPI;

         if (!plugin_plugin_generateinventorynumber_haveRight($module, $right)) {
            // Gestion timeout session
            if (!isset ($_SESSION["glpiID"])) {
               glpi_header($CFG_GLPI["root_doc"] . "/index.php");
               exit ();
            }

            displayRightError();
         }
      }

function plugin_generateinventorynumber_Install() {

         global $DB, $INVENTORY_TYPES;

         if(!TableExists("glpi_plugin_generateinventorynumber")){
            $sql = "CREATE TABLE IF NOT EXISTS `glpi_plugin_generateinventorynumber_config` (
              `ID` int(11) NOT NULL auto_increment,
              `FK_entities` int(11)  NOT NULL default -1,
              `active` int(1)  NOT NULL default 0,
              `template_computer` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
              `template_monitor` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
              `template_printer` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
              `template_peripheral` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
              `template_phone` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
              `template_networking` varchar(255)  collate utf8_unicode_ci NOT NULL default '',
              `generate_ocs` int(1)  NOT NULL default 1,
              `generate_data_injection` int(1)  NOT NULL default 1,
              `generate_internal` int(1)  NOT NULL default 1,
              `computer_gen_enabled` int(1)  NOT NULL default 1,
              `monitor_gen_enabled` int(1)  NOT NULL default 1,
              `printer_gen_enabled` int(1)  NOT NULL default 1,
              `peripheral_gen_enabled` int(1)  NOT NULL default 1,
              `phone_gen_enabled` int(1)  NOT NULL default 1,
              `networking_gen_enabled` int(1)  NOT NULL default 1,
              `computer_global_index` int(1)  NOT NULL default 1,
              `monitor_global_index` int(1)  NOT NULL default 1,
              `printer_global_index` int(1)  NOT NULL default 1,
              `peripheral_global_index` int(1)  NOT NULL default 1,
              `phone_global_index` int(1)  NOT NULL default 1,
              `networking_global_index` int(1)  NOT NULL default 1,
              `next_number` int(11)  NOT NULL default 0,
              PRIMARY KEY  (`ID`)
            ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($sql) or die($DB->error());

            $sql = "INSERT INTO `glpi_plugin_generateinventorynumber_config` (
               `ID` ,`FK_entities` ,`active` ,`template_computer` ,`template_monitor` ,`template_printer` ,
               `template_peripheral` ,`template_phone` ,`template_networking`,
               `generate_ocs`,`generate_data_injection`,`generate_internal`,
               `computer_gen_enabled`,`monitor_gen_enabled`,`printer_gen_enabled`,`peripheral_gen_enabled`,`phone_gen_enabled`,`networking_gen_enabled`,
               `computer_global_index`,`monitor_global_index`,`printer_global_index`,`peripheral_global_index`,`phone_global_index`,`networking_global_index`,
               `next_number`)
               VALUES (NULL , '-1', '0', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;', '&lt;#######&gt;',
               '1','1','1','1','1','1','1','1','1','1','1','1','1','1','1','0');";
            $DB->query($sql) or die($DB->error());

            $sql = "
            CREATE TABLE  IF NOT EXISTS `glpi_plugin_generateinventorynumber_profiles` (
              `ID` int(11) NOT NULL auto_increment,
              `name` varchar(255) default NULL,
              `interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'generateinventorynumber',
              `is_default` int(6) NOT NULL default '0',
              `generate` char(1) default NULL,
              `generate_overwrite` char(1) default NULL,
              PRIMARY KEY  (`ID`)
            ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
            $DB->query($sql) or die($DB->error());

            $sql = "CREATE TABLE  IF NOT EXISTS `glpi_plugin_generateinventorynumber_indexes` (
            `ID` INT( 11 ) NOT NULL AUTO_INCREMENT ,
            `FK_entities` INT( 11 ) NOT NULL DEFAULT '0',
            `type` INT( 11 ) NOT NULL DEFAULT '-1',
            `field` VARCHAR( 255 ) NOT NULL DEFAULT 'otherserial',
            `next_number` INT( 11 ) NOT NULL DEFAULT '0',
            PRIMARY KEY ( `ID` )
            ) ENGINE = MYISAM CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
            $DB->query($sql) or die($DB->error());

            foreach ($INVENTORY_TYPES as $type => $name) {
               $sql = "INSERT INTO `glpi_plugin_generateinventorynumber_indexes` (
            `ID` ,`FK_entities` ,`type` ,`field` ,`next_number`) VALUES (NULL , '0', $type, 'otherserial', '0');";
               $DB->query($sql) or die($DB->error());
            }

            plugin_generateinventorynumber_createfirstaccess($_SESSION['glpiactiveprofile']['ID']);
         }
         else
         {
            plugin_generateinventorynumber_updatev11();
         }

         return true;
      }

function plugin_generateinventorynumber_Uninstall() {

         global $DB;
         $tables = array("glpi_plugin_generateinventorynumber_config","glpi_plugin_generateinventorynumber_profiles","glpi_plugin_generateinventorynumber_indexes");

         foreach ($tables as $table) {
            $DB->query("DROP TABLE `$table`;") or die($DB->error());
         }
      }
?>