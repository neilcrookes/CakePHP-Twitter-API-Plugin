<?php
/**
 * Configuration options for datasource for Twitter API (and Twitpic).
 *
 * The minimum required to configure the datasource is the datasource option
 * itself:
 *
 *     public $twitter = array(
 *       'datasource' => 'Twitter.TwitterSource'
 *     );
 *
 * You can also optionally specify the OAuth parameters, for example:
 *
 *     public $twitter = array(
 *       'datasource' => 'Twitter.TwitterSource',
 *       'oauth_consumer_key' => '',
 *       'oauth_consumer_secret' => '',
 *       'oauth_token' => '',
 *       'oauth_token_secret' => '',
 *     );
 *
 * However, if you are only making API calls that don't require authentication
 * you don't need to specify the OAuth parameters at all, or if your app has the
 * requirement for multiple oauth options, e.g. you interact with different
 * twitter accounts that have different oauth tokens and oauth token secrets, or
 * you need to use different twitter applications, you do not need to specify
 * them in the config but can dynamically add them into the datasource object
 * config property at run time. E.g.
 *
 *     MyModel->getDataSource()->config['option'] = 'value';
 *
 * or call:
 *
 *     MyModel->setDataSourceConfig()
 *
 * and pass in an array of options to merged into the existing config.
 * 
 * To specify these config parameters, you can either:
 *
 * 1. Add the full twitter datasource config property to your DATABASE_CONFIG
 * class in app/config/database.php file, for example:
 *
 *     public $twitter = array(
 *       'datasource' => 'Twitter.TwitterSource',
 *       'oauth_consumer_key' => '',
 *       'oauth_consumer_secret' => '',
 *       'oauth_token' => '',
 *       'oauth_token_secret' => '',
 *     );
 *
 * This is the CakePHP recommended method and is good if you are happy for the
 * configuration options to be separate from the plugin and all your
 * environments, production, development etc, use the same options and you don't
 * have the ability, or want to, have different database.php files on each
 * enviroment.
 * 
 * If however, you prefer to have the options bundled with the plugin, if say
 * all instances of the plugin, development, production, or for multiple apps,
 * all use the same configuration options, you can use:
 *
 * 2. Rename or copy plugins/twitter/config/twitter_config.default.php to
 * plugins/twitter/config/twitter_config.php and specify the options in the
 * twitter property as required. E.g.
 *
 *     public $twitter = array(
 *       'oauth_consumer_key' => '',
 *       'oauth_consumer_secret' => '',
 *       'oauth_token' => '',
 *       'oauth_token_secret' => '',
 *     );
 *
 * Alternatively, if you need to have some logic around what configuration
 * options are used, e.g. different options depending on the enviroment, and you
 * don't have the ability, or want to, have different database.php files on each
 * enviroment...
 *
 * 3. Add the options to the Configure singleton before the model's constructor
 * is run, in a 'Twitter' key. For example:
 *
 *     // app/config/bootstrap.php
 *     if (strpos(env('HOST_NAME'), 'localhost') !== false) {
 *       Configure::write('Twitter', array(
 *         'oauth_consumer_key' => '',
 *         'oauth_consumer_secret' => '',
 *         'oauth_token' => '',
 *         'oauth_token_secret' => '',
 *       ));
 *     } else {
 *       ... // Other settings
 *     }
 *
 * Finally, if you don't need any OAuth params...
 *
 * 4. Do nothing.
 *
 * This is because when a model in the plugin is instantiated, the constructor
 * in TwitterAppModel checks to see if the the model's datasource (set in the 
 * useDbConfig property) is in the ConnectionManager's list of known sources.
 * With option 1, it will be, but if you don't use option 1, it won't be, so i
 * looks for any of the optional configuration options as described in 2 and 3
 * above, and if they exist merges them with the default datasource option, then
 * adds the datasource, and it's config, to the ConnectionManager, before
 * finally calling parent::__construct(), so that when CakePHP ties a model to
 * it's datasource, the ConnectionManager *is* aware of the datasource.
 *
 * Twitpic
 * -------
 *
 * To use the Twitpic functionality to upload images to Twitpic you also need to
 * specify the api key you get from Twitpic when you register your application.
 * For example:
 *
 *     public $twitter = array(
 *       'oauth_consumer_key' => '',
 *       'oauth_consumer_secret' => '',
 *       'oauth_token' => '',
 *       'oauth_token_secret' => '',
 *       'twitpic_api_key' => '',
 *     );
 *
 * The OAuth params must be in the datasource config before you call the method
 * to create the Twitpic. As above, you can hard code them in the datasource
 * config or add them dynamically at run time.
 *
 * You can get an api key here: http://dev.twitpic.com/apps/new
 *
 * @author Neil Crookes <neil@neilcrookes.com>
 * @link http://www.neilcrookes.com
 * @copyright (c) 2010 Neil Crookes
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class TWITTER_CONFIG {

  var $twitter = array(
//    'datasource' => 'Twitter.TwitterSource',
//    'oauth_consumer_key' => '',
//    'oauth_consumer_secret' => '',
//    'oauth_token' => '',
//    'oauth_token_secret' => '',
//    'twitpic_api_key' => '',
  );

}
?>