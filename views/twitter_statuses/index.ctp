<?php echo $this->element('../twitter_statuses/add'); ?>

<?php if (!$typeRequiresAuth || $this->Session->read('Twitter.Auth.isAuthorized')) : ?>

  <?php if (!empty($statuses)) : ?>

    <?php echo $this->element('pagination'); ?>

    <ol id="timeline" class="statuses">

      <?php foreach ($statuses as $status) : ?>

        <?php echo $this->element('status', array('status' => $status)); ?>

      <?php endforeach; ?>

    </ol>

    <?php echo $this->element('pagination'); ?>

  <?php else : ?>

    <p><?php __('There are no tweets'); ?></p>

  <?php endif; ?>

<?php else : ?>

  <?php echo $this->element('oauth_login_link'); ?>

<?php endif; ?>