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


/**
* Migration operations from version 1.1.0 to 1.2.0
* 
* @return	null
*/
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

/**
* Migration operations from version 1.2.0 to 1.3.0
* 
* @return	null
*/
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
      
      $query = "ALTER TABLE `glpi_plugin_geninventorynumber_config`
                 DROP `template_computer`,
                 DROP `template_monitor`,
                 DROP `template_printer`,
                 DROP `template_peripheral`,
                 DROP `template_phone`,
                 DROP `template_networking`,
                 DROP `generate_ocs`,
                 DROP `generate_data_injection`,
                 DROP `generate_internal`,
                 DROP `computer_gen_enabled`,
                 DROP `monitor_gen_enabled`,
                 DROP `printer_gen_enabled`,
                 DROP `peripheral_gen_enabled`,
                 DROP `phone_gen_enabled`,
                 DROP `networking_gen_enabled`,
                 DROP `computer_global_index`,
                 DROP `monitor_global_index`,
                 DROP `printer_global_index`,
                 DROP `peripheral_global_index`,
                 DROP `phone_global_index`,
                 DROP `networking_global_index`;";
      $DB->query($query);
   }
}
/**
* Migration operations from version 1.3.0 to 1.4.0
* 
* @return	null
*/
function plugin_geninventorynumber_updatev140() {
	global $DB;
	
	//Rename _config table to _configs
	if (TableExists("glpi_plugin_geninventorynumber_config")) {
		$query = "RENAME TABLE `glpi_plugin_geninventorynumber_config` TO `glpi_plugin_geninventorynumber_configs`";
		$DB->query($query);
	}
	
	//Rename _fields table to _configfields
	if (TableExists("glpi_plugin_geninventorynumber_fields")) {
		$query = "RENAME TABLE `glpi_plugin_geninventorynumber_fields` TO `glpi_plugin_geninventorynumber_configfields`";
		$DB->query($query);
	}
   
	//Tables : change ID column to id
	$query = "ALTER TABLE glpi_plugin_geninventorynumber_configs CHANGE ID id INTEGER" ;
	$DB->query($query) or die($DB->error());
	$query = "ALTER TABLE glpi_plugin_geninventorynumber_profiles CHANGE ID id INTEGER" ;
	$DB->query($query) or die($DB->error());
	$query = "ALTER TABLE glpi_plugin_geninventorynumber_configfields CHANGE ID id INTEGER" ;
	$DB->query($query) or die($DB->error());
	$query = "ALTER TABLE glpi_plugin_geninventorynumber_indexes CHANGE ID id INTEGER" ;
	$DB->query($query) or die($DB->error());
	
	
	//Change device_type and type columns (from "integer" to "varchar")
	$query = "SELECT * FROM glpi_plugin_geninventorynumber_configfields WHERE device_type=1";
	$result = $DB->query($query);
	if ($DB->numrows($result)) {
		$query = "ALTER TABLE glpi_plugin_geninventorynumber_configfields MODIFY device_type VARCHAR(250)";
		$DB->query($query) or die($DB->error());
		
		$query = "ALTER TABLE glpi_plugin_geninventorynumber_indexes MODIFY type VARCHAR(250)";
		$DB->query($query) or die($DB->error());
		
		//Transforms common equipment types from older values (integers) to new values (string)
		$TRANSLATION_VALUES = array();
		$TRANSLATION_VALUES['1'] = 'Computer';
		$TRANSLATION_VALUES['2'] = 'NetworkEquipment';
		$TRANSLATION_VALUES['3'] = 'Printer';
		$TRANSLATION_VALUES['4'] = 'Monitor';
		$TRANSLATION_VALUES['5'] = 'Peripheral';
		$TRANSLATION_VALUES['23'] = 'Phone';
		 
		foreach ($TRANSLATION_VALUES as $key=>$value) {
			$query = "UPDATE glpi_plugin_geninventorynumber_configfields SET device_type = '$value' WHERE device_type='$key'";
			$DB->query($query) or die($DB->error());
			
			$query = "UPDATE glpi_plugin_geninventorynumber_indexes SET type = '$value' WHERE type='$key'";
			$DB->query($query) or die($DB->error());
		}
	}

}

?>