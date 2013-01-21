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
 @copyright Copyright (c) 2008-2013 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */

class PluginGeninventorynumberProfile extends CommonDBTM {

   static function changeProfile() {
      $profile = new self();
      if ($profile->getFromDBByProfile($_SESSION['glpiactiveprofile']['id'])) {
         foreach (array('plugin_geninventorynumber_generate',
                          'plugin_geninventorynumber_overwrite') as $field) {
            $_SESSION['glpiactiveprofile'][$field] = $profile->fields[$field];
         }
      }
   }
   
   function canCreate() {
      return Session::haveRight('profile', 'w');
   }
   
   function canDelete() {
      return false;
   }
   
   function canView() {
      return Session::haveRight('profile', 'r');
   }
   
   //if profile deleted
   static function purgeProfiles(Profile $prof) {
      $profile = new self();
      $profile->deleteByCriteria(array('profiles_id' => $prof->getField("id")));
   }
     
   function getFromDBByProfile($profiles_id) {
      global $DB;
    
      $query = "SELECT * FROM `".$this->getTable()."`
                WHERE `profiles_id` = '$profiles_id'";
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
    
   static function createFirstAccess() {
       $tmp['plugin_geninventorynumber_overwrite'] = 'w';
       $tmp['plugin_geninventorynumber_generate']  = 'w';
       self::createAccess($_SESSION['glpiactiveprofile']['id'], $tmp);
   }

   static function createAccess($profiles_id, $rights = array()) {
      if (!countElementsInTable(getTableForItemType(__CLASS__), "`profiles_id`='$profiles_id'")) {
         $rights['profiles_id'] = $profiles_id;
         $profile = new self();
         $profile->add($rights);
      }
   }
    
   function showForm($id) {
      global $LANG;
      if (!Session::haveRight("profile", "r")) {
         return false;
      }

      $this->getFromDBByProfile($id);
      $this->showFormHeader();

      echo "<tr class='tab_bg_2'>";
      echo "<td>" . $LANG["plugin_geninventorynumber"]["massiveaction"][0] . ":</td>";
      echo "<td>";
      Profile::dropdownNoneReadWrite("plugin_geninventorynumber_generate",
                                     $this->fields["plugin_geninventorynumber_generate"], 1, 0, 1);
      echo "</td>";
      echo "<td>" . $LANG["plugin_geninventorynumber"]["massiveaction"][1] . ":</td>";
      echo "<td>";
      Profile::dropdownNoneReadWrite("plugin_geninventorynumber_overwrite",
                                     $this->fields["plugin_geninventorynumber_overwrite"], 1, 0, 1);
      echo "</td>";
      echo "</tr>";
      echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";

      $options['candel'] = false;
      $this->showFormButtons($options);
   }

   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
       
      if (TableExists("glpi_plugin_generateinventorynumber_profiles")) {
         $migration->renameTable("glpi_plugin_generateinventorynumber_profiles", $table);
      }
       
      if (!TableExists($table)) {
         $sql = "CREATE TABLE  IF NOT EXISTS `$table` (
                   `id` int(11) NOT NULL auto_increment,
                   `profiles_id` int(11) NOT NULL default '0' COMMENT 'RELATION to glpi_profiles (id)',
                   `plugin_geninventorynumber_generate` char(1) default NULL,
                   `plugin_geninventorynumber_overwrite` char(1) default NULL,
                   PRIMARY KEY  (`id`)
              ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($sql) or die($DB->error());
         PluginGeninventoryNumberProfile::createFirstAccess();
      } else {
         $migration->changeField($table, 'ID', 'id', 'autoincrement');
         $migration->changeField($table, 'generate', 'plugin_geninventorynumber_generate', 'char');
         $migration->changeField($table, 'generate_overwrite', 'plugin_geninventorynumber_overwrite',
                                 'char');
         if ($migration->addField($table, 'profiles_id', 'integer')) {
            $migration->migrationOneTable($table);
            foreach ($DB->request($table, "", array('name', 'id')) as $data) {
               $query = "SELECT `id` FROM `glpi_profiles` WHERE `name`='".$data['name']."'";
               $results = $DB->query($query);
               if ($DB->numrows($results)) {
                  $query_update = "UPDATE `$table`
                                   SET `profiles_id`='".$DB->result($results, 0, 'id')."'
                                   WHERE `id`='".$data['id']."'";
                  $DB->query($query_update);
               } else {
                  $query_drop = "DELETE FROM `$table` WHERE `id`='".$data['id']."'";
                  $DB->query($query_drop);
               }
            }
         }
         $migration->dropField($table, 'interface');
         $migration->dropField($table, 'name');
         $migration->migrationOneTable($table);
     }
     self::changeProfile($_SESSION['glpiactiveprofile']['id']);
   }
    
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;
   
      $type = get_class($item);
      if ($type == 'Profile') {
         if ($item->getField('id') && $item->getField('interface')!='helpdesk') {
            return array(1 => $LANG["plugin_geninventorynumber"]["title"][1]);
         }
      }
      return '';
   }
   
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      if (get_class($item) == 'Profile') {
         $profile = new self();
         self::createAccess($item->getField('id'));
         $profile->showForm($item->getField('id'));
         return true;
      }
   }
}