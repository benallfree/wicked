<?

class Mixin extends WickedBase
{
  static $config;
  
  static function init($config = array())
  {
    self::$config = $config;
  }
}