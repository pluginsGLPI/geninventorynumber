<<<<<<< .mine
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

class PluginGeninventorynumberConfig extends CommonDBTM {

    function defineTabs($options=array()) {
        global $LANG;
        $ong[1] = $LANG["plugin_geninventorynumber"]["config"][7];
        return $ong;
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
        return $LANG['plugin_geninventorynumber']['types'][0];
    }

    function cleanDBonPurge() {
        $temp = new PluginGeninventorynumberConfigFields();
        $temp->deleteByCriteria(array('config_id' => $this->fields['id']));
    }

    function getSearchOptions() {
        global $LANG;

        $sopt = array();
        $sopt['common'] = $LANG["plugin_geninventorynumber"]["title"][1];

        $sopt[1]['table'] = $this->gettable();
        $sopt[1]['field'] = 'name';
        $sopt[1]['name'] = $LANG['common'][16];
        $sopt[1]['datatype'] = 'itemlink';

        $sopt[2]['table'] = $this->gettable();
        $sopt[2]['field'] = 'is_active';
        $sopt[2]['name'] = $LANG['common'][60];
        $sopt[2]['datatype'] = 'bool';

        $sopt[3]['table'] = $this->gettable();
        $sopt[3]['field'] = 'comment';
        $sopt[3]['name'] = $LANG['common'][25];

        $sopt[30]['table'] = $this->gettable();
        $sopt[30]['field'] = 'id';
        $sopt[30]['name'] = $LANG["common"][2];

        return $sopt;
    }

    function showForm($id, $options=array()) {
        global $LANG, $CFG_GLPI;

        if (!$this->canView())
            return false;
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
        autocompletionTextField($this, "name");
        echo "</td>";
        echo "<td class='tab_bg_1' align='center'>" . $LANG["plugin_geninventorynumber"]["config"][0] . "</td>";
        echo "<td class='tab_bg_1'>";
        Dropdown::showYesNo("active", $this->fields["active"]);
        echo "</td>";
        echo "</tr>";
        echo "<tr>";
        echo "<td class='tab_bg_1' align='center'>" . $LANG["plugin_geninventorynumber"]["config"][8] . "</td>";
        echo "<td class='tab_bg_1'>";
        plugin_geninventorynumber_dropdownFields('field', $this->fields['field']);
        echo "</td>";
        echo "<td class='tab_bg_1' align='center'>" . $LANG["plugin_geninventorynumber"]["config"][6] . " " . $LANG["common"][59] . "</td>";
        echo "<td class='tab_bg_1'>";
        echo "<input type='text' name='next_number' value='" . $this->fields["next_number"] . "' size='12'>&nbsp;";
        echo "</td>";
        echo "</tr>";

        echo "<tr>";
        echo "<td class='tab_bg_1' colspan='4'>";
        echo "<table>";
        echo "<tr>";
        echo "<td class='tab_bg_1'>" . $LANG['common'][25] . "</td><td>";
        echo "<textarea cols='60' rows='4' name='comments' >" . $this->fields["comments"] . "</textarea>";
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

    function getSelectLinkedItem() {
        return "SELECT `id`
              FROM `glpi_plugin_geninventorynumber_configfields`
              WHERE config_id='" . $this->fields['id'] . "'";
    }

    static function install(Migration $migration) {
       global $DB;
       $table = getTableForItemType(__CLASS__);
    
       if (TableExists("glpi_plugin_generateinventorynumber_config")) {
          $fields = array('template_computer', 'template_monitor', 'template_printer',
                           'template_peripheral', 'template_phone' , 'template_networking',
                           'generate_ocs', 'generate_data_injection', 'generate_internal',
                           'computer_gen_enabled', 'monitor_gen_enabled', 'printer_gen_enabled',
                           'peripheral_gen_enabled', 'phone_gen_enabled', 'networking_gen_enabled',
                           'computer_global_index', 'monitor_global_index', 'printer_global_index',
                           'peripheral_global_index', 'phone_global_index',
                           'networking_global_index');
          foreach ($fields as $field) {
             $migration->dropField("glpi_plugin_generateinventorynumber_config", $field);
          }
          $migration->renameTable("glpi_plugin_generateinventorynumber_config", $table);
       }
       if (TableExists("glpi_plugin_geninventorynumber_config")) {
          $migration->renameTable("glpi_plugin_geninventorynumber_config", $table);
       }
       if (!TableExists($table)) {
          $sql = "CREATE TABLE IF NOT EXISTS `$table` (
                    `id` int(11) NOT NULL auto_increment,
                    `name`  varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
                    `field`  varchar(255) COLLATE utf8_unicode_ci DEFAULT '',
                    `entities_id` int(11)  NOT NULL default '-1',
                    `is_recursive` tinyint(1)  NOT NULL default 0,
                    `is_active` tinyint(1)  NOT NULL default 0,
                    `next_number` int(11)  NOT NULL default 0,
                    `comment` text COLLATE utf8_unicode_ci,
                    PRIMARY KEY  (`id`)
                 ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
          $DB->query($sql) or die($DB->error());
          
          $tmp['id']           = 1;
          $tmp['name']         = 'otherserial';
          $tmp['field']        = 'otherserial';
          $tmp['is_active']    = 1;
          $tmp['entities_id']  = 0;
          $tmp['is_recursive'] = 1;
          $tmp['next_number']  = 0;
          $config = new self();
          $config->add($tmp);
       } else {
          $migration->addField($table, 'name', 'string', array('value' => 'otherserial'));
          $migration->addField($table, 'field', 'string', array('value' => 'otherserial'));
          $migration->changeField($table, 'ID', 'ID', 'autoincrement');
          $migration->changeField($table, 'FK_entities', 'entities_id', 'integer', array('value' => -1));
          $migration->changeField($table, 'active', 'is_active', 'bool');
          if (!$migration->addField($table, 'comment', 'text')) {
             $migration->changeField($table, 'comments', 'comment', 'text');
          }
          $migration->changeField($table, 'is_active', 'is_active', 'bool');
          $migration->addField($table, 'is_recursive', 'bool');
       }
       
       $migration->migrationOneTable($table);
    }
    
    static function uninstall(Migration $migration) {
       $migration->dropTable(getTableForItemType(__CLASS__));
    }
}