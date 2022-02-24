## CSV Generator 

### What's Done
    - Deleting the controller and moving the work to the command class
    - Separeting the getData method to three specific methods
    - Getting the data and change it to collections via Collect()
    - filtering the data and matching the customers and the orders 
    - checking for the shipping address and not the billing one
    - mapping on the items and associate the needed informations foreach item
    - Building the final array data 
    - Proccessing the file creation to create the CSV file 

### Build & Run
```sh
git clone this-repository
composer install
php artisan command:generatecsv
````