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

namespace GlpiPlugin\Geninventorynumber\Tests\Units;

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
            'Config: ' . json_encode($config) . ' Date: ' . $date
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
            $computer_field->getFromDB($computer_field->getID())
        );

        // Unregister Computer itemtype
        PluginGeninventorynumberConfigField::unregisterNewItemType(\Computer::class);

        // Ensure the config field was deleted
        $this->assertFalse(
            $computer_field->getFromDB($computer_field->getID())
        );
    }

    public function testIsActiveForItemType(): void
    {
        $this->initConfig();

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
    }
}
