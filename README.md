## About This Laravel Test

I have created a small application to get to know the technologies:
- Lavarel
- Vue JS

## Task achievements
- Single page form with file upload functionality and a checkbox
- Form handling in Vue.js
- The form must upload data without a page reload
- Show error on upload/parse failure
    - Server side
        - Validate formmat of columns: date(YYYY-mm-dd), Number of crimes(number), Houses sold(number) 
        - Validate file empty
        - Validate file header's format
    - Client side
        - Validate not choosing a file
- If the optional checkbox "Save to Database" is ticked it inserts the data into the database
    - Using database.sqlite to manage DB
- On data upload show:
    - The average of all prices sold
    - Count of all houses sold
    - Number of crimes in 2011
    - The average price per year in the London area
- Test:
    - Feature/UploadTest(8 testcases) of the implemented UploadController for upload file functionality

## Remaining issues
- The design of the form is minimal

## How to run
- Navigate to the project's directory
- On server side:
    - Create an empty file: "database/database.sqlite" and change to "DB_CONNECTION=sqlite" in .env file
    - To create database run: "php artisan migrate"
    - To run laravel's server run: "php artisan serve"
- On client side:
    - "npm install"
    - "npm run watch"
- Standard file: housing_in_london_monthly_variables.csv

## Mainly working files
- resources/js/app.js
- resources/views/welcome.blade.php
- resources/js/components/UploadComponent.vue

- routes/web.php
- app/Http/Controllers/UploadController.php
- database/migrations/2021_02_05_062433_create_data_uploads_table.php