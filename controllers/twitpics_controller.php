<?php
/**
 * Sample controller with sample actions - not intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitpicsController extends TwitterAppController {

  /**
   * Sample action for uploading a twitpic. Not intended for use in your apps.
   */
  public function add() {
    if (!empty($this->data)) {
      if ($this->TwitterAuth->isAuthorized) {
        if ($this->Twitpic->save($this->data)) {
          $this->Session->setFlash(__('Twitpic created successfully', true));
          $this->redirect(array('action' => 'view', $this->Twitpic->getInsertID()));
        } else {
          if (!empty($this->Twitpic->response['error'])) {
            $flashMessage = $this->Twitpic->response['error'];
          } elseif (!empty($this->Twitpic->validationErrors)) {
            $flashMessage = '';
            foreach ($this->Twitpic->validationErrors as $field => $errorMessage) {
              $flashMessage .= Inflector::humanize($field) . ': ' . $errorMessage;
            }
          } else {
            $flashMessage = __('Unknown error', true);
          }
        }
      } else {
        $flashMessage = __('You are not authorized', true);
      }
      $this->Session->setFlash($flashMessage);
    }
  }

  /**
   * Sample action for fetching details of a twitpic. Not intended for use in
   * your apps.
   * 
   * @param integer $id ID of the twitpic you want to view
   */
  public function view($id = null) {
    if (!$id) {
      $this->cakeError('error404');
    }
    $twitpic = $this->Twitpic->find('first', array('conditions' => array('Twitpic.id' => $id)));
    if (!$twitpic) {
      $this->cakeError('error404');
    }
    $this->set(compact('twitpic'));
  }
  
}
?>