<?php
App::import('Datasource', 'Rest.RestSource');
/**
 * CakePHP Datasource for accessing the Twitter API (And Twitpic)
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitterSource extends RestSource {

  /**
   * Overrides RestSource constructor to use the OAuth extension to CakePHP's
   * default HttpSocket class to issue the requests.
   *
   * If no config is passed into the constructor, i.e. the config is not in
   * app/config/database.php check if any config is in the config directory of
   * the plugin, or in the configure class and use that instead.
   *
   * @param array $config
   */
  public function __construct($config = null) {

    if (!is_array($config)) {
      $config = array();
    }

    // Default config
    $defaults = array(
      'datasource' => 'Twitter.TwitterSource',
    );

    // Try and import the plugins/twitter/config/twitter_config.php file and
    // merge the config with the defaults above
    if (App::import(array('type' => 'File', 'name' => 'Twitter.TWITTER_CONFIG', 'file' => 'config'.DS.'twitter_config.php'))) {
      $TWITTER_CONFIG = new TWITTER_CONFIG();
      if (isset($TWITTER_CONFIG->twitter)) {
        $defaults = array_merge($defaults, $TWITTER_CONFIG->twitter);
      }
    }

    // Add any config from Configure class that you might have added at any
    // point before the model is instantiated.
    if (($configureConfig = Configure::read('Twitter.config')) != false) {
      $defaults = array_merge($defaults, $configureConfig);
    }

    $config = array_merge($defaults, $config);
      
    App::import('Vendor', 'HttpSocketOauth');
    parent::__construct($config, new HttpSocketOauth());
    
  }

  /**
   * Adds in common elements to the request such as the host and extension and
   * OAuth params from config if not set in the request already
   *
   * @param AppModel $model The model the operation is called on. Should have a
   *  request property in the format described in HttpSocket::request
   * @return mixed Depending on what is returned from RestSource::request()
   */
  public function request(&$model) {

    // If auth key is set and not false, fill the request with auth params from
    // config if not already present in the request and set the method to OAuth
    // to trigger HttpSocketOauth to sign the request
    if (array_key_exists('auth', $model->request)
    && $model->request['auth'] !== false) {

      if (!is_array($model->request['auth'])) {
        $model->request['auth'] = array();
      }
      if (!isset($model->request['auth']['method'])) {
        $model->request['auth']['method'] = 'OAuth';
      }
      $oAuthParams = array(
        'oauth_consumer_key',
        'oauth_consumer_secret',
        'oauth_token',
        'oauth_token_secret',
      );
      foreach ($oAuthParams as $oAuthParam) {
        if (!isset($model->request['auth'][$oAuthParam])) {
          $model->request['auth'][$oAuthParam] = $this->config[$oAuthParam];
        }
      }
    }

    // Set default host, N.B. some API calls use api.twitter.com, in which case
    // they should be set in the individual model call
    if (!isset($model->request['uri']['host'])) {
      $model->request['uri']['host'] = 'api.twitter.com';
    }

    // Append '.json' to path if not already got an extension
    if (strpos($model->request['uri']['path'], '.') === false) {
      $model->request['uri']['path'] .= '.json';
    }

    // Get the response from calling request on the Rest Source (it's parent)
    $response = parent::request($model);

//    echo '<pre>';
//    echo htmlspecialchars($this->Http->request['raw']);
//    echo htmlspecialchars($this->Http->response['raw']['response']);
//    echo '</pre>';
//    die();

    return $response;

  }

}
?>