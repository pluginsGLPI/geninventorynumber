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

namespace GlpiPlugin\Geninventorynumber\Tests\Units;

use Glpi\Asset\Capacity;
use Glpi\Asset\Capacity\HasDomainsCapacity;
use Glpi\Asset\Capacity\HasVolumesCapacity;
use GlpiPlugin\Geninventorynumber\Capacity\HasInventoryNumberGenerationCapacity;
use GlpiPlugin\Geninventorynumber\Tests\GenInventoryNumberTestCase;
use PluginGeninventorynumberConfigField;
use PHPUnit\Framework\Attributes\DataProvider;

class ConfigFieldTest extends GenInventoryNumberTestCase
{
    public function testResetIndex(): void
    {
        $this->initConfig();
        $computer_field = $this->setConfigField(\Computer::class, [
            'index' => 10,
        ]);
        $monitor_field = $this->setConfigField(\Monitor::class, [
            'index' => 10,
        ]);
        PluginGeninventorynumberConfigField::resetIndex(\Computer::class);
        $this->assertTrue($computer_field->getFromDB($computer_field->getID()));
        $this->assertEquals(0, $computer_field->fields['index']);
        $this->assertTrue($monitor_field->getFromDB($monitor_field->getID()));
        $this->assertEquals(10, $monitor_field->fields['index']);
    }

    public static function needIndexResetProvider(): iterable
    {
        $date = '2025-01-01 00:00:00';

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => null,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 day')),
            'need_reset' => true,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 day')),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 month')),
            'need_reset' => true,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 day')),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 month')),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 year')),
            'need_reset' => true,
        ];
    }

    #[DataProvider('needIndexResetProvider')]
    public function testNeedIndexReset(array $config, string $date, bool $need_reset): void
    {
        $this->initConfig();

        $_SESSION['glpi_currenttime'] = $date;

        $this->setConfigField(\Computer::class, $config);

        $this->assertEquals(
            $need_reset,
            PluginGeninventorynumberConfigField::needIndexReset(\Computer::class),
            'Config: ' . json_encode($config) . ' Date: ' . $date,
        );
    }

    public static function getNextIndexProvider(): iterable
    {
        $date = '2025-01-01 00:00:00';

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 day',
            'index' => [1, 1, 1],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 day',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 month',
            'index' => [1, 1, 1],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 day',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 month',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => \PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 year',
            'index' => [1, 1, 1],
        ];
    }

    #[DataProvider('getNextIndexProvider')]
    public function testGetNextIndex(array $config, string $date, array $index): void
    {
        $this->initConfig();

        $computer_field = $this->setConfigField(\Computer::class, $config);

        $base_time = $config['date_last_generated'];
        $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime($base_time . $date));

        foreach ($index as $expected) {
            $this->assertEquals($expected, PluginGeninventorynumberConfigField::getNextIndex(\Computer::class), "Config: " . json_encode($config) . " Date: $date");
            $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime($_SESSION['glpi_currenttime'] . $date));
            $this->updateItem(PluginGeninventorynumberConfigField::class, $computer_field->getID(), [
                'index' => $expected,
            ]);
        }
    }

    public function testUpdateIndexUpdatesDateLastGenerated(): void
    {
        $this->initConfig();

        $date = '2025-01-01 10:00:00';
        $_SESSION['glpi_currenttime'] = $date;

        $computer_field = $this->setConfigField(\Computer::class, [
            'index' => 5,
            'date_last_generated' => '2024-12-31 10:00:00',
        ]);

        // Update the index
        PluginGeninventorynumberConfigField::updateIndex(\Computer::class);

        // Reload the config field
        $this->assertTrue($computer_field->getFromDB($computer_field->getID()));

        // Verify index was incremented
        $this->assertEquals(6, $computer_field->fields['index']);

        // Verify date_last_generated was updated
        $this->assertEquals($date, $computer_field->fields['date_last_generated']);
    }

    public function testUnregisterNewItemType(): void
    {
        $this->initConfig();

        // Create config field for Computer
        $computer_field = $this->setConfigField(\Computer::class);

        $this->assertTrue(
            $computer_field->getFromDB($computer_field->getID()),
        );

        // Unregister Computer itemtype
        PluginGeninventorynumberConfigField::unregisterNewItemType(\Computer::class);

        // Ensure the config field was deleted
        $this->assertFalse(
            $computer_field->getFromDB($computer_field->getID()),
        );

        // Initialize custom asset
        $definition = $this->initAssetDefinition();
        $custom_asset_class = $definition->getAssetClassName();
        $custom_asset_field = $this->setConfigField($custom_asset_class);

        $this->assertTrue(
            $custom_asset_field->getFromDB($custom_asset_field->getID()),
        );

        // Unregister custom asset itemtype
        PluginGeninventorynumberConfigField::unregisterNewItemType($custom_asset_class);

        // Ensure the config field was deleted
        $this->assertFalse(
            $custom_asset_field->getFromDB($custom_asset_field->getID()),
        );
    }

    public function testIsActiveForItemType(): void
    {
        $this->initConfig();

        /* Test 1: Verify is_active behavior for native GLPI asset */

        $this->setConfigField(\Computer::class, ['is_active' => 1]);

        $this->assertEquals(
            1,
            PluginGeninventorynumberConfigField::isActiveForItemType(\Computer::class),
        );

        $this->setConfigField(\Computer::class, ['is_active' => 0]);

        $this->assertEquals(
            0,
            PluginGeninventorynumberConfigField::isActiveForItemType(\Computer::class),
        );

        // Unregister Computer itemtype
        PluginGeninventorynumberConfigField::unregisterNewItemType(\Computer::class);

        // Ensure it is no longer active
        $this->assertEquals(
            0,
            PluginGeninventorynumberConfigField::isActiveForItemType(\Computer::class),
        );

        /* Test 2: Verify is_active behavior for custom asset */

        // Initialize custom asset
        $definition = $this->initAssetDefinition();
        $custom_asset_class = $definition->getAssetClassName();

        // Ensure capacity is initially inactive
        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Set is_active to 1
        $this->setConfigField($custom_asset_class, ['is_active' => 1]);

        // Verify configfield for custom asset and capacity is active
        $this->assertEquals(
            1,
            PluginGeninventorynumberConfigField::isActiveForItemType($custom_asset_class),
        );
        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Set is_active to 0
        $this->setConfigField($custom_asset_class, ['is_active' => 0]);

        // Verify configfield for custom asset and capacity is inactive
        $this->assertEquals(
            0,
            PluginGeninventorynumberConfigField::isActiveForItemType($custom_asset_class),
        );
        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Unregister custom asset itemtype
        PluginGeninventorynumberConfigField::unregisterNewItemType($custom_asset_class);

        // Ensure it is no longer active
        $this->assertEquals(
            0,
            PluginGeninventorynumberConfigField::isActiveForItemType($custom_asset_class),
        );
    }

    public function testEnableCapacityForAsset(): void
    {
        $this->initConfig();

        /* Test 1: Verify HasInventoryNumberGenerationCapacity capacity is enabled when it is initially disabled */

        $config_field = new PluginGeninventorynumberConfigField();

        $definition = $this->initAssetDefinition(capacities: []);

        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Enable the capacity
        $config_field->enableCapacityForAsset($definition);

        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        /* Test 2: Verify that enabling capacity has no effect when it is already enabled */

        $definition = $this->initAssetDefinition(capacities: [
            new Capacity(HasInventoryNumberGenerationCapacity::class),
        ]);

        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Enable the capacity
        $config_field->enableCapacityForAsset($definition);

        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        /* Test 3: Verify only HasInventoryNumberGenerationCapacity capacity is enabled when other capacities are present */

        $definition = $this->initAssetDefinition(capacities: [
            new Capacity(name: HasDomainsCapacity::class),
            new Capacity(name: HasVolumesCapacity::class),
        ]);

        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasDomainsCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasVolumesCapacity()));

        // Enable the capacity
        $config_field->enableCapacityForAsset($definition);

        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasDomainsCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasVolumesCapacity()));
    }

    public function testDisableCapacityForAsset(): void
    {
        $this->initConfig();

        /* Test 1: Verify that enabling capacity has no effect when it is already disabled */

        $config_field = new PluginGeninventorynumberConfigField();

        $definition = $this->initAssetDefinition(capacities: []);

        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Enable the capacity
        $config_field->disableCapacityForAsset($definition);

        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        /* Test 2: Verify HasInventoryNumberGenerationCapacity capacity is disabled when it is initially enabled */

        $definition = $this->initAssetDefinition(capacities: [
            new Capacity(name: HasInventoryNumberGenerationCapacity::class),
        ]);

        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        // Enable the capacity
        $config_field->disableCapacityForAsset($definition);

        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));

        /* Test 3: Verify only HasInventoryNumberGenerationCapacity capacity is disabled when other capacities are present */

        $definition = $this->initAssetDefinition(capacities: [
            new Capacity(name: HasInventoryNumberGenerationCapacity::class),
            new Capacity(name: HasDomainsCapacity::class),
            new Capacity(name: HasVolumesCapacity::class),
        ]);

        $this->assertTrue($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasDomainsCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasVolumesCapacity()));

        // Enable the capacity
        $config_field->disableCapacityForAsset($definition);

        $this->assertFalse($definition->hasCapacityEnabled(new HasInventoryNumberGenerationCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasDomainsCapacity()));
        $this->assertTrue($definition->hasCapacityEnabled(new HasVolumesCapacity()));
    }
}
