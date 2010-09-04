<?php echo $this->element('../twitter_statuses/add'); ?>
<?php if (!empty($users)) : ?>
  <?php
  $paginationOptions = array();
  if (isset($users['next_cursor'])
   && isset($users['previous_cursor'])) {
    $paginationOptions['previous_cursor'] = $users['previous_cursor'];
    $paginationOptions['next_cursor'] = $users['next_cursor'];
  }
  $pagination = $this->element('pagination', $paginationOptions);
  ?>
  <?php echo $pagination; ?>
  <ol>
    <?php foreach ($users['users'] as $user) : ?>
      <?php echo $this->element('user', array('user' => $user)); ?>
    <?php endforeach; ?>
  </ol>
  <?php echo $pagination; ?>
<?php else : ?>
  <p><?php __('There are no users'); ?></p>
<?php endif; ?>