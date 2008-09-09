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

$title = "Génération des numéros d'inventaire";

$LANGGENINVENTORY["title"][1] = "".$title."";

$LANGGENINVENTORY["config"][0] = "Génération activée (global)";
$LANGGENINVENTORY["config"][1] = "Composants impactés";
$LANGGENINVENTORY["config"][2] = "Actif sur remontées OCS";
$LANGGENINVENTORY["config"][3] = "Actif sur création dans GLPI";
$LANGGENINVENTORY["config"][4] = "Actif sur injection de données";
$LANGGENINVENTORY["config"][10] = "Modèle de génération : ";

$LANGGENINVENTORY["setup"][0] = "Configuration du plugin";
$LANGGENINVENTORY["setup"][1] = "Installer le plugin";
$LANGGENINVENTORY["setup"][2] = "Désinstaller le plugin";
$LANGGENINVENTORY["setup"][3] = "Attention, la désinstallation du plugin est irréversible.<br> Vous perdrez toutes les données.";
$LANGGENINVENTORY["setup"][4] = "Merci de vous placer sur l'entité racine (voir tous)";
$LANGGENINVENTORY["setup"][5] = "Gestion des droits";

$LANGGENINVENTORY["profiles"][0] = "Gestion des droits";
$LANGGENINVENTORY["profiles"][1] = "Générer des numéros d'inventaire";
$LANGGENINVENTORY["profiles"][2] = "Listes des profils déjà configurés";

$LANGGENINVENTORY["massiveaction"][0] = "Générer un numéro d'inventaire";
$LANGGENINVENTORY["massiveaction"][1] = "Regénérer un numéro d'iventaire (écraser)";
?>