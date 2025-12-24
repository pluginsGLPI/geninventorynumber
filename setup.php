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
 * @copyright Copyright (C) 2008-2025 by GenInventoryNumber plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/geninventorynumber
 * -------------------------------------------------------------------------
 */

use Glpi\Plugin\Hooks;
use Glpi\Asset\AssetDefinitionManager;
use GlpiPlugin\Geninventorynumber\Capacity\HasInventoryNumberGenerationCapacity;

use function Safe\define;

define('PLUGIN_GENINVENTORYNUMBER_VERSION', '2.10.0');

// Minimal GLPI version, inclusive
define('PLUGIN_GENINVENTORYNUMBER_MIN_GLPI', '11.0.0');
// Maximum GLPI version, exclusive
define('PLUGIN_GENINVENTORYNUMBER_MAX_GLPI', '11.0.99');

function plugin_init_geninventorynumber()
{
    /** @var array $GENINVENTORYNUMBER_TYPES */
    /** @var array $PLUGIN_HOOKS */
    /** @var array $CFG_GLPI */
    global $PLUGIN_HOOKS, $CFG_GLPI, $GENINVENTORYNUMBER_TYPES;

    $PLUGIN_HOOKS[Hooks::CONFIG_PAGE]['geninventorynumber'] = 'front/config.php';

    $PLUGIN_HOOKS[Hooks::MENU_TOADD]['geninventorynumber'] = [
        'tools' => 'PluginGeninventorynumberConfig',
    ];

    $PLUGIN_HOOKS[Hooks::POST_INIT]['geninventorynumber']      = 'plugin_geninventorynumber_postinit';

    // Initialize with native asset types
    $GENINVENTORYNUMBER_TYPES = ['Computer', 'Monitor', 'Printer', 'NetworkEquipment',
        'Peripheral', 'Phone', 'SoftwareLicense', 'Cable',
        'Appliance', 'Certificate', 'ConsumableItem', 'Enclosure',
        'PassiveDCEquipment', 'PDU', 'Rack',
    ];

    // Add active custom assets
    $asset_manager = AssetDefinitionManager::getInstance();
    foreach ($asset_manager->getDefinitions(true) as $definition) {
        $custom_asset_class = $definition->getAssetClassName();
        if (!in_array($custom_asset_class, $GENINVENTORYNUMBER_TYPES)) {
            $GENINVENTORYNUMBER_TYPES[] = $custom_asset_class;
        }
    }

    $plugin = new Plugin();
    if ($plugin->isActivated('geninventorynumber')) {
        $PLUGIN_HOOKS[Hooks::USE_MASSIVE_ACTION]['geninventorynumber'] = 1;

        // Register custom capacity for custom assets
        AssetDefinitionManager::getInstance()->registerCapacity(
            new HasInventoryNumberGenerationCapacity(),
        );

        Plugin::registerClass(
            'PluginGeninventorynumberProfile',
            ['addtabon' => ['Profile']],
        );
        Plugin::registerClass('PluginGeninventorynumberConfig');
        Plugin::registerClass('PluginGeninventorynumberConfigField');

        if (Session::haveRight('config', UPDATE)) {
            $PLUGIN_HOOKS[hooks::MENU_TOADD]['geninventorynumber']
              = ['tools' => 'PluginGeninventorynumberConfig'];
        }
    }
}

function plugin_version_geninventorynumber()
{
    return [
        'name'         => __s('Inventory number generation', 'geninventorynumber'),
        'version'      => PLUGIN_GENINVENTORYNUMBER_VERSION,
        'author'       => "<a href='http://www.teclib.com'>TECLIB'</a> + KK",
        'homepage'     => 'https://github.com/pluginsGLPI/geninventorynumber',
        'license'      => 'GPLv3+',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_GENINVENTORYNUMBER_MIN_GLPI,
                'max' => PLUGIN_GENINVENTORYNUMBER_MAX_GLPI,
            ],
        ],
    ];
}
