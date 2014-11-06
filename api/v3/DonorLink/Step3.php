<?php
/**
 * DonorLink.Step3 API
 * replace subgroups in civicrm_subscription_history
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_step3($params) {
  $replaceGroups = getReplaceGroups();
  $querySelect = 'SELECT * FROM civicrm_subscription_history WHERE date > %1 AND '
    . 'group_id BETWEEN %2 AND %3 AND contact_id = 1461';
  $paramsSelect = array(
    1 => array('2014-01-01 00:00:00', 'String'),
    2 => array(6526, 'Positive'),
    3 => array(6583, 'Positive'));
  $daoSelect = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
  while ($daoSelect->fetch()) {
    if (isset($replaceGroups[$daoSelect->group_id])) {
      correctdonorgroup2014_add_contact($daoSelect->contact_id);
      $queryUpdate = 'UPDATE civicrm_subscription_history SET group_id = %1 WHERE id = %2';
      $paramsUpdate = array(
        1 => array($replaceGroups[$daoSelect->group_id], 'Positive'),
        2 => array($daoSelect->id, 'Positive'));
      CRM_Core_DAO::executeQuery($queryUpdate, $paramsUpdate);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step3');
}
function getReplaceGroups() {
  $replaceGroups = array(
    6571 => 6510,
    6574 => 6510,
    6576 => 6510,
    6578 => 6510,
    6565 => 6511,
    6566 => 6511,
    6567 => 6511,
    6568 => 6511,
    6581 => 6512,
    6583 => 6512,
    6572 => 6513,
    6573 => 6513,
    6575 => 6513,
    6577 => 6513,
    6556 => 6514,
    6557 => 6514,
    6558 => 6514,
    6559 => 6514,
    6560 => 6514,
    6561 => 6514,
    6562 => 6514,
    6563 => 6514,
    6564 => 6514,
    6569 => 6515,
    6570 => 6515,
    6526 => 6517,
    6528 => 6517,
    6547 => 6517,
    6548 => 6517,
    6549 => 6517,
    6550 => 6517,
    6554 => 6517,
    6555 => 6517,
  );
  return $replaceGroups;
}

