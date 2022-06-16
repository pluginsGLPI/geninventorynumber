<?php

/**
 * -------------------------------------------------------------------------
 * GenInventoryNumber plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of GenInventoryNumber.
 *
 * GenInventoryNumber is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * GenInventoryNumber is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with GenInventoryNumber. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2008-2022 by GenInventoryNumber plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/geninventorynumber
 * -------------------------------------------------------------------------
 */

use Glpi\Application\View\TemplateRenderer;

if (!defined('GLPI_ROOT')) {
    die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberConfig extends CommonDBTM {

   static $rightname = 'config';
   public $dohistory = true;

   /**
    * Never reset the index
    * @var int
    */
   public const AUTO_RESET_NONE = 0;
   /**
    * Reset the index if the last number was generated on a different day
    * @var int
    */
   public const AUTO_RESET_DAILY = 1;
   /**
    * Reset the index if the last number was generated on a different month
    * @var int
    */
   public const AUTO_RESET_MONTHLY = 2;
   /**
    * Reset the index if the last number was generated on a different year
    * @var int
    */
   public const AUTO_RESET_YEARLY = 3;

   static function getTypeName($nb = 0) {
      return __('Inventory number generation', 'geninventorynumber');
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addStandardTab(__CLASS__, $ong, $options);
      $this->addStandardTab("PluginGeninventorynumberConfigField", $ong, $options);
      $this->addStandardTab("Log", $ong, $options);
      return $ong;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (get_class($item) == __CLASS__) {
         $array_ret = [];
         $array_ret[0] = __('General setup');
         $array_ret[1] = __('GLPI\'s inventory items configuration', 'geninventorynumber');
         return $array_ret;
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($tabnum) {
         case 0:
            $item->showForm(1);
            break;
         case 1:
            PluginGeninventorynumberConfigField::showForConfig($item->getID());
            break;
      }
      return true;
   }

   function rawSearchOptions() {
      $sopt = [];

      $sopt[] = [
         'id'                 => 'common',
         'name'               => __('Inventory number generation', 'geninventorynumber'),
      ];

      $sopt[] = [
         'id'                 => '1',
         'table'              => $this->getTable(),
         'field'              => 'name',
         'name'               => __('Field'),
         'datatype'           => 'itemlink',
      ];

      $sopt[] = [
         'id'                 => '2',
         'table'              => $this->getTable(),
         'field'              => 'is_active',
         'name'               => __('Active', 'geninventorynumber'),
         'datatype'           => 'bool',
      ];

      $sopt[] = [
         'id'                 => '3',
         'table'              => $this->getTable(),
         'field'              => 'index',
         'name'               => __('Global index position', 'geninventorynumber'),
      ];

      return $sopt;
   }

    /**
     * Get all auto-reset options
     * @return array
     */
   public static function getAutoResetOptions(): array {
       return [
           self::AUTO_RESET_NONE => __('Never', 'geninventorynumber'),
           self::AUTO_RESET_DAILY => __('Daily', 'geninventorynumber'),
           self::AUTO_RESET_MONTHLY => __('Monthly', 'geninventorynumber'),
           self::AUTO_RESET_YEARLY => __('Yearly', 'geninventorynumber'),
       ];
   }

   function showForm($id, $options = []) {
      if ($id > 0) {
          $this->getFromDB($id);
      } else {
          $this->getEmpty();
      }

      $options['candel'] = false;

      $twig = TemplateRenderer::getInstance();
      $twig->display('@geninventorynumber/config.html.twig', [
         'item'               => $this,
         'params'             => $options,
         'auto_reset_methods' => self::getAutoResetOptions(),
      ]);
      return true;
   }

   /**
    * Check if the index needs to be reset based on the configured auto-reset method
    * @return bool
    */
   static function needIndexReset(): bool {
       $config = new self();
       $config->getFromDB(1);

       if (
           $config->fields['auto_reset_method'] === self::AUTO_RESET_NONE
           || $config->fields['date_last_generated'] === null
       ) {
           return false;
       }

       $current_date = strtotime($_SESSION['glpi_currenttime']);
       $last_gen_date = strtotime($config->fields['date_last_generated']);

       switch ($config->fields['auto_reset_method']) {
           case self::AUTO_RESET_DAILY:
               return date('Y-m-d', $last_gen_date) !== date('Y-m-d', $current_date);
           case self::AUTO_RESET_MONTHLY:
               return date('Y-m', $last_gen_date) !== date('Y-m', $current_date);
           case self::AUTO_RESET_YEARLY:
               return date('Y', $last_gen_date) !== date('Y', $current_date);
       }
       return false;
   }

   /**
    * Reset the index to 0 and reset the last generated date
    */
   public static function resetIndex(): void {
       global $DB;

       $DB->update(self::getTable(), [
           'index' => 0,
           'date_last_generated' => $_SESSION['glpi_currenttime']
       ], [
           'id' => 1
       ]);
   }

   static function getNextIndex() {
      global $DB;

      if (self::needIndexReset()) {
          self::resetIndex();
      }

      $query = "SELECT `index`
                FROM `".getTableForItemType(__CLASS__)."`";
      $results = $DB->query($query);
      if ($DB->numrows($results)) {
         return ($DB->result($results, 0, 'index') + 1);
      } else {
         return 0;
      }
   }

   static function install(Migration $migration) {
      global $DB;

      $default_charset = DBConnection::getDefaultCharset();
      $default_collation = DBConnection::getDefaultCollation();
      $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

      $table = getTableForItemType(__CLASS__);
      if ($DB->tableExists("glpi_plugin_generateinventorynumber_config")) {
         $fields = ['template_computer', 'template_monitor', 'template_printer',
                     'template_peripheral', 'template_phone' , 'template_networking',
                     'generate_ocs', 'generate_data_injection', 'generate_internal',
                     'computer_gen_enabled', 'monitor_gen_enabled', 'printer_gen_enabled',
                     'peripheral_gen_enabled', 'phone_gen_enabled', 'networking_gen_enabled',
                     'computer_global_index', 'monitor_global_index', 'printer_global_index',
                     'peripheral_global_index', 'phone_global_index',
                     'networking_global_index'];
         foreach ($fields as $field) {
            $migration->dropField("glpi_plugin_generateinventorynumber_config", $field);
         }
         $migration->renameTable("glpi_plugin_generateinventorynumber_config", $table);
      }

      if ($DB->tableExists("glpi_plugin_geninventorynumber_config")) {
         $migration->renameTable("glpi_plugin_geninventorynumber_config", $table);
      }

      if (!$DB->tableExists($table)) {
         $sql = "CREATE TABLE IF NOT EXISTS `$table` (
             `id` int {$default_key_sign} NOT NULL auto_increment,
             `name`  varchar(255) DEFAULT '',
             `is_active` tinyint  NOT NULL default 0,
             `index` int  NOT NULL default 0,
             `comment` text,
             `date_last_generated` timestamp NULL DEFAULT NULL,
             `auto_reset_method` int unsigned NOT NULL default '0',
             PRIMARY KEY  (`id`)
             ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
         $DB->query($sql) or die($DB->error());

         $tmp['id']           = 1;
         $tmp['name']         = 'otherserial';
         $tmp['is_active']    = 1;
         $tmp['index']        = 0;
         $config = new self();
         $config->add($tmp);
      } else {
         $migration->addField($table, 'name', 'string', ['value' => 'otherserial']);
         $migration->addField($table, 'field', 'string', ['value' => 'otherserial']);
         $migration->changeField($table, 'ID', 'id', 'autoincrement');
         $migration->changeField($table, 'active', 'is_active', 'bool');
         if (!$migration->addField($table, 'comment', 'text')) {
            $migration->changeField($table, 'comments', 'comment', 'text');
         }
         $migration->changeField($table, 'is_active', 'is_active', 'bool');
         $migration->changeField($table, 'next_number', 'index', 'integer');
         $migration->dropField($table, 'field');

         $migration->dropField($table, 'FK_entities');
         $migration->dropField($table, 'entities_id');
         $migration->addField($table, 'date_last_generated', 'timestamp');
         $migration->addField($table, 'auto_reset_method', "int unsigned NOT NULL default '0'");
      }

      //Remove unused table
      if ($DB->tableExists('glpi_plugin_geninventorynumber_indexes')) {
         $migration->dropTable('glpi_plugin_geninventorynumber_indexes');
      }
      $migration->migrationOneTable($table);
   }

   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }

   static function updateIndex() {
      global $DB;

      $query = "UPDATE `".getTableForItemType(__CLASS__)."`
                SET `index`=`index`+1,`date_last_generated`='{$_SESSION['glpi_currenttime']}'
                WHERE `is_active`='1'";
      $DB->query($query);
   }

   static function isGenerationActive() {
      $config = new self();
      $config->getFromDB(1);
      return $config->fields['is_active'];
   }

   static function getIcon() {
      return "fas fa-random";
   }
}
