<?php
class TwitterUser extends TwitterAppModel {
  var $_schema = array(
    'id' => array('type' => 'integer'),
    'name' => array('type' => 'string'),
    'screen_name' => array('type' => 'string'),
    'location' => array('type' => 'string'),
    'description' => array('type' => 'string'),
    'profile_image_url' => array('type' => 'string'),
    'url' => array('type' => 'string'),
    'protected' => array('type' => 'string'),
    'followers_count' => array('type' => 'integer'),
    'profile_background_color' => array('type' => 'string'),
    'profile_text_color' => array('type' => 'string'),
    'profile_link_color' => array('type' => 'string'),
    'profile_sidebar_fill_color' => array('type' => 'string'),
    'profile_sidebar_border_color' => array('type' => 'string'),
    'friends_count' => array('type' => 'integer'),
    'created_at' => array('type' => 'string'),
    'favourites_count' => array('type' => 'integer'),
    'utc_offset' => array('type' => 'integer'),
    'time_zone' => array('type' => 'string'),
    'profile_background_image_url' => array('type' => 'string'),
    'profile_background_tile' => array('type' => 'string'),
    'statuses_count' => array('type' => 'integer'),
    'notifications' => array('type' => 'string'),
    'verified' => array('type' => 'string'),
  );
  var $_findMethods = array(
    'show' => true,
    'friends' => true,
    'followers' => true,
    'search' => true,
  );
  protected function _findShow($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = 'users/show';
      if (isset($query['conditions']['id'])) {
        $this->request['uri']['path'] .= $query['conditions']['id'];
      }
      if (isset($query['conditions']['user_id'])) {
        $this->request['uri']['query']['user_id'] = $query['conditions']['user_id'];
      }
      if (isset($query['conditions']['screen_name'])) {
        $this->request['uri']['query']['screen_name'] = $query['conditions']['screen_name'];
      }
      return $query;
    } else {
      return $results;
    }
  }
  protected function _findFriends($state, $query = array(), $results = array()) {
    if ($state == 'before') {

      $this->request['uri']['path'] = 'statuses/friends';

      if (!isset($query['conditions']['id'])
      && !isset($query['conditions']['screen_name'])
      && !isset($query['conditions']['user_id'])) {
        $this->request['auth']['method'] = 'Basic';
      } else {
        if (isset($query['conditions']['id'])) {
          $this->request['uri']['path'] .= $query['conditions']['id'];
        }
        if (isset($query['conditions']['user_id'])) {
          $this->request['uri']['query']['user_id'] = $query['conditions']['user_id'];
        }
        if (isset($query['conditions']['screen_name'])) {
          $this->request['uri']['query']['screen_name'] = $query['conditions']['screen_name'];
        }
      }

      if (!isset($query['conditions']['cursor'])) {
        $query['conditions']['cursor'] = -1;
      }
      $this->request['uri']['query']['cursor'] = $query['conditions']['cursor'];
      
      return $query;
    } else {
      return $results;
    }
  }
  protected function _findFollowers($state, $query = array(), $results = array()) {
    if ($state == 'before') {

      $this->request['uri']['path'] = 'statuses/followers';

      if (!isset($query['conditions']['id'])
      && !isset($query['conditions']['screen_name'])
      && !isset($query['conditions']['user_id'])) {
        $this->request['auth']['method'] = 'Basic';
      } else {
        if (isset($query['conditions']['id'])) {
          $this->request['uri']['path'] .= $query['conditions']['id'];
        }
        if (isset($query['conditions']['user_id'])) {
          $this->request['uri']['query']['user_id'] = $query['conditions']['user_id'];
        }
        if (isset($query['conditions']['screen_name'])) {
          $this->request['uri']['query']['screen_name'] = $query['conditions']['screen_name'];
        }
      }

      if (!isset($query['conditions']['cursor'])) {
        $query['conditions']['cursor'] = -1;
      }
      $this->request['uri']['query']['cursor'] = $query['conditions']['cursor'];

      return $query;
    } else {
      return $results;
    }
  }
  protected function _findSearch($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['host'] = 'api.twitter.com';
      $this->request['uri']['path'] = '1/users/search';
      if (isset($query['conditions']['id'])) {
        $this->request['uri']['path'] .= $query['conditions']['id'];
      }
      if (isset($query['conditions']['user_id'])) {
        $this->request['uri']['query']['user_id'] = $query['conditions']['user_id'];
      }
      if (isset($query['conditions']['screen_name'])) {
        $this->request['uri']['query']['screen_name'] = $query['conditions']['screen_name'];
      }
      return $query;
    } else {
      return $results;
    }
  }
}
?>
