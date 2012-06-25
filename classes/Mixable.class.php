<?

require_once('WickedBase.class.php');

class Mixable extends WickedBase
{
  static $mixin_classes = array();
  
  static function add_mixin($class_name)
  {
    self::$mixin_classes[] = $class_name;
  }
  
  static function init()
  {
    $args = func_get_args();
    foreach(self::$mixin_classes as $class_name)
    {
      call_user_func_array(array($class_name, 'init'), $args);
    }
  }
  
  static function __callstatic($name, $args)
  {
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
