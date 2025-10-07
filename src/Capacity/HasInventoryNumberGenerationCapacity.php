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

namespace GlpiPlugin\Geninventorynumber\Capacity;

use Glpi\Asset\Capacity\AbstractCapacity;
use Glpi\Asset\CapacityConfig;
use Override;
use Plugin;
use PluginGeninventorynumberConfig;
use PluginGeninventorynumberConfigField;

/**
 * Capacity to enable automatic inventory number generation for custom assets
 */
class HasInventoryNumberGenerationCapacity extends AbstractCapacity
{
    public function getLabel(): string
    {
        return __('Inventory number generation', 'geninventorynumber');
    }

    public function getIcon(): string
    {
        return 'ti ti-hash';
    }

    #[Override]
    public function getDescription(): string
    {
        return __('Enable automatic inventory number generation for these assets', 'geninventorynumber');
    }

    /**
     * Check if the capacity is used by the given asset class
     * It's used if there's a configuration for this asset type
     */
    public function isUsed(string $classname): bool
    {
        return parent::isUsed($classname)
            && PluginGeninventorynumberConfigField::isActiveForItemType($classname);
    }

    public function getCapacityUsageDescription(string $classname): string
    {
        $total_assets = $this->countAssets($classname);
        $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType($classname);

        if (!empty($config)) {
            return sprintf(
                __('Configured for %d assets', 'geninventorynumber'),
                $total_assets
            );
        }

        return __('Not configured yet', 'geninventorynumber');
    }

    /**
     * Called when the asset class is loaded/bootstrapped
     * This is where we register the asset type for inventory number generation
     */
    public function onClassBootstrap(string $classname, CapacityConfig $config): void
    {
        // Nothing to do here - the registration is already done in setup.php
        // by checking which assets have this capacity enabled
    }

    /**
     * Called when the capacity is enabled for an asset class
     * Create the configuration entry for this asset type
     */
    public function onCapacityEnabled(string $classname, CapacityConfig $config): void
    {
        // Create configuration entry for this asset type if it doesn't exist
        $table = PluginGeninventorynumberConfigField::getTable();
        if (!countElementsInTable($table, ['itemtype' => $classname])) {
            PluginGeninventorynumberConfigField::registerNewItemType($classname);
        }
    }

    /**
     * Called when the capacity is disabled for an asset class
     * Clean up all related data
     */
    public function onCapacityDisabled(string $classname, CapacityConfig $config): void
    {
        // Delete configuration for this asset type
        $config_field = new PluginGeninventorynumberConfigField();
        $config_field->deleteByCriteria([
            'itemtype' => $classname
        ], true);
    }

    /**
     * Get the search options for this capacity
     * Add inventory number field to search options
     */
    public function getSearchOptions(string $classname): array
    {
        return [];
    }

    /**
     * Get specific rights for this capacity
     */
    public function getSpecificRights(): array
    {
        return [];
    }

    /**
     * Get relations to clone when asset is cloned
     */
    public function getCloneRelations(): array
    {
        return [];
    }

    /**
     * Check if the capacity can be enabled/disabled
     * Always return true for custom assets
     */
    public function isEnabledByDefault(): bool
    {
        return false;
    }
}
