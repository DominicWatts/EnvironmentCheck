# Php / Environemnt CLI Check

![phpcs](https://github.com/DominicWatts/EnvironmentCheck/workflows/phpcs/badge.svg)

![PHPCompatibility](https://github.com/DominicWatts/EnvironmentCheck/workflows/PHPCompatibility/badge.svg)

![PHPStan](https://github.com/DominicWatts/EnvironmentCheck/workflows/PHPStan/badge.svg)

![php-cs-fixer](https://github.com/DominicWatts/EnvironmentCheck/workflows/php-cs-fixer/badge.svg)

CLI script to check php and environment settings

# Install instructions #

`composer require dominicwatts/environmentcheck`

`php bin/magento setup:upgrade`

# Usage instructions #

Run console command

`xigen:check:platform [-t|--type TYPE] [--]`

    php/bin magento xigen:check:platform
    
Verbose output

    php/bin magento xigen:check:platform -v

    php/bin magento xigen:check:platform -t installer

    php/bin magento xigen:check:platform -t updater

## Example Output

```
bin/magento xigen:check:platform
[2020-06-06 09:13:52] Start
26/26 [============================] 100% < 1 sec 70.2 MiB      | Missing : None
+---------------+------------------------------------------------------------------------------+
| Test          | Result                                                                       |
+---------------+------------------------------------------------------------------------------+
| PHP Memory    | Requirements met                                                             |
| PHP Version   | Required : ~7.1.3||~7.2.0                                                    |
| PHP Version   | Current : 7.2.23                                                             |
| PHP Extension | Required : iconv                                                             |
| PHP Extension | Required : mbstring                                                          |
| PHP Extension | Required : curl                                                              |
| PHP Extension | Required : dom                                                               |
| PHP Extension | Required : hash                                                              |
| PHP Extension | Required : openssl                                                           |
| PHP Extension | Required : xmlwriter                                                         |
| PHP Extension | Required : pcre                                                              |
| PHP Extension | Required : json                                                              |
| PHP Extension | Required : gd                                                                |
| PHP Extension | Required : bcmath                                                            |
| PHP Extension | Required : simplexml                                                         |
| PHP Extension | Required : spl                                                               |
| PHP Extension | Required : xsl                                                               |
| PHP Extension | Required : intl                                                              |
| PHP Extension | Required : ctype                                                             |
| PHP Extension | Required : pdo_mysql                                                         |
| PHP Extension | Required : soap                                                              |
| PHP Extension | Required : zip                                                               |
| PHP Extension | Required : libxml                                                            |
| PHP Extension | Required : phar                                                              |
| PHP Extension | Missing : None                                                               |
| PHP Setting   | Update : You must have installed GD library with --with-jpeg-dir=DIR option. |
| Permissions   | Missing : None                                                               |
+---------------+------------------------------------------------------------------------------+

[2020-06-06 09:13:52] Finish
```