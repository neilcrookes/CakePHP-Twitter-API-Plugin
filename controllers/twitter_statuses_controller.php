<?php
/**
 * Sample controller with sample actions - not intended for your apps.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitterStatusesController extends TwitterAppController {

  /**
   * Helpers this controller's views use
   * 
   * @var array
   */
  public $helpers = array('Text', 'Time');

  /**
   * Sample action for listing and paginating over a timeline.
   *
   * Supports the following timelines:
   * - publicTimeline
   * - homeTimeline
   * - userTimeline
   * - mentions
   * - retweetsToMe
   * - retweetsOfMe
   * - retweetsByMe
   * - friendsTimeline
   */
  public function index() {
    if (!isset($this->passedArgs['type'])) {
      $this->redirect(array('type' => 'publicTimeline'));
    }
    $typeRequiresAuth = in_array($this->passedArgs['type'], $this->TwitterStatus->findMethodsRequiringAuth);
    if (!$typeRequiresAuth || $this->TwitterAuth->isAuthorized) {
      $this->paginate[] = $this->passedArgs['type'];
      $allowedPassedArgs = array_intersect_key($this->passedArgs, array_flip($this->TwitterStatus->allowedFindOptions[$this->passedArgs['type']]));
      $this->paginate = array_merge($this->paginate, $allowedPassedArgs);
      $this->set('statuses', $this->paginate());
    }
    $this->set(compact('typeRequiresAuth'));
  }

  /**
   * Sample action for showing a status
   *
   * @param integer $id
   */
  public function view($id = null) {
    if (!$id) {
      $this->cakeError('error404', array('message' => __('Missing parameter ID', true)));
    }
    $status = $this->TwitterStatus->find('show', array('id' => $id));
    if (!$status) {
      $this->cakeError('error404', array('message' => __('Invalid ID', true)));
    }
    $this->set(compact('status'));
  }

  /**
   * Sample action for creating a tweet
   */
  public function add() {
    if (!empty($this->data)) {
      if ($this->TwitterAuth->isAuthorized) {
        if ($this->TwitterStatus->tweet($this->data)) {
          $flashMessage = __('Your status has been updated', true);
        } else {
          if (!empty($this->TwitterStatus->response['error'])) {
            $flashMessage = $this->TwitterStatus->response['error'];
          } elseif (!empty($this->TwitterStatus->validationErrors)) {
            $flashMessage = '';
            foreach ($this->TwitterStatus->validationErrors as $field => $errorMessage) {
              $flashMessage .= Inflector::humanize($field) . ': ' . $errorMessage;
            }
          } else {
            $flashMessage = __('Unknown error', true);
          }
        }
      } else {
        $flashMessage = __('You are not authorized', true);
      }
    }
    $this->Session->setFlash($flashMessage);
    $this->redirect($this->referer('/', true));
  }

  /**
   * Sample action for retweeting a tweet
   */
  public function retweet($id = null) {
    if (!$id) {
      $this->cakeError('error404', array('message' => __('Missing parameter ID', true)));
    }
    if ($this->TwitterAuth->isAuthorized) {
      if ($this->TwitterStatus->retweet($id)) {
        $flashMessage = __('Your status has been updated', true);
      } else {
        if (!empty($this->TwitterStatus->response['error'])) {
          $flashMessage = $this->TwitterStatus->response['error'];
        } else {
          $flashMessage = __('Unknown error', true);
        }
      }
    } else {
      $flashMessage = __('You are not authorized', true);
    }
    $this->Session->setFlash($flashMessage);
    $this->redirect($this->referer('/', true));
  }

  /**
   * Sample action for deleting a tweet
   */
  public function delete($id = null) {
    if (!$id) {
      $this->cakeError('error404', array('message' => __('Missing parameter ID', true)));
    }
    if ($this->TwitterAuth->isAuthorized) {
      if ($this->TwitterStatus->delete($id)) {
        $flashMessage = __('Your status has been deleted', true);
      } else {
        if (!empty($this->TwitterStatus->response['error'])) {
          $flashMessage = $this->TwitterStatus->response['error'];
        } else {
          $flashMessage = __('Unknown error', true);
        }
      }
    } else {
      $flashMessage = __('You are not authorized', true);
    }
    $this->Session->setFlash($flashMessage);
    $this->redirect($this->referer('/', true));
  }
  
}
?>
