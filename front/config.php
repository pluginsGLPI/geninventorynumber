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

include ('../../../inc/includes.php');

$config = new PluginGeninventorynumberConfig();
$config->getFromDB(1);
if (Plugin::isPluginActive("geninventorynumber")) {
   Html::header(__('Inventory number generation', 'geninventorynumber'),
                $_SERVER['PHP_SELF'], "tools", "plugins", "geninventorynumber");
   if (isset($_GET['glpi_tab'])) {
      $_SESSION['glpi_tabs']['plugingeninventorynumberconfig'] = $_GET['glpi_tab'];
      Html::redirect(Toolbox::getItemTypeFormURL($config->getType()));
   }
   $config->showTabsContent();
   Html::footer();
}
