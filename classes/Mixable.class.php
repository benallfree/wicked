<?

require_once('WickedBase.class.php');

class Mixable extends WickedBase
{
  static $mixin_classes = array();
  static $is_initialized = false;
  
  static function add_mixin($class_name)
  {
    self::$mixin_classes[] = $class_name;
  }
  
  protected static function init()
  {
    $args = func_get_args();
    foreach(self::$mixin_classes as $class_name)
    {
      call_user_func_array(array($class_name, 'init'), $args);
    }
    self::$is_initialized = true;
  }

  protected static function ensure_init()
  {
    if(!self::$is_initialized)
    {
      call_user_func(array(get_called_class(), 'init'));
    }
  }
  
  static function __callstatic($name, $args)
  {
    self::ensure_init();
    foreach(self::$mixin_classes as $class_name)
    {
      if(array_search($name, get_class_methods($class_name))!==false)
      {
        return call_user_func_array(array($class_name, $name), $args);
      }
    }
    trigger_error('Non-existent method was called in class '.__CLASS__.': '.$name, E_USER_ERROR);
  }
  
  
}
