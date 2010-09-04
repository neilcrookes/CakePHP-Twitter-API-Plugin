<tr id="user_<?php echo $user['id']; ?>" class="user following odd">
  <td class="thumb vcard">
    <?php
    echo $html->link(
      $html->image($user['profile_image_url'], array(
        'height' => '48',
        'width' => '48'
      )),
      array(
        'plugin' => 'twitter',
        'controller' => 'twitter_statuses',
        'action' => 'index',
        'type' => 'user_timeline',
        $user['screen_name']
      ),
      array('hreflang' => $user['lang']),
      null,
      false
    );
    ?>
  </td>
  <td class="user-detail">
    <address class="about vcard">
      <span class="label screenname">
        <?php
        echo $html->link($user['screen_name'], array(
          'plugin' => 'twitter',
          'controller' => 'twitter_statuses',
          'action' => 'index',
          'type' => 'user_timeline',
          $user['screen_name']
        ),
        array(
          'hreflang' => $user['lang'],
          'title' => $user['name']
        ));
        ?>
      </span>
      <br />
      <span>
        <span class="label fullname"><?php echo $user['name']; ?></span>
          <span class="location"> | <?php echo $user['location']; ?></span>
        </span>
      <span class="is-relationship"></span>
    </address>
    <span class="user-body">
      <?php if (isset($user['status'])) : ?>
        <span class="currently">
          <?php echo $text->autoLink($user['status']['text']); ?>&nbsp;
          <em><?php echo $time->timeAgoInWords($user['status']['created_at']); ?></em>
        </span>
      <?php endif; ?>
      <div class="list-tags-outer" style="display: none;">
        <label>Your lists:</label>
        <ul id="list_tags_<?php echo $user['id']; ?>" class="list-tags">
        </ul>
      </div>
    </span>
  </td>
  <td class="user-actions-outer">
    <ul class="user-actions">
      <li class="list-menu menu">
        <button class="btn" title="Manage lists in which <?php echo $user['screen_name']; ?> appears"><b></b><i></i></button>
      </li>
      <li class="action-menu menu">
        <button class="btn" value="Actions"><i></i></button>
      </li>
    </ul>
  </td>
</tr>