#Translation

The Asgard Translation package helps you export and import translations.

- [Installation](#installation)
- [Commands](#commands)

<a name="installation"></a>
##Installation

	composer require asgard/translation 0.*

<a name="commands"></a>
##Commands

###Export CSV
Export new translations to a CSV file, along with original translations.

Usage:

	php console translation:export-csv [srcLocale] [dstLocale] [file]

###Import CSV
Import new translations from a CSV file and export them yo YAML.

Usage:

	php console translation:import [src] [dst]

###Export YAML
Export new translations to a YAML file.

Usage:

	php console translation:export-yaml [dstLocale] [dst]

##Contributing

Please submit all issues and pull requests to the [asgardphp/asgard](http://github.com/asgardphp/asgard) repository.

## License

The Asgard framework is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)