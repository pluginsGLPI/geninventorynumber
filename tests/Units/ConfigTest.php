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
use PluginGeninventorynumberConfig;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * Test class for plugin configuration
 */
final class ConfigTest extends GenInventoryNumberTestCase
{
    public function testResetIndex(): void
    {
        $this->initConfig(['index' => 10]);

        $date = '2025-01-01 00:00:00';
        $_SESSION['glpi_currenttime'] = $date;

        PluginGeninventorynumberConfig::resetIndex();
        $config = $this->getConfig();
        $this->assertEquals(0, $config->fields['index']);
        $this->assertEquals($date, $config->fields['date_last_generated']);
    }

    public static function needIndexResetProvider(): iterable
    {
        $date = '2025-01-01 00:00:00';

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => null,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 day')),
            'need_reset' => true,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 day')),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 month')),
            'need_reset' => true,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date)),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 day')),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 month')),
            'need_reset' => false,
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
            ],
            'date' => date('Y-m-d H:i:s', strtotime($date . ' +1 year')),
            'need_reset' => true,
        ];
    }

    #[DataProvider('needIndexResetProvider')]
    public function testNeedIndexReset(array $config, string $date, bool $need_reset): void
    {
        $this->initConfig($config);

        $_SESSION['glpi_currenttime'] = $date;

        $this->assertEquals($need_reset, PluginGeninventorynumberConfig::needIndexReset());
    }

    public static function getNextIndexProvider(): iterable
    {
        $date = '2025-01-01 00:00:00';

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => null,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_NONE,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_DAILY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 day',
            'index' => [1, 1, 1],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 day',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_MONTHLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 month',
            'index' => [1, 1, 1],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => '',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 day',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
                'date_last_generated' => $date,
                'index' => 0,
            ],
            'date' => ' +1 month',
            'index' => [1, 2, 3],
        ];

        yield [
            'config' => [
                'auto_reset_method' => PluginGeninventorynumberConfig::AUTO_RESET_YEARLY,
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
        $this->initConfig($config);

        $base_time = $config['date_last_generated'] ?? date('Y-m-d H:i:s');
        $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime($base_time . $date));

        foreach ($index as $expected) {
            $this->assertEquals($expected, PluginGeninventorynumberConfig::getNextIndex(), "Config: " . json_encode($config) . " Date: $date");
            $_SESSION['glpi_currenttime'] = date('Y-m-d H:i:s', strtotime($_SESSION['glpi_currenttime'] . $date));
            $this->updateConfig(['index' => $expected]);
        }
    }
}
