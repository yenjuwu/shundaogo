# ShundaoGo

## Synopsis

ShundaoGo is an online food ordering system. 

## Code Example

Code include FoodPicky Theme, and the child theme which should be use to modified any changes to the parent theme. 
Any modification should be done by adding Wordpress "Action" and "Filter" in the child theme or write a custom plugin.

## Motivation

Provide an online ordering system. 

## Installation

1. Install a new wordpress
2. Name your database to shundao
3. Change the table_prefix to sd_
4. Enable Wordpress Multisite - by adding this line of code to your wp-config.php define('MULTISITE', true);
5. Import shundao-local.sql file
6. Unizp the the upload.zip file into your wp-content/ folder
7. Init your local repo to https://github.com/zheng4uga/shundaogo.git
    a. git init
    b. git remote add origin https://github.com/zheng4uga/shundaogo.git
    c. git remote -v
    d. git reset --hard HEAD ( this function should reset your local to the HEAD where your local should be sync with git )



## API Reference

This wordpress site contain WooCommerce plugins

## Contributors

Yong Qui Zheng \n
Jay Kim \n
Wei Wang \n

## License

A short snippet describing the license (MIT, Apache, etc.)
