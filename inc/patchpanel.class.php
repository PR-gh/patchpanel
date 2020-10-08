<?php
/**
 * -------------------------------------------------------------------------
 * PatchPanel plugin for GLPI
 * Copyright (C) 2019 by the PatchPanel Development Team.
 *
 * https://github.com/pluginsGLPI/patchpanel
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of PatchPanel.
 *
 * PatchPanel is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * PatchPanel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PatchPanel. If not, see <http://www.gnu.org/licenses/>.
 * --------------------------------------------------------------------------
 */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access this file directly");
}

/**
 * Patch Panel Class
**/
class PluginPatchpanelPatchpanel extends CommonDBTM {
    use Glpi\Features\DCBreadcrumb;

    // From CommonDBTM
   public $dohistory                   = true;
   static protected $forward_entity_to = [];

   static $rightname                   = 'networking';
   protected $usenotepad               = true;

    /**
     * Name of the type
     *
     * @param $nb  integer  number of item in the type (default 0)
    **/
   static function getTypeName($nb = 0) {
       return _n('Patch panel', 'Patch panels', $nb, 'patchpanel');
   }

   function defineTabs($options = []) {
      $ong = [];
      $this->addDefaultFormTab($ong, $options)
        ->addStandardTab('PluginPatchpanelItem_Patchpanel', $ong, $options)
        ->addStandardTab('Log', $ong, $options);

      return $ong;
   }

   /**
    * Print the Patch Panel form
    *
    * @param $ID        integer ID of the item
    * @param $options   array
    *     - target filename : where to go when done.
    *     - withtemplate boolean : template or basic item
    *
    *@return boolean item found
   **/
   function showForm($ID, $options = []) {

      $this->initForm($ID, $options);
      $this->showFormHeader($options);

      $tplmark = $this->getAutofillMark('name', $options);
      echo "<tr class='tab_bg_1'>";
      //TRANS: %1$s is a string, %2$s a second one without spaces between them : to change for RTL
      echo "<td>".sprintf(__('%1$s%2$s'), __('Name'), $tplmark).
        "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["name"], "name",
                          (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                          $this->getType(), $this->fields["entities_id"]);
      Html::autocompletionTextField($this, "name", ['value' => $objectName]);
      echo "</td>";
      echo "<td>".__('Status')."</td>";
      echo "<td>";
      State::dropdown([
       'value'     => $this->fields["states_id"],
       'entity'    => $this->fields["entities_id"],
       'condition' => ['is_visible_networkequipment' => 1]
      ]);
      echo "</td></tr>";

      $this->showDcBreadcrumb();

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Location')."</td>";
      echo "<td>";
      Location::dropdown(['value'  => $this->fields["locations_id"],
                           'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      echo "<td>".__('Type')."</td>";
      echo "<td>";
      PluginPatchpanelPatchpanelType::dropdown(['value' => $this->fields["plugin_patchpanel_patchpaneltypes_id"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Technician in charge of the hardware')."</td>";
      echo "<td>";
      User::dropdown(['name'   => 'users_id_tech',
                        'value'  => $this->fields["users_id_tech"],
                        'right'  => 'own_ticket',
                        'entity' => $this->fields["entities_id"]]);
      echo "</td>";
      echo "<td>".__('Manufacturer')."</td>";
      echo "<td>";
      Manufacturer::dropdown(['value' => $this->fields["manufacturers_id"]]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group in charge of the hardware')."</td>";
      echo "<td>";
      Group::dropdown([
       'name'      => 'groups_id_tech',
       'value'     => $this->fields['groups_id_tech'],
       'entity'    => $this->fields['entities_id'],
       'condition' => ['is_assign' => 1]
      ]);
      echo "</td>";
      echo "<td>".__('Model')."</td>";
      echo "<td>";
      PluginPatchpanelPatchpanelModel::dropdown([
         'value'  => $this->fields['pluginpatchpanelpatchpanelmodels_id'],
         'name'   => 'pluginpatchpanelpatchpanelmodels_id'
      ]);
      echo "</td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Serial number')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this, "serial");
      echo "</td>";
      $tplmark = $this->getAutofillMark('otherserial', $options);
      echo "<td>".sprintf(__('%1$s%2$s'), __('Inventory number'), $tplmark).
        "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["otherserial"], "otherserial",
                          (isset($options['withtemplate']) && ($options['withtemplate'] == 2)),
                          $this->getType(), $this->fields["entities_id"]);
      Html::autocompletionTextField($this, "otherserial", ['value' => $objectName]);
      echo "</td></tr>";

      $rowspan = 4;

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('User')."</td>";
      echo "<td>";
      User::dropdown(['value'  => $this->fields["users_id"],
                        'entity' => $this->fields["entities_id"],
                        'right'  => 'all']);
      echo "</td>";
      echo "<td rowspan='$rowspan'>".__('Comments')."</td>";
       echo "<td rowspan='$rowspan'>
            <textarea cols='45' rows='".($rowspan+3)."' name='comment' >".$this->fields["comment"];
       echo "</textarea></td></tr>";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__('Group')."</td>";
      echo "<td>";
      Group::dropdown([
       'value'     => $this->fields["groups_id"],
       'entity'    => $this->fields["entities_id"],
       'condition' => ['is_itemgroup' => 1]
      ]);
      echo "</td></tr>";

      $rowspan = 2;
      echo "<tr class='tab_bg_1'>";
      echo "<td rowspan='$rowspan' colspan='$rowspan'>";
      echo "</td></tr><tr></tr>";

      $this->showFormButtons($options);
      return true;
   }
}
