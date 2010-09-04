<?php
/**
 * Twitpic model. Provides methods for fetching info about a twitpic given an id
 * and uploading a picture to twitpic.
 *
 * Note, I originally tried to develop the Twitpic functionality in a plugin of
 * it's own, but to upload an image to Twitpic you have to sign in with Twitter
 * which requires you to have registered an application with Twitter and uses
 * that Twitter applications OAuth consumer key and secret. I was also using the
 * TwitterAuth component from this Twitter plugin but it was becoming
 * unnecessarily complicated with the component having to fetch your Twitter
 * application's OAuth consumer details from the Twitter plugin and the api key
 * from the Twitpic plugin, or duplicate the Twitter details in the Twitpic
 * config, which didn't make sense because you needed the Twitter plugin anyway.
 * So I decided to just incorporate the Twitpic functionality into the twitter
 * plugin, and if you only need Twitpic functionality, just ignore the rest.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class Twitpic extends TwitterAppModel {

  /**
   * Schema for the model. Used by FormHelper.
   * 
   * @param array
   */
  public $_schema = array(
    'id' => array('type' => 'integer', 'length' => '11'),
    'message' => array('type' => 'string', 'length' => '255'),
    'media' => array('type' => 'string', 'length' => '255'),
  );

  /**
   * Validation rules for the model
   * 
   * @param array
   */
  public $validate = array(
    'media' => array(
      'rule' => array('extension', array('gif', 'jpeg', 'png', 'jpg')),
      'message' => 'Please supply a valid image file'
    ),
    'message' => array(
      'rule' => array('maxLength', 140),
      'message' => 'Max length of a message is 140 characters'
    ),
  );

  /**
   * Show a twitpic record
   * ---------------------
   *
   *     Twitpic::find('first', $options);
   *
   * **Options:**
   *
   * - conditions
   *  - id (or Twitpic.id) The id of the Twitpic record you want to view
   *
   * **Results:**
   *
   * An array containing details of the Twitpic including:
   * - id
   * - message
   * - created timestamp
   * - number of views
   * - details of the twitter user that uploaded the file
   * - comments on the twitpic
   */
  public function _findFirst($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      $this->request['uri']['path'] = '2/media/show';
      if (isset($query['conditions']['id'])) {
        $this->request['uri']['query']['id'] = $query['conditions']['id'];
      } elseif (isset($query['conditions']['Twitpic.id'])) {
        $this->request['uri']['query']['id'] = $query['conditions']['Twitpic.id'];
      } else {
        return false;
      }
      $this->request['uri']['host'] = 'api.twitpic.com';
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Upload an image and optional message to Twitpic
   * -----------------------------------------------
   *
   *     Twitpic::save(array(
   *       'Twitpic' => array(
   *         'message' => 'Optional message',
   *         'media' => array(
   *           'tmp_name' => '/path/to/file',
   *           'type' => 'image/jpeg', // mimetype
   *           'name' => 'filename.jpg', //
   *         )
   *       )
   *     ));
   *
   * **Notes:**
   *
   * - The media key in the $data array is the same (or a subset of) the data
   * you get when you upload a file through an html form.
   * - There are validation rules for extension to be an image and message
   * length < 140 chars
   * - See here for more details http://dev.twitpic.com/docs/2/upload/
   *
   * @param mixed $data
   * @param mixed $validate
   * @param mixed $fieldList
   * @return mixed
   */
  public function save($data = null, $validate = true, $fieldList = array()) {

    $ds = $this->getDataSource();

    // Get the authorization header that twitpic will pass on to twitter to
    // verify the credentials of the request.
    $authorizationHeader = $ds->Http->authorizationHeader(array(
      'uri' => array(
        'scheme' => 'https',
        'host' => 'api.twitter.com',
        'path' => '1/account/verify_credentials.json',
      ),
      'method' => 'GET',
      'auth' => array(
        'realm' => 'http://api.twitter.com/',
        'oauth_consumer_key' => $ds->config['oauth_consumer_key'],
        'oauth_consumer_secret' => $ds->config['oauth_consumer_secret'],
        'oauth_token' => $ds->config['oauth_token'],
        'oauth_token_secret' => $ds->config['oauth_token_secret'],
      )
    ));

    // The boundary string is used to identify the different parts of a
    // multipart http request
    $boundaryString = 'Next_Part_' . String::uuid();

    // Build the multipart body of the http request, the first part is the api
    // key of your application whihc you get from Twitpic
    $body = "--$boundaryString\r\n";
    $body.= "Content-Disposition: form-data; name=\"key\"\r\n";
    $body.= "\r\n";
    $body.= $ds->config['twitpic_api_key'] . "\r\n";

    // If there is a message, add this to the next part of the request body.
    if (!empty($data[$this->alias]['message'])) {
      $body.= "--$boundaryString\r\n";
      $body.= "Content-Disposition: form-data; name=\"message\"\r\n";
      $body.= "\r\n";
      $body.= $data[$this->alias]['message'] . "\r\n";
    }

    // Finally add the headers and binary data for the image
    $body.= "--$boundaryString\r\n";
    $body.= "Content-Disposition: form-data; name=\"media\"; filename=\"{$data[$this->alias]['media']['name']}\"\r\n";
    $body.= "Content-Type: {$data[$this->alias]['media']['type']}\r\n";
    $body.= "\r\n";
    $body.= file_get_contents($data[$this->alias]['media']['tmp_name'])."\r\n";
    $body.= "--$boundaryString--\r\n";

    // Tie it all together in the model's request property
    $this->request = array(
      'uri' => array(
        'path' => '2/upload',
        'host' => 'api.twitpic.com',
      ),
      'header' => array(
        'Content-Type' => 'multipart/form-data; boundary="' . $boundaryString . '"',
        'X-Auth-Service-Provider' => 'https://api.twitter.com/1/account/verify_credentials.json',
        'X-Verify-Credentials-Authorization' => $authorizationHeader
      ),
      'body' => $body,
    );

    $result = parent::save($data, $validate, $fieldList);

    if ($result && !empty($this->response['id'])) {
      $this->setInsertID($this->response['id']);
    }

    return $result;
    
  }
  
}
?>