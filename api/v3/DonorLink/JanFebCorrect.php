<?php

/**
 * DonorLink.JanFebCorrect API
 * Correct donations from Silver to Gold in Jan/Feb
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_janfebcorrect($params) {
  $querySelect = 
    'SELECT DISTINCT(a.contribution_id)
FROM civicrm_contribution_donorgroup a
JOIN civicrm_contribution b ON a.contribution_id = b.id
WHERE b.contribution_status_id = %1
AND b.contribution_recur_id > %2 
AND b.receive_date BETWEEN %3 AND %4
AND a.group_id = %5';
  
  $paramsSelect = array(
    1 => array(1, 'Positive'),
    2 => array(0, 'Positive'),
    3 => array('2014-01-01 00:00:00', 'String'),
    4 => array('2014-02-28 23:59:59', 'String'),
    5 => array(6514, 'Positive'));
    
  $dao = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
  while ($dao->fetch()) {
    $queryUpdate = 'UPDATE civicrm_contribution_donorgroup SET group_id = %1 WHERE contribution_id = %2';
    $paramsUpdate = array(
      1 => array(6513, 'Positive'),
      2 => array($dao->contribution_id, 'Positive'));
    CRM_Core_DAO::executeQuery($queryUpdate, $paramsUpdate);
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'JanFebCorrect');
}

