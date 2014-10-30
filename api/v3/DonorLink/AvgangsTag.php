<?php
/**
 * DonorLink.AvgangsTag API
 * 
 * Tag members of the AvgangsHallen donor journey groups with their
 * donor journey group_id as a tag
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_donor_link_avgangstag($params) {
  $avgangsTags = array(6526, 6528, 6547, 6548, 6549, 6550, 6554, 6555);
  foreach ($avgangsTags as $avgangsTag) {
    $tag = civicrm_api3('Tag', 'Create', array('name' => (string) $avgangsTag));
    $tagId = $tag['id'];
    $groupContactParams = array(
      'group_id' => $avgangsTag,
      'status' => 'Added');
    $groupContacts = civicrm_api3('GroupContact', 'Get', $groupContactParams);
    foreach ($groupContacts['values'] as $groupContact) {
      $entityTagParams = array(
        'tag_id' => $tagId,
        'contact_id' => $groupContact['contact_id']
      );
      civicrm_api3('EntityTag', 'Create', $entityTagParams);
    }
  }
  
  $returnValues = array();
  return civicrm_api3_create_success($returnValues, $params, 'DonorLink', 'AvgansTag');
}
