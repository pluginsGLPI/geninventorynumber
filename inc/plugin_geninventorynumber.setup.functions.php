<?php


/*
   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

function plugin_geninventorynumber_createfirstaccess($ID) {

   global $DB;

   include_once(GLPI_ROOT."/inc/profile.class.php");
   $inventoryProfile = new geninventorynumberProfile;
   if (!$inventoryProfile->getFromDB($ID)) {

      $Profile = new Profile();
      $Profile->getFromDB($ID);
      $name = $Profile->fields["name"];

      $query = "INSERT INTO `glpi_plugin_geninventorynumber_profiles` ( `ID`, `name` , `interface`, `is_default`, `generate`, `generate_overwrite`) VALUES ('$ID', '$name','geninventorynumber','0','w','w');";
      $DB->query($query);
   }
}

function plugin_geninventorynumber_createaccess($ID) {

   global $DB;

   $Profile = new Profile();
   $Profile->getFromDB($ID);
   $name = $Profile->fields["name"];

   $query = "INSERT INTO `glpi_plugin_geninventorynumber_profiles` ( `ID`, `name` , `interface`, `is_default`, `generate`, `generate_overwrite`) VALUES ('$ID', '$name','geninventorynumber','0',NULL,NULL);";
   $DB->query($query);

}

function plugin_geninventorynumber_updatev11() {
   global $DB;

   $sql = " CREATE TABLE `glpi_generateinventorynumber_indexes` (
      `ID` INT( 11 ) NOT NULL AUTO_INCREMENT ,
      `FK_entities` INT( 11 ) NOT NULL DEFAULT '0',
      `type` INT( 11 ) NOT NULL DEFAULT '-1',
      `field` VARCHAR( 255 ) NOT NULL DEFAULT 'otherserial',
      `index` INT( 11 ) NOT NULL DEFAULT '0',
      PRIMARY KEY ( `ID` )
      ) ENGINE = MYISAM CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
   $DB->query($sql) or die($DB->error());
}

function plugin_geninventorynumber_updatev120() {
	global $DB;
   if (TableExists("glpi_plugin_generateinventorynumber")) {
   	$query = "RENAME TABLE `glpi_plugin_generateinventorynumber` TO `glpi_plugin_geninventorynumber`";
      $DB->query($query);
   }
   
   if (TableExists("glpi_plugin_generateinventorynumber_config")) {
      $query = "RENAME TABLE `glpi_plugin_generateinventorynumber_config` TO `glpi_plugin_geninventorynumber_config`";
      $DB->query($query);
   }

   if (TableExists("glpi_plugin_generateinventorynumber_profiles")) {
      $query = "RENAME TABLE `glpi_plugin_generateinventorynumber_profiles` TO `glpi_plugin_geninventorynumber_profiles`";
      $DB->query($query);
   }

   if (TableExists("glpi_plugin_generateinventorynumber_indexes")) {
      $query = "RENAME TABLE `glpi_plugin_generateinventorynumber_indexes` TO `glpi_plugin_geninventorynumber_indexes`";
      $DB->query($query);
   }
}

function plugin_geninventorynumber_updatev130() {
   global $DB;

   $query = "UPDATE `glpi_plugin_geninventorynumber_config` SET `FK_entities`='0' WHERE `FK_entities`='-1'";
   $DB->query($query);
   
   if (!FieldExists("glpi_plugin_geninventorynumber_config","name")) {
      $query = "ALTER TABLE `glpi_plugin_geninventorynumber_config` ADD `name` VARCHAR( 255 ) NOT NULL AFTER `ID`";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_geninventorynumber_config` SET `name`='otherserial'";
      $DB->query($query);
   }

   if (!FieldExists("glpi_plugin_geninventorynumber_config","field")) {
      $query = "ALTER TABLE `glpi_plugin_geninventorynumber_config` ADD `field` VARCHAR( 255 ) NOT NULL AFTER `name`";
      $DB->query($query);
      $query = "UPDATE `glpi_plugin_geninventorynumber_config` SET `field`='otherserial'";
      $DB->query($query);
   }

   if (!FieldExists("glpi_plugin_geninventorynumber_config","comments")) {
      $query = "ALTER TABLE `glpi_plugin_geninventorynumber_config` ADD `comments` TEXT";
      $DB->query($query);
   }
   
   if (!TableExists("glpi_plugin_geninventorynumber_fields")) {
         	$query = "CREATE TABLE IF NOT EXISTS `glpi_plugin_geninventorynumber_fields` (
        `ID` int(11) NOT NULL auto_increment,
        `config_id` int(11) NOT NULL default '0',
        `device_type` int(11) NOT NULL default '0',
        `template` varchar(255) NOT NULL,
        `enabled` smallint(1) NOT NULL default '0',
        `use_index` smallint(1) NOT NULL default '0',
        `index` bigint(20) NOT NULL default '0',
        PRIMARY KEY  (`ID`)
      ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
      $DB->query($query);
      
      $config = new PluginGenInventoryNumberConfig;
      $config->getFromDB(1);
      
      $types = array (
         COMPUTER_TYPE=>"computer",
         MONITOR_TYPE=>"monitor",
         PRINTER_TYPE=>"printer",
         NETWORKING_TYPE=>"networking",
         PERIPHERAL_TYPE=>"peripheral",
         PHONE_TYPE=>"phone");
         $field = new PluginGenInventoryNumberFieldDetail;
      foreach ($types as $type => $value) {
      	$input["config_id"] = 1;
         $input["device_type"] = $type;
         $input["template"] = $config->fields["template_".$value];
         $input["enabled"] = $config->fields[$value."_gen_enabled"];
         $input["index"] = $config->fields[$value."_global_index"];
         $field->add($input);
      }
   }
}


?>