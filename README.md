# Flight Application

This app allows storing new flight trips and searching for trips details.

### Installation & Configuration :

Install the dependencies and devDependencies and start local server by following these steps:

* Clone the project from this [link][git_project_url]
* Move the project to local root folder
* Rename the .env.example file to .env
* Run this command to generate new key
    ```sh
    $ php artisan key:generate
    ```
* Create new MySQL database and set the name to [DB_DATABASE] key in .env file
* Open the terminal in the project directory
* Create database tables by running this command
     ```sh
    $ php artisan migrate
    ```
* Run trips seeder to insert testing data by running this command
     ```sh
    $ php artisan db:seed
    ```
* Start the project by running this command
     ```sh
    $ php artisan serve --port=8000
    ```
  or any port you need

## Test the APIs using Postman

### 1- Add new trip

* URL => http://localhost:8000/api/trips/store
* Method => POST
* Body => form-data
    * origin_city => String
    * destination_city => String
    * price => Number
    * take_off_time => timestamp in format like (2020-07-26 23:00:00)
    * landing_time => timestamp in format like (2020-07-27 11:00:00)

### 2- Search for a trip

* URL => http://localhost:8000/api/trips/get
* Method => GET
* Body => row
    * origin_city => String
    * destination_city => String
    * type => Number [0: Cheapest trip, 1: Fastest trip]

[//]: # (These are reference links used in the body of this note)


[git_project_url]: <https://github.com/AhmedElmaghraby207/flight-app>
