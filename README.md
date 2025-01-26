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
     |`from`|```api/articles?from=2025-01-25```|This will return all the articles which are published from 25-01-2025|
     |`to`|```api/articles?to=2025-01-26```|This will return all the articles which are published till 26-01-2025|

     + API Response Structure
     
       ##### Root Object

       | Key               | Type       | Description                                                                                   |
       |--------------------|------------|-----------------------------------------------------------------------------------------------|
       | `current_page`     | Integer    | The current page of the paginated response.                                                   |
       | `data`             | Array      | An array containing the paginated items. Can be empty if no data is available.               |
       | `first_page_url`   | String     | The URL of the first page in the pagination.                                                  |
       | `from`             | Integer    | The starting index of the items in the current page.                                          |
       | `last_page`        | Integer    | The total number of pages available.                                                         |
       | `last_page_url`    | String     | The URL of the last page in the pagination.                                                  |
       | `links`            | Array      | A list of pagination links with their labels, URLs, and active status.                       |
       | `next_page_url`    | String     | The URL for the next page, or `null` if there is no next page.                                |
       | `path`             | String     | The base path of the API without query parameters.                                           |
       | `per_page`         | Integer    | The number of items per page.                                                                |
       | `prev_page_url`    | String     | The URL for the previous page, or `null` if there is no previous page.                       |
       | `to`               | Integer    | The ending index of the items in the current page.                                           |
       | `total`            | Integer    | The total number of items across all pages.                                                  |

       ##### links Array Object

       | Key        | Type    | Description                                                                 |
       |------------|---------|-----------------------------------------------------------------------------|
       | `url`      | String  | The URL of the pagination link, or `null` if it's a disabled link.          |
       | `label`    | String  | The label for the pagination link, e.g., page number or navigation text.    |
       | `active`   | Boolean | Whether the link represents the current active page (`true` or `false`).    |

       ##### `data` (Array of Articles)

       Each item in the `data` array is an object with the following keys:

       | Key            | Type        | Description                                                      |
       |----------------|-------------|------------------------------------------------------------------|
       | `id`           | Integer     | Unique identifier for the article.                             |
       | `title`        | String      | Title of the article.                                          |
       | `description`  | String      | Brief summary of the article.                                  |
       | `url`          | String (URL)| Link to the full article.                                      |
       | `publishedAt`  | String (ISO)| Date and time when the article was published, in ISO 8601 format. |
       | `urlToImage`   | String (URL)| URL of the article's featured image.                           |
       | `provider`     | String      | The API provider that sourced this article.                   |
       | `news_source`  | String      | Name of the news source that published the article.            |
       | `categories`   | Array       | Categories assigned to the article (e.g., `general`).         |
       | `authors`      | Array       | List of authors who contributed to the article.  |
       
     + API Response

       ```
       api/articles?search=Aston&provider=The Guardian&source=Sport&categories=Football&from=2025-01-25&to=2025-01-26
       ```

       respone

       ```
       {
       "current_page": 1,
       "data": [
        {
            "id": 17,
            "title": "Aston Villa v West Ham United: Premier League – live",
            "description": "Aston Villa v West Ham United: Premier League – live",
            "content": "Aston Villa v West Ham United: Premier League – live",
            "url": "https://www.theguardian.com/football/live/2025/jan/26/aston-villa-v-west-ham-united-premer-league-live-score-updates",
            "publishedAt": "2025-01-26T16:27:04.000000Z",
            "urlToImage": "",
            "provider": "The Guardian",
            "news_source": "Sport",
            "categories": [
                "Football"
            ],
            "authors": [
                "Anonymous"
            ]
        }
       ],
       "first_page_url": "http://127.0.0.1:8000/api/articles?page=1",
       "from": 1,
       "last_page": 1,
       "last_page_url": "http://127.0.0.1:8000/api/articles?page=1",
       "links": [
        {
            "url": null,
            "label": "&laquo; Previous",
            "active": false
        },
        {
            "url": "http://127.0.0.1:8000/api/articles?page=1",
            "label": "1",
            "active": true
        },
        {
            "url": null,
            "label": "Next &raquo;",
            "active": false
        }
       ],
       "next_page_url": null,
       "path": "http://127.0.0.1:8000/api/articles",
       "per_page": 10,
       "prev_page_url": null,
       "to": 1,
       "total": 1
       }
       ```
   - ##### Show Particular Article
     ```
     api/articles/{id}
     ```

     + ##### API Response Structure

       ## Root Object
       | Key              | Type     | Description                                                                                     |
       |-------------------|----------|-------------------------------------------------------------------------------------------------|
       | `id`             | Integer  | The unique identifier for the article.                                                         |
       | `title`          | String   | The title of the article.                                                                      |
       | `description`    | String   | A brief description or summary of the article.                                                 |
       | `url`            | String   | The URL link to the full article.                                                              |
       | `urlToImage`     | String   | The URL to the image associated with the article.                                              |
       | `publishedAt`    | String   | The publication date and time of the article in ISO 8601 format.                               |
       | `content`        | String   | The main content of the article, truncated if necessary.                                       |
       | `provider`       | String   | The name of the provider or API service that supplied the article.                             |
       | `news_source_id` | Integer  | The unique identifier for the news source.                                                     |
       | `authors`        | Array    | An array of objects representing the authors of the article.                                   |
       | `categories`     | Array    | An array of objects representing the categories the article belongs to.                        |
       | `source`         | Object   | An object representing the source of the article.                                              |

       ##### `authors` Array Object

       | Key        | Type     | Description                                                                                     |
       |------------|----------|-------------------------------------------------------------------------------------------------|
       | `id`       | Integer  | The unique identifier for the author.                                                          |
       | `name`     | String   | The name of the author.                                                                         |
       | `pivot`    | Object   | A pivot object representing the relationship between the article and the author.                |

       ##### `pivot` Object (within `authors`)
       | Key           | Type     | Description                                                                                 |
       |---------------|----------|---------------------------------------------------------------------------------------------|
       | `article_id`  | Integer  | The unique identifier for the article.                                                      |
       | `author_id`   | Integer  | The unique identifier for the author.                                                       |

       ##### `categories` Array Object

       | Key        | Type     | Description                                                                                     |
       |------------|----------|-------------------------------------------------------------------------------------------------|
       | `id`       | Integer  | The unique identifier for the category.                                                        |
       | `name`     | String   | The name of the category.                                                                       |
       | `pivot`    | Object   | A pivot object representing the relationship between the article and the category.              |

       ##### `pivot` Object (within `categories`)
       | Key           | Type     | Description                                                                                 |
       |---------------|----------|---------------------------------------------------------------------------------------------|
       | `article_id`  | Integer  | The unique identifier for the article.                                                      |
       | `category_id` | Integer  | The unique identifier for the category.                                                     |

       ##### `source` Object

       | Key   | Type     | Description                                                                                     |
       |-------|----------|-------------------------------------------------------------------------------------------------|
       | `id`  | Integer  | The unique identifier for the source.                                                          |
       | `name`| String   | The name of the source.                                                                         |
     
2. #### User
   - ##### Authentication
