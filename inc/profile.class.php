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

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

class PluginGeninventorynumberProfile extends CommonDBTM {
   static $rightname = "config";

   /**
    * @param $ID  integer
    */
   static function createFirstAccess($profiles_id) {
      include_once(Plugin::getPhpDir('geninventorynumber')."/inc/profile.class.php");
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         self::addDefaultProfileInfos($profiles_id,
                                      [$right['field'] => ALLSTANDARDRIGHT]);
      }
   }

   static function addDefaultProfileInfos($profiles_id, $rights) {
      $profileRight = new ProfileRight();
      foreach ($rights as $right => $value) {
         if (!countElementsInTable('glpi_profilerights',
                                   ['profiles_id' => $profiles_id, 'name' => $right])) {
            $myright['profiles_id'] = $profiles_id;
            $myright['name']        = $right;
            $myright['rights']      = $value;
            $profileRight->add($myright);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   static function removeRightsFromSession() {
      $profile = new self();
      foreach ($profile->getAllRights() as $right) {
         if (isset($_SESSION['glpiactiveprofile'][$right['field']])) {
            unset($_SESSION['glpiactiveprofile'][$right['field']]);
         }
      }
      ProfileRight::deleteProfileRights([$right['field']]);
   }

   function showForm($ID, array $options = []) {
      echo "<div class='firstbloc'>";
      if ($canedit = Session::haveRightsOr(self::$rightname, [CREATE, UPDATE, PURGE])) {
         $profile = new Profile();
         echo "<form method='post' action='".$profile->getFormURL()."'>";
      }

      $profile = new Profile();
      $profile->getFromDB($ID);

      $rights = $this->getAllRights();
      $profile->displayRightsChoiceMatrix($rights, ['canedit'       => $canedit,
                                                    'default_class' => 'tab_bg_2',
                                                    'title'         => __('General')]);

      if ($canedit) {
         echo "<div class='center'>";
         echo Html::hidden('id', ['value' => $ID]);
         echo Html::submit(_sx('button', 'Save'), ['name' => 'update']);
         echo "</div>\n";
         Html::closeForm();
      }
      echo "</div>";
   }

   static function getAllRights() {
      return [
               ['itemtype'  => 'PluginGeninventorynumber',
                'label'     => __('Generate inventory number', 'geninventorynumber'),
                'field'     => 'plugin_geninventorynumber',
                'rights' => [CREATE    => __('Create'),
                             UPDATE    => __('Update')]
                            ]
               ];
   }

   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);

      if (isset($_SESSION['glpiactiveprofile'])) {
          PluginGeninventorynumberProfile::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
      }

      if ($DB->tableExists("glpi_plugin_geninventorynumber_profiles")) {
         foreach (getAllDataFromTable($table) as $data) {
            $profile = new self();
            foreach ($profile->getAllRights() as $right => $rights) {
               if (!countElementsInTable('glpi_profilerights',
                                         ['profiles_id' => $data['profiles_id'],
                                          'name' => $rights['field']])) {

                  $profileRight = new ProfileRight();
                  $myright = [];
                  $myright['name']        = $rights['field'];
                  $myright['profiles_id'] = $data['profiles_id'];

                  if (!strcmp($data['plugin_geninventorynumber_generate'], 'w')) {
                     $myright['rights'] = CREATE;
                  }
                  if (!strcmp($data['plugin_geninventorynumber_overwrite'], 'w')) {
                     $myright['rights'] += UPDATE;
                  }
                  $profileRight->add($myright);
               }
            }
         }
         $migration->dropTable($table);
      }
   }

   static function uninstallProfile() {
      $pfProfile = new self();
      $a_rights  = $pfProfile->getAllRights();

      foreach ($a_rights as $data) {
         ProfileRight::deleteProfileRights([$data['field']]);
      }
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if ($item->fields['interface'] == 'central') {
         return self::createTabEntry(__('Inventory number generation', 'geninventorynumber'));
      }
   }

   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      $profile = new self();
      $profile->showForm($item->getID());
      return true;
   }
}
