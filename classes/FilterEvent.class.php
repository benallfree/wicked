<?

class FilterEvent extends EventMixin
{
  static function register_filter($filter_name, $callback, $weight=10)
  {
    self::register_event('Filter', $filter_name, $callback, $weight);
  }

  static function do_filter($filter_name)
  {
    $args = func_get_args();
    array_unshift($args, 'Filter');
    return call_user_func_array(array(parent, 'do_event'), $args);
  }
}