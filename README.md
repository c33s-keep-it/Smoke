# Smoke

- License: MIT
- Build: [![Build Status](https://secure.travis-ci.org/phmLabs/Smoke.png)](http://travis-ci.org/phmLabs/Smoke)
- Smoke Online: [http://smoke.phmlabs.com/](http://smoke.phmlabs.com/)
- Homepage: [http://www.thewebhatesme.com/](http://www.thewebhatesme.com/)


## Purpose

Smoke can be used to check if a web platform is basically working.

The only thing you have to do is to define the entry point for the scanner and start. The Smoke crawler will scan the first n (defined as command line parameter) websites it finds and check them against a group of defined rules.

```
Smoke.phar analyse "http://www.amilio.de"
```

## Installation

Download the phar archive using curl
```
curl -O -LSs https://raw.github.com/phmLabs/Smoke/master/bin/Smoke.phar && chmod +x Smoke.phar
```

Installation complete.

## Configuration

### Command Line Parameters for Analysis

- **--num_urls** defines how many urls should be checked. Standard is 20.  
  Example: `Smoke.phar analyse --num-urls="20" test.com` 

- **--parallel_requests** defines how many requests are done in parallel. Standard is 10.  
  Example: `Smoke.phar analyse --parallel_requests="10" test.com` 

- **--config_file** sets the configuration file to use for subsequently testing  
  Example: `Smoke.phar analyse --config_file="path/to/my.yml" test.com` 


###Configuration File

You may configure any Smoke run using URL *whitelists* and *blacklists* as well as ruleset *definitions*. The configuration is stored in a singe YAML file, so it can be used in subsequent test runs. The configuration file may contain up to three elements:

- **whitelist** contains a dash list of URL regex patterns. This allows Smoke to test an URL in question when it is found somewhere in the HTML source.

- **blacklist** *optional* – contains a dash list of URL regex patterns Smoke is not allowed to test. This is useful for any linked third-party domains or services that are a project of their own, e.g. online shops on a subdomain. 

- **rules** *optional* – contains a list of named tests with their corresponding PHP classes and test parameters.

Currently, it is not possible to *blacklist* a certain URL pattern and *whitelist* another that matches a blacklisted one.

####Configuration example and usage
 
```yaml
whitelist:
 - ^www.amilio.de^
 
blacklist: 
 - ^www.amilio.de/api^

rules:
  HtmlSize:
    class: whm\Smoke\Rules\Html\SizeRule
    parameters:
      maxSize: 1

  ImageSize:
      class: whm\Smoke\Rules\Image\SizeRule
      parameters:
        maxSize: 1
```

For more examples, see the *examples* directory. 
To call Smoke with your config file, just issue on command line:

```bash
Smoke.phar analyse --config_file="test.yml" test.com
```

##How to write custom rules

Each rule is defined in a simple PHP class that implements the `whm\Smoke\Rules\Rule` interface with a *validate* method. This is what *Rule* basically prescribes:

```php
<?php
namespace whm\Smoke\Rules;

use whm\Smoke\Http\Response;

interface Rule
{
    public function validate(Response $response);
}
```

###Write your own rule

An optional ***init*** method for rule configuration may take a parameter which value is defined within your Smoke config file. Inside your ***validate*** method, use the *Response* object which is passed to it; throw a *ValidationFailedException* if your test fails. 


```php
<?php
namespace MyApplication;

use whm\Smoke\Http\Response;
use whm\Smoke\Rules\Rule;
use whm\Smoke\Rules\ValidationFailedException;

class FooTest implements Rule
{
	private $search;
	
    public function init($foo = "foo")
    {
        $this->foo = $foo;
    }
    
    public function validate(Response $response)
    {
        if ( stripos($response->getBody(), $this->foo ) === false) {
            throw new ValidationFailedException( $this->foo . ' not found' );
        }
    }
}
```

###Use with config file
Within your Smoke config file, you may reference your rule like this:

```yaml
rules:
  MyFooTest:
    class: MyApplication\FooTest
    parameters:
      foo: "bar"    
```


##Response object

The *Response* object offers a bunch of useful shortcuts to things that happen 
until a page request is finished:

```php
<?php
use whm\Smoke\Http\Response;

class BarTest implements Rule
{
    public function validate(Response $response)
    {
    	// HTTP status code, e.g. 400
    	$status       = $response->getStatus();
    	
    	// S'th like "text/html"
    	$content_type = $response->getContentType();
    	
    	// String, usually page HTML
    	$body         = $response->getBody();
    	
    	// Integer (milliseconds)
    	$duration     = $response->getDuration();
    	
    	// To be explained
    	$request      = $response->getRequest();
    	$header       = $response->getHeader()
	}
}
```



