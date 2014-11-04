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
function civicrm_api3_donor_link_step8($params) {
  /*
   * rebuild group_contactfor affected contacts
   */
  $dao = CRM_Core_DAO::executeQuery('SELECT * FROM donorlink_corrections');
  while ($dao->fetch()) {
    deleteGroupContact($dao->contact_id);
    rebuildGroupContact($dao->contact_id);
    $dao->delete();
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step8');
}

function deleteGroupContact($contactId) {
  $query = 'DELETE FROM civicrm_group_contact WHERE contact_id = %1';
  $params = array(1 => array($contactId, 'Positive'));
  CRM_Core_DAO::executeQuery($query, $params);
}

function rebuildGroupContact($contactId) {
  $query = 'SELECT DISTINCT(group_id) FROM civicrm_subscription_history WHERE contact_id = %1';
  $params = array(1 => array($contactId, 'Positive'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  while ($dao->fetch) {
    $groupStatus = getGroupContactStatus($contactId, $dao->group_id);
    if (!empty($groupStatus)) {
      createGroupContact($contactId, $dao->group_id, $groupStatus);
    }
  }
}

function createGroupContact($contactId, $groupId, $status) {
  $query = 'INSERT INTO civicrm_group_contact (contact_id, group_id, status) '
    . 'VALUES(%1, %2, %3)';
  $params = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'),
    3 => array($status, 'String'));
  CRM_Core_DAO::executeQuery($query, $params);
}

function getGroupContactStatus($contactId, $groupId) {
  $groupStatus = '';
  $query = 'SELECT status FROM civicrm_group_contact WHERE contact_id = %1 '
    . 'AND group_id = %2 ORDER BY date DESC LIMIT 1';
  $params = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  if ($dao->fetch()) {
    $groupStatus = $dao->status;
  }
  return $groupSatus;
}

