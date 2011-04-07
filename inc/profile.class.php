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

    function __construct() {
        $this->table = "glpi_plugin_geninventorynumber_profiles";
    }

    function canCreate() {
        return haveRight("config", "w");
    }

    function canView() {
        return haveRight("config", "r");
    }

    function canDelete() {
        return haveRight("config", "w");
    }

    static function getTypeName() {
        global $LANG;
        return $LANG['plugin_geninventorynumber']['types'][2];
    }

    static function createFirstAccess($id) {
        $Profile = new Profile();
        $Profile->getFromDB($id);
        $Prof = new self();
        if (!$Prof->getFromDB($id)) {
            $Prof->add(array(
                'id' => $id,
                'name' => $Profile->fields["name"],
                'interface' => 'Geninventorynumber',
                'is_default' => '0',
                'generate' => 'w',
                'generate_overwrite' => 'w'
            ));
        }
    }

    function createAccess($id) {
        $Profile = new Profile();
        $Profile->getFromDB($id);
        $this->add(array(
            'id' => $id,
            'name' => $Profile->fields["name"],
            'interface' => 'Geninventorynumber',
            'is_default' => '0'
        ));
    }

    //if profile deleted
    function cleanProfiles($id) {
        global $DB;
        $query = "DELETE FROM glpi_plugin_geninventorynumber_profiles WHERE id='$id' ";
        $DB->query($query);
    }

    function getFromDBByProfile($profiles_id) {
        global $DB;

        $query = "SELECT * FROM `" . $this->getTable() . "`
					WHERE `profiles_id` = '" . $profiles_id . "' ";
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

    function showForm($id, $options=array()) {
        global $LANG;
        if (!haveRight("profile", "r"))
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
        Profile::dropdownNoneReadWrite("generate_overwrite", $this->fields["generate_overwrite"], 1, 0, 1);
        echo "</td>";
        echo "</tr>";

        echo "<input type='hidden' name='id' value=" . $this->fields["id"] . ">";

        $options['candel'] = false;
        $this->showFormButtons($options);
    }

}

?>