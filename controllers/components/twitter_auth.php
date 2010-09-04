<?php
/**
 * CakePHP Component for checking current status of your app as to whether it is
 * authorized to interact with a user's Twitter account via the API. And
 * provides methods for the "OAuth Dance" you need to have with Twitter in order
 * to get authorized, that you can call from your controller actions.
 *
 * Authorization status is determined by whether you have an OAuth access token
 * and secret. These can be present in the datasource config or in the session.
 *
 * The checks for authorization status happen automatically in the startup()
 * method, and if the credentials are in the session, but not in the datasource
 * config (which is where they are needed for making authenticated requests),
 * they are automatically copied to the datasource config.
 *
 * In order to get authorization you need to:
 * 1. Get an OAuth Request Token
 * 2. Authorize the request token (and the app to interact with a users account)
 * 3. Get an OAuth Access Token
 *
 * See the methods below for doing these tasks. You can call these from your
 * controller. For example controller actions, see
 * TwitterAppController::connect() and TwitterAppController::callback() or you
 * can use the convenience methods in this component that provide all the
 * default functionality for you. See the connect() and callback() methods below
 * for details.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitterAuthComponent extends Object {

  /**
   * The other components used by this component
   *
   * @var array
   * @access public
   */
  public $components = array('Session');

  /**
   * The default/common elements of the requests for the oauth request and
   * access tokens made through HttpSocketOauth.
   *
   * @var array
   * @access protected
   */
  protected $_oAuthRequestDefaults = array(
    'uri' => array(
      'scheme' => 'https',
      'host' => 'api.twitter.com',
    ),
    'method' => 'GET',
    'auth' => array(
      'method' => 'OAuth'
    ),
  );

  /**
   * Tells whether in the current request the app is authorized to interact with
   * the twitter API.
   *
   * boolean Default false, set to true in start up if access token and secret
   * are in the datasource config or the session.
   */
  public $isAuthorized = false;

  /**
   * Called before Controller::beforeFilter(), stores reference to Controller
   * object
   *
   * @param AppController $controller
   * @return void
   * @access public
   */
  public function initialize(&$controller) {
    $this->controller =& $controller;
  }

  /**
   * Checks if we are authorized to access a users Twitter account via the API
   * by first checking if the OAuth credentials are in the datasource config,
   * else if they are in the session. If they are not in the config but are in
   * the session, copy them into the datasource config. Finally, set the
   * isAuthorized session variable so the authorization state can be easily
   * determined in the view.
   *
   * @return void
   */
  public function startup() {
    if ($this->accessTokenConfig()) {
      $this->isAuthorized = true;
    } elseif ($this->accessTokenSession()) {
      $ds = $this->getDataSource();
      $this->isAuthorized = true;
      if (!$ds) {
        break;
      }
      $ds->config['oauth_token'] = $this->Session->read('Twitter.Auth.oauth_token');
      $ds->config['oauth_token_secret'] = $this->Session->read('Twitter.Auth.oauth_token_secret');
    }
    $this->Session->write('Twitter.Auth.isAuthorized', $this->isAuthorized);
  }

  /**
   * Returns true if OAuth credentials are in the config of the datasource
   *
   * @return boolean
   */
  public function accessTokenConfig() {
    $ds = $this->getDataSource();
    if (!$ds) {
      return false;
    }
    return !empty($ds->config['oauth_token']) && !empty($ds->config['oauth_token_secret']);
  }

  /**
   * Returns true if OAuth credentials are in the session
   *
   * @return boolean
   */
  public function accessTokenSession() {
    return $this->Session->check('Twitter.Auth.oauth_token') && $this->Session->check('Twitter.Auth.oauth_token_secret');
  }

  /**
   * Returns the datasource object for the current controller's modelClass
   *
   * @return DataSource object
   */
  public function getDataSource() {

    // Get the list of datasources that the ConnectionManager is aware of
    $sources = ConnectionManager::sourceList();

    // If the twitter datasource is in it, return it
    if (in_array('twitter', $sources)) {
      return ConnectionManager::getDataSource('twitter');
    }
    
    App::import('DataSource', 'Twitter.TwitterSource');
    return new TwitterSource();
    
  }

  /**
   * The first stage of the OAuth Dance with Twitter. Gets OAuth Request Token
   * and OAuth Request Token Secret from Twitter.
   *
   * @param string $oAuthConsumerKey
   * @param string $oAuthConsumerSecret
   * @param string $oAuthCallback The url in your app that gets the Access Token
   * @return array Array containing keys oauth_token and oauth__token_secret
   */
  public function getOAuthRequestToken($oAuthConsumerKey, $oAuthConsumerSecret, $oAuthCallback) {

    $request = Set::merge($this->_oAuthRequestDefaults, array(
      'uri' => array(
        'path' => 'oauth/request_token',
      ),
      'auth' => array(
        'oauth_consumer_key' => $oAuthConsumerKey,
        'oauth_consumer_secret' => $oAuthConsumerSecret,
        'oauth_callback' => $oAuthCallback,
      ),
    ));

    App::import('Vendor', 'HttpSocketOauth');
    $Http = new HttpSocketOauth();

    $response = $Http->request($request);

    if ($Http->response['status']['code'] != 200) {
      return false;
    }

    parse_str($response, $requestToken);

    return $requestToken;

  }

  /**
   * The second stage of the OAuth Dance with Twitter. Redirects the user to
   * Twitter so they can authorize your application.
   * 
   * @param string $oAuthRequestToken
   * @return void
   */
  public function authorize($oAuthRequestToken) {
    $this->controller->redirect('https://api.twitter.com/oauth/authorize?oauth_token=' . $oAuthRequestToken);
  }

  /**
   * The third stage of the OAuth Dance with Twitter. Gets OAuth Access Token
   * and OAuth Access Token Secret from Twitter.
   * 
   * @param string $oAuthConsumerKey
   * @param string $oAuthConsumerSecret
   * @param string $oAuthRequestToken
   * @param string $oAuthRequestTokenSecret
   * @param string $oAuthVerifier
   * @return array Array containing keys oauth_token and oauth__token_secret
   */
  public function getOAuthAccessToken($oAuthConsumerKey, $oAuthConsumerSecret, $oAuthRequestToken, $oAuthRequestTokenSecret, $oAuthVerifier) {

    $request = Set::merge($this->_oAuthRequestDefaults, array(
      'uri' => array(
        'path' => 'oauth/access_token',
      ),
      'auth' => array(
        'oauth_consumer_key' => $oAuthConsumerKey,
        'oauth_consumer_secret' => $oAuthConsumerSecret,
        'oauth_token' => $oAuthRequestToken,
        'oauth_token_secret' => $oAuthRequestTokenSecret,
        'oauth_verifier' => $oAuthVerifier,
      ),
    ));

    App::import('Vendor', 'HttpSocketOauth');
    $Http = new HttpSocketOauth();

    $response = $Http->request($request);

    if ($Http->response['status']['code'] != 200) {
      return false;
    }

    parse_str($response, $accessToken);

    return $accessToken;

  }

  /**
   * This is a convenience method that you can call from your controller action
   * that you link to from your views to sign in with twitter, if you don't need
   * to do anything special that deviates from the default approach.
   * 
   * In your controller action you simply do:
   * 
   *     public function twitter_connect($redirect = null) {
   *       $this->TwitterAuth->connect(urldecode($redirect));
   *     }
   *
   * The method first tries to obtain any required data that is not supplied in
   * the parameters. See below for the parameters you can specify and what
   * happens if they are not specified.
   *
   * It then tries to get an OAuth Request Token and OAuth Request Token Secret
   * from Twitter then redirects the user to Twitter to authorize the OAuth
   * Request Token.
   *
   * If for some reason we couldn't get a request token, an error message is set
   * in the session flash and the user is redirected to the redirect param. If
   * that is not set, the error message is dumped out.
   *
   * @param string $redirect The URL the user will be redirected back to after
   * successfully connecting with twitter or an error occured. If not specified
   * errors or results are just displayed on screen.
   * @param string $oAuthCallback The URL twitter will redirect the user to
   * after they authorize your application. If not specified it will be to a
   * twitter_callback action in the current controller.
   */
  public function connect($redirect = null, $oAuthCallback = null) {

    if (!empty($redirect)) {
      $this->Session->write('Twitter.Auth.redirect', $redirect);
    }

    if (empty($oAuthCallback)) {
      $oAuthCallback = Router::url(array('action' => 'twitter_callback'), true);
    }

    $ds = $this->getDataSource();
    if (!$ds) {
      $this->_error(__('Could not get datasource', true), $redirect);
    }

    if (!isset($ds->config['oauth_consumer_key'])) {
      $this->_error(__('Could not get OAuth Consumer Key', true), $redirect);
    }
    $oAuthConsumerKey = $ds->config['oauth_consumer_key'];

    if (!isset($ds->config['oauth_consumer_secret'])) {
      $this->_error(__('Could not get OAuth Consumer Secret', true), $redirect);
    }
    $oAuthConsumerSecret = $ds->config['oauth_consumer_secret'];

    $requestToken = $this->getOAuthRequestToken($oAuthConsumerKey, $oAuthConsumerSecret, $oAuthCallback);

    if ($requestToken) {
      $this->Session->write('Twitter.Auth.oauth_request_token', $requestToken['oauth_token']);
      $this->Session->write('Twitter.Auth.oauth_request_token_secret', $requestToken['oauth_token_secret']);
      $this->TwitterAuth->authorize($requestToken['oauth_token']);
    } else {
      $this->_error(__('Could not get OAuth Request Token from Twitter', true), $redirect);
    }
    
  }

  /**
   * This is a convenience method that you can call from your controller action
   * that twitter redirects the user back to after they authorized your
   * application, if you don't need to do anything special that deviates from
   * the default approach.
   * 
   * In your controller action you simply do:
   * 
   *     public function twitter_callback() {
   *       $this->TwitterAuth->callback();
   *     }
   * 
   * This method exchanges the authorised request token for the OAuth Access
   * Token and OAuth Access Token Secret and stores them in the session before
   * redirecting the user back to the URL passed in in the redirect parameter to
   * the connect action above, or if that is not set, the details are dumped
   * out.
   */
  public function callback() {

    $redirect = null;
    if ($this->Session->check('Twitter.Auth.redirect')) {
      $redirect = $this->Session->read('Twitter.Auth.redirect');
    }

    $ds = $this->getDataSource();
    if (!$ds) {
      $this->_error(__('Could not get datasource', true), $redirect);
    }

    if (!isset($ds->config['oauth_consumer_key'])) {
      $this->_error(__('Could not get OAuth Consumer Key', true), $redirect);
    }
    $oAuthConsumerKey = $ds->config['oauth_consumer_key'];

    if (!isset($ds->config['oauth_consumer_secret'])) {
      $this->_error(__('Could not get OAuth Consumer Secret', true), $redirect);
    }
    $oAuthConsumerSecret = $ds->config['oauth_consumer_secret'];

    if (!$this->Session->check('Twitter.Auth.oauth_request_token')) {
      $this->_error(__('Could not get OAuth Request Token from session', true), $redirect);
    }
    $oAuthRequestToken = $this->Session->read('Twitter.Auth.oauth_request_token');

    if (!$this->Session->check('Twitter.Auth.oauth_request_token_secret')) {
      $this->_error(__('Could not get OAuth Request Token Secret from session', true), $redirect);
    }
    $oAuthRequestTokenSecret = $this->Session->read('Twitter.Auth.oauth_request_token_secret');

    if (empty($this->params['url']['oauth_verifier'])) {
      $this->_error(__('Could not get OAuth Verifier from querystring', true), $redirect);
    }
    $oAuthVerifier = $this->params['url']['oauth_verifier'];

    $accessToken = $this->TwitterAuth->getOAuthAccessToken($oAuthConsumerKey, $oAuthConsumerSecret, $oAuthRequestToken, $oAuthRequestTokenSecret, $oAuthVerifier);

    if ($accessToken) {

      $sessionData = $this->Session->read('Twitter.Auth');
      $sessionData = array_merge($sessionData, $accessToken);
      $this->Session->write('Twitter.Auth', $sessionData);

      if ($redirect) {
        $this->redirect($redirect);
      } else {
        die(pr($this->Session->read('Twitter.Auth')));
      }
      
    } else {
      $this->_error(__('Could not get OAuth Access Token from Twitter', true), $redirect);
    }
    
  }

  /**
   * Sets message in session flash and redirects to redirect URL if not empty,
   * else just dump the message out on the screen.
   * 
   * @param string $message
   * @param string $redirect
   */
  protected function _error($message, $redirect) {

    if ($redirect) {
      $this->Session->setFlash($message);
      $this->controller->redirect($redirect);
    }

    die($message);
    
  }

}
?>