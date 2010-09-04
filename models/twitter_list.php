<?php
class TwitterList extends TwitterAppModel {
  var $name = 'TwitterList';
  var $useTable = false;
  var $useDbConfig = 'twitter';
  var $_schema = array(
    'id' => array('type' => 'integer'),
    'name' => array('type' => 'string'),
    'full_name' => array('type' => 'string'),
    'slug' => array('type' => 'string'),
    'description' => array('type' => 'string'),
    'subscriber_count' => array('type' => 'integer'),
    'member_count' => array('type' => 'integer'),
    'uri' => array('type' => 'string'),
    'mode' => array('type' => 'string'),
  );

}
?>
