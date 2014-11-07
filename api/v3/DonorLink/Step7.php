<?php


/**
 * DonorLink.Step7 API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
set_time_limit(0);
function civicrm_api3_donor_link_step7($params) {
  /*
   * select all adds from civicrm_subscription_history for 2014
   */
  $query = 'SELECT contact_id, group_id FROM civicrm_subscription_history WHERE '
    . 'status = %1 AND date >= %2 AND contact_id BETWEEN 40000 AND 47500 GROUP BY contact_id, group_id';
  $paramsQuery = array(
    1 => array('Added', 'String'),
    2 => array('2014-01-01 00:00:00', 'String'));
  $dao = CRM_Core_DAO::executeQuery($query, $paramsQuery);
  while ($dao->fetch()) {
    /*
     * check if processing required (only if no removes and more than one adds)
     */
    if (step7Processing($dao->contact_id, $dao->group_id) == TRUE) {
      correctdonorgroup2014_add_contact($dao->contact_id);
      processStep7($dao->contact_id, $dao->group_id);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step7');
}
/*
 * function returns true if there are > 1 adds and no removals for contact and group
 */
function step7Processing($contactId, $groupId) {
  $queryAdded = 'SELECT COUNT(*) AS count_added FROM civicrm_subscription_history '
    . 'WHERE contact_id = %1 AND group_id = %2 AND status = %3';
  $paramsAdded = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'),
    3 => array('Added', 'String'));
  
  $daoAdded = CRM_Core_DAO::executeQuery($queryAdded, $paramsAdded);
  if ($daoAdded->fetch()) {
    if ($daoAdded->count_added > 1) {
      $queryRemoved = 'SELECT COUNT(*) AS count_removed FROM civicrm_subscription_history '
        . 'WHERE contact_id = %1 AND group_id = %2 AND status = %3';
      $paramsRemoved = array(
        1 => array($contactId, 'Positive'),
        2 => array($groupId, 'Positive'),
        3 => array('Removed', 'String'));
      $daoRemoved = CRM_Core_DAO::executeQuery($queryRemoved, $paramsRemoved);
      if ($daoRemoved->fetch()) {
        if ($daoRemoved->count_removed == 0) {
          return TRUE;
        }
      }
    } 
  }
  return FALSE;
}
/*
 * Function to remove all adds for a contact/group but the oldest
 */
function processStep7($contactId, $groupId) {
  $query = 'SELECT id FROM civicrm_subscription_history WHERE contact_id = %1 '
    . 'AND group_id = %2 ORDER BY date';
  $params = array(
    1 => array($contactId, 'Positive'), 
    2 => array($groupId, 'Positive'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  $firstRow = TRUE;
  while ($dao->fetch()) {
    if ($firstRow) {
      $firstRow = FALSE;
    } else {
      $queryDelete = 'DELETE FROM civicrm_subscription_history WHERE id = %1';
      $paramsDelete = array(1 => array($dao->id, 'Positive'));
      CRM_Core_DAO::executeQuery($queryDelete, $paramsDelete);
    }
  }
}