<<?php
/*
 * @version $Id$
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

class PluginGeninventorynumberIndex extends CommonDBTM {

   static function updateIndex($new_value, $itemtype) {
      global $DB;
      $query = "UPDATE `".getTableForItemType(__CLASS__)."`
                SET `next_number`='$new_value'
                WHERE `itemtype`='$itemtype'";
      $DB->query($query);
   }
      
   static function getTypeName() {
       global $LANG;
       return $LANG['plugin_geninventorynumber']['types'][2];
   }

   static function getIndexByTypeName($itemtype, $field = 'otherserial') {
      global $DB;
        
      $query = "SELECT `next_number`
                FROM `glpi_plugin_geninventorynumber_indexes`
                WHERE `itemtype`='$itemtype' AND `field`='$field'";
      $result = $DB->query($query);
      if (!$DB->numrows($result)) {
         return 0;
      } else {
         return $DB->result($result, 0, "next_number");
      }
   }

   static function install(Migration $migration) {
      global $DB, $GENINVENTORYNUMBER_TYPES;
      $table = getTableForItemType(__CLASS__);
       
      if (TableExists("glpi_plugin_generateinventorynumber_indexes")) {
         $migration->renameTable("glpi_plugin_generateinventorynumber_indexes", $table);
      }
       
       if (!TableExists($table)) {
          $sql = "CREATE TABLE  IF NOT EXISTS `$table` (
                    `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                    `entities_id` INT( 11 ) NOT NULL DEFAULT '0',
                    `itemtype` varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
                    `field` varchar(255) COLLATE utf8_unicode_ci DEFAULT 'otherserial',
                   `next_number` INT( 11 ) NOT NULL DEFAULT '0',
                   PRIMARY KEY ( `id` )
                  ) ENGINE = MYISAM CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
         $DB->query($sql) or die($DB->error());

         $index = new self();
         foreach ($GENINVENTORYNUMBER_TYPES as $type) {
            $tmp['entities_id']  = 0;
            $tmp['itemtype']     = $type;
            $tmp['field']        = 'otherserial';
            $tmp['next_number']  = 0;
            $index->add($tmp);
         }
      } else {
         $migration->changeField($table, 'ID', 'ID', 'autoincrement');
         $migration->changeField($table, 'FK_entities', 'entities_id', 'integer');
         $migration->changeField($table, 'field', 'field', 'string',
                                 array('value' => 'otherserial'));
         if ($migration->changeField($table, 'type', 'itemtype', 'string')) {
            $migration->migrationOneTable($table);
            Plugin::migrateItemType(array(), array("glpi_displaypreferences"), array($table));
         }
      }
      $migration->migrationOneTable($table);
   }
    
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }
}