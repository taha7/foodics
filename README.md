# Installation

Clone the repository

    git clone git@github.com:taha7/foodics.git

Switch to the repo folder

    cd foodics


Install all the dependencies using composer

    composer install

Copy the example env file and make the required configuration changes in the .env file

    cp .env.example .env

Generate a new application key

    php artisan key:generate

(**Set the configuration needed in .env**)
I've added the default database configuration and a fake email configuration that you can use to see your emails in https://ethereal.email/

Run the database migrations

    php artisan migrate

Run the database seed

    php artisan db:seed

Start the local development server

    php artisan serve