<?php
/**
 * Demo view. If authorized, displays upload file form, else prints link to get
 * authorized.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
if ($this->Session->read('Twitter.Auth.isAuthorized')) {
  echo $this->Form->create('Twitpic', array('type' => 'file'));
  echo $this->Form->input('media', array('type' => 'file', 'label' => 'Image'));
  echo $this->Form->input('message');
  echo $this->Form->end('Upload');
} else {
  echo $this->element('oauth_login_link');
}
?>