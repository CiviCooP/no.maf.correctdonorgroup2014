<?php

require_once 'correctdonorgroup2014.civix.php';

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function correctdonorgroup2014_civicrm_config(&$config) {
  _correctdonorgroup2014_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function correctdonorgroup2014_civicrm_xmlMenu(&$files) {
  _correctdonorgroup2014_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function correctdonorgroup2014_civicrm_install() {
  return _correctdonorgroup2014_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function correctdonorgroup2014_civicrm_uninstall() {
  return _correctdonorgroup2014_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function correctdonorgroup2014_civicrm_enable() {
  CRM_Core_DAO::executeQuery('CREATE TABLE IF NOT EXISTS donorlink_corrections (
  contact_id int(11)
  PRIMARY KEY (contact_id),
  UNIQUE KEY contact_id_UNIQUE (contact_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8');
  return _correctdonorgroup2014_civix_civicrm_enable();
}
/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function correctdonorgroup2014_civicrm_disable() {
  return _correctdonorgroup2014_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function correctdonorgroup2014_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _correctdonorgroup2014_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function correctdonorgroup2014_civicrm_managed(&$entities) {
  return _correctdonorgroup2014_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function correctdonorgroup2014_civicrm_caseTypes(&$caseTypes) {
  _correctdonorgroup2014_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function correctdonorgroup2014_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _correctdonorgroup2014_civix_civicrm_alterSettingsFolders($metaDataFolders);
}
/*
 * function to create record in donorlink_corrections
 * if not exists
 */
function correctdonorgroup2014_add_contact($contactId) {
  $params = array(1 => array($contactId, 'Positive'));
  $querySelect = 'SELECT COUNT(*) AS count_contact FROM donorlink_corrections WHERE contact_id = %1';
  $dao = CRM_COre_DAO::executeQuery($querySelect, $params);
  if ($dao->fetch()) {
    if ($dao->count_contact == 0) {
    $query = 'INSERT INTO donorlink_corrections SET contact_id = %1';
    CRM_Core_DAO::executeQuery($query, $params);
    }
  }
}

