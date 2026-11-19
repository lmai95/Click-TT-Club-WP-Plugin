<?php
if (!defined('WP_UNINSTALL_PLUGIN')) exit;
delete_option('cttcm_settings');
// Stale-/Transient-Daten bleiben absichtlich unangetastet, um keine breit gefassten SQL-Löschungen beim Uninstall auszuführen.
