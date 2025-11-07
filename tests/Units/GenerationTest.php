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

namespace GlpiPlugin\Geninventorynumber\Tests\Units;

use Monitor;
use Computer;
use GlpiPlugin\Geninventorynumber\Tests\GenInventoryNumberTestCase;
use PluginGeninventorynumberGeneration;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for inventory number generation
 */
final class GenerationTest extends GenInventoryNumberTestCase
{
    /**
     * Data provider for autoName tests
     */
    public static function autoNameProvider(): iterable
    {
        // Note: getNextIndex() returns current index + 1, so we need to set index to expected - 1

        // Test simple template with # mask
        yield 'Simple mask with 7 digits' => [
            'config' => [
                'template' => '<#######>',
                'use_index' => true,
            ],
            'global_index' => 0,  // getNextIndex will return 1
            'field_index' => 0,
            'item_data' => [],
            'expected' => '0000001',
        ];

        // Test with prefix
        yield 'Template with prefix' => [
            'config' => [
                'template' => 'PC-<#####>',
                'use_index' => true,
            ],
            'global_index' => 41,  // getNextIndex will return 42
            'field_index' => 0,
            'item_data' => [],
            'expected' => 'PC-00042',
        ];

        // Test with suffix
        yield 'Template with suffix' => [
            'config' => [
                'template' => '<####>-END',
                'use_index' => true,
            ],
            'global_index' => 122,  // getNextIndex will return 123
            'field_index' => 0,
            'item_data' => [],
            'expected' => '0123-END',
        ];

        // Test with prefix and suffix
        yield 'Template with prefix and suffix' => [
            'config' => [
                'template' => 'INV-<######>-2025',
                'use_index' => true,
            ],
            'global_index' => 998,  // getNextIndex will return 999
            'field_index' => 0,
            'item_data' => [],
            'expected' => 'INV-000999-2025',
        ];

        // Test with year placeholder \Y
        yield 'Template with year placeholder' => [
            'config' => [
                'template' => '<\Y-####>',
                'use_index' => true,
            ],
            'global_index' => 4,  // getNextIndex will return 5
            'field_index' => 0,
            'item_data' => [],
            'expected' => date('Y') . '-0005',
        ];

        // Test with short year placeholder \y
        yield 'Template with short year placeholder' => [
            'config' => [
                'template' => '<\y-####>',
                'use_index' => true,
            ],
            'global_index' => 9,  // getNextIndex will return 10
            'field_index' => 0,
            'item_data' => [],
            'expected' => date('y') . '-0010',
        ];

        // Test with month placeholder \m
        yield 'Template with month placeholder' => [
            'config' => [
                'template' => '<\Y\m-####>',
                'use_index' => true,
            ],
            'global_index' => 6,  // getNextIndex will return 7
            'field_index' => 0,
            'item_data' => [],
            'expected' => date('Ym') . '-0007',
        ];

        // Test with day placeholder \d
        yield 'Template with day placeholder' => [
            'config' => [
                'template' => '<\Y-\m-\d_####>',
                'use_index' => true,
            ],
            'global_index' => 0,  // getNextIndex will return 1
            'field_index' => 0,
            'item_data' => [],
            'expected' => date('Y-m-d') . '_0001',
        ];

        // Test with serial placeholder \s
        yield 'Template with serial placeholder' => [
            'config' => [
                'template' => '<\s-###>',
                'use_index' => true,
            ],
            'global_index' => 0,  // getNextIndex will return 1
            'field_index' => 0,
            'item_data' => ['serial' => 'SN123456'],
            'expected' => 'SN123456-001',
        ];

        // Test with name placeholder \n
        yield 'Template with name placeholder' => [
            'config' => [
                'template' => '<\n-###>',
                'use_index' => true,
            ],
            'global_index' => 4,  // getNextIndex will return 5
            'field_index' => 0,
            'item_data' => ['name' => 'PC-Office'],
            'expected' => 'PC-Office-005',
        ];

        // Test with multiple placeholders
        yield 'Template with multiple placeholders' => [
            'config' => [
                'template' => '<\Y\m\d-\s-####>',
                'use_index' => true,
            ],
            'global_index' => 98,  // getNextIndex will return 99
            'field_index' => 0,
            'item_data' => ['serial' => 'ABC'],
            'expected' => date('Ymd') . '-ABC-0099',
        ];

        // Test with field index instead of global index
        yield 'Template using field index' => [
            'config' => [
                'template' => 'MON-<####>',
                'use_index' => false,
            ],
            'global_index' => 100,
            'field_index' => 25,  // getNextIndex will return 26
            'item_data' => [],
            'expected' => 'MON-0026',
        ];

        // Test with different mask lengths
        yield 'Template with 3 digits mask' => [
            'config' => [
                'template' => '<###>',
                'use_index' => true,
            ],
            'global_index' => 7,  // getNextIndex will return 8
            'field_index' => 0,
            'item_data' => [],
            'expected' => '008',
        ];

        yield 'Template with 10 digits mask' => [
            'config' => [
                'template' => '<##########>',
                'use_index' => true,
            ],
            'global_index' => 12344,  // getNextIndex will return 12345
            'field_index' => 0,
            'item_data' => [],
            'expected' => '0000012345',
        ];

        // Test with single digit mask
        yield 'Template with single digit mask' => [
            'config' => [
                'template' => '<#>',
                'use_index' => true,
            ],
            'global_index' => 8,  // getNextIndex will return 9
            'field_index' => 0,
            'item_data' => [],
            'expected' => '9',
        ];

        // Test complex template
        yield 'Complex template with all features' => [
            'config' => [
                'template' => 'GLPI-<\Y-\m-######>-PROD',
                'use_index' => true,
            ],
            'global_index' => 4566,  // getNextIndex will return 4567
            'field_index' => 0,
            'item_data' => [],
            'expected' => 'GLPI-' . date('Y-m') . '-004567-PROD',
        ];

        // Test invalid template (no < > markers)
        yield 'Invalid template without markers' => [
            'config' => [
                'template' => 'INVALID-####',
                'use_index' => true,
            ],
            'global_index' => 0,
            'field_index' => 0,
            'item_data' => [],
            'expected' => 'INVALID-####',
        ];

        // Test template with empty prefix
        yield 'Template with empty prefix' => [
            'config' => [
                'template' => '<####>-SUFFIX',
                'use_index' => true,
            ],
            'global_index' => 76,  // getNextIndex will return 77
            'field_index' => 0,
            'item_data' => [],
            'expected' => '0077-SUFFIX',
        ];

        // Test template with empty suffix
        yield 'Template with empty suffix' => [
            'config' => [
                'template' => 'PREFIX-<####>',
                'use_index' => true,
            ],
            'global_index' => 87,  // getNextIndex will return 88
            'field_index' => 0,
            'item_data' => [],
            'expected' => 'PREFIX-0088',
        ];

        // Test with index 0
        yield 'Template with index 0' => [
            'config' => [
                'template' => '<####>',
                'use_index' => true,
            ],
            'global_index' => 0,  // getNextIndex will return 1 (not 0!)
            'field_index' => 0,
            'item_data' => [],
            'expected' => '0001',  // Changed from 0000 to 0001
        ];

        // Test with large index number
        yield 'Template with large index' => [
            'config' => [
                'template' => '<######>',
                'use_index' => true,
            ],
            'global_index' => 999998,  // getNextIndex will return 999999
            'field_index' => 0,
            'item_data' => [],
            'expected' => '999999',
        ];

        // Test with index overflow (larger than mask)
        yield 'Template with index overflow' => [
            'config' => [
                'template' => '<###>',
                'use_index' => true,
            ],
            'global_index' => 12344,  // getNextIndex will return 12345
            'field_index' => 0,
            'item_data' => [],
            'expected' => '12345',
        ];
    }

    /**
     * Test autoName method with various templates and configurations
     */
    #[DataProvider('autoNameProvider')]
    public function testAutoName(
        array $config,
        int $global_index,
        int $field_index,
        array $item_data,
        string $expected
    ): void {
        // Initialize global config with the index
        $this->initConfig(['index' => $global_index]);

        // Initialize field config if not using global index
        if (!$config['use_index']) {
            $this->setConfigField(Computer::class, [
                'index' => $field_index,
                'template' => $config['template'],
            ]);
        }

        // Create a Computer instance with the provided data
        $item = new Computer();
        $item->fields = [];
        $item->input = $item_data;

        $config['itemtype'] = Computer::class;

        // Call autoName and verify the result
        $result = PluginGeninventorynumberGeneration::autoName($config, $item);

        $this->assertEquals(
            $expected,
            $result,
            "AutoName generation failed for template: {$config['template']}",
        );
    }

    /**
     * Data provider for item lifecycle tests (add + update)
     * Plugin is always active in these tests
     */
    public static function itemLifecycleProvider(): iterable
    {
        // Test 1: Simple template - generate and protect
        yield 'Simple template - generate and protect' => [
            'field_config' => [
                'itemtype' => Computer::class,
                'is_active' => 1,
                'use_index' => 1,
                'template' => 'PC-<#####>',
            ],
            'global_index' => 0,
            'item_data' => [
                'name' => 'Test Computer',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^PC-\d{5}$/',
            'update_otherserial' => 'PC-99999',
        ];

        // Test 2: With date placeholders
        yield 'With date placeholders' => [
            'field_config' => [
                'itemtype' => Computer::class,
                'is_active' => 1,
                'use_index' => 1,
                'template' => '<\Y\m\d-####>',
            ],
            'global_index' => 0,
            'item_data' => [
                'name' => 'Test Computer',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^\d{8}-\d{4}$/',
            'update_otherserial' => '20991231-9999',
        ];

        // Test 3: With serial placeholder
        yield 'With serial placeholder' => [
            'field_config' => [
                'itemtype' => Computer::class,
                'is_active' => 1,
                'use_index' => 1,
                'template' => '<\s-###>',
            ],
            'global_index' => 0,
            'item_data' => [
                'name' => 'Test Computer',
                'serial' => 'SN123',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^SN123-\d{3}$/',
            'update_otherserial' => 'SN999-999',
        ];

        // Test 4: Try to clear otherserial - should be blocked
        yield 'Try to clear generated otherserial' => [
            'field_config' => [
                'itemtype' => Computer::class,
                'is_active' => 1,
                'use_index' => 1,
                'template' => 'PC-<#####>',
            ],
            'global_index' => 0,
            'item_data' => [
                'name' => 'Test Computer',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^PC-\d{5}$/',
            'update_otherserial' => '',
        ];

        // Test 5: Field index instead of global
        yield 'Using field index' => [
            'field_config' => [
                'itemtype' => Monitor::class,
                'is_active' => 1,
                'use_index' => 0,  // Use field index
                'template' => 'MON-<####>',
            ],
            'global_index' => 100,
            'item_data' => [
                'name' => 'Test Monitor',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^MON-\d{4}$/',
            'update_otherserial' => 'MON-9999',
        ];

        // Test 6: Complex template
        yield 'Complex template' => [
            'field_config' => [
                'itemtype' => Computer::class,
                'is_active' => 1,
                'use_index' => 1,
                'template' => 'GLPI-<\Y-######>-PROD',
            ],
            'global_index' => 0,
            'item_data' => [
                'name' => 'Production Server',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^GLPI-\d{4}-\d{6}-PROD$/',
            'update_otherserial' => 'CUSTOM-999999',
        ];

        // Test 7: Test with CustomAsset
        yield 'Test with CustomAsset' => [
            'field_config' => [
                'itemtype' => 'Glpi\\CustomAsset\\Test01Asset',
                'is_active' => 1,
                'use_index' => 1,
                'template' => 'GLPI-<\Y-######>-PROD',
            ],
            'global_index' => 0,
            'item_data' => [
                'name' => 'Production Server',
                'entities_id' => 0,
            ],
            'expected_pattern' => '/^GLPI-\d{4}-\d{6}-PROD$/',
            'update_otherserial' => 'CUSTOM-999999',
        ];
    }

    /**
     * Test item lifecycle: creation (preItemAdd) and update (preItemUpdate)
     * Plugin is always active in these tests
     */
    #[DataProvider('itemLifecycleProvider')]
    public function testItemLifecycle(
        array $field_config,
        int $global_index,
        array $item_data,
        string $expected_pattern,
        string $update_otherserial
    ): void {
        // === PART 1: TEST ITEM CREATION (preItemAdd) ===

        // Initialize global config with plugin active
        $this->initConfig([
            'is_active' => 1,
            'index' => $global_index,
        ]);

        // Initialize field config
        $config_field = $this->setConfigField($field_config['itemtype'], [
            'is_active' => $field_config['is_active'],
            'use_index' => $field_config['use_index'],
            'template' => $field_config['template'],
            'index' => 0,
        ]);

        // Store initial index values
        $initial_global_index = $this->getConfig()->fields['index'];
        $initial_field_index = $config_field->fields['index'];

        // Create the item
        $itemtype = $field_config['itemtype'];
        $item = $this->createItem($itemtype, $item_data);

        // Verify otherserial was generated and matches pattern
        $this->assertMatchesRegularExpression(
            $expected_pattern,
            $item->fields['otherserial'],
            "[ADD] Generated otherserial should match expected pattern",
        );

        // Verify index was incremented
        if ($field_config['use_index']) {
            $config = $this->getConfig();
            $this->assertEquals(
                $initial_global_index + 1,
                $config->fields['index'],
                "[ADD] Global index should be incremented",
            );
        } else {
            $config_field->getFromDB($config_field->getID());
            $this->assertEquals(
                $initial_field_index + 1,
                $config_field->fields['index'],
                "[ADD] Field index should be incremented",
            );
        }

        // Store the generated otherserial
        $generated_otherserial = $item->fields['otherserial'];

        // === PART 2: TEST ITEM UPDATE (preItemUpdate) ===

        // Try to update the otherserial (should be blocked by plugin)
        // Use updateItem with skip_fields to ignore otherserial verification
        // since we're testing that it should NOT change
        $update_data = [
            'name' => 'Updated Item Name',
            'otherserial' => $update_otherserial,
        ];

        $item = $this->updateItem($itemtype, $item->getID(), $update_data, ['otherserial']);

        // Verify otherserial was NOT modified (protection worked)
        $this->assertEquals(
            $generated_otherserial,
            $item->fields['otherserial'],
            "[UPDATE] Otherserial should not be modified when plugin is active",
        );
    }

    /**
     * Test item lifecycle when ConfigField is INACTIVE
     * Tests that:
     * - On ADD: otherserial is NOT generated automatically
     * - On UPDATE: otherserial is NOT generated automatically
     */
    public function testItemLifecycleWithConfigFieldInactive(): void
    {
        // Initialize plugin as active
        $this->initConfig(['is_active' => 1]);

        // Initialize field config as INACTIVE
        $this->setConfigField(Computer::class, [
            'is_active' => 0,
            'use_index' => 1,
            'template' => 'INACTIVE-<####>',
            'index' => 0,
        ]);

        // === PART 1: TEST CREATION - Should NOT generate otherserial ===

        $item = $this->createItem(Computer::class, [
            'name' => 'Test Computer',
            'entities_id' => 0,
        ]);

        // Should NOT generate otherserial
        $this->assertEmpty(
            $item->fields['otherserial'] ?? '',
            "[ADD] Otherserial should not be generated when ConfigField is inactive",
        );

        // Verify index was NOT incremented
        $config = $this->getConfig();
        $this->assertEquals(0, $config->fields['index'], "[ADD] Index should not be incremented");

        // === PART 2: TEST UPDATE - Should NOT generate otherserial ===

        $item = $this->updateItem(Computer::class, $item->getID(), [
            'name' => 'Updated Name',
        ]);

        // Should still not have otherserial
        $this->assertEmpty(
            $item->fields['otherserial'] ?? '',
            "[UPDATE] Otherserial should not be generated on update when ConfigField is inactive",
        );

        // Verify index still not incremented
        $config = $this->getConfig();
        $this->assertEquals(0, $config->fields['index'], "[UPDATE] Index should still not be incremented");
    }
}
