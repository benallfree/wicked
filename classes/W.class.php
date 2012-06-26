<?

require_once('Mixable.class.php');

class W extends Mixable
{
  static $config_defaults = array(
    'mixins'=>array(
      'ActionEvent',
      'FilterEvent',
    )
  );

  static function init($config=array())
  {
    $config = array_merge(self::$config_defaults, $config);
    spl_autoload_register('W::autoload');    
    parent::init(self::ensure_key($config, 'mixins'));
    if(isset($config['mixins']))
    {
      foreach($config['mixins'] as $class_name)
      {
        self::add_mixin($class_name);
      }
    }
  } 
  
  static function autoload($class_name)
  {
    $fname = dirname(__FILE__)."/{$class_name}.class.php";
    if(file_exists($fname))
    {
      require($fname);
      return true;
    }
    return false;
  }
}