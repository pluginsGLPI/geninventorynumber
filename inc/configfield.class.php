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

class PluginGeninventorynumberConfigField extends CommonDBChild
{
    public $dohistory       = true;
    public static $itemtype = 'PluginGeninventorynumberConfig';
    public static $items_id = 'plugin_geninventorynumber_configs_id';

    public static function getTypeName($nb = 0)
    {
        return __('GLPI\'s inventory items configuration', 'geninventorynumber');
    }

    public static function getConfigFieldByItemType($itemtype)
    {
        $infos = getAllDataFromTable(getTableForItemType(__CLASS__), ['itemtype' => $itemtype]);
        if (!empty($infos)) {
            return array_pop($infos);
        } else {
            return $infos;
        }
    }

    public static function install(Migration $migration)
    {
        /** @var DBmysql $DB */
        /** @var array $GENINVENTORYNUMBER_TYPES */
        global $DB, $GENINVENTORYNUMBER_TYPES;

        $default_charset = DBConnection::getDefaultCharset();
        $default_collation = DBConnection::getDefaultCollation();
        $default_key_sign = DBConnection::getDefaultPrimaryKeySignOption();

        $table = getTableForItemType(__CLASS__);

        if ($DB->tableExists("glpi_plugin_geninventorynumber_fields")) {
           //Only migrate itemtypes when it's only necessary, otherwise it breaks upgrade procedure !
            $migration->renameTable("glpi_plugin_geninventorynumber_fields", $table);
        }

        if (!$DB->tableExists($table)) {
            $query = "CREATE TABLE IF NOT EXISTS `$table` (
            `id` int {$default_key_sign} NOT NULL auto_increment,
            `plugin_geninventorynumber_configs_id` int {$default_key_sign} NOT NULL default '0',
            `itemtype` varchar(255) DEFAULT '',
            `template` varchar(255) DEFAULT '',
            `is_active` tinyint NOT NULL default '0',
            `use_index` tinyint NOT NULL default '0',
            `index` bigint NOT NULL default '0',
            `date_last_generated` timestamp NULL DEFAULT NULL,
            `auto_reset_method` int unsigned NOT NULL default '0',
            PRIMARY KEY  (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query);
        } else {
            $migration->changeField($table, 'ID', 'id', 'autoincrement');
            $migration->changeField($table, 'config_id', 'plugin_geninventorynumber_configs_id', "int {$default_key_sign} NOT NULL default '0'");
            if ($migration->changeField($table, 'device_type', 'itemtype', 'string')) {
                $migration->migrationOneTable($table);
                Plugin::migrateItemType([], ["glpi_displaypreferences"], [$table]);
            }
            $migration->changeField($table, 'enabled', 'is_active', 'boolean');
            $migration->changeField($table, 'use_index', 'use_index', 'boolean');
            $migration->addField($table, 'date_last_generated', 'timestamp');
            $migration->addField($table, 'auto_reset_method', "int unsigned NOT NULL default '0'");
            $migration->migrationOneTable($table);
        }

        $field = new self();
        foreach ($GENINVENTORYNUMBER_TYPES as $type) {
            if (class_exists($type) && !countElementsInTable($table, ['itemtype' => $type])) {
                $input["plugin_geninventorynumber_configs_id"] = 1;
                $input["itemtype"]                             = $type;
                $input["template"]                             = "&lt;#######&gt;";
                $input["is_active"]                            = 0;
                $input["index"]                                = 0;
                $field->add($input);
            }

           // Init date_last_generated
            $cfield = new self();
            if (
                $cfield->getFromDBByCrit(['itemtype' => $type])
                && $cfield->fields['date_last_generated'] === null
                && countElementsInTable($type::getTable())
            ) {
                $max = $DB->request([
                    'SELECT' => ['MAX' => 'date_creation as date'],
                    'FROM' => $type::getTable()
                ])->current()['date'];

                $DB->update($cfield::getTable(), [
                    'id' => $cfield->getID(),
                    'date_last_generated' => $max
                ]);
            }
        }
    }

    public static function uninstall(Migration $migration)
    {
        $migration->dropTable(getTableForItemType(__CLASS__));
    }

    public static function showForConfig($id)
    {
        /** @var array $CFG_GLPI */
        /** @var DBmysql $DB */
        global $CFG_GLPI, $DB;

        $config = new PluginGeninventorynumberConfig();
        $config->getFromDB($id);
        $target = Toolbox::getItemTypeFormUrl(__CLASS__);

        echo "<form name='form_core_config' method='post' action=\"$target\">";
        echo "<div align='center'>";
        echo "<table class='tab_cadre_fixe'><thead>";
        echo "<tr><th colspan='6'>" . __('GLPI\'s inventory items configuration', 'geninventorynumber') . "</th></tr>";

        echo "<input type='hidden' name='id' value='$id'>";

        echo "<tr><th colspan='2'>" . __('Generation templates', 'geninventorynumber');
        echo "</th><th>" . __('Active') . "</th>";
        echo "<th>" . __('Use global index', 'geninventorynumber') . "</th>";
        echo "<th>" . __('Index position', 'geninventorynumber') . "</th>";
        echo "<th>" . __('Index auto-reset method', 'geninventorynumber') . "</th></tr></thead>";

        echo "<tbody>";
        $rows = getAllDataFromTable(getTableForItemType(__CLASS__));
        foreach ($rows as $data) {
            $itemtype = $data['itemtype'];
            $typename = is_a($itemtype, CommonDBTM::class, true) ? $itemtype::getTypeName() : $itemtype;
            echo "<td class='tab_bg_1' align='center'>" . $typename . "</td>";
            echo "<td class='tab_bg_1'>";
            echo "<input type='hidden' name='ids[$itemtype][id]' value='" . $data["id"] . "'>";
            echo "<input type='hidden' name='ids[$itemtype][itemtype]' value='$itemtype'>";
            echo "<input type='text' name='ids[$itemtype][template]' value=\"" . $data["template"] . "\">";
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            Dropdown::showYesNo("ids[$itemtype][is_active]", $data["is_active"]);
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            Dropdown::showYesNo("ids[$itemtype][use_index]", $data["use_index"]);
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            if ($data["is_active"] && !$data["use_index"]) {
                echo "<input type='text' name='ids[$itemtype][index]' value='" .
                $data['index'] . "' size='12'>";
            }
            echo "</td>";
            echo "<td class='tab_bg_1' align='center'>";
            if ($data["is_active"] && !$data["use_index"]) {
                Dropdown::showFromArray("ids[$itemtype][auto_reset_method]", PluginGeninventorynumberConfig::getAutoResetOptions(), [
                    'value' => $data['auto_reset_method'] ?? 0
                ]);
            }
            echo "</td>";
            echo "</tr>";
        }

        echo "<tr class='tab_bg_1'><td align='center' colspan='6'>";
        echo "<input type='submit' name='update_fields' value=\"" . _sx('button', 'Save') . "\" class='submit'>";
        echo "</td></tr>";

        echo "</tbody></table>";
        Html::closeForm();
    }

    public function prepareInputForAdd($input)
    {
        $input = parent::prepareInputForAdd($input);
        $check_auto_reset = isset($input['is_active']) && isset($input['use_index']);
        if ($check_auto_reset && (!$input["is_active"] || $input["use_index"])) {
            $input['auto_reset_method'] = 0;
        }
        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $input = parent::prepareInputForUpdate($input);
        $check_auto_reset = isset($input['is_active']) && isset($input['use_index']);
        if ($check_auto_reset && (!$input["is_active"] || $input["use_index"])) {
            $input['auto_reset_method'] = 0;
        }
        return $input;
    }

    public static function getEnabledItemTypes()
    {
        /** @var DBmysql $DB */
        global $DB;

        $it = $DB->request([
            'SELECT' => ['itemtype'],
            'DISTINCT' => true,
            'FROM' => getTableForItemType(__CLASS__),
            'ORDER' => ['itemtype']
        ]);
        $types = [];
        foreach ($it as $data) {
            $types[] = $data['itemtype'];
        }
        return $types;
    }

    public static function isActiveForItemType($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        $it = $DB->request([
            'SELECT' => ['is_active'],
            'FROM' => getTableForItemType(__CLASS__),
            'WHERE' => ['itemtype' => $itemtype]
        ]);
        if (count($it)) {
            return $it->current()['is_active'];
        }

        return false;
    }

   /**
    * Check if the index needs to be reset based on the configured auto-reset method
    * @param string $itemtype
    * @return bool
    */
    public static function needIndexReset($itemtype): bool
    {
        /** @var DBmysql $DB */
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['date_last_generated', 'auto_reset_method'],
            'FROM'   => self::getTable(),
            'WHERE'  => [
                'itemtype' => $itemtype
            ]
        ]);
        if ($iterator->count() > 0) {
            $data = $iterator->current();
            if (
                $data['auto_reset_method'] === PluginGeninventorynumberConfig::AUTO_RESET_NONE
                || $data['date_last_generated'] === null
            ) {
                return false;
            }

            $current_date = strtotime($_SESSION['glpi_currenttime']);
            $last_gen_date = strtotime($data['date_last_generated']);

            switch ($data['auto_reset_method']) {
                case PluginGeninventorynumberConfig::AUTO_RESET_DAILY:
                    return date('Y-m-d', $last_gen_date) !== date('Y-m-d', $current_date);
                case PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY:
                    return date('Y-m', $last_gen_date) !== date('Y-m', $current_date);
                case PluginGeninventorynumberConfig::AUTO_RESET_YEARLY:
                    return date('Y', $last_gen_date) !== date('Y', $current_date);
            }
        }
        return false;
    }

   /**
    * Reset the index for the given itemtype to 0 and reset the last generated date
    */
    public static function resetIndex(string $itemtype): void
    {
        /** @var DBmysql $DB */
        global $DB;

        $DB->update(self::getTable(), [
            'index' => 0,
            'date_last_generated' => $_SESSION['glpi_currenttime']
        ], [
            'itemtype' => $itemtype
        ]);
    }

    public static function getNextIndex($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        if (self::needIndexReset($itemtype)) {
            self::resetIndex($itemtype);
        }

        $it = $DB->request([
            'SELECT' => ['index'],
            'FROM' => getTableForItemType(__CLASS__),
            'WHERE' => ['itemtype' => $itemtype]
        ]);
        if (count($it)) {
            return $it->current()['index'] + 1;
        }
        return 0;
    }

    public static function updateIndex($itemtype)
    {
        /** @var DBmysql $DB */
        global $DB;

        $DB->update(getTableForItemType(__CLASS__), [
            'index' => new QueryExpression($DB::quoteName('index') . ' + 1'),
            'date_last_generated' => $_SESSION['glpi_currenttime']
        ], [
            'itemtype' => $itemtype
        ]);
    }

    public static function registerNewItemType($itemtype)
    {
        if (!class_exists($itemtype)) {
            return;
        }

        if (!countElementsInTable(getTableForItemType(__CLASS__), ['itemtype' => $itemtype])) {
            $config = new self();
            $input["plugin_geninventorynumber_configs_id"] = 1;
            $input["itemtype"]                             = $itemtype;
            $input["template"]                             = "&lt;#######&gt;";
            $input["is_active"]                            = 0;
            $input["index"]                                = 0;
            $config->add($input);
        }
    }

    public static function unregisterNewItemType($itemtype)
    {
        if (countElementsInTable(getTableForItemType(__CLASS__), ['itemtype' => $itemtype])) {
            $config = new self();
            $config->deleteByCriteria(['itemtype' => $itemtype]);
        }
    }
}
