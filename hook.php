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

/**
 * Plugin install process
 *
 * @return boolean
 */
function plugin_patchpanel_install() {
   global $DB;

   //instanciate migration with version
   $migration = new Migration(100);

   //Create table only if it does not exists yet!
   if (!$DB->tableExists('glpi_plugin_patchpanel_patchpanels')) {
      //table creation query
      $query = "CREATE TABLE `glpi_plugin_patchpanel_patchpanels` (
                  `id` INT(11) NOT NULL AUTO_INCREMENT,
                  `entities_id` int(11) NOT NULL DEFAULT '0',
                  `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `serial` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `states_id` int(11) NOT NULL DEFAULT '0',
                  `locations_id` int(11) NOT NULL DEFAULT '0',
                  `plugin_patchpanel_patchpaneltypes_id` int(11) NOT NULL DEFAULT '0',
                  `users_id_tech` int(11) NOT NULL DEFAULT '0',
                  `manufacturers_id` int(11) NOT NULL DEFAULT '0',
                  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
                  `is_template` tinyint(1) NOT NULL DEFAULT '0',
                  `template_name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
                  `groups_id_tech` int(11) NOT NULL DEFAULT '0',
                  `pluginpatchpanelpatchpanelmodels_id` int(11) NOT NULL DEFAULT '0',
                  `otherserial` VARCHAR(255) NOT NULL,
                  `users_id` VARCHAR(255) NOT NULL,
                  `networks_id` VARCHAR(255) NOT NULL,
                  `groups_id` VARCHAR(255) NOT NULL,
                  `comment` text COLLATE utf8_unicode_ci,
                  `date_mod` datetime DEFAULT NULL,
                  `date_creation` datetime DEFAULT NULL,
                  PRIMARY KEY  (`id`),
                  KEY `name` (`name`),
                  KEY `serial` (`serial`),
                  KEY `states_id` (`states_id`),
                  KEY `locations_id` (`locations_id`),
                  KEY `plugin_patchpanel_patchpaneltypes_id` (`plugin_patchpanel_patchpaneltypes_id`),
                  KEY `users_id_tech` (`users_id_tech`),
                  KEY `manufacturers_id` (`manufacturers_id`),
                  KEY `groups_id_tech` (`groups_id_tech`),
                  KEY `pluginpatchpanelpatchpanelmodels_id` (`pluginpatchpanelpatchpanelmodels_id`),
                  KEY `is_deleted` (`is_deleted`),
                  KEY `is_template` (`is_template`),
                  KEY `groups_id` (`groups_id`),
                  KEY `date_mod` (`date_mod`),
                  KEY `date_creation` (`date_creation`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, $DB->error());
   }
   if ($DB->fieldExists("glpi_plugin_patchpanel_patchpanels", "pluginpatchpanelpatchpaneltypes_id")) {
      $query = "ALTER TABLE glpi_plugin_patchpanel_patchpanels CHANGE pluginpatchpanelpatchpaneltypes_id plugin_patchpanel_patchpaneltypes_id int(11) DEFAULT 0 NOT NULL;";
      $DB->queryOrDie($query, $DB->error());
   }

   if (!$DB->tableExists('glpi_plugin_patchpanel_patchpanelmodels')) {
      //table creation query
      $query = "CREATE TABLE `glpi_plugin_patchpanel_patchpanelmodels` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
         `comment` text COLLATE utf8_unicode_ci,
         `product_number` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
         `weight` int(11) NOT NULL DEFAULT '0',
         `required_units` int(11) NOT NULL DEFAULT '1',
         `depth` float NOT NULL DEFAULT 1,
         `is_half_rack` tinyint(1) NOT NULL DEFAULT '0',
         `picture_front` text COLLATE utf8_unicode_ci,
         `picture_rear` text COLLATE utf8_unicode_ci,
         `date_mod` datetime DEFAULT NULL,
         `date_creation` datetime DEFAULT NULL,
         PRIMARY KEY (`id`),
         KEY `name` (`name`),
         KEY `date_mod` (`date_mod`),
         KEY `date_creation` (`date_creation`),
         KEY `product_number` (`product_number`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, $DB->error());
   }

   if (!$DB->tableExists('glpi_plugin_patchpanel_patchpaneltypes')) {
      //table creation query
      $query = "CREATE TABLE `glpi_plugin_patchpanel_patchpaneltypes` (
         `id` int(11) NOT NULL AUTO_INCREMENT,
         `name` varchar(255) COLLATE utf8_unicode_ci DEFAULT NULL,
         `comment` text COLLATE utf8_unicode_ci,
         `date_mod` datetime DEFAULT NULL,
         `date_creation` datetime DEFAULT NULL,
         PRIMARY KEY (`id`),
         KEY `name` (`name`),
         KEY `date_mod` (`date_mod`),
         KEY `date_creation` (`date_creation`)
         ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, $DB->error());
   }

   if (!$DB->tableExists('glpi_plugin_patchpanel_items_patchpanels')) {
      //table creation query
      $query = "CREATE TABLE `glpi_plugin_patchpanel_items_patchpanels` (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `logical_number` int(11) NOT NULL,
                  `name` VARCHAR(255) NOT NULL,
                  `pluginpatchpanelpatchpanel_id` INT(11) NOT NULL DEFAULT '0',
                  `netpoints_id` INT(11) NOT NULL DEFAULT '0',
                  `itemtype` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                  `items_id` int(11) NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`id`),
                  KEY `name` (`name`),
                  KEY `pluginpatchpanelpatchpanel_id` (`pluginpatchpanelpatchpanel_id`),
                  KEY `netpoints_id` (`netpoints_id`),
                  KEY `logical_number` (`logical_number`)
               ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci";
      $DB->queryOrDie($query, $DB->error());
   }

   //execute the whole migration
   $migration->executeMigration();

   return true;
}

/**
 * Plugin uninstall process
 *
 * @return boolean
 */
function plugin_patchpanel_uninstall() {
   global $DB;

   $tables = [
      'patchpanels',
      'patchpanel_netpoint',
      'items_patchpanels',
      'types',
      'patchpanelmodels'
   ];

   foreach ($tables as $table) {
      $tablename = 'glpi_plugin_patchpanel_' . $table;
      //Create table only if it does not exists yet!
      if ($DB->tableExists($tablename)) {
         $DB->queryOrDie(
            "DROP TABLE `$tablename`",
            $DB->error()
         );
      }
   }

   return true;
}

function plugin_patchpanel_getDatabaseRelations() {
   return [
      "glpi_plugin_patchpanel_patchpaneltypes" => ["glpi_plugin_patchpanel" => "pluginpatchpanelpatchpaneltypes_id"],
      "glpi_plugin_patchpanel_patchpanelmodels" => ["glpi_plugin_patchpanel" => "pluginpatchpanelpatchpanelmodels_id"],
      "glpi_plugin_patchpanel_patchpanel" => ["glpi_plugin_patchpanel" => "pluginpatchpanelpatchpanel_id"],
   ];
}

function plugin_patchpanel_getDropdown() {
   return [
      'PluginPatchpanelPatchpanelModel' => PluginPatchpanelPatchpanelModel::getTypeName(2),
      'PluginPatchpanelPatchpanelType' => PluginPatchpanelPatchpanelType::getTypeName(2),
   ];
}