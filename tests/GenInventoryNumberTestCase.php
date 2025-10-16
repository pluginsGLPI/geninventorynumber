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
 * the Free Software Foundation; either version 2 of the License, or
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
 * @copyright Copyright (C) 2008-2024 by GenInventoryNumber plugin team.
 * @license   GPLv2 https://www.gnu.org/licenses/gpl-2.0.html
 * @link      https://github.com/pluginsGLPI/geninventorynumber
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Geninventorynumber\Tests;

use DbTestCase;

/**
 * Base test case class for GenInventoryNumber plugin tests
 */
abstract class GenInventoryNumberTestCase extends DbTestCase
{
    /**
     * Initialize plugin configuration
     *
     * @param array $config Configuration parameters
     * @return void
     */
    public function initConfig(array $config = []): void
    {
        $this->login();

        // Default configuration values
        $default_config = [
            'name' => 'otherserial',
            'is_active' => 1,
            'index' => 0,
            'comment' => '',
            'date_last_generated' => '',
            'auto_reset_method' => 0,
        ];

        // Merge provided config with defaults
        $config = array_merge($default_config, $config);

        // Update plugin config
        $this->updateConfig($config);
    }

    public function updateConfig(array $config = []): void
    {
        $this->updateItem(\PluginGeninventorynumberConfig::class, 1, $config);
    }

    public function getConfig(): \PluginGeninventorynumberConfig
    {
        $config = new \PluginGeninventorynumberConfig();
        $this->assertTrue($config->getFromDB(1));
        return $config;
    }

    /**
     * Create a config field for inventory number generation
     *
     * @param string $itemtype Itemtype to configure
     * @param array $config Configuration parameters
     * @return \PluginGeninventorynumberConfigField
     */
    public function setConfigField(string $itemtype, array $config = []): \PluginGeninventorynumberConfigField
    {
        $config_field = new \PluginGeninventorynumberConfigField();

        if ($config_field->getFromDBByCrit(['itemtype' => $itemtype])) {
            return $this->updateItem(\PluginGeninventorynumberConfigField::class, $config_field->getID(), $config);
        }

        // Ensure the itemtype is registered
        \PluginGeninventorynumberConfigField::registerNewItemType($itemtype);

        // Only one active config per itemtype
        $this->assertTrue(
            $config_field->getFromDBByCrit(['itemtype' => $itemtype])
        );

        // Set configuration values if is provided
        return $this->updateItem(\PluginGeninventorynumberConfigField::class, $config_field->getID(), $config);
    }
}
