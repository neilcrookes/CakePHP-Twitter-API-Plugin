<?php
class TwitterUsersController extends TwitterAppController {
  
  var $name = 'TwitterUsers';

  var $helpers = array('Text', 'Time');

  var $conditionsByType = array(
    'search' => array(),
    'friends' => array('screen_name', 'user_id', 'cursor'),
    'followers' => array('screen_name', 'user_id', 'cursor'),
  );

  function index($id = null) {
    if (!isset($this->params['type'])) {
      $this->cakeError('error404');
    }
    $extraArgsForPaging['type'] = $this->paginate[0] = $this->params['type'];
    if (isset($id)) {
      $this->paginate['conditions']['id'] = $id;
    }
    foreach ($this->passedArgs as $arg => $value) {
      if (in_array($arg, $this->conditionsByType[$this->params['type']])) {
        $this->paginate['conditions'][$arg] = $value;
      }
    }
    if (!empty($this->paginate['conditions'])) {
      Router::connectNamed(array_keys($this->paginate['conditions']), array('default' => true));
    }
    $users = $this->paginate();
    $this->set(compact('users', 'extraArgsForPaging'));
  }

  public function view($id = null) {
    if (!$id) {
      $this->cakeError('error404');
    }
    $status = $this->TwitterStatus->find('show', array('conditions' => array('id' => $id)));
    $this->set(compact('status'));
  }  
}
?>
