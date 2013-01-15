<<?php
/*
 * @version $Id$
 LICENSE

 This file is part of the geninventorynumber plugin.

 geninventorynumber plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 geninventorynumber plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with geninventorynumber. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   geninventorynumber
 @author    the geninventorynumber plugin team
 @copyright Copyright (c) 2010-2011 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

class PluginGeninventorynumberGeneration {
   static function autoName($objectName, $itemtype, $entities_id = 0,
                              $fields, $field_params = array ()) {
   
      $len = strlen($objectName);
      if ($len > 8 && substr($objectName, 0, 4) === '&lt;'
         && substr($objectName, $len -4, 4) === '&gt;') {
   
         $autoNum = substr($objectName, 4, $len -8);
         $mask    = '';
         if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
            $serial = (isset ($fields['serial']) ? $fields['serial'] : '');
   
            $global  = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
            $autoNum = str_replace(array ('\\y', '\\Y', '\\m', '\\d', '_', '%', '\\g', '\\s'),
                                   array (date('y'), date('Y'), date('m'), date('d'), '\\_',
                                           '\\%', '', $serial), $autoNum);
            $mask    = $mask[0];
            $pos     = strpos($autoNum, $mask) + 1;
            $len     = strlen($mask);
            $like    = str_replace('#', '_', $autoNum);
   
            if ($field_params[$type]['use_index']) {
               $index = PluginGeninventorynumberConfig::getNextIndex('otherserial');
            } else {
               $index = PluginGeninventorynumberIndex::getIndexByTypeName($itemtype, 'otherserial');
            }
   
            $next_number = str_pad($index, $len, '0', STR_PAD_LEFT);
            $objectName  = str_replace(array ($mask, '\\_', '\\%'),
                                       array ($next_number,  '_',  '%'),
                                       $autoNum);
         }
      }
      return $objectName;
   }

   function plugin_item_add_geninventorynumber($parm, $massive_action = false, $field = 'otherserial') {
      global $DB, $LANG;
   
      $fields = plugin_geninventorynumber_getFieldInfos($field);
   
      if (isset ($parm["type"]) && isset ($fields[$parm["type"]])) {
         $config = plugin_geninventorynumber_getConfig();
   
         //Globally check if auto generation is on
         if ($config->fields['active']) {
   
            if ($fields[$parm["type"]]['enabled']) {
               $template = addslashes_deep($fields[$parm["type"]]['template']);
   
               $commonitem = new CommonItem;
               $commonitem->getFromDB($parm["type"], $parm["ID"]);
   
               $generated_field = plugin_geninventorynumber_autoName($template, $parm["type"], 0, $commonitem->obj->fields, $fields);
   
               //Cannot use update() because it'll launch pre_item_update and clean the inventory number...
               $sql = "UPDATE " . $commonitem->obj->table . " SET otherserial='" . $generated_field . "' WHERE ID=" . $parm["ID"];
               $DB->query($sql);
   
               if (!$massive_action && strstr($_SESSION["MESSAGE_AFTER_REDIRECT"], $LANG["plugin_geninventorynumber"]["massiveaction"][3]) === false)
                  $_SESSION["MESSAGE_AFTER_REDIRECT"] .= $LANG["plugin_geninventorynumber"]["massiveaction"][3];
   
               if ($fields[$parm["type"]]['use_index'])
                  $sql = "UPDATE glpi_plugin_geninventorynumber_config SET next_number=next_number+1 WHERE FK_entities=0";
               else
                  $sql = "UPDATE glpi_plugin_geninventorynumber_indexes SET next_number=next_number+1 WHERE FK_entities=0 AND type='".$parm["type"]."' AND field='otherserial'";
               $DB->query($sql);
   
            }
         }
      }
   
      return $parm;
   }

   function plugin_geninventorynumber_updateIndexes($params) {
      global $DB;
   
      if (isset ($params["update"])) {
         $config = new PluginGenInventoryNumberConfig;
         $config->update($params);
      }
   
      if (isset ($params["update_fields"]) || isset ($params["update_unicity"])) {
         $field = new PluginGenInventoryNumberFieldDetail;
   
         //Update each type's index
         foreach ($params["IDS"] as $type => $datas) {
            $field->update($datas);
         }
      }
}