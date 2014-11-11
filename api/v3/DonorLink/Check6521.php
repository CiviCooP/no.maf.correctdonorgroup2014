<?php
/**
 * DonorLink.Check6521 API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_check6521($params) {
  $qry_gc_select = 'SELECT id, contact_id FROM civicrm_group_contact WHERE group_id = %1 AND status = %2';
  $params_gc_select = array(
    1 => array(6521, 'Positive'),
    2 => array('Removed', 'String'));
  $dao_gc_select = CRM_Core_DAO::executeQuery($qry_gc_select, $params_gc_select);
  while ($dao_gc_select->fetch()) {
    if (activeHistory($dao_gc_select->contact_id)) {
      $qry_gc_update = 'UPDATE civicrm_group_contact SET status = %1 WHERE id = %2';
      $params_gc_update = array(
        1 => array('Added', 'String'),
        2 => array($dao_gc_select->id, 'Positive'));
      CRM_Core_DAO::executeQuery($qry_gc_update, $params_gc_update);
    }
  }
  return civicrm_api3_create_success(array(), $params, 'DonorLink', 'Check6521');
}
function activeHistory($contact_id) {
  $qry_sh_select = 'SELECT count(*) AS countRemoved FROM civicrm_subscription_history '
    . 'WHERE group_id = %1 AND contact_id = %2 AND status = %3';
  $params_sh_select = array(
    1 => array(6521, 'Positive'),
    2 => array($contact_id, 'Positive'),
    3 => array('Removed', 'String'));
  $dao_sh = CRM_Core_DAO::executeQuery($qry_sh_select, $params_sh_select);
  if ($dao_sh->fetch()) {
    if ($dao_sh->countRemoved == 0) {
      return TRUE;
    }
  }
  return FALSE;
}

