<?php
/**
 * Plugin base controller.
 *
 *
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitterAppController extends AppController {

  /**
   * Components the controllers use
   *
   * @var array
   */
  public $components = array('TwitterAuth');

  /**
   * Overrides CakePHP's default paging settings
   *
   * @var array
   */
  public $paginate = array(
    'page' => 1,
    'limit' => 10,
  );

  /**
   * Called automatically before any action
   *
   * Sets any action to be allowed in case you've got Auth enabled in your app
   */
  public function beforeFilter() {
    parent::beforeFilter();
    if (isset($this->Auth)) {
      $this->Auth->allow('*');
    }
  }

  /**
   * Overrides Controller::paginate() to set paging options in the params
   * property, which is then available in the view, and is used by the Paginator
   * helper.
   *
   * @param mixed $object
   * @param mixed $scope
   * @param mixed $whitelist
   * @return array The result set
   */
  public function paginate($object = null, $scope = array(), $whitelist = array()) {
    if (isset($this->passedArgs['page'])) {
      $this->paginate['page'] = $this->passedArgs['page'];
    }
    if (isset($this->passedArgs['show'])) {
      $this->paginate['limit'] = $this->passedArgs['show'];
    }
    $options = $this->paginate;
    if (isset($options[$object])) {
      $options = array_merge($options, $options[$object]);
      unset($options[$object]);
    }
    $type = 'all';
    if (isset($options[0])) {
      $type = $options[0];
      unset($options[0]);
    }
    $results = $this->{$this->modelClass}->find($type, $options);
		$this->params['paging'][$object] = array(
			'page'		=> $options['page'],
			'current'	=> count($results),
			'prevPage'	=> ($options['page'] > 1),
			'nextPage'	=> true,
			'defaults'	=> array_merge(array('limit' => 20, 'step' => 1), $options),
			'options'	=> $options
		);
		if (!in_array('Paginator', $this->helpers) && !array_key_exists('Paginator', $this->helpers)) {
			$this->helpers[] = 'Paginator';
		}
		return $results;
  }

  /**
   * This action gets an OAuth Request Token and OAuth Request Token Secret from
   * Twitter then redirects the user to Twitter to authorize the OAuth Request
   * Token.
   *
   * If for some reason we couldn't get a request token, an error message is set
   * in the session flash and the user is redirected to the return to param. If
   * that is not set, the error message is dumped out.
   *
   * @param string $returnTo The url to return to on success or failure. N.B.
   * should be urlencode()'d 3 times in HtmlHelper::link().
   */
  public function connect($returnTo = null) {

    if ($returnTo) {
      $this->Session->write('Twitter.Auth.return_to', urldecode($returnTo));
    }

    $ds = $this->TwitterAuth->getDataSource();
    $oAuthConsumerKey = $ds->config['oauth_consumer_key'];
    $oAuthConsumerSecret = $ds->config['oauth_consumer_secret'];

    $oAuthCallback = Router::url(array('action' => 'callback'), true);
    
    $requestToken = $this->TwitterAuth->getOAuthRequestToken($oAuthConsumerKey, $oAuthConsumerSecret, $oAuthCallback);

    if ($requestToken) {
      $this->Session->write('Twitter.Auth.oauth_request_token', $requestToken['oauth_token']);
      $this->Session->write('Twitter.Auth.oauth_request_token_secret', $requestToken['oauth_token_secret']);
      $this->TwitterAuth->authorize($requestToken['oauth_token']);
    } else {
      $error = __('Could not get OAuth Request Token from Twitter', true);
      if ($returnTo) {
        $this->Session->setFlash($error);
        $this->redirect($returnTo);
      }
      die($error);
    }
  }

  /**
   * This action is the callback url specified in the connect action above that
   * Twitter redirects the user back to after they have authorised the request
   * token.
   *
   * This actually exchanges the authorised request token for the OAuth Access
   * Token and OAuth Access Token Secret and stores them in the session before
   * redirecting the user back to the URL passed in in the returnTo parameter to
   * the connect action above, or if that is not set, the details are dumped
   * out.
   */
  public function callback() {

    $ds = $this->TwitterAuth->getDataSource();
    $oAuthConsumerKey = $ds->config['oauth_consumer_key'];
    $oAuthConsumerSecret = $ds->config['oauth_consumer_secret'];

    $oAuthRequestToken = $this->Session->read('Twitter.Auth.oauth_request_token');
    $oAuthRequestTokenSecret = $this->Session->read('Twitter.Auth.oauth_request_token_secret');

    $oAuthVerifier = $this->params['url']['oauth_verifier'];

    $accessToken = $this->TwitterAuth->getOAuthAccessToken($oAuthConsumerKey, $oAuthConsumerSecret, $oAuthRequestToken, $oAuthRequestTokenSecret, $oAuthVerifier);

    if ($accessToken) {
      $sessionData = $this->Session->read('Twitter.Auth');
      $sessionData = array_merge($sessionData, $accessToken);
      $this->Session->write('Twitter.Auth', $sessionData);
      if ($this->Session->check('Twitter.Auth.return_to')) {
        $this->redirect($this->Session->read('Twitter.Auth.return_to'));
      } else {
        die(pr($this->Session->read('Twitter.Auth')));
      }
    } else {
      $error = __('Could not get OAuth Access Token from Twitter', true);
      if ($this->Session->check('Twitter.Auth.return_to')) {
        $this->redirect($this->Session->read('Twitter.Auth.return_to'));
      } else {
        die($error);
      }
    }
  }

  public function logout() {
    $this->Session->delete('Twitter.Auth');
    $this->redirect($this->referer('/', true));
  }

}
?>