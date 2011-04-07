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

class PluginGeninventorynumberConfig extends CommonDBTM {

    function __construct() {
        $this->table = "glpi_plugin_geninventorynumber_configs";
    }

    function defineTabs($options=array()) {
        global $LANG;
        $ong[1] = $LANG["plugin_geninventorynumber"]["config"][7];
        return $ong;
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
        $sopt[1]['linkfield'] = '';
        $sopt[1]['name'] = $LANG['common'][16];
        $sopt[1]['datatype'] = 'itemlink';

        $sopt[2]['table'] = $this->gettable();
        $sopt[2]['field'] = 'active';
        $sopt[2]['linkfield'] = '';
        $sopt[2]['name'] = $LANG['common'][60];
        $sopt[2]['datatype'] = 'bool';

        $sopt[3]['table'] = $this->gettable();
        $sopt[3]['field'] = 'comments';
        $sopt[3]['linkfield'] = '';
        $sopt[3]['name'] = $LANG['common'][25];

        $sopt[30]['table'] = $this->gettable();
        $sopt[30]['field'] = 'id';
        $sopt[30]['linkfield'] = '';
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

}

?>