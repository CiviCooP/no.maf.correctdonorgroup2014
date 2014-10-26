<?php

/**
 * DonorSubgroups.Replace API
 * API correction to replace donor journey subgroups with 1st level groups
 * One-time job
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_subgroups_replace($params) {
  $replacementGroups = getReplacements();
  $query = 'SELECT id, group_id FROM civicrm_subscription_history';
  $dao = CRM_Core_DAO::executeQuery($query);
  while ($dao->fetch()) {
    if (in_array($dao->group_id, $replacementGroups)) {
      $update = 'UPDATE civicrm_subscription_history SET group_id = %1 WHERE id = %2';
      $params = array(
        1 => array($replacementGroups[$dao->group_id], 'Positive'),
        2 => array($dao->id, 'Positive'));
      CRM_Core_DAO::executeQuery($update, $params);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorSubgroups', 'Replace');
}
/**
 * Function to set the replacement groups and subgroups
 * 
 * @return array $replacements
 */
function getReplacements() {
  $replacements = array(
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
    6531 => 6520,
    6532 => 6520,
    6533 => 6520,
    6534 => 6520,
    6535 => 6520,
    6536 => 6520,
    6537 => 6520,
    6538 => 6520,
    6539 => 6520,
    6540 => 6520,
    6541 => 6520,
    6542 => 6521,
    6543 => 6521,
    6544 => 6521,
    6545 => 6521,
    6587 => 6521,
    6546 => 6523,
    6579 => 6523);
  return $replacements;
}

