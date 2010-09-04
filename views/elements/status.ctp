<li>
    <?php
    echo $this->Html->link(
      $this->Html->image($status['user']['profile_image_url'], array(
        'alt' => $status['user']['name'],
        'height' => '48',
        'width' => '48'
      )),
      array(
        'plugin' => 'twitter',
        'controller' => 'twitter_statuses',
        'action' => 'index',
        'type' => 'userTimeline',
        'screen_name' => $status['user']['screen_name']
      ),
      array(
        'escape' => false
      )
    );
    ?>
    <strong>
      <?php
      echo $this->Html->link($status['user']['screen_name'], array(
        'plugin' => 'twitter',
        'controller' => 'twitter_statuses',
        'action' => 'index',
        'type' => 'userTimeline',
        'screen_name' => $status['user']['screen_name']
      ));
      ?>
    </strong>
    <?php echo $this->Text->autoLink($status['text']); ?>
    <?php
    echo $this->Html->link(
      $time->timeAgoInWords($status['created_at']),
      array('plugin' => 'twitter', 'controller' => 'twitter_statuses', 'action' => 'view', $status['id'])
    );
    ?>
    from <?php echo $status['source']; ?>
    <ul>
      <li>
        <?php
        echo $this->Html->link(
          __('Reply', true),
          array(
            'status' => '@' .  $status['user']['screen_name'],
            'in_reply_to_status_id' => $status['id'],
            'in_reply_to' => $status['user']['screen_name']
          )
        );
        ?>
      </li>
      <li>
        <?php
        echo $this->Html->link(
          __('Favorite', true),
          array(
            'plugin' => 'twitter',
            'controller' => 'twitter_statuses',
            'action' => 'favorite',
            $status['id']
          )
        );
        ?>
      </li>
      <li>
        <?php
        echo $this->Html->link(
          __('Retweet', true),
          array(
            'plugin' => 'twitter',
            'controller' => 'twitter_statuses',
            'action' => 'retweet',
            $status['id']
          )
        );
        ?>
      </li>
      <li>
        <?php
        echo $this->Html->link(
          __('Retweets', true),
          array(
            'plugin' => 'twitter',
            'controller' => 'twitter_statuses',
            'action' => 'index',
            'type' => 'retweets',
            'id' => $status['id']
          )
        );
        ?>
      </li>
      <li>
        <?php
        echo $this->Html->link(
          __('Retweeted By', true),
          array(
            'plugin' => 'twitter',
            'controller' => 'twitter_statuses',
            'action' => 'index',
            'type' => 'retweetedBy',
            'id' => $status['id']
          )
        );
        ?>
      </li>
      <?php if ($this->Session->read('Twitter.Auth.isAuthorized') && $this->Session->read('Twitter.Auth.user_id') == $status['user']['id']) : ?>
      <li>
        <?php
        echo $this->Html->link(
          __('Delete', true),
          array(
            'plugin' => 'twitter',
            'controller' => 'twitter_statuses',
            'action' => 'delete',
            $status['id']
          )
        );
        ?>
      </li>
      <?php endif; ?>
    </ul>
</li>