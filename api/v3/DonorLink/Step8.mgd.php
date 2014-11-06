<?php
// This file declares a managed database record of type "Job".
// The record will be automatically inserted, updated, or deleted from the
// database as appropriate. For more details, see "hook_civicrm_managed" at:
// http://wiki.civicrm.org/confluence/display/CRMDOC42/Hook+Reference
return array (
  0 => 
  array (
    'name' => 'Cron:DonorLink.Step8',
    'entity' => 'Job',
    'params' => 
    array (
      'version' => 3,
      'name' => 'Call DonorLink.Step8 API',
      'description' => 'Call DonorLink.Step8 API',
      'run_frequency' => 'Daily',
      'api_entity' => 'DonorLink',
      'api_action' => 'Step8',
      'parameters' => '',
      'is_active' => 0
    ),
  ),
);