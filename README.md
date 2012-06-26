# Wicked - The Event-Driven Web Framework

Wicked is a modular event-driven web framework based on the wisdom of WordPress, Drupal, Ruby on Rails, and similar architectures. In particular, Wicked focuses on being event-based. You can use Wicked to build any type of web application or mobile application using a event-driven programming techniques. If you are familiar with WordPress actions/filters or Drupal events, you will feel right at home in Wicked.

## Installing

Wicked comes with a CLI tool that gives you easy access to the Wicked core and Wicked Registry of community contributions. It manages dependencies and installs the right things in the right places.

    $ git clone git@github.com:benallfree/wicked.git ~/wicked/w
    $ nano ~/bin/wicked


Copy this into `wicked`:

    #!/bin/sh
    php ~/wicked/w/cli/run.php $1 $2 $3 $4 $5 $6 $7

    $ chmod 755 ~/bin/wicked
    $ wicked --help

Wicked will install modules locally. By default, Wicked will use ~/wicked as your local repo path. If you don't like that, set `WICKED_HOME` to whatever you want.

Once you like where it lives, try creating your first app:

    $ wicked create stub ~/path/to/www
        
Wicked will install any dependencies in $WICKED_HOME and then create an `~/path/to/www/index.php` that looks something like this:

    $path = "~/path/to/wicked/repo"; // This is where $WICKED_HOME points to
    set_include_path(get_include_path() . PATH_SEPARATOR . $path);
    require('w/cked.php');
    
    function hello($s)
    {
      return "Hello, world!";
    }
    
    Wicked::register_filter('run', 'hello');
    
    echo Wicked::do_filter('run');

That's it. You just created your first Wicked web application. Browse to it and test it out.

## Getting More Wicked

By installing the Wicked CLI, you have done a lot. The CLI provides access to community contributions.

For example, you can browse the popular contributions like this:

    $ wicked list --popular
    

    Popular Modules
    ---------------
    active_record 1.0.0..........42 others
    module 1.0.0................24 others


If you want to see who depends on who, do this:

    $ wicked info ActiveRecord -v 1.0.0

    active_record 1.0.0
    ------------------
    An mySQL-backed ORM for Wicked.
    
    Depends upon:
    db ~>1.0.1
    collections ~>1.0.2
    inflection ~>1.0.0
    module ~>1.0.4
    
    Used by:
    account 1.0.0
    attachment 1.0.0
    
If you want to install a module, it's easy:

    $ wicked install active_record
    
That will install the latest version of active_record into $WICKED_HOME.

Some contributions are "platform" contributions upon which many other contributions are built. You'll begin to see how the pieces fit together as you become more familiar with the community. 

The Wicked microkernel (`w`) is at the core. It provides the facilities for PHP observer/events and class mixins. It turns out that establishing a convention for these two decisions makes life a lot easier for endlessly extending your application.

## Getting Even More Wicked

If you do much developing with other people's stuff, you'll arrive at a point where you want to break out the source code and fork your own version of it so you can modify it and submit a pull request like a good citizen. Or maybe you just want to look at it.

Module forks are always installed in your local repo.

Wicked automatically looks in your $WICKED_HOME location when loading modules. But before it looks there, it will also look for a local repo named `wicked` in the current directory or a parent directory. If it finds one, it will assume that is the repo you mean to use. (You can override the repo locations by using a custom `Wicked` file, see below).

    $ wicked fork active_record -v 1.0.0
    
    Forking active_record-1.0.0 to local wicked/activerecord-1.0.0
    
Now when you load `active_record`, it will load from the local forked copy instead.


## Getting Wicked with Friends

Who else is using Wicked? What are they doing with it? Use your CLI tool to find out, or browse the Wicked Registry at http://wickedphp.com. You will find tens, maybe even dozens of add-ons to use.

## Extending Wicked

You can extend Wicked directly simply by using its built-in event system as shown above. But consider standing on the shoulders of giants instead.

You probably want to make a (Wicked Module)[http://github.com/benallfree/wicked-module] instead of writing against the raw Wicked class. The module framework makes some intelligent decisions about file convention organization, and dozens of other compatible modules already exist. Almost any conceivable extension to Wicked can be accomplished through the very popular Wicked Module add-on. Check that out.

To contribute a Wicked add-on, or an add-on that is compatible with (or minor to) some other Wicked add-on, use these steps:

1. Create a github repo
1. Add your `wicked` meta file
1. Push your changes
1. Submit your github URL to the [Wicked Registry](http://wickedphp.com)

Once added to the registry, your repo will automatically be scanned for the `wicked` metadata file.

Your metadata file should look like this:

    Format=1.0
    Version=1.0.0
    Dependency[] = <user contrib> [<version qualifier>]
    Dependency[] = <user contrib> [<version qualifier>]
    Dependency[] = <user contrib> [<version qualifier>]

Where `<user contrib>` is a Wicked Registry contribution name and the optional `<version qualifier>` states which version (or range) of the contrib is required for your contribution to work. These version markers translate into git tags tracked by the registry.

## Contributing to the Wicked Core

If you do have a core enhancement or patch that is core:

1. Fork
1. Change
1. Test
1. Submit pull request :)

