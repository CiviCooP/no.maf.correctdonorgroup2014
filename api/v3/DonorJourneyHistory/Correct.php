<?php
/**
 * DonorJourneyHistory.Correct API
 * 
 * The aim of this job is to correct the subscription history that has been 
 * muddled with data from attempts with subgroups for donor journeys.
 * 
 * Rationale:
 * - get all contacts in subscription history and for each contact:
 *   - remove all old subgroup history (any group membership that is part of
 *     the donor journey but group is no longer active)
 *   - if there is no group membership starting on 1-1-2014, leave contact alone
 *   - if there is a group membership starting on 1-1-2014 and group is part of
 *     the donor journey, remove all other membership records for that specific
 *     group
 *   - if there is another donor journey group in the history, add a remove of the
 *     first group on the date the other donor journey group starts
 *
 * @param array $params
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
function civicrm_api3_donor_journey_history_correct($params) {
  if (!function_exists('ocr_check_group_is_donorgroup')) {
    throw new Exception('Could not find function ocr_check_group_is_donorgroup, can not execute correction');
  }

  $contacts = array();
  $contactsSelect = 'SELECT DISTINCT(contact_id) AS contact_id FROM civicrm_subscription_history '
    . 'WHERE contact_id = 23825 ORDER BY contact_id';
  $daoContacts = CRM_Core_DAO::executeQuery($contactsSelect);
  while ($daoContacts->fetch()) {
    $contacts[] = $daoContacts->contact_id;
  }
  unset($daoContacts);
  
  foreach ($contacts as $contactId) {
    processSubscriptionHistory($contactId);
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorJourneyHistory', 'Correct');
}
/**
 * Function to determine processing for a contact
 * 
 * @param int $contactId
 */
function processSubscriptionHistory($contactId) {
  checkOldSubGroups($contactId);
  /*
   * only process if there is a subscription history on 01-01-2014 and found
   * group is part of donor journey
   */
  $query = 'SELECT group_id FROM civicrm_subscription_history WHERE date BETWEEN %1 AND %2';
  $params = array(1 => array('2013-10-01 00:00:00', 'String'), 2=> array('2013-10-01 23:59:59', 'String'));
  $dao = CRM_Core_DAO($query, $params);
  while ($dao->fetch) {
    if (ocr_check_group_is_donorgroup($dao->group_id) == TRUE) {
      removeCurrentGroupHistory($dao->group_id, $contactId);
      checkOtherDonorGroupHistory($dao->group_id, $contactId);
    }
  }
}
/**
 * Function to remove oldSubGroups from subscription_history
 * 
 * @param int $contactId
 */
function checkOldSubGroups($contactId) {
  $contactGroups = civicrm_api3('Groups', 'Get', array('contact_id' => $contactId));
  foreach ($contactGroups['values'] as $contactGroup) {
    /*
     * remove data if group is no longer active
     */
    $query = 'SELECT is_active FROM civicrm_group WHERE id = %1';
    $params = array(1 => array($contactGroup['group_id'], 'Positive'));
    $dao = CRM_Core_DAO::executeQuery($query, $params);
    if ($dao->fetch()) {
      removeOldSubGroup($contactId, $contactGroup['group_id'], $dao->is_active);
    }
  }
}
/**
 * Function to remove old group history if group is donor journey group
 * and no longer active
 * 
 * @param int $contactId
 * @param int $groupId
 * @param int $isActive
 */
function removeOldSubGroup($contactId, $groupId, $isActive) {
  if ($isActive == 0) {
    if (ocr_check_group_is_donorgroup($groupId) == TRUE) {
      $query = 'DELETE FROM civicrm_subscription_history WHERE contact_id = %1 AND group_id = %2';
      $params = array(
        1 => array($contactId, 'Positive'), 
        2 => array($groupId, 'Positive'));
      CRM_Core_DAO::executeQuery($query, $params);
    }
  }
}
/**
 * Function to remove subscription_history for the incoming group that is 
 * NOT on 01-01-2014
 * 
 * @param type $groupId
 * @param type $contactId
 */
function removeCurrentGroupHistory($groupId, $contactId) {
  $query = 'DELETE FROM civicrm_subscription_history WHERE contact_id = %1 AND group_id = %2 AND date > %3';
  $params = array(
    1 => array($contactId, 'Positive'), 
    2 => array($groupId, 'Positive'),
    3 => array('2013-10-01 23:59:59', 'String'));
  CRM_Core_DAO::executeQuery($query, $params);  
}
/**
 * Function to deal with another donor journey group if there is one
 * 
 * @param type $groupId
 * @param type $contactId
 */
function checkOtherDonorGroupHistory($groupId, $contactId) {
  $query = 'SELECT group_id, date FROM civicrm_subscription_history WHERE contact_id = %1 '
    . 'AND group_id != %2 AND date > %3 AND status = %4 ORDER BY date';
  $params = array(
    1 => array($contactId, 'Positive'), 
    2 => array($groupId, 'Positive'),
    3 => array('2013-10-01 23:59:59', 'String'),
    4 => array('Added', 'String'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  while ($dao->fetch()) {
    if (ocr_check_group_is_donorgroup($dao->group_id) == TRUE) {
      insertRemove($contactId, $groupId, $dao->date);
      break;
    }
  }
}
function insertRemove($contactId, $groupId, $removeDate) {
  $query = 'INSERT INTO civicrm_subscription_history (contact_id, group_id, date, method, status) '
    . 'VALUES(%1, %2, %3, %4, %5';
  $params = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'),
    3 => array($removeDate, 'String'),
    4 => array('API', 'String'),
    5 => array('Removed', 'String')
  );
  CRM_Core_DAO::executeQuery($query, $params);
}
