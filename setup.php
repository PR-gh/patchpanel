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

define('PLUGIN_PATCHPANEL_VERSION', '0.9.4.0');

/**
 * Init hooks of the plugin.
 * REQUIRED
 *
 * @return void
 */
function plugin_init_patchpanel() {
   global $PLUGIN_HOOKS, $CFG_GLPI;

   Plugin::registerClass('PluginPatchpanelPatchpanelType');
   Plugin::registerClass('PluginPatchpanelPatchpanelModel');
   Plugin::registerClass('PluginPatchpanelPatchPanel');

   $itemsTypeParent = array_merge(['Netpoint'], $CFG_GLPI['networkport_types']);
   Plugin::registerClass('PluginPatchpanelItem_Patchpanel',
      ['addtabon' => $itemsTypeParent]
   );
   $CFG_GLPI['rackable_types'][] = 'PluginPatchpanelPatchPanel';

   $PLUGIN_HOOKS['csrf_compliant']['patchpanel'] = true;
   $PLUGIN_HOOKS['menu_toadd']['patchpanel'] = ['assets' => 'PluginPatchpanelPatchPanel'];
}


/**
 * Get the name and the version of the plugin
 * REQUIRED
 *
 * @return array
 */
function plugin_version_patchpanel() {
   return [
      'name'           => 'PatchPanel',
      'version'        => PLUGIN_PATCHPANEL_VERSION,
      'author'         => '<a href="https://github.com/PR-gh">PR-gh</a>',
      'license'        => 'GPLv2+',
      'homepage'       => '',
      'requirements'   => [
         'glpi' => [
            'min' => '9.4',
         ]
      ]
   ];
}

/**
 * Check pre-requisites before install
 * OPTIONNAL, but recommanded
 *
 * @return boolean
 */
function plugin_patchpanel_check_prerequisites() {

   //Version check is not done by core in GLPI < 9.2 but has to be delegated to core in GLPI >= 9.2.
   $version = preg_replace('/^((\d+\.?)+).*$/', '$1', GLPI_VERSION);
   if (version_compare($version, '9.4', '<')) {
      echo "This plugin requires GLPI >= 9.4";
      return false;
   }
   return true;
}

/**
 * Check configuration process
 *
 * @param boolean $verbose Whether to display message on failure. Defaults to false
 *
 * @return boolean
 */
function plugin_patchpanel_check_config($verbose = false) {
   if (true) { // Your configuration check
      return true;
   }

   if ($verbose) {
      echo __('Installed / not configured');
   }
   return false;
}
