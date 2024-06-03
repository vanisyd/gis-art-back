# Installation
1. Open the terminal in your root project directory
2. Use the following command to install dependencies
<br>`composer install`
3. Run the following command to generate the key
<br>`php artisan key:generate`
4. Create necessary tables in the DB
<br>`php artisan migrate`
5. To serve the application, you need to run the following command in the project directory
<br>`php artisan serve`

# Usage
First of all you have to import data from your CSV file.
You can do that using artisan command:
<br>`php artisan driver:import-trips`
<br>After that you are free to use the service in any way you prefer, either by artisan
commands or by API endpoints.

# Local development
The project is configured with Laravel Sail. To use it, please refer to the official
documentation https://laravel.com/docs/11.x/sail

# Requirements
- PHP 8.3
- MySQL 8
- Composer
- (Optional) Docker
