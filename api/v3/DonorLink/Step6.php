<?php


/**
 * DonorLink.Step6 API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
set_time_limit(0);
function civicrm_api3_donor_link_step6($params) {
  /*
   * read all removes from subscription history in 2014
   */
  $ignored_ids = array();
  $querySelect = 'SELECT id, contact_id, group_id, date FROM civicrm_subscription_history '
    . 'WHERE status = %1 AND date >= %2 AND contact_id BETWEEN 15000 AND 30000';
  $paramsSelect = array(
    1 => array('Removed', 'String'),
    2 => array('2014-01-01 00:00:00', 'String'));
  $daoSelect = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
  while ($daoSelect->fetch()) {
    $ignored_ids[] = $daoSelect->id;

    /*
     * remove all removes on same date/contact/group
     */
    $querySameDay = 'SELECT id FROM civicrm_subscription_history WHERE id NOT IN('
      .implode(',', $ignored_ids).') AND contact_id = %1 AND group_id = %2 AND '
      . 'status = %3 AND date BETWEEN %4 AND %5';
    $paramsSameDay = array(
      1 => array($daoSelect->contact_id, 'Positive'),
      2 => array($daoSelect->group_id, 'Positive'),
      3 => array('Removed', 'String'),
      4 => array(date('Y-m-d', strtotime($daoSelect->date)).' 00:00:00', 'String'),
      5 => array(date('Y-m-d', strtotime($daoSelect->date)).' 23:59:59', 'String'));
    $daoSameDay = CRM_Core_DAO::executeQuery($querySameDay, $paramsSameDay);
    while ($daoSameDay->fetch()) {
      $ignored_ids[] = $daoSameDay->id;
      correctdonorgroup2014_add_contact($daoSelect->contact_id);
      $queryDelete = 'DELETE FROM civicrm_subscription_history WHERE id = %1';
      $paramsDelete = array(1 => array($daoSameDay->id, 'Positive'));
      CRM_Core_DAO::executeQuery($queryDelete, $paramsDelete);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step6');
}