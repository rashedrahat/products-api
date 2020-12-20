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

## Step 4: Run the app

    php artisan serve
    
Note: `http://127.0.0.1:8000` by visiting if you see something an exception `No application encryption key has been specified.` page at your browser then run the `php artisan key:generate` into your terminal. After this run the app again.
