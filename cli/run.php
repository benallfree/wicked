#!/usr/bin/php
<?
error_reporting(E_ALL|E_STRICT);
ini_set('dislay_errors', 'On');
date_default_timezone_set('UTC');

function dprint($s, $should_exit = true)
{
  ob_start();
  var_dump($s);
  $s = ob_get_clean();
  puts($s);
  if($should_exit) die;
}

function puts($s)
{
  echo($s."\n");
}

function interpolate()
{
  $args = func_get_args();
  $s = array_shift($args);
  foreach($args as $arg)
  {
    $s = preg_replace_callback("/([!?])/", function($matches) use ($arg) {
      if(count($matches)<=1) return;
      switch($matches[1])
      {
        case '?':
          return escapeshellarg($arg);
          break;
        case '!':
          return $arg;
          break;
        default:
          dprint("Unknown type $type in interpolate");
      }
      
    }, $s, 1);
  }
  return $s;
}

function cmd($cmd)
{
  $args = func_get_args();
  $s = call_user_func_array('interpolate', $args);
  puts($s);
  exec($s . " 2>&1",$output, $result);
  if($result!=0)
  {
    puts("Error: $result");
    puts("Command failed: $s");
    puts("Command output: ");
    var_dump($output);
    die;
  }
  return $output;
}

function cmd_up($repo_fpath, $argv)
{
  require('Wicked');
  return;
  foreach($dependencies as $d)
  {
    list($module_name, $version) = $d;
    $fpath = $repo_fpath."/{$module_name}";
    $lookup = array(
      'w'=>'git@github.com:benallfree/wicked.git',
    );
    if(file_exists($fpath))
    {
      puts("found");
    } else {
      cmd("git clone ? ?", $lookup[$module_name], $fpath);
      require($fpath."/Wicked");
      $dst_fpath = $fpath."-{$config['version']}";
      //rename($fpath, $dst_fpath);
    }
  }
}

function conditional_write($fpath, $s, $default)
{
  if(file_exists($fpath) && confirm("File exists {$fpath}, overwrite?", $default))
  {
    puts("Overwriting $fpath");
  } else {
    puts("Skipping $fpath");
    return false;
  }
  file_put_contents($fpath, $s);
  return true;
}

function confirm($message, $default='y')
{
  puts($message . ( $default=='y' ? ' [Y/n]' : ' [y/N]'));
  flush();
  $confirmation  =  strtolower(trim( fgets( STDIN ) ));
  if(!$confirmation) $confirmation = $default;
  return $confirmation == 'y';
}

function cmd_create($repo_fpath, $argv)
{
  $arg = array_shift($argv);
  switch($arg)
  {
    case 'stub':
      $arg = array_shift($argv);
      $dst_fpath = realpath(str_replace("~/", $_SERVER['HOME'], $arg));
      $cfg = <<<CFG
php_value date.timezone UTC
php_value memory_limit 200M

AddHandler php-legacy .php
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteBase /
RewriteEngine on
RewriteRule ^index\\.php\$ - [L]
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule . /index.php [L]
</IfModule>
CFG;
      //file_put_contents($dst_fpath."/.htaccess", $cfg);
      $php = <<<PHP
<?
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__) . PATH_SEPARATOR . "{$repo_fpath}/..");
require('wicked/w/cked.php');
PHP;
      conditional_write($dst_fpath."/w.php", $php, 'y');

      $php = <<<PHP
<?
require('w.php');
function hello(\$s)
{
return "Hello, world!";
}

W::register_filter('run', 'hello');

echo W::do_filter('run');
PHP;
      conditional_write($dst_fpath."/index.php", $php, 'n');
      touch($dst_fpath."/Wicked");
      cmd_up($repo_fpath, $argv);
      break;
  }
}

function cmd_update($repo_fpath, $args)
{
  foreach(glob($repo_fpath."/*", GLOB_ONLYDIR) as $fname)
  {
    puts(basename($fname));
    if(!file_exists($fname."/.git")) continue;
    chdir($fname);
    cmd("git pull origin master");
    $config_defaults = array('requires'=>array());
    $config = array();
    if(file_exists($fname."/Wicked")) require($fname."/Wicked");
    $config = array_merge($config_defaults, $config);
    foreach($config['requires'] as $r)
    {
      if(file_exists($repo_fpath."/$r")) continue;
      cmd_install($repo_fpath, array($r));
    }
  }
  chdir('..');
}

function help($repo_fpath)
{
  puts("Wicked 1.0.0 CLI Tool");
  puts("---------------------");
  puts("Repo Location: {$repo_fpath}");
}

function cmd_install($repo_fpath, $args)
{
  $repo_name = array_shift($args);
  $fname = $repo_fpath."/$repo_name";
  if(file_exists($fname))
  {
    puts ("Skipping $repo_name, already installed.");
    return;
  }
  puts("Installing $repo_name");
  $repos = array(
    'request'=>'git@github.com:benallfree/wicked-request.git',
    'path_utils'=>'git@github.com:benallfree/wicked-path-utils.git',
    'class_lazyloader'=>'git@github.com:benallfree/wicked-class-lazyloader.git',
    'string'=>'git@github.com:benallfree/wicked-string.git',
    'url'=>'git@github.com:benallfree/wicked-url.git',
    'debug'=>'git@github.com:benallfree/wicked-debug.git',
    'presentation'=>'git@github.com:benallfree/wicked-presentation.git',
    'request'=>'git@github.com:benallfree/wicked-request.git',
    'haml'=>'git@github.com:benallfree/wicked-haml.git',
    'php_sandbox'=>'git@github.com:benallfree/wicked-php-sandbox.git',
    'sass'=>'git@github.com:benallfree/wicked-sass.git',
    'collections'=>'git@github.com:benallfree/wicked-collections.git',
    'coolbook'=>'git@github.com:benallfree/wicked-coolbook.git',
    'monochrome'=>'git@github.com:benallfree/wicked-monochrome.git',
    'account'=>'git@github.com:benallfree/wicked-account.git',
    'db'=>'git@github.com:benallfree/wicked-db.git',
    'activerecord'=>'git@github.com:benallfree/wicked-activerecord.git',
    'exec'=>'git@github.com:benallfree/wicked-exec.git',
    'cookie_session'=>'git@github.com:benallfree/wicked-cookie-session.git',
    'inflection'=>'git@github.com:benallfree/wicked-inflection.git',
    'http'=>'git@github.com:benallfree/wicked-http.git',
  );
  cmd("git clone ? ?", $repos[$repo_name], $fname);
  $config = array();
  if(file_exists($fname."/Wicked")) require($fname."/Wicked");
  $config = array_merge($config_defaults, $config);
  foreach($config['requires'] as $r)
  {
    if(file_exists($repo_fpath."/$r")) continue;
    cmd_install($repo_fpath, array($r));
  }
  
}

$repo_fpath = $_SERVER['HOME']."/wicked";
if(isset($_SERVER['WICKED_HOME']))
{
  $repo_fpath = $_SERVER['WICKED_HOME'];
}

array_shift($argv);

$arg = array_shift($argv);
switch($arg)
{
  case 'install':
    cmd_install($repo_fpath, $argv);
    break;
  case 'up':
    cmd_up($repo_fpath, $argv);
    break;
  case 'create':
    cmd_create($repo_fpath, $argv);
    break;
  case 'update':
    cmd_update($repo_fpath, $argv);
    break;
  default:
    help($repo_fpath);  
}
