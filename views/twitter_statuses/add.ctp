<?php if ($this->Session->read('Twitter.Auth.isAuthorized')) : ?>
  <?php
  echo $this->Form->create('TwitterStatus', array('action' => 'add'));
  $options = array();
  if (isset($this->params['url']['status'])) {
    $options['value'] = $this->params['url']['status'];
  }
  echo $this->Form->input('text', $options);
  if (isset($this->params['url']['in_reply_to_status_id'])) {
    echo $this->Form->input('in_reply_to_status_id', array(
      'value' => $this->params['url']['in_reply_to_status_id'],
      'type' => 'hidden'
    ));
  }
  echo $this->Form->end(__('Tweet', true));
  ?>
<?php endif; ?>