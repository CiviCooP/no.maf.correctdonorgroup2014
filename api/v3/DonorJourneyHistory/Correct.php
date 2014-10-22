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
 * Run in parts because requires too much server time otherwise
 *
 * @param array $params
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */
set_time_limit(0);
function civicrm_api3_donor_journey_history_correct($params) {
  if (!function_exists('ocr_check_group_is_donorgroup')) {
    throw new Exception('Could not find function ocr_check_group_is_donorgroup, can not execute correction');
  }

  $contacts = array();
  $selectContacts = 'SELECT DISTINCT(contact_id) AS contact_id FROM civicrm_subscription_history '
    . ' WHERE contact_id < 10000 ORDER BY contact_id';
  $daoContacts = CRM_Core_DAO::executeQuery($selectContacts);
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
  $queryFirstDay = 'SELECT group_id FROM civicrm_subscription_history WHERE contact_id = %1 AND (date >= %2 AND date <= %3)';
  $paramsFirstDay = array(
    1 => array($contactId, 'Positive'),
    2 => array('2014-01-01 00:00:00', 'String'), 
    3 => array('2014-01-01 23:59:59', 'String'));
  $daoFirstDay = CRM_Core_DAO::executeQuery($queryFirstDay, $paramsFirstDay);
  while ($daoFirstDay->fetch()) {
    if (ocr_check_group_is_donorgroup($daoFirstDay->group_id) == TRUE) {
      removeCurrentGroupHistory($daoFirstDay->group_id, $contactId);
      checkOtherDonorGroupHistory($daoFirstDay->group_id, $contactId);
    }
  }
}
/**
 * Function to remove oldSubGroups from subscription_history
 * 
 * @param int $contactId
 */
function checkOldSubGroups($contactId) {
  $queryGroupContactSelect = 'SELECT group_id FROM civicrm_group_contact WHERE contact_id = %1';
  $paramsGroupContactSelect = array(1 => array($contactId, 'Positive'));
  $daoGroupContactSelect = CRM_Core_DAO::executeQuery($queryGroupContactSelect, $paramsGroupContactSelect);
  while ($daoGroupContactSelect->fetch()) {
    /*
     * remove data if group is no longer active
     */
    $queryGroup = 'SELECT is_active FROM civicrm_group WHERE id = %1';
    $paramsGroup = array(1 => array($daoGroupContactSelect->group_id, 'Positive'));
    $daoGroup = CRM_Core_DAO::executeQuery($queryGroup, $paramsGroup);
    if ($daoGroup->fetch()) {
      removeOldSubGroup($contactId, $daoGroupContactSelect->group_id, $daoGroup->is_active);
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
    $subGroups = getSubGroups();
    if (in_array($groupId, $subGroups)) {
      $queryOldSub = 'DELETE FROM civicrm_subscription_history WHERE contact_id = %1 AND group_id = %2';
      $paramsOld = array(
        1 => array($contactId, 'Positive'), 
        2 => array($groupId, 'Positive'));
      CRM_Core_DAO::executeQuery($queryOldSub , $paramsOld );
      $queryOldGroup = 'DELETE FROM civicrm_group_contact WHERE contact_id = %1 AND group_id = %2';
      CRM_Core_DAO::executeQuery($queryOldGroup, $paramsOld);
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
  $queryCurrentHist = 'DELETE FROM civicrm_subscription_history WHERE contact_id = %1 '
    . 'AND group_id = %2 AND date > %3';
  $paramsCurrentHist = array(
    1 => array($contactId, 'Positive'), 
    2 => array($groupId, 'Positive'),
    3 => array('2014-01-01 23:59:59', 'String'));
  $result = CRM_Core_DAO::executeQuery($queryCurrentHist, $paramsCurrentHist);
  /*
   * update GroupContact to reflect that donor group was added
   */
  updateGroupContacts($contactId, $groupId);
}
/**
 * Function to remove GroupContacts if group no longer in history
 * 
 * @param int $contactId
 * @param int $groupId
 */
function updateGroupContacts($contactId, $groupId) {
  /*
   * remove groupcontacts if group no longer in history
   * or make sure there is an 'Added' record if it is
   */
  $countHist = 'SELECT COUNT(*) AS countGroup FROM civicrm_subscription_history WHERE group_id = %1';
  $paramsHist = array(1 => array($groupId, 'Positive'));
  $daoHist = CRM_Core_DAO::executeQuery($countHist, $paramsHist);
  if ($daoHist->fetch()) {
    $paramsGroupContact = array(
      1 => array($contactId, 'Positive'),
      2 => array($groupId, 'Positive'));
    if ($daoHist->countGroup == 0) {
      $deleteGroupContact = 'DELETE FROM civicrm_group_contact WHERE contact_id = %1 AND group_id = %2';
      CRM_Core_DAO::executeQuery($deleteGroupContact, $paramsGroupContact);
    } else {
      $selectGroupContact = 'SELECT id FROM civicrm_group_contact WHERE contact_id = %1 AND group_id = %2';
      $daoGroupContact = CRM_Core_DAO::executeQuery($selectGroupContact, $paramsGroupContact);
      if ($daoGroupContact->fetch()) {
        $updateGroupContact = 'UPDATE civicrm_group_contact SET status = "Added" WHERE id = %1';
        $updateParams = array(1 => array($daoGroupContact->id, 'Positive'));
        CRM_Core_DAO::executeQuery($updateGroupContact, $updateParams);
      } else {
        $insertGroupContact = 'INSERT INTO civicrm_group_contact (group_id, contact_id, '
          . 'status) VALUES(%1, %2, %3)';
        $insertParams = array(
          1 => array($groupId, 'Positive'),
          2 => array($contactId, 'Positive'),
          3 => array('Added', 'String'));
        CRM_Core_DAO::executeQuery($insertGroupContact, $insertParams);
      }
    }
  }
}
/**
 * Function to deal with another donor journey group if there is one
 * 
 * @param type $groupId
 * @param type $contactId
 */
function checkOtherDonorGroupHistory($groupId, $contactId) {
  $queryDonorGroupHist = 'SELECT group_id, date FROM civicrm_subscription_history WHERE contact_id = %1 '
    . 'AND group_id != %2 AND date > %3 AND status = %4 ORDER BY date';
  $paramsDonorGroupHist = array(
    1 => array($contactId, 'Positive'), 
    2 => array($groupId, 'Positive'),
    3 => array('2014-01-01 23:59:59', 'String'),
    4 => array('Added', 'String'));
  $daoDonorGroupHist = CRM_Core_DAO::executeQuery($queryDonorGroupHist, $paramsDonorGroupHist);
  while ($daoDonorGroupHist->fetch()) {
    if (ocr_check_group_is_donorgroup($daoDonorGroupHist->group_id) == TRUE) {
      insertRemove($contactId, $groupId, $daoDonorGroupHist->date);
      break;
    }
  }
}
function insertRemove($contactId, $groupId, $removeDate) {
  $queryInsert = 'INSERT INTO civicrm_subscription_history (contact_id, group_id, date, method, status) '
    . 'VALUES(%1, %2, %3, %4, %5)';
  $paramsInsert = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'),
    3 => array($removeDate, 'String'),
    4 => array('API', 'String'),
    5 => array('Removed', 'String')
  );
  CRM_Core_DAO::executeQuery($queryInsert, $paramsInsert);
}
function getSubGroups() {
  return array(6571, 6574, 6576, 6578, 6565, 6566, 6567, 6568, 6572, 6573, 6575, 6577, 
    6556, 6557, 6558, 6559, 6560, 6561, 6562, 6563, 6564, 6569, 6570, 6531, 6532,
    6533, 6534, 6535, 6536, 6537, 6538, 6539, 6540, 6541, 6542, 6543, 6544, 6545, 6546, 6524);
}
