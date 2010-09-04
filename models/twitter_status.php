<?php
/**
 * TwitterStatus model provides custom find types and other methods for managing
 * twitter statuses through the twitter API.
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitterStatus extends TwitterAppModel {

  /**
   * The model's schema. Used by FormHelper
   *
   * @var array
   */
  public $_schema = array(
    'id' => array('type' => 'integer', 'length' => '11'),
    'text' => array('type' => 'string', 'length' => '140'),
    'in_reply_to_status_id' => array('type' => 'integer', 'length' => '11'),
    'in_reply_to_user_id' => array('type' => 'integer', 'length' => '11'),
    'in_reply_to_screen_name' => array('type' => 'string', 'length' => '255'),
  );

  /**
   * Validation rules for the model
   *
   * @var array
   */
  public $validate = array(
    'text' => array(
      'notEmpty' => array(
        'rule' => 'notEmpty',
        'message' => 'Please enter some text',
      ),
      'maxLength' => array(
        'rule' => array('maxLength', 140),
        'message' => 'Text cannot exceed 140 characters',
      ),
    ),
    'in_reply_to_status_id' => array(
      'numeric' => array(
        'rule' => 'numeric',
        'message' => 'The ID of the status you are replying to should be numeric',
        'required' => false,
        'allowEmpty' => true,
      ),
    ),
    'in_reply_to_user_id' => array(
      'numeric' => array(
        'rule' => 'numeric',
        'message' => 'The ID of the user you are replying to should be numeric',
        'required' => false,
        'allowEmpty' => true,
      ),
    ),
  );

  /**
   * Custom find types available on this model
   * 
   * @var array
   */
  public $_findMethods = array(
    'publicTimeline' => true,
    'homeTimeline' => true,
    'userTimeline' => true,
    'mentions' => true,
    'retweetedByMe' => true,
    'retweetedToMe' => true,
    'show' => true,
    'retweetsOfMe' => true,
    'retweets' => true,
    'retweetedBy' => true,
  );

  /**
   * The custom find types that require authentication
   *
   * @var array
   */
  public $findMethodsRequiringAuth = array(
    'homeTimeline',
    'userTimeline',
    'mentions',
    'retweetedByMe',
    'retweetedToMe',
    'retweetsOfMe',
    'retweets',
    'retweetedBy'
  );

  /**
   * The options allowed by each of the custom find types
   * 
   * @var array
   */
  public $allowedFindOptions = array(
    'publicTimeline'  => array('skip_user', 'include_rts', 'include_entities'),
    'homeTimeline'    => array('since_id', 'max_id', 'count', 'page', 'skip_user', 'include_entities'),
    'userTimeline'    => array('since_id', 'max_id', 'count', 'page', 'skip_user', 'include_rts', 'include_entities', 'id', 'user_id', 'screen_name'),
    'mentions'        => array('since_id', 'max_id', 'count', 'page', 'include_rts', 'include_entities'),
    'retweetedByMe'   => array('since_id', 'max_id', 'count', 'page'),
    'retweetedToMe'   => array('since_id', 'max_id', 'count', 'page'),
    'retweetsOfMe'    => array('since_id', 'max_id', 'count', 'page'),
    'show'            => array('id'),
    'retweets'        => array('count', 'id'),
    'retweetedBy'     => array('count', 'page', 'id'),
  );

  /**
   * The vast majority of the custom find types actually follow the same format
   * so there was little point explicitly writing them all out. Instead, if the
   * method corresponding to the custom find type doesn't exist, the options are
   * applied to the model's request property here and then we just call
   * parent::find('all') to actually trigger the request and return the response
   * from the API.
   *
   * In addition, if you try to fetch a timeline that supports paging, but you
   * don't specify paging params, you really want all tweets in that timeline
   * since time imemoriam. But twitter will only return a maximum of 200 per
   * request. So, we make multiple calls to the API for 200 tweets at a go, for
   * subsequent pages, then merge the results together before returning them.
   *
   * Twitter's API uses a count parameter where in CakePHP we'd normally use
   * limit, so we also copy the limit value to count so we can use our familiar
   * params.
   * 
   * @param string $type
   * @param array $options
   * @return mixed
   */
  public function find($type, $options = array()) {
    if (!empty($options['limit']) && empty($options['count'])) {
      $options['count'] = $options['limit'];
    }
    if ((empty($options['page']) || empty($options['count']))
    && array_key_exists($type, $this->allowedFindOptions)
    && in_array('page', $this->allowedFindOptions[$type])
    && in_array('count', $this->allowedFindOptions[$type])) {
      $options['page'] = 1;
      $options['count'] = 200;
      $results = array();
      while (($page = $this->find($type, $options)) != false) {
        $results = array_merge($results, $page);
        $options['page']++;
      }
      return $results;
    }
    if (method_exists($this, '_find' . Inflector::camelize($type))) {
      return parent::find($type, $options);
    }
    $this->request['uri']['path'] = '1/statuses/' . Inflector::underscore($type);
    if (array_key_exists($type, $this->allowedFindOptions)) {
      $this->request['uri']['query'] = array_intersect_key($options, array_flip($this->allowedFindOptions[$type]));
    }
    if (in_array($type, $this->findMethodsRequiringAuth)) {
      $this->request['auth'] = true;
    }
    return parent::find('all', $options);
  }

  /**
   * Retweeted By
   * -------------
   *
   *     TwitterStatus::find('retweetedBy', $options)
   *
   * **Options:**
   *
   * - id integer
   * - limit integer
   * - page integer
   *
   * See http://dev.twitter.com/doc/get/statuses/user_timeline for details
   *
   * **Results:**
   *
   * An array of tweets
   *
   * **Notes:**
   *
   * You should provide either user_id or screen_name in the conditions
   *
   * @param $state string 'before' or 'after'
   * @param $query array
   * @param $results array
   * @return mixed
   * @access protected
   * */
  protected function _findRetweetedBy($state, $query = array(), $results = array()) {
    if ($state == 'before') {
      if (empty($query['id'])) {
        return false;
      }
      $this->request = array(
        'uri' => array(
          'path' => '1/statuses/' . $query['id'] . '/retweeted_by'
        ),
        'auth' => true,
      );
      $this->request['uri']['query'] = array_intersect_key($query, array_flip($this->allowedFindOptions['retweetedBy']));
      return $query;
    } else {
      return $results;
    }
  }

  /**
   * Creates a tweet
   * 
   * @param mixed $data
   * @param mixed $validate
   * @param mixed $fieldList
   * @return mixed
   */
  public function tweet($data = null, $validate = true, $fieldList = array()) {
    $this->request = array(
      'uri' => array(
        'path' => '1/statuses/update',
      ),
    );
    if (isset($data['TwitterStatus']['text'])) {
      $this->request['body'] = array(
        'status' => $data['TwitterStatus']['text'],
      );
    }
    return $this->save($data, $validate, $fieldList);
  }

  /**
   * Retweets a tweet
   *
   * @param integer $id Id of the tweet you want to retweet
   * @return mixed
   */
  public function retweet($id = null) {
    if (!$id) {
      return false;
    }
    if (!is_numeric($id)) {
      return false;
    }
    $this->request = array(
      'uri' => array(
        'path' => '1/statuses/retweet/'.$id,
      ),
    );
    $this->create();
    // Dummy data ensures Model::save() does in fact call DataSource::create()
    $data = array('TwitterStatus' => array('text' => 'dummy'));
    return $this->save($data);
  }

  /**
   * Called by tweet or retweet
   * 
   * @param mixed $data
   * @param mixed $validate
   * @param mixed $fieldList
   * @return mixed
   */
  public function save($data = null, $validate = true, $fieldList = array()) {
    $this->request['auth'] = true;
    $result = parent::save($data, $validate, $fieldList);
    if ($result && !empty($this->response['id'])) {
      $this->setInsertID($this->response['id']);
    }
    return $result;
  }

  /**
   * Deletes a tweet
   * 
   * @param integer $id Id of the tweet to be deleted
   * @param boolean $cascade
   * @return boolean
   */
  public function delete($id = null, $cascade = true) {
    $this->request = array(
      'uri' => array(
        'path' => '1/statuses/destroy',
        'query' => array(
          'id' => $id,
        ),
      ),
      'auth' => true,
    );
    return parent::delete($id, $cascade);
  }

}

?>