<?php
/*

   ----------------------------------------------------------------------
   GLPI - Gestionnaire Libre de Parc Informatique
   Copyright (C) 2003-2005 by the INDEPNET Development Team.

   http://indepnet.net/   http://glpi-project.org/

   ----------------------------------------------------------------------
   LICENSE

   This program is free software; you can redistribute it and/or
   modify it under the terms of the GNU General Public License (GPL)
   as published by the Free Software Foundation; either version 2
   of the License, or (at your option) any later version.

   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.

   To read the license please visit http://www.gnu.org/copyleft/gpl.html
   ----------------------------------------------------------------------
// Original Author of file: Walid Nouh
Purpose of file:
----------------------------------------------------------------------
 */

$title = "Inventory number generation";

$LANGGENINVENTORY["title"][1] = "".$title."";

$LANGGENINVENTORY["config"][0] = "Active";
$LANGGENINVENTORY["config"][1] = "Inventory number templates : ";
$LANGGENINVENTORY["config"][2] = "Active for OCS";
$LANGGENINVENTORY["config"][3] = "Active for GLPI";
$LANGGENINVENTORY["config"][4] = "Active for data injection";
$LANGGENINVENTORY["config"][5] = "Use global index";
$LANGGENINVENTORY["config"][6] = "Global index position";

$LANGGENINVENTORY["config"][10] = "Generation templates : ";

$LANGGENINVENTORY["setup"][0] = "Configure plugin ".$title;
$LANGGENINVENTORY["setup"][1] = "Install plugin $title";
$LANGGENINVENTORY["setup"][2] = "Uninstall plugin $title";
$LANGGENINVENTORY["setup"][3] = "Attention, la désinstallation du plugin est irréversible.<br> Vous perdrez toutes les données.";
$LANGGENINVENTORY["setup"][4] = "Please place yourself in the root entity";
$LANGGENINVENTORY["setup"][5] = "Rights management";

$LANGGENINVENTORY["profiles"][0] = "Rights management";
$LANGGENINVENTORY["profiles"][1] = "Generation inventory numbers";
$LANGGENINVENTORY["profiles"][2] = "Profiles list";

$LANGGENINVENTORY["massiveaction"][0] = "Generate inventory number";
$LANGGENINVENTORY["massiveaction"][1] = "Regenerate inventory number (overwrite)";
$LANGGENINVENTORY["massiveaction"][2] = "You can't modify inventory number";
$LANGGENINVENTORY["massiveaction"][3] = "An inventory number have been generated";
?>