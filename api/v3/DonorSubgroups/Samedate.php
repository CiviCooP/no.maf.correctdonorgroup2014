<?php

/**
 * DonorSubgroups.Samedate API
 * API correction to remove subscription_history records when add and remove
 * are on the same day
 * One-time job
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_subgroups_samedate($params) {
  $queryAdded = 'SELECT id, contact_id, group_id, date FROM civicrm_subscription_history WHERE status = %1';
  $paramsAdded = array(1 => array('Added', 'String'));
  $daoAdded = CRM_Core_DAO::executeQuery($queryAdded, $paramsAdded);
  while ($daoAdded->fetch()) {
    $testDate = formatTestDate($daoAdded->date);
    $queryRemoved = 'SELECT id FROM civicrm_subscription_history WHERE contact_id = %1 '
      . 'AND group_id = %2 AND status = %3 AND (date >= %4 AND date <= %4)';
    $paramsRemoved = array(
      1 => array($daoAdded->contact_id, 'Positive'),
      2 => array($daoAdded->group_id, 'Positive'),
      3 => array('Removed', 'String'),
      4 => array($testDate, 'String'));
    $daoRemoved = CRM_Core_DAO::executeQuery($queryRemoved, $paramsRemoved);
    if ($daoAdded->fetch()) {
      $queryDelete = 'DELETE FROM civicrm_subscription_history WHERE id = %1 OR id = %2';
      $paramsDelete = array(
        1 => array($daoAdded->id, 'Positive'),
        2 => array($daoRemoved->id, 'Positive'));
      CRM_Core_DAO::executeQuery($queryDelete, $paramsDelete);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorSubgroups', 'Samedate');
}
/**
 * Function to format date to Y-m-d
 * @param string $inDate
 * @return string
 */
function formatTestDate($inDate) {
  $date = new DateTime($inDate);
  return $date->format('Y-m-d');
}

