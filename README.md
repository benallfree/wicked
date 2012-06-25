# Wicked - The Event-Driven Web Framework

Wicked is a modular event-driven web framework based on the wisdom of WordPress, Drupal, Ruby on Rails, and similar architectures. In particular, Wicked focuses on being event-based. You can use Wicked to build any type of web application or mobile application using a event-driven programming techniques. If you are familiar with WordPress actions/filters or Drupal events, you will feel right at home in Wicked.

## Getting Started

Install the Wicked microkernel in your web root:

    $ get clone git@github.com:benallfree/wicked.git w
  
Create an `index.php` that looks something like this:

    require('w/cked.php');
    
    function hello($s)
    {
      return "Hello, world!";
    }
    
    Wicked::register_filter('run', 'hello');
    
    echo Wicked::do_filter('run');

That's it. You just created your first Wicked web application.

## Getting More Wicked

By installing the Wicked microkernel, you have done a lot. The microkernel provides the facilities for PHP observer/events and class mixins. It turns out that establishing a convention for these two decisions makes life a lot easier for endlessly extending your application.

## Getting Wicked with Friends

Who else is using Wicked? Browse the Wicked Registry at http://wickedphp.com. You will find tens, maybe even dozens of add-ons to use.

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

