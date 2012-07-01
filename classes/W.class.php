<?
require_once('Mixable.class.php');
require_once('ActionEvent.class.php');
require_once('FilterEvent.class.php');
require_once('ModuleLoader.class.php');

class W extends Mixable
{
  static $config_defaults = array(
    'mixins'=>array(
      'ActionEvent',
      'FilterEvent',
      'ModuleLoader',
    )
  );
  
  static $root_fpath;
  static $root_vpath;
  
  static function init($config=array())
  {
    self::$root_fpath = realpath($_SERVER['SITE_HTMLROOT']);
    self::$root_vpath = '';
    $config = array_merge(self::$config_defaults, $config);
    foreach($config['mixins'] as $class_name)
    {
      self::add_mixin($class_name);
    }
  } 
}