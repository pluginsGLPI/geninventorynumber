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

use Glpi\Toolbox\Sanitizer;

class PluginGeninventorynumberGeneration {

   static function autoName($config, CommonDBTM $item) {

      $template = Sanitizer::unsanitize($config['template']);

      $matches = [];
      if (preg_match('/^<[^#]*(#{1,10})[^#]*>$/', $template, $matches) !== 1) {
         return $template;
      }

      $autoNum = Toolbox::substr($template, 1, Toolbox::strlen($template) - 2);
      $mask    = $matches[1];

      $autoNum = str_replace(
         [
            '\\y',
            '\\Y',
            '\\m',
            '\\d',
            '\\g'
         ], [
            date('y'),
            date('Y'),
            date('m'),
            date('d'),
            ''
         ],
         $autoNum
      );

      $len  = Toolbox::strlen($mask);

      if ($config['use_index']) {
         $newNo = PluginGeninventorynumberConfig::getNextIndex();
      } else {
         $newNo = PluginGeninventorynumberConfigField::getNextIndex($config['itemtype']);
      }

      $template = str_replace($mask, Toolbox::str_pad($newNo, $len, '0', STR_PAD_LEFT), $autoNum);

      return Sanitizer::sanitize($template);
   }

   /**
    * @override CommonDBTM::preItemAdd
    */
   static function preItemAdd(CommonDBTM $item) {
      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
         if ((!Session::haveRight("plugin_geninventorynumber", CREATE))) {
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(__('You can\'t modify inventory number',
                                                'geninventorynumber'), true, ERROR);
            }
            return;
         }

         if (PluginGeninventorynumberConfig::isGenerationActive()
            && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
            $item->input['otherserial'] = self::autoName($config, $item);
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(__('An inventory number have been generated', 'geninventorynumber'), true);
            }

            if ($config['use_index']) {
               PluginGeninventorynumberConfig::updateIndex();
            } else {
               PluginGeninventorynumberConfigField::updateIndex(get_class($item));
            }
         }
      }
   }

   /**
    * @override CommonDBTM::preItemUpdate
    */
   static function preItemUpdate(CommonDBTM $item) {
      if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
         return;
      }

      if (PluginGeninventorynumberConfig::isGenerationActive()
         && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))
            && !isset($item->input['massiveaction'])) {

         if (isset($item->fields['otherserial'])
            && isset($item->input['otherserial'])
               && $item->fields['otherserial'] != $item->input['otherserial']) {
            $item->input['otherserial'] = $item->fields['otherserial'];
            if (!isCommandLine()) {
               Session::addMessageAfterRedirect(
                  __('You can\'t modify inventory number', 'geninventorynumber'),
                  true, ERROR);
            }
         }
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
   **/
   static function showMassiveActionsSubForm(MassiveAction $ma) {
      global $GENINVENTORYNUMBER_TYPES;

      // KK TODO: check if MassiveAction itemtypes are concerned
      //if (in_array ($options['itemtype'], $GENINVENTORYNUMBER_TYPES)) {
      switch ($ma->getAction()) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            echo "&nbsp;<input type=\"submit\" name=\"massiveaction\" class=\"submit\" value=\"" .
               _sx('button', 'Add') . "\" >";
            break;
         default :
            break;
      }
       return true;
   }

   /**
    * Generate numbers from a massive update
    *
    * @since 9.1+1.0
    *
    * @param CommonDBTM $item Existing item to update
    */
   static function doMassiveUpdate(CommonDBTM $item) {
      $config = PluginGeninventorynumberConfigField::getConfigFieldByItemType(get_class($item));

      if (in_array(get_class($item), PluginGeninventorynumberConfigField::getEnabledItemTypes())) {
         $tmp    = clone $item;
         $values = ['id' => $item->getID()];

         if (PluginGeninventorynumberConfig::isGenerationActive()
            && PluginGeninventorynumberConfigField::isActiveForItemType(get_class($item))) {
            $values['otherserial']   = self::autoName($config, $item);
            $values['massiveaction'] = true;
            $tmp->update($values);

            if ($config['use_index']) {
               PluginGeninventorynumberConfig::updateIndex();
            } else {
               PluginGeninventorynumberConfigField::updateIndex(get_class($item));
            }
            return ['ok'];
         } else {
            $values['otherserial'] = '';
            $tmp->update($values);
         }
      }
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
   **/
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item,
                                                       array $ids) {
      $results = ['ok' => 0, 'ko' => 0, 'noright' => 0, 'messages' => []];

      switch ($ma->getAction()) {
         case "plugin_geninventorynumber_generate" :
         case "plugin_geninventorynumber_overwrite" :
            //KK Not sure we could have multiple itemtimes
            foreach ($ma->items as $itemtype => $val) {
               foreach ($val as $key => $item_id) {
                  $item = new $itemtype;
                  $item->getFromDB($item_id);
                  if ($ma->getAction() == "plugin_geninventorynumber_generate") {
                     //Only generates inventory number for object without it !
                     if (isset ($item->fields["otherserial"])
                        && ($item->fields["otherserial"] == "")) {

                        if (!Session::haveRight("plugin_geninventorynumber", CREATE)) {
                           $results['noright']++;
                        } else {
                           $myresult = self::doMassiveUpdate($item);
                           $results[$myresult[0]]++;
                        }
                     } else {
                        $results['ko']++;
                     }
                  }

                  //Or is overwrite action is selected
                  if (($ma->getAction() == "plugin_geninventorynumber_overwrite")) {
                     if (!Session::haveRight("plugin_geninventorynumber", UPDATE)) {
                        $results['noright']++;
                     } else {
                        $myresult = self::doMassiveUpdate($item);
                        $results[$myresult[0]]++;
                     }
                  }
               }
            }
            break;

         default :
            break;
      }
      $ma->results=$results;
   }
}
