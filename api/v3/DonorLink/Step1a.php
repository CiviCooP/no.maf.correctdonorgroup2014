<?php


/**
 * DonorLink.Step1a API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_step1a($params) {
  /*
   * read all removes from subscription history in 2014
   */
  $querySelect = 'SELECT id, contact_id, group_id, date FROM civicrm_subscription_history '
    . 'WHERE status = %1 AND date >= %2 AND contact_id = 48877';
  $paramsSelect = array(
    1 => array('Removed', 'String'),
    2 => array('2014-01-01 00:00:00', 'String'));
  $daoSelect = CRM_Core_DAO::executeQuery($querySelect, $paramsSelect);
  while ($daoSelect->fetch()) {
    /*
     * if to be removed (no added for contact/group if it is donorgroup
     */
    if (ocr_check_group_is_donorgroup($daoSelect->group_id) == TRUE &&
    checkToBeDeleted($daoSelect->contact_id, $daoSelect->group_id, $daoSelect->date) == TRUE) {
      correctdonorgroup2014_add_contact($daoSelect->contact_id);
      $queryDelete = 'DELETE FROM civicrm_subscription_history WHERE id = %1';
      $paramsDelete = array(1 => array($daoSelect->id, 'Positive'));
      CRM_Core_DAO::executeQuery($queryDelete, $paramsDelete);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'Step1a');
}

function checkToBeDeleted($contactId, $groupId, $dateRemoved) {
  $query = 'SELECT COUNT(*) AS count_added FROM civicrm_subscription_history AS count_added WHERE '
    . 'contact_id = %1 AND group_id = %2 AND status = %3 AND date < %4';
  $params = array(
    1 => array($contactId, 'Positive'),
    2 => array($groupId, 'Positive'),
    3 => array('Added', 'String'),
    4 => array($dateRemoved, 'String'));
  $dao = CRM_Core_DAO::executeQuery($query, $params);
  if ($dao->fetch()) {
    if ($dao->count_added > 0) {
      return FALSE;
    } else {
      return TRUE;
    }
  }
}

