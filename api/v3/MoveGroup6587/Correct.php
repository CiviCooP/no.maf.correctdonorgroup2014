<?php
/**
 * MoveGroup6587.Correct API
 * 
 * Correct ciivcrm_group_contact to reflect group 6587 => 6521
 * 
 * @param array $params
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 */

function civicrm_api3_move_group6587_correct($params) {
  $query6587 = 'SELECT * FROM civicrm_group_contact WHERE group_id = %1';
  $params6587 = array(1 => array(6587, 'Positive'));
  $dao6587 = CRM_Core_DAO::executeQuery($query6587, $params6587);
  while ($dao6587->fetch()) {
    /*
     * if there is no group_contact for 6521 yet, update the found record
     * if there is already a group_contact for 6521, just delete the 6587
     */
    $query6521 = 'SELECT * FROM civicrm_group_contact WHERE group_id = %1 AND contact_id = %2';
    $params6521 = array(
      1 => array(6521, 'Positive'),
      2 => array($dao6587->contact_id, 'Positive'));
    $dao6521 = CRM_Core_DAO::executeQuery($query6521, $params6521);
    if ($dao6521->fetch()) {
      $delete = 'DELETE FROM civicrm_group_contact WHERE id = %1';
      $paramsDelete = array(1 => array($dao6587->id, 'Positive'));
      CRM_Core_DAO::executeQuery($delete, $paramsDelete);
    } else {
      $update = 'UPDATE civicrm_group_contact SET group_id = %1 WHERE id = %2';
      $paramsUpdate = array(
        1 => array(6521, 'Positive'),
        2 => array($dao6587->id));
      CRM_Core_DAO::executeQuery($update, $paramsUpdate);
    }
  }
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'MoveGroup6587', 'Correct');
}

