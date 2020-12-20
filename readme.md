# Products API application

### Do the following steps in the root directory inside the project in your local machine...

## Step 1: Install all the depedencies

    composer install
    
## Step 2: Configure database
- Create a `.env` file
- Copy from `.env.sample` file and paste all things into `.env` file
- Change the following environment variables:

  `DB_DATABASE=your_db_name`
  
  `DB_USERNAME=your_user_name`
  
  `DB_PASSWORD=your_password`
  
## Step 3: Generate all the needed tables in the database

    php artisan migrate

## Step 4: Generate the application encryption key

    php artisan key:generate
    
## Step 5: Create laravel storage symbolic link

    php artisan storage:link
    
## Step 6: Run the app

    php artisan serve

**Note:** To see the structure and behaviour of the API endpoints you can import `products-api.json` file into any tool such as Postman, Talend API Tester, etc.
