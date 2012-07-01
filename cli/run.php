#!/usr/bin/php
<?
error_reporting(E_ALL|E_STRICT);
ini_set('dislay_errors', 'On');
date_default_timezone_set('UTC');

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
  echo($s."\n");
  exec($s . " 2>&1",$output, $result);
  if($result!=0)
  {
    puts("Error: $result");
    puts($s);
    puts($output);
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

function help($repo_fpath)
{
  puts("Wicked 1.0.0 CLI Tool");
  puts("---------------------");
  puts("Repo Location: {$repo_fpath}");
}

function cmd_install($repo_fpath, $args)
{
  $repo_name = array_shift($args);
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
  );
  cmd("git clone ? ?", $repos[$repo_name], $repo_fpath."/$repo_name");
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
  default:
    help($repo_fpath);  
}
