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

function cmd_create($repo_fpath, $argv)
{
  $arg = array_shift($argv);
  switch($arg)
  {
    case 'app':
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
set_include_path(get_include_path() . PATH_SEPARATOR . dirname(__FILE__)."/wicked" . PATH_SEPARATOR . "{$repo_fpath}");
require('w/cked.php');

function hello(\$s)
{
return "Hello, world!";
}

W::register_filter('run', 'hello');

echo W::do_filter('run');
PHP;
      file_put_contents($dst_fpath."/index.php", $php);
      touch($dst_fpath."/Wicked");
      cmd_up($repo_fpath, $argv);
      break;
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
  case '--version':
    
    break;
  case '--help':
    puts("Help!");
    break;
  case 'up':
    cmd_up($repo_fpath, $argv);
    break;
  case 'create':
    cmd_create($repo_fpath, $argv);
    break;
  
}
