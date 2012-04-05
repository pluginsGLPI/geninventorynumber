<?php

/*
  ----------------------------------------------------------------------
  GLPI - Gestionnaire Libre de Parc Informatique
  Copyright (C) 2003-2008 by the INDEPNET Development Team.

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
// Original Author of file: Dévi Balpe
// Purpose of file:
// ----------------------------------------------------------------------


class PluginGeninventorynumberProfile extends CommonDBTM {

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
        return $LANG['plugin_geninventorynumber']['types'][2];
    }

    static function createAdminAccess($id) {
        $profile = new Profile();
        $prof    = new self();
        if ($profile->getFromDB($id) && !$prof->getFromDB($id)) {
            $tmp['id']                 = $id;
            $tmp['name']               = $profile->getName();
            $tmp['interface']          = 'geninventorynumber';
            $tmp['is_default']         = 0;
            $tmp['generate']           = 'w';
            $tmp['generate_overwrite'] = 'w';
            $prof->add($tmp);
        }
    }

    function createUserAccess($id) {
        $profile = new Profile();
        if ($profile->getFromDB($id)) {
           $this->add(array('id' => $id, 'name' => $profile->getName(),
                            'interface' => 'geninventorynumber', 'is_default' => '0'));
        }
    }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if ($item->getType()=='Profile') {
            return $LANG["plugin_geninventorynumber"]["title"][1];
      }
      return '';
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
       if ($item->getType()=='Profile') {
         $ID = $item->getField('id');
         $prof = new self();
         if ($prof->getfromDB($ID) || $prof->createUserAccess($item)) {
            $prof->showForm($ID);
         }
      }
      return true;
   }

   static function cleanProfiles(Profile $prof) {
      $profile = new self();
      $profile->delete(array('id' => $prof->getField("id")));
   }

   function showForm($id, $options=array()) {
        global $LANG;
        if (!Session::haveRight("profile", "r"))
            return false;

        $prof = new Profile();
        if ($id) {
            $this->getFromDB($id);
            $prof->getFromDB($id);
        }
        $this->showFormHeader($options);

        echo "<tr class='tab_bg_2'>";
        echo "<td>" . $LANG["plugin_geninventorynumber"]["profiles"][1] . ":</td>";
        echo "<td>";
        Profile::dropdownNoneReadWrite("generate", $this->fields["generate"], 1, 0, 1);
        echo "</td>";
        echo "<td>" . $LANG["plugin_geninventorynumber"]["massiveaction"][1] . ":</td>";
        echo "<td>";
        Profile::dropdownNoneReadWrite("generate_overwrite", $this->fields["generate_overwrite"], 
                                       1, 0, 1);
        echo "</td>";
        echo "</tr>";

        echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";

        $options['candel'] = false;
        $this->showFormButtons($options);
    }

   /**
   * Replace plugin rights in Session var when switching Profile
   * 
   * @return   null
   */
   static function plugin_geninventorynumber_changeprofile(){
      $prof= new self();
      if($prof->getFromDB($_SESSION['glpiactiveprofile']['id'])) {
         $_SESSION["glpi_plugin_geninventorynumber_profile"] = $prof->fields;
      }
      else {
         unset($_SESSION["glpi_plugin_geninventorynumber_profile"]);
      }
   }

   static function install(Migration $migration) {
      global $DB;
      
      $table = getTableForItemType(__CLASS__);
      
      if (TableExists("glpi_plugin_generateinventorynumber_profiles")) {
         $migration->renameTable("glpi_plugin_generateinventorynumber_profiles", $table);
      }

      if (!TableExists($table)) {
         $sql = "CREATE TABLE  IF NOT EXISTS `glpi_plugin_geninventorynumber_profiles` (
                    `id` int(11) NOT NULL auto_increment,
                    `name` varchar(255) default NULL,
                    `interface` varchar(50) collate utf8_unicode_ci NOT NULL default 'genInventoryNumber',
                    `is_default` int(6) NOT NULL default '0',
                    `generate` char(1) default NULL,
                    `generate_overwrite` char(1) default NULL,
                 PRIMARY KEY  (`id`)
                 ) ENGINE=MyISAM CHARSET=utf8 COLLATE=utf8_unicode_ci;";
         $DB->query($sql) or die($DB->error());
         
         //Create an admin access
         self::createAdminAccess(Session::getLoginUserID());
      } else {
         if (TableExists("glpi_plugin_generateinventorynumber_profiles")) {
            $migration->renameTable("glpi_plugin_generateinventorynumber_profiles", $table);
         }
         
      }
   }
   
   static function uninstall(Migration $migration) {
      $migration->dropTable(getTableForItemType(__CLASS__));
   }
}