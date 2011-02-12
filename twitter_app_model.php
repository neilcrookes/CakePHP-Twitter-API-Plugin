<?php
/**
 * Plugin base model. Configures all models with regards the database
 * configuration (the datasource for the plugin), table to use (none).
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TwitterAppModel extends AppModel {

  /**
   * The datasource all models in the plugin use.
   *
   * @var string
   */
  public $useDbConfig = 'twitter';

  /**
   * The models in the plugin get data from the web service, so they don't need
   * a table.
   *
   * @var string
   */
  public $useTable = false;

  /**
   * Methods in the models result in HTTP requests using the HttpSocket. So
   * rather than do all the heavy lifting in the datasource, we set the various
   * params of the request in the individual model methods. This ties the model
   * to the data layer, but these models are especially for this datasource.
   *
   * @var array
   */
  public $request = array();

  /**
   * Adds the datasource to the connection manager if it's not already there,
   * which it won't be if you've not added it to your app/config/database.php
   * file.
   *
   * @param $id
   * @param $table
   * @param $ds
   */
  public function __construct($id = false, $table = null, $ds = null) {

    $sources = ConnectionManager::sourceList();

    if (!in_array('twitter', $sources)) {
      ConnectionManager::create('twitter', array('datasource' => 'Twitter.TwitterSource'));
    }

    parent::__construct($id, $table, $ds);

  }

  /**
   * Merges passed config with datasource existing config and returns the merged
   * array.
   * 
   * @param array $config
   * @return array
   */
  public function setDataSourceConfig($config = array()) {
    $ds = $this->getDataSource($this->useDbConfig);
    if (!is_array($ds->config)) {
      $ds->config = array($ds->config);
    }
    $ds->config = array_merge($ds->config, $config);
    return $ds->config;
  }

}
?>