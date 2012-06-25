<?

require_once('Mixable.class.php');

class Wicked extends Mixable
{
  static function init($config=array())
  {
    spl_autoload_register('Wicked::autoload');    
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