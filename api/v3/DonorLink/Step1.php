<?php
/**
 * DonorLink.Step1 API
 * Remove all add/remove pairs on same day from civicrm_subscription_history
 * 
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_step1($params) {
  $queryAdded = 'SELECT * FROM civicrm_subscription_history WHERE status = %1 AND date >= %2 AND contact_id BETWEEN 30001 AND 35000';
  $paramsAdded = array(
    1 => array('Added', 'String'),
    2 => array('2014-01-01 00:00:00', 'String'));
  $daoAdded = CRM_Core_DAO::executeQuery($queryAdded, $paramsAdded);
  while ($daoAdded->fetch()) {
    correctdonorgroup2014_add_contact($daoAdded->contact_id);
    $dateAdded = date('Y-m-d', strtotime($daoAdded->date));
    $queryRemoved = 'SELECT id FROM civicrm_subscription_history WHERE status = %1 '
      . 'AND group_id = %2 AND contact_id = %3 AND date BETWEEN %4 AND %5';
    $paramsRemoved = array(
      1 => array('Removed', 'String'),
      2 => array($daoAdded->group_id, 'Positive'),
      3 => array($daoAdded->contact_id, 'Positive'),
      4 => array($dateAdded.' 00:00:00', 'String'),
      5 => array($dateAdded.' 23:59:59', 'String'));
    
    $daoRemoved = CRM_Core_DAO::executeQuery($queryRemoved, $paramsRemoved);
    if ($daoRemoved->fetch()) {
      $delete = 'DELETE FROM civicrm_subscription_history WHERE id = %1 OR id = %2';
      $deleteParams = array(
        1 => array($daoAdded->id, 'Positive'),
        2 => array($daoRemoved->id, 'Positive'));
      CRM_Core_DAO::executeQuery($delete, $deleteParams);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step1');
}

