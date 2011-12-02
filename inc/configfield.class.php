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


if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberConfigField extends CommonDBChild {

   // Mapping between DB fields
   public $itemtype = 'PluginGeninventorynumberConfig'; // Class name or field name (start with itemtype) for link to Parent
   public $items_id = 'plugin_geninventorynumber_configs_id'; // Field name

   static function select($entities_id, $itemtype, $field) {
    $config = getAllDatasFromTable(getTableForItemType(__CLASS__), 
                                   "`entities_id`='$entities_id' AND `field`='$field' " .
                                       "AND `itemtype`='$itemtype'");
     if (!empty($config)) {
         return array_pop($config);
     } else {
        return false;
     }
   }
   
   function canCreate() {
      return Session::haveRight("config", "w");
   }

   function canView() {
      return Session::haveRight("config", "r");
   }
   
   function canDelete() {
     return Session::haveRight("config", "w");
   }
   
   static function getTypeName() {
      global $LANG;
      return $LANG['plugin_geninventorynumber']['types'][1];
   }

   /**
   * Get all configfields for a given field (ex : 'otherserial')
   *
   *  Return array format: $item[type] = line
   *     where type is object type (ex. 'Computer') 
   *     and line issued from table plugin_geninventorynumber_configfields
   *
   * @param string   Name of the field(ex : 'otherserial')  
   * @return   array List of configfields
   */
   static function getFieldInfos($field) {
      global $DB;

      $fields = array();
      $query = "SELECT fields.* 
                FROM `glpi_plugin_geninventorynumber_configfields` as fields,
                  `glpi_plugin_geninventorynumber_configs` as config
                     WHERE `config`.`field`='$field' 
                        AND `config`.`id`=`fields`.`plugin_geninventorynumber_configs_id`
                ORDER BY `fields`.`itemtype`";
      foreach ($DB->request($query) as $data) {
         $fields[$data['itemtype']] = $data;
      }
      return $fields;
   }

   static function install(Migration $migration) {
      global $GENINVENTORYNUMBER_TYPES, $DB;
      
      $table = getTableForItemType(__CLASS__);
      if (TableExists("glpi_plugin_geninventorynumber_fields")) {
         $migration->renameTable("glpi_plugin_geninventorynumber_fields", $table);
      }
      
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                 `id` int(11) NOT NULL auto_increment,
                 `plugin_geninventorynumber_configs_id` int(11) NOT NULL default '0',
                 `itemtype` int(11) NOT NULL default '0',
                 `template` varchar(255) NOT NULL,
                 `is_active` smallint(1) NOT NULL default '0',
                 `use_index` smallint(1) NOT NULL default '0',
                 `index` bigint(20) NOT NULL default '0',
                 PRIMARY KEY  (`ID`),
                 KEY `is_active` (`is_active`) 
               ) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
         $DB->query($query);
         
         $field = new self();
         foreach ($GENINVENTORYNUMBER_TYPES as $itemtype) {
            if ($configs = PluginGeninventorynumberConfig::select(0, 'otherserial')) {
               $input["plugin_geninventorynumber_configs_id"] = $configs['id'];
               $input["template"] = "&lt;#######&gt;";
               $input["is_active"] = 0;
               $input["use_index"] = 0;
               $input["index"]     = 0;
               $field->add($input);
            }
         }
      } else {
         $migration->changeField($table, "ID", "id", "integer", "NOT NULL auto_increment");
         $migration->changeField($table, "device_type", "itemtype", "string");
         if ($migration->changeField($table, "enabled", "is_active", "bool")) {
            $migration->addKey($table, "is_active");
         }
         $migration->changeField($table, "config_id", "plugin_geninventorynumber_configs_id", 
                                 "integer");
         $migration->migrationOneTable($table);
      }
   }
   
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }
}