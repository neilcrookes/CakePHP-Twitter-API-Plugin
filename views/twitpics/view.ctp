<?php
/**
 * Demo view. Shows details about a twitpic.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
?>
<p><?php echo $this->Html->link('New', array('action' => 'add')); ?></p>

<?php
echo $this->Html->link(
  $this->Html->image(
    'http://twitpic.com/show/thumb/' . $twitpic['short_id'],
    array(
      'width' => 150,
      'height' => 150,
      'alt' => $twitpic['message'],
    )
  ),
  'http://twitpic.com/' . $twitpic['short_id'],
  array(
    'escape' => false
  )
);
?>
<p><?php echo $twitpic['message']; ?></p>
<p><?php echo $twitpic['views']; ?> views</p>
<p>Posted on <?php echo $twitpic['timestamp']; ?> by
<?php
echo $this->Html->link(
  $this->Html->image(
     $twitpic['user']['avatar_url'],
    array(
      'width' => 69,
      'height' => 69,
      'alt' => $twitpic['user']['name'] . ' - ' . $twitpic['user']['location'] . ' - ' . $twitpic['user']['website'] . ' - ' . $twitpic['user']['bio'],
    )
  ),
  'http://twitter.com/' . $twitpic['user']['username'],
  array(
    'escape' => false
  )
);
?>
<?php echo $this->Html->link($twitpic['user']['username'], 'http://twitter.com/' . $twitpic['user']['username']); ?>
</p>
<?php if (!empty($twitpic['comments'])) : ?>
  <h2>Comments</h2>
  <ul>
  <?php foreach ($twitpic['comments'] as $comment) : ?>
    <li>
      <?php
      echo $this->Html->link(
        $this->Html->image(
          $comment['user']['avatar_url'],
          array(
            'width' => 69,
            'height' => 69,
            'alt' => $comment['user']['name'] . ' - ' . $comment['user']['location'] . ' - ' . $comment['user']['website'] . ' - ' . $comment['user']['bio'],
          )
        ),
        'http://twitter.com/' . $comment['user']['username'],
        array(
          'escape' => false
        )
      );
      ?>
      <p><?php echo $this->Html->link($twitpic['user']['username'], 'http://twitter.com/' . $twitpic['user']['username']); ?>: <?php echo $comment['message']; ?> at <?php echo $comment['timestamp']; ?></p>
    </li>
  <?php endforeach; ?>
  </ul>
<?php endif; ?>