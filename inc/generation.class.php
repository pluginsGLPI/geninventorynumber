<?php
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
 @copyright Copyright (c) 2008-2013 geninventorynumber plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://forge.indepnet.net/projects/geninventorynumber
 @link      http://www.glpi-project.org/
 @since     2008
 ---------------------------------------------------------------------- */

class PluginGeninventorynumberGeneration {

   static function autoName($config, CommonDBTM $item) {
   
      $template = $config['template'];
      $len      = strlen($template);
      if ($len > 8
         && substr($template, 0, 4) === '&lt;'
            && substr($template, $len - 4, 4) === '&gt;') {
   
         $autoNum = substr($template, 4, $len -8);
         $mask    = '';
         if (preg_match("/\\#{1,10}/", $autoNum, $mask)) {
            $serial = (isset ($item->fields['serial']) ? $item->fields['serial'] : '');
   
            $global  = strpos($autoNum, '\\g') !== false && $type != INFOCOM_TYPE ? 1 : 0;
            $autoNum = str_replace(array ('\\y', '\\Y', '\\m', '\\d', '_', '%', '\\g', '\\s'),
                                   array (date('y'), date('Y'), date('m'), date('d'), '\\_',
                                           '\\%', '', $serial), $autoNum);
            $mask    = $mask[0];
            $pos     = strpos($autoNum, $mask) + 1;
            $len     = strlen($mask);
            $like    = str_replace('#', '_', $autoNum);

            if ($config['use_index']) {
               $index = PluginGeninventorynumberConfig::getNextIndex();
            } else {
               $index = PluginGeninventorynumberConfigField::getNextIndex($config['itemtype']);
            }
   
            $next_number = str_pad($index, $len, '0', STR_PAD_LEFT);
            $template    = str_replace(array ($mask, '\\_', '\\%'),
                                       array ($next_number,  '_',  '%'),
                                       $autoNum);
         }
      }
      return $template;
   }

   static function itemAdd(CommonDBTM $item, $massiveaction = false) {
      global $LANG;
      
      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));
      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
         $field
            = self::autoName($config, $item);
         $tmp = clone $item;
         $item->fields['otherserial'] = $field;
         $tmp->update($item->fields);
         if (!$massiveaction &&
               strstr($_SESSION["MESSAGE_AFTER_REDIRECT"],
                      $LANG["plugin_geninventorynumber"]["massiveaction"][3]) === false) {
            Session::addMessageAfterRedirect($LANG["plugin_geninventorynumber"]["massiveaction"][3]);
         }
         
         if ($config['use_index']) {
            PluginGeninventorynumberConfig::updateIndex();
         } else {
            PluginGeninventorynumberConfigField::updateIndex(get_class($item));
         }
      }
   }
   
   static function preItemUpdate(CommonDBTM $item) {
      global $LANG;
      if (PluginGeninventorynumberConfig::isGenerationActive()
            && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
         if ($item->fields['otherserial'] != $item->input['otherserial']) {
            $item->input['otherserial'] = $item->fields['otherserial'];
            Session::addMessageAfterRedirect($LANG["plugin_geninventorynumber"]["massiveaction"][2],
                                             true, ERROR);
         }
      }
   }
}