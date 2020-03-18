# Php / Environemnt CLI Check

CLI script to check php and environment settings

# Install instructions #

`composer require dominicwatts/environmentcheck`

`php bin/magento setup:upgrade`

# Usage instructions #

Run console command

`xigen:check:platform [-t|--type TYPE]`

    php/bin magento xigen:check:platform

    php/bin magento xigen:check:platform -t installer

    php/bin magento xigen:check:platform -t updater

## Example Output

```
[2020-03-18 17:03:22] Start
 0/3 [>---------------------------]   0%
[2020-03-18 17:03:22] PHP Version Required : ~7.1.3||~7.2.0||~7.3.0
[2020-03-18 17:03:22] PHP Version Current : 7.2.23
 1/3 [=========>------------------]  33%
[2020-03-18 17:03:22] PHP Extension Required : curl
[2020-03-18 17:03:22] PHP Extension Required : iconv
[2020-03-18 17:03:22] PHP Extension Required : mbstring
[2020-03-18 17:03:22] PHP Extension Required : dom
[2020-03-18 17:03:22] PHP Extension Required : hash
[2020-03-18 17:03:22] PHP Extension Required : openssl
[2020-03-18 17:03:22] PHP Extension Required : xmlwriter
[2020-03-18 17:03:22] PHP Extension Required : pcre
[2020-03-18 17:03:22] PHP Extension Required : json
[2020-03-18 17:03:22] PHP Extension Required : gd
[2020-03-18 17:03:22] PHP Extension Required : bcmath
[2020-03-18 17:03:22] PHP Extension Required : simplexml
[2020-03-18 17:03:22] PHP Extension Required : spl
[2020-03-18 17:03:22] PHP Extension Required : xsl
[2020-03-18 17:03:22] PHP Extension Required : intl
[2020-03-18 17:03:22] PHP Extension Required : ctype
[2020-03-18 17:03:22] PHP Extension Required : pdo_mysql
[2020-03-18 17:03:22] PHP Extension Required : soap
[2020-03-18 17:03:22] PHP Extension Required : zip
[2020-03-18 17:03:22] PHP Extension Required : libxml
[2020-03-18 17:03:22] PHP Extension Missing : None
 2/3 [==================>---------]  66%
[2020-03-18 17:03:22] PHP Setting Update : You must have installed GD library with --with-jpeg-dir=DIR option.
 3/3 [============================] 100%
[2020-03-18 17:03:22] Finish
```