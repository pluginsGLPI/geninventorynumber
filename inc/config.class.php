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

class PluginGeninventorynumberConfig extends CommonDropdown {

   //Menu & navigation
   public $first_level_menu  = "plugins";
   public $second_level_menu = "geninventorynumber";
   public $third_level_menu  = "config"; 
   public $dohistory         = true;
   
   function canCreate() {
       return Session::haveRight("config", "w");
   }

   function canView() {
       return Session::haveRight("config", "r");
   }

   function canDelete() {
       return Session::haveRight("config", "w");
   }
   
   //Remove standard dropdown header
   function title() {
      return "";
   }
   
   //Tabs management
   function defineTabs($options = array()) {
      $tabs = array();
      $this->addStandardTab(__CLASS__, $tabs, $options);
      $this->addStandardTab('Log', $tabs, $options);
      return $tabs;
   }
   
   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType() == __CLASS__) {
         return $LANG["plugin_geninventorynumber"]["config"][7];
      } else {
         return '';
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      switch ($item->getType()) {
         case __CLASS__:
         self::showConfig($item->getID());
         default:
            break;
      }
      return false;
   }
   //End tabs management
   
   static function getTypeName($nb = 1) {
       global $LANG;
       return $LANG["plugin_geninventorynumber"]["title"][1];
   }

   function cleanDBonPurge() {
       $temp = new PluginGeninventorynumberConfigFields();
       $temp->deleteByCriteria(array('plugin_geninventorynumber_configs_id' => $this->fields['id']));
   }

    function getSearchOptions() {
        global $LANG;

        $sopt           = parent::getSearchOptions();

        $sopt['common'] = $LANG["plugin_geninventorynumber"]["title"][1];

        $sopt[3]['table']     = $this->getTable();
        $sopt[3]['field']     = 'is_active';
        $sopt[3]['name']      = $LANG['common'][60];
        $sopt[3]['datatype']  = 'bool';

        $sopt[4]['table']         = $this->getTable();
        $sopt[4]['field']         = 'field';
        $sopt[4]['name']          = $LANG["plugin_geninventorynumber"]["config"][8];
        $sopt[4]['massiveaction'] = false;

        $sopt[5]['table']         = $this->getTable();
        $sopt[5]['field']         = 'next_number';
        $sopt[5]['name']          = $LANG["plugin_geninventorynumber"]["config"][6] . " " . 
            $LANG["common"][59];
        $sopt[5]['massiveaction'] = false;
        $sopt[5]['datatype']      = 'integer';

        return $sopt;
    }

   /**
    * Get configuration id by entity and field
    * 
    * @params $entities_id
    * @params $field
    * 
    * @return the configuration id or false
    */
   static function select($entities_id, $field) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      $query = "SELECT `$table`.`id` FROM `$table` 
                LEFT JOIN `glpi_entities` ON `$table`.`entities_id` = `glpi_entities`.`id` 
                WHERE `field`='$field' ".
                  getEntitiesRestrictRequest(" AND", $table, "entities_id", $entities_id, true).
                "ORDER BY `glpi_entities`.`level` DESC LIMIT 1";
      $results = $DB->query($query);
      if ($DB->numrows($results) > 0) {
         return $DB->result($results, 0, "id");
      } else {
         return false;
      }
   }
   
    function showForm($id, $options=array()) {
        global $LANG, $CFG_GLPI;

        if (!$this->canView()) {
            return false;
        }
        if ($id > 0) {
            $this->check($id, 'r');
        } else {
            // Create item
            $this->check(-1, 'w');
            $this->getEmpty();
        }

        $this->showTabs($options);
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_1'>";
        echo "<td class='tab_bg_1' align='center'>" . $LANG['common'][16] . "</td>";
        echo "<td class='tab_bg_1'>";
        Html::autocompletionTextField($this, "name");
        echo "</td>";
        echo "<td class='tab_bg_1' align='center'>" . 
           $LANG["plugin_geninventorynumber"]["config"][0] . "</td>";
        echo "<td class='tab_bg_1'>";
        Dropdown::showYesNo("is_active", $this->fields["is_active"]);
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td class='tab_bg_1' align='center'>" . 
           $LANG["plugin_geninventorynumber"]["config"][8] . "</td>";
        echo "<td class='tab_bg_1'>";
        self::dropdownFields('field', $this->fields['field']);
        echo "</td>";
        echo "<td class='tab_bg_1' align='center'>" . 
           $LANG["plugin_geninventorynumber"]["config"][6] . " " . $LANG["common"][59] . "</td>";
        echo "<td class='tab_bg_1'>";
        echo "<input type='text' name='next_number' value='" . $this->fields["next_number"] . 
           "' size='12'>&nbsp;";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='tab_bg_1' colspan='4'>";
        echo "<table>";
        echo "<tr>";
        echo "<td class='tab_bg_1'>" . $LANG['common'][25] . "</td><td>";
        echo "<textarea cols='60' rows='4' name='comment' >" . 
           $this->fields["comment"] . "</textarea>";
        echo "</td>";
        echo "</tr>";
        echo "</table>";
        echo "</td>";
        echo "</tr>";
        echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";

        $this->showFormButtons($options);
        $this->addDivForTabs();

        return true;
    }

   static function showConfig($id) {
      global $LANG, $CFG_GLPI, $DB;
      
      $config = new self();
      if ($config->getFromDB($id)) {
         $fields = PluginGeninventorynumberConfigField::getFieldInfos($config->fields['field']);
         
         echo "<form name='form_core_config' method='post' action='".
            Toolbox::getItemTypeFormURL(__CLASS__)."'>";
         echo "<div align='center'>";
         echo "<table class='tab_cadre_fixe' cellpadding='5'>";
         echo "<tr><th colspan='5'>" . $LANG["plugin_geninventorynumber"]["config"][9] . "</th></tr>";
      
         echo "<input type='hidden' name='id' value='$id'>";
         echo "<input type='hidden' name='entities_id' value='0'>";
      
         echo "<tr><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][10] . 
            "</th><th>" . $LANG["common"][60] . "</th>";
         echo "<th>" . $LANG["plugin_geninventorynumber"]["config"][5] . 
            "</th><th colspan='2'>" . $LANG["plugin_geninventorynumber"]["config"][6] . "</th></tr>";
      
         foreach ($fields as $itemtype => $value) {
            echo "<td class='tab_bg_1' align='center'>" . $itemtype::getTypeName(). "</td>";
            echo "<td class='tab_bg_1'>";
            echo "<input type='hidden' name='ids[$itemtype][id]' value='".$value["id"]."'>";
            echo "<input type='hidden' name='ids[$itemtype][itemtype]' value='$itemtype'>";
            echo "<input type='text' name='ids[$itemtype][template]' value=\"" . $value["template"] . "\">";
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            Dropdown::showYesNo("ids[$itemtype][is_active]", $value["is_active"]);
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            Dropdown::showYesNo("ids[$itemtype][use_index]", $value["use_index"]);
            echo "</td>";
            echo "<td class='tab_bg_1'>";
            if ($value["is_active"] && !$value["use_index"]) {
               $disabled = "";
            } else {
               $disabled = "disabled";
            }
      
            echo "<input type='text' name='ids[$itemtype][index]' value='".
               $value['index']."' size='12' " . $disabled . ">";
            echo "</td>";
            echo "</tr>";
         }
      
         echo "<tr class='tab_bg_1'><td align='center' colspan='5'>";
         echo "<input type='submit' name='update_fields' value=\"" . $LANG["buttons"][7] . 
            "\" class='submit'>";
         echo "</td></tr>";
         echo "</table></form>";
      }
   }

   function getSelectLinkedItem() {
       return "SELECT `id`
             FROM `glpi_plugin_geninventorynumber_configfields`
             WHERE config_id='" . $this->fields['id'] . "'";
   }

   /**
   * Dropdown of fields the plugin can manage (generate)
   * 
   * @param string   Name of the dropdown
   * @param string   ??? should be an array of options ?
   * @return   null
   */
   function dropdownFields($name, $value) {
      global $LANG;
      
      $fields = array ('otherserial' => $LANG['common'][20]);
                       //'immo_number' => $LANG['financial'][20]);
      return Dropdown::showFromArray($name, $fields, array('value' => $value));
   }
   
   static function getNextIndex($entities_id, $field, $itemtype = false) {
      $configs_id = self::select($entities_id, $field);
      if ($configs_id) {
         $config = new self();
         $config->getFromDB($configs_id);
         if ($itemtype) {
            return $config->fields['next_number'];
         } else {
            return PluginGeninventorynumberIndex::getIndexByitemtype($configs_id, $itemtype);
         }
      } else {
         return false;
      }
   }
   
   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
   
      //Rename table if it exists when an old name
      if (TableExists("glpi_plugin_generateinventorynumber_config")) {
         $migration->renameTable("glpi_plugin_generateinventorynumber_config", $table);
      }

      if (!TableExists($table)) {
         $sql = "CREATE TABLE IF NOT EXISTS `glpi_plugin_geninventorynumber_configs` (
                    `id` int(11) NOT NULL auto_increment,
                    `name` varchar(255) DEFAULT NULL,
                    `field` varchar(255) DEFAULT NULL,
                    `entities_id` int(11)  NOT NULL default '-1',
                    `is_recursive` tinyint(1)  NOT NULL default '0',
                    `is_active` int(1)  NOT NULL default '0',
                    `next_number` int(11)  NOT NULL default '0',
                    `comment` text NULL,
                    `date_mod` datetime NULL,
                 PRIMARY KEY  (`id`),
                 KEY `entities_id` (`entities_id`),
                 KEY `is_recursive` (`is_recursive`), 
                 KEY `is_active` (`is_active`) 
                 ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($sql) or die($DB->error());
         
         //Add default config
         $tmp['name']         = 'otherserial';
         $tmp['field']        = 'otherserial';
         $tmp['entities_id']  = 0;
         $tmp['is_recursive'] = 1;
         $tmp['is_active']    = 0;
         $tmp['next_number']  = 0;
         $config              = new self();
         $config->add($tmp);
         
      } else {
         $fields = array("template_computer", "template_monitor", "template_printer",
                         "template_peripheral", "template_phone", "template_networking", 
                         "generate_ocs", "generate_data_injection", "generate_internal",
                         "computer_gen_enabled", "monitor_gen_enabled", "printer_gen_enabled",
                         "peripheral_gen_enabled", "phone_gen_enabled", "networking_gen_enabled",
                         "computer_global_index", "monitor_global_index", "printer_global_index",
                         "peripheral_global_index", "phone_global_index", "networking_global_index");
         foreach ($fields as $field) {
            $migration->dropField($table, $field);
         } 
         $migration->addField($table, "field", "string", array('value' => 'otherserial'));
         if ($migration->changeField($table, "FK_entities", "entitites_id", "integer", 
                                 array('value' => -1))) {
            $migration->addKey($table, "entities_id");
         }
         if ($migration->changeField($table, "active", "is_active", "bool")) {
            $migration->addKey($table, "is_active");
         }
         $migration->addField($table, "field", "string");
         if (!$migration->changeField($table, "comments", "comment", "text")) {
            $migration->addField($table, "comment", "text");
         }
         if ($migration->addField($table, "is_recursive", "bool")) {
            $migration->addKey($table, "is_recursive");
         }
         $migration->addField($table, "date_mod", "datetime");
         $migration->migrationOneTable($table);
      }
   }
   
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }
}

?>