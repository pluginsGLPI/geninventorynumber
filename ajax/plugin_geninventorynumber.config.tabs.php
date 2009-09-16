<?php


/*----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2008 by the INDEPNET Development Team.

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
   ----------------------------------------------------------------------*/
/*----------------------------------------------------------------------
    Original Author of file: 
    Purpose of file:
    ----------------------------------------------------------------------*/
$NEEDED_ITEMS=array("reservation","link","computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();


useplugin('geninventorynumber',true);

if(!isset($_POST["ID"])) {
	exit();
}

$config = new PluginGenInventoryNumberConfig;
$config->getFromDB($_POST["ID"]);

		switch($_POST['glpi_tab']){
			case -1:
            plugin_geninventorynumber_showCoreConfig($_POST['target'],$_POST["ID"]);
            if (!displayPluginAction(PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE,$_POST["ID"],$_POST['glpi_tab'])){
            }               
				break;
         case 1:
            plugin_geninventorynumber_showCoreConfig($_POST['target'],$_POST["ID"]);
         break;
         default:
            if (!displayPluginAction(PLUGIN_GENINVENTORYNUMBER_CONFIG_TYPE,$_POST["ID"],$_POST['glpi_tab'])){
            }               
		}
?>