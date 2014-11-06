<?php
/**
 * DonorLink.Step2 API
 * remove all subgroup removes on 17-10-2014
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_step2($params) {
  $subgroups = array(6521, 6522, 6523, 6256, 6528, 6547, 6548, 6549, 6550, 6554, 
    6555, 6569, 6570, 6562, 6563, 6564, 6556, 6557, 6558, 6559, 6560, 6561, 6572, 
    6573, 6575, 6577, 6581, 6583, 6565, 6566, 6567, 6568, 6571, 6574, 6576, 6578);
  $query = 'SELECT * FROM civicrm_subscription_history WHERE status = %1 AND date BETWEEN %2 AND %3 and contact_id = 47398';
  $paramsQuery = array(
    1 => array('Removed', 'String'),
    2 => array('2014-10-17 00:00:00', 'String'),
    3 => array('2014-10-17 23:59:59', 'String')
  );
  $dao = CRM_Core_DAO::executeQuery($query, $paramsQuery);
  while ($dao->fetch()) {
    if (in_array($dao->group_id, $subgroups)) {
      correctdonorgroup2014_add_contact($dao->contact_id);
      $delete = 'DELETE FROM civicrm_subscription_history WHERE id = %1';
      $deleteParams = array(1 => array($dao->id, 'Positive'));
      CRM_Core_DAO::executeQuery($delete, $deleteParams);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step2');
}

