<?php
/**
 * MafContribution.LinkDonorGroup API
 * Migration API to correct all contributions of 2014 and set the 
 * linked donor group if it is not there already
 * 
 * @author Erik Hommel (CiviCooP) <erik.hommel@civicoop.org>
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_maf_contribution_linkdonorgroup($params) {
  set_time_limit(0);
  $totalProcessed = 0;
  /*
   * get all contributions with status completed in 2014
   */
  $contributionQuery = 'SELECT id, contact_id, receive_date FROM civicrm_contribution '
    . 'WHERE contribution_status_id = %1 AND (receive_date BETWEEN %2 AND %3)';
  $contributionParams = array(
    1 => array(1, 'Positive'),
    2 => array(date('Ymd', strtotime('2014-01-01')), 'Date'),
    3 => array(date('Ymd', strtotime('2014-12-31')), 'Date'));
  $contributionDao = CRM_Core_DAO::executeQuery($contributionQuery, $contributionParams);
  while ($contributionDao->fetch()) {
    /*
     * only process if there is no link to donor group yet (no record or record with
     * value 0)
     */
    $donorLinkQuery = 'SELECT * FROM civicrm_contribution_donorgroup WHERE contribution_id = %1';
    $donorLinkParams = array(1 => array($contributionDao->id, 'Positive'));
    $donorLinkDao = CRM_Core_DAO::executeQuery($donorLinkQuery, $donorLinkParams);
    if ($donorLinkDao->fetch()) {
      if (empty($donorLinkDao->group_id)) {
        _processContribution($contributionDao);
        $totalProcessed++;
        $returnValues['contribution'][] = $contributionDao->id.'- contact '.$contributionDao->contact_id;
      }
    } else {
      _processContribution($contributionDao);
      $returnValues['contribution'][] = $contributionDao->id.'- contact '.$contributionDao->contact_id;
      $totalProcessed++;
    }
  }
  $returnValues['message'] = $totalProcessed.' contributions linked to donor groups';
  return civicrm_api3_create_success($returnValues, $params, 'DonorGroup', 'AddContribution');
}
/*
 * Function to process the contribution
 */
function _processContribution($contribution) {
  $donorGroupId = ocr_get_contribution_donorgroup($contribution->id, $contribution->receive_date, 
    $contribution->contact_id);
  if ($donorGroupId != 0) {
    ocr_create_contribution_donorgroup($contribution->id, $donorGroupId);
  }
}

  
