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
  
  static $root_fpath;
  
  static $modules = array();
  
  static $autoloads = array();

  protected static function init($config=array())
  {
    self::$root_fpath = realpath($_SERVER['SITE_HTMLROOT']);
    self::$autoloads[] = dirname(__FILE__);
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
  
  protected static function autoload($class_name)
  {
    foreach(self::$autoloads as $fpath)
    {
      $fname = $fpath."/{$class_name}.class.php";
      if(file_exists($fname))
      {
        require($fname);
        return true;
      }
    }
    return false;
  }
  
  static function find_module($module_name, $desired_version = null)
  {
    self::ensure_init();
    if(file_exists($module_name)) return $module_name; // If file path is passed, just return it
    $paths = explode(PATH_SEPARATOR, get_include_path());
    $latest_version_int = 0;
    foreach($paths as $path)
    {
      $module_glob = $path."/{$module_name}*";
      $files = glob($module_glob, GLOB_ONLYDIR);
      if(!$files) continue;
      foreach($files as $file)
      {
        list($name,$version) = explode('-', basename($file).'-');
        if($name != $module_name) continue; // Not a match;
        if(!$version && $desired_version == null)
        {
          return $file;
        }
        
        var_dump($version);
        list($major, $minor, $dot) = explode('.', $version);
        $version_int = (int)sprintf("%03d%03d%03d", $major, $minor, $dot);
        
        if($version_int > $latest_version_int)
        {
          $latest_version = $version;
          $latest_version_int = $version_int;
          $module_fpath = $path."/{$module_name}-{$latest_version}";
        }
      }
    }
    if($latest_version_int==0) return null;
    return $module_fpath;
  }
  
  static function load($module_name, $version=null)
  {
    self::ensure_init();
    $module_fpath = self::find_module($module_name, $version);
    $parts = pathinfo($module_name);
    $module_name = $parts['filename'];
    if(!$module_fpath) 
    {
      trigger_error("Wicked Module '{$module_name}' not found.", E_USER_ERROR);
    }
    
    $config_defaults = array(
      'format'=>'1.0.0',
      'fpath'=>$module_fpath,
      'vpath'=>substr($module_fpath, strlen(self::$root_fpath)),
      'cache_fpath'=>self::$root_fpath."/cache/{$module_name}",
      'cache_vpath'=>"/cache/{$module_name}",
      'requires'=>array(),
    );
    
    // Add autoloads
    $autoload_fpath = $module_fpath."/classes";
    if(file_exists($autoload_fpath))
    {
      self::$autoloads[] = $autoload_fpath;
    }
    
    // Load the metadata file
    $config_fpath = $module_fpath."/Wicked";
    $config = array();
    if(file_exists($config_fpath))
    {
      require($config_fpath);
    }
    $config = array_merge($config_defaults, $config);
    $config = W::do_filter('config', $config, $module_name);


    self::$modules[$module_name] = $config;

    // Handle requires
    foreach($config['requires'] as $req_info)
    {
      if(is_array($req_info))
      {
        list($req_name, $req_version) = $req_info;
      } else {
        $req_name = $req_info;
        $req_version = null;
      }
      self::load($req_name, $req_version);
    }
    $load_fpath = $module_fpath."/load.php";
    if(file_exists($load_fpath))
    {
      require($load_fpath);
    }
  }
}