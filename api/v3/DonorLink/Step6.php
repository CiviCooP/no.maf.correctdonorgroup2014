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
function civicrm_api3_donor_link_step6($params) {
  /*
   * read all removes from subscription history in 2014
   */
  $querySelect = 'SELECT id, contact_id, group_id, date FROM civicrm_subscription_history '
    . 'WHERE status = %1 AND date >= %2 AND contact_id = 46294';
  $paramsSelect = array(
    1 => array('Removed', 'String'),
    2 => array('2014-01-01 00:00:00', 'String'));
  $daoSelect = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
  while ($daoSelect->fetch()) {
    /*
     * remove all removes on same date/contact/group
     */
    $querySameDay = 'SELECT id FROM civicrm_subscription_history WHERE id != %1 '
      . 'AND contact_id = %2 AND group_id = %3 AND status = %4 '
      . 'AND date BETWEEN %5 AND %6';
    $paramsSameDay = array(
      1 => array($daoSelect->id, 'Positive'),
      2 => array($daoSelect->contact_id, 'Positive'),
      3 => array($daoSelect->group_id, 'Positive'),
      4 => array('Removed', 'String'),
      5 => array(date('Y-m-d', strtotime($daoSelect->date)).' 00:00:00', 'String'),
      6 => array(date('Y-m-d', strtotime($daoSelect->date)).' 23:59:59', 'String'));
    $daoSameDay = CRM_Core_DAO::executeQuery($querySameDay, $paramsSameDay);
    while ($daoSameDay->fetch()) {
      correctdonorgroup2014_add_contact($daoSelect->contact_id);
      $queryDelete = 'DELETE FROM civicrm_subscription_history WHERE id = %1';
      $paramsDelete = array(1 => array($daoSameDay->id, 'Positive'));
      CRM_Core_DAO::executeQuery($queryDelete, $paramsDelete);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step6');
}