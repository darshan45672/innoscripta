# Backend Laravel Challenge

A RESTful API for a news aggregator service built using Laravel. This application pulls articles from multiple sources, stores them locally, and provides endpoints for a personalized news experience.

## Features

- **User Authentication**: Secure user registration, login, logout, and password reset using Laravel Sanctum.
- **Article Management**: Fetch, search, and filter articles by keyword, date, category or source with pagination support.
- **User Preferences**: Save and retrieve preferred news sources, categories, and authors; get personalized news feeds.
- **Data Aggregation**: Regularly fetch articles from 3 external news APIs, stored locally for optimized retrieval.
- **API Documentation**: Comprehensive API documentation using Postman for seamless integration.
- **Dockerized Environment**: Easy setup with Docker and `docker-compose.yml`.

## Tech Stack

- **Framework**: Laravel 11
- **Authentication**: Laravel Sanctum
- **APIs**: Integrated with NewsAPI.org, The Guardian, and New York Times
- **Database**: SQLite (default laravel 11 DB )
- **Testing**: Feature tests for reliability.

Setting up [Docker Environment](Docker.md)

# Setting Up The Local Development Environment

1. ### Clone the repository
   ```
   https://github.com/darshan45672/innoscripta.git
   ```

2. ### Install the dependencies
   ```
   composer install
   ```
3. ### Update the dependencies with the latest
   ```
   composer update
   ```

4. ### Copy the environment variables
   ```
   cp .env.example .env
   ```
5. ### Update the Database Credentials
   - For SQLite (default DB of laravel 11)
     ```
     DB_CONNECTION=sqlite
     # DB_HOST=
     # DB_PORT=
     # DB_DATABASE=
     # DB_USERNAME=
     # DB_PASSWORD=
     ```
   - For MySQL
     ```
     DB_CONNECTION=mysql
     DB_HOST= 127.0.0.1
     DB_PORT= 3306
     DB_DATABASE=your_database_name
     DB_USERNAME=your_preffered_user_name
     DB_PASSWORD=your_password
     ```
   - For Postgresql
     ```
     DB_CONNECTION=pgsql
     DB_HOST=<your_database_IP_address>
     DB_PORT=5432
     DB_DATABASE=postgres
     DB_USERNAME=postgres
     DB_PASSWORD=postgres
     ```
6. ### Generate the Application Key
   ```
   php artisan key:generate
   ```
7. ### Migrate all the tables and seed the Database
   ```
   php artian migrate:fresh --seed
   ```
8. ### Start the Application Server
   ```
   php artisan serve
   ```
9. ### Split the Terminal or Open New Terminal to run cron jobs
    ```
    php artisan schedule:work
    ```

## API Documentation

### APIs Available

1. #### Article
   - ##### Get All Articles with filters
     
     ```
     api/articles
     ```
     | Parameter           | Values (example)                                                | Description                               |
     |---------------------|-----------------------------------------------------------------|-------------------------------------------|
     | No Parameter|  ```api/articles```| This will return all articles with thier associated categories, authors and source |
     | `search`| ```api/articles?search=Trump```| This will return all the articles which have `Trump` in thier `title`, `description`, `content`, `author`|
     | `provider`| ```api/articles?provider=newsapi```| This will return all the articles whose provider api `newsapi`|
     |`source` |```api/articles?source=CNN```| This will return all the articles whose provider api `newsapi`|
     |`categories`|```api/articles?categories=Food```|This will return all the articles whose categories is `Food`|

