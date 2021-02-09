# Commission app by Kestutis

Made with PHP 7.4 on docker

Following steps:
- To use application, install all dependencies `composer install`
- To run commission calculation run `php application.php commission:calculate input.csv`
- To change weekly discount ammount, change it in App/Service/Operation::private $discount
- To run tests execute `composer test`
