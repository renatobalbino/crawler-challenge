# Crawler Challenge

Create a crawler for a job opportunity (PHP)

##### Objectives:
  - Fetch all cars from https://seminovos.com.br and store the data locally
  - Provide a simple Rest API to consume the data

# How to begin?

  - Clone or download the project to your machine
  
  ```git clone git@github.com:renatobalbino/crawler-challenge.git```
  - Download project dependencies
  
  ```composer install```
  - Duplicate the file **.env.example** an save it as **.env** and configure your database parameters
  - Run the following command to begin fetching data ***(WARNING: THIS COMMAND WILL ERASE YOUR DATABASE TABLES WHEN EXECUTE)***
  
  ```php artisan crawler:seminovos```
  - Run the command in another terminal to run php builtin server
  
  ```php artisan serve```
  - You are ready to go!

# Endpoints:
**[GET]** /api/carros 

Description: Lists all vehicles crawled so far
Parameters:
  - registrosPagina (10, 25, 50, 100)
    - Description: Total records allowed per page
    - Optional (defaults to 10)
    - Type: integer
  - ordenar (1 - Price ascending, 2 - Price descending, 3 - Year ascending, 4 - Year descending)
    - Description: Order list by price, year (more to be implemented)
    - Optional
    - Type: integer
  - ano_de
    - Description: Only vehicles newer than
    - Optional
    - Type: int
  - ano_ate
    - Description: Only vehicles older than
    - Optional
    - Type: int
  - km_de
    - Description: Only vehicles with more than
    - Optional
    - Type: string
  - km_ate
    - Description: Only vehicles with less than
    - Optional
    - Type: string
  - preco_de
    - Description: Only vehicles more expensive than
    - Optional
    - Type: string
  - preco_ate
    - Description: Only vehicles cheaper than
    - Optional
    - Type: string

**[GET]** /api/carros/[SLUG]

Description: Lists vehicle details
Parameters:
  - slug (Ex.: chevrolet-onix-hatch-ltz-14-8v-flexpower-5p-mec-1592986720)
    - Description: Short and unique url like description for the vehicle
    - Optional (defaults to '/carros')
    - Type: string

---

###### More information on how to setup your Laravel environment, plese go to [Laravel Website](https://laravel.com/docs/7.x)
