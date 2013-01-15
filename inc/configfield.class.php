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
@copyright Copyright (c) 2010-2011 geninventorynumber plugin team
@license   GPLv2+
http://www.gnu.org/licenses/gpl.txt
@link      https://forge.indepnet.net/projects/geninventorynumber
@link      http://www.glpi-project.org/
@since     2009
---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberConfigField extends CommonDBTM {

   function getFromDBbyConfigAndType($config_id,$itemtype) {
      global $DB;

      $query = "SELECT * FROM '".$this->getTable()."' " .
            "WHERE 'config_id' = '" . $config_id . "'
                  AND 'device_type' = '" . $itemtype . "'";
      if ($result = $DB->query($query)) {
         if ($DB->numrows($result) != 1) {
            return false;
         }
         $this->fields = $DB->fetch_assoc($result);
         if (is_array($this->fields) && count($this->fields)) {
            return true;
         } else {
            return false;
         }
      }
      return false;
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

   static function install(Migration $migration) {
      global $DB, $GENINVENTORYNUMBER_TYPES;
      $table = getTableForItemType(__CLASS__);
      
      if (TableExists("glpi_plugin_geninventorynumber_fields")) {
         //Only migrate itemtypes when it's only necessary, otherwise it breaks upgrade procedure !
         $migration->renameTable("glpi_plugin_geninventorynumber_fields", $table);
      }
      if (!TableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` int(11) NOT NULL auto_increment,
            `plugin_geninventorynumber_configs_id` int(11) NOT NULL default '0',
            `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
            `template` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
            `is_active` tinyint(1) NOT NULL default '0',
            `use_index` tinyint(1) NOT NULL default '0',
            `index` bigint(20) NOT NULL default '0',
            PRIMARY KEY  (`id`)
            ) ENGINE=MyISAM  CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($query);
          
      } else {
         $migration->changeField($table, 'ID', 'ID', 'autoincrement');
         $migration->changeField($table, 'config_id', 'plugin_geninventorynumber_configs_id', 'integer');
         if ($migration->changeField($table, 'device_type', 'itemtype', 'string')) {
            Plugin::migrateItemType(array(), array("glpi_displaypreferences"), array($table));
         }
         $migration->changeField($table, 'enabled', 'is_active', 'boolean');
         $migration->changeField($table, 'use_index', 'use_index', 'boolean');
         $migration->migrationOneTable($table);
      }

      $field = new self();
      foreach ($GENINVENTORYNUMBER_TYPES as $type) {
         if (!countElementsInTable($table, "`itemtype`='$type'")) {
            $input["plugin_geninventorynumber_configs_id"] = 1;
            $input["itemtype"]                             = $type;
            $input["template"]                             = "&lt;#######&gt;";
            $input["is_active"]                            = 0;
            $input["index"]                                = 0;
            $field->add($input);
            
         }
      }

   }
    
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }

   static function showForConfig($id) {
      global $LANG, $CFG_GLPI, $DB;
      
      $config = new PluginGeninventorynumberConfig();
      $config->getFromDB($id);
      $fields = self::getFieldInfos($config->fields['field']);
      $target = Toolbox::getItemTypeFormUrl(__CLASS__);

      echo "<form name='form_core_config' method='post' action=\"$target\">";
      echo "<div align='center'>";
      echo "<table class='tab_cadre_fixe' cellpadding='5'>";
      echo "<tr><th colspan='5'>" . $LANG["plugin_geninventorynumber"]["config"][9] . "</th></tr>";
      
      echo "<input type='hidden' name='id' value='$id'>";
      echo "<input type='hidden' name='entities_id' value='0'>";
      
      echo "<tr><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][10];
      echo "</th><th>" . $LANG["common"][60] . "</th>";
      echo "<th>" . $LANG["plugin_geninventorynumber"]["config"][5] . "</th>";
      echo "<th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][6] . "</th></tr>";
      
      foreach ($fields as $type => $value) {
         echo "<td class='tab_bg_1' align='center'>" . $type. "</td>";
         echo "<td class='tab_bg_1'>";
         echo "<input type='hidden' name='ids[$type][id]' value='".$value["id"]."'>";
         echo "<input type='hidden' name='ids[$type][itemtype]' value='$type'>";
         echo "<input type='text' name='ids[$type][template]' value=\"" . $value["template"] . "\">";
         echo "</td>";
         echo "<td class='tab_bg_1' align='center'>";
         Dropdown::showYesNo("ids[$type][is_active]", $value["is_active"]);
         echo "</td>";
         echo "<td class='tab_bg_1' align='center'>";
         Dropdown::showYesNo("ids[$type][use_index]", $value["use_index"]);
         echo "</td>";
         echo "<td class='tab_bg_1'>";
         if ($value["is_active"] && !$value["use_index"]) {
            $disabled = "";
         } else {
            $disabled = "disabled";
         }
      
         echo "<input type='text' name='ids[$type][index]' value='" .
            PluginGeninventorynumberIndex::getIndexByTypeName($type) . "' size='12' " . $disabled . ">";
         echo "</td>";
         echo "</tr>";
      }
      
      echo "<tr class='tab_bg_1'><td align='center' colspan='5'>";
      echo "<input type='submit' name='update_fields' value=\"" . $LANG["buttons"][7] . "\" class='submit'>";
      echo "</td></tr>";
      
      echo "</table>";
      Html::closeForm();
   }

   static function getFieldInfos($field) {
      global $DB;
      $query = "SELECT fields.*
                FROM `glpi_plugin_geninventorynumber_configfields` as fields,
                     `glpi_plugin_geninventorynumber_configs` as config
                WHERE `config`.`field`='$field'
                   AND `config`.`id`=`fields`.`plugin_geninventorynumber_configs_id`
                ORDER BY `fields`.`itemtype`";
      $fields = array();
      foreach ($DB->request($query) as $data) {
         $fields[$data['itemtype']] = $data;
      }
      return $fields;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
       
      $type = get_class($item);
      if ($type == 'PluginGeninventorynumberConfig') {
         return array(1 => $LANG['title'][26]);
      }
      return '';
   }
    
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      $type = get_class($item);
      if ($type == 'PluginGeninventorynumberConfig') {
         $fields = new self();
         $fields->showForConfig($item->getID());
         return true;
      }
   }
}