<?php
/**
 * DonorLink.Step8 API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
set_time_limit(0);
function civicrm_api3_donor_link_step8($params) {
  /*
   * rebuild group_contactfor affected contacts
   */
  $daoLink = CRM_Core_DAO::executeQuery('SELECT * FROM donorlink_corrections WHERE contact_id BETWEEN 30001 AND 32500');
  while ($daoLink->fetch()) {
    deleteGroupContact($daoLink->contact_id);
    rebuildGroupContact($daoLink->contact_id);
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step8');
}

function deleteGroupContact($contactId) {
  $queryDeleteGroupContact = 'DELETE FROM civicrm_group_contact WHERE contact_id = %1';
  $paramsDeleteGroupContact = array(1 => array($contactId, 'Positive'));
  CRM_Core_DAO::executeQuery($queryDeleteGroupContact, $paramsDeleteGroupContact);
}

function rebuildGroupContact($contactId) {
  $queryDistinct = 'SELECT DISTINCT(group_id) FROM civicrm_subscription_history WHERE contact_id = %1';
  $paramsDistinct = array(1 => array($contactId, 'Positive'));
  $daoDistinct = CRM_Core_DAO::executeQuery($queryDistinct, $paramsDistinct);
  while ($daoDistinct->fetch()) {
    $groupStatus = getGroupContactStatus($contactId, $daoDistinct->group_id);
    if (!empty($groupStatus)) {
      createGroupContact($contactId, $daoDistinct->group_id, $groupStatus);
    }
  }
}

function createGroupContact($contactId, $groupId, $status) {
  if ($status == 'Added' || $status == 'Removed') {
    $queryInsert = 'INSERT INTO civicrm_group_contact (contact_id, group_id, status) '
      . 'VALUES(%1, %2, %3)';
    $paramsInsert = array(
      1 => array($contactId, 'Positive'),
      2 => array($groupId, 'Positive'),
      3 => array($status, 'String'));
    CRM_Core_DAO::executeQuery($queryInsert, $paramsInsert);
  }
}

function getGroupContactStatus($contactId, $groupId) {
  $groupStatus = '';
  $queryHist = 'SELECT status FROM civicrm_subscription_history WHERE contact_id = %1 '
    . 'AND group_id = %2 ORDER BY date DESC LIMIT 1';
  $paramsHist = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'));
  
  $daoHist = CRM_Core_DAO::executeQuery($queryHist, $paramsHist);
  if ($daoHist->fetch()) {
    $groupStatus = $daoHist->status;
  }
  return $groupStatus;
}

