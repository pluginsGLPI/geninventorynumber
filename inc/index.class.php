<?php

/*
 * @version $Id$
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

class PluginGeninventorynumberIndex extends CommonDBTM {
   
   static function install(Migration $migration) {
      global $GENINVENTORYNUMBER_TYPES, $DB;

      $table = getTableForItemType(__CLASS__);

      if (TableExists("glpi_plugin_generateinventorynumber_indexes")) {
         $migration->renameTable("glpi_plugin_generateinventorynumber_indexes", $table);
      }

      if (!TableExists($table)) {
         $sql = " CREATE TABLE IF NOT EXISTS `$table` (
                  `id` INT( 11 ) NOT NULL AUTO_INCREMENT ,
                  `entities_id` INT( 11 ) NOT NULL DEFAULT '0',
                  `type` INT( 11 ) NOT NULL DEFAULT '-1',
                  `field` VARCHAR( 255 ) NOT NULL DEFAULT 'otherserial',
                  `index` INT( 11 ) NOT NULL DEFAULT '0',
                  PRIMARY KEY ( `id` )
                  ) ENGINE = MYISAM CHARSET=utf8 COLLATE=utf8_unicode_ci; ";
         $DB->query($sql) or die($DB->error());
     } else {
        $migration->changeField($table, "ID", "id", "integer", "NOT NULL auto_increment");
        $migration->changeField($table, "FK_entities", "entities_id", "integer");
        $migration->addKey($table, "entities_id");
        $migration->migrationOneTable($table);
     }
   }
   
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }
}