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
     GET: api/articles
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
       GET api/articles?search=Aston&provider=The Guardian&source=Sport&categories=Football&from=2025-01-25&to=2025-01-26
       ```

       respone

       ```json
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
     GET api/articles/{id}
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
     + User Registeration

       ```
       POST api/register
       ```

       - ##### API Request Structure
         ###### Request Body
         | Key                     | Type       | Description                                                                                 |
         |--------------------------|------------|---------------------------------------------------------------------------------------------|
         | `name`                  | String     | The name of the user.                                                                       |
         | `email`                 | String     | The email address of the user.                                                             |
         | `password`              | String     | The password for the user account.                                                         |
         | `password_confirmation` | String     | Confirmation of the password for verification purposes.                                     |
         | `phone`                 | String     | The phone number of the user.                                                              |
         | `preferred_categories`  | Array[Int] | An array of IDs representing the user's preferred categories.                              |
         | `preferred_authors`      | Array[Int] | An array of IDs representing the user's preferred authors.                                 |
         | `preferred_sources`      | Array[Int] | An array of IDs representing the user's preferred news sources.                            |

         ##### Example Request Body
         ```json
         {
         "name": "admin",
         "email": "admin@admin.com","password": "12345678",
         "password_confirmation": "12345678",
         "phone": "9019003490",
         "preferred_categories": [1],
         "preferred_authors": [2],
         "preferred_sources": [3]
         }
         ```
       - ##### API Response Structure

         ##### Response Body
         | Key                     | Type       | Description                                                                                 |
         |--------------------------|------------|---------------------------------------------------------------------------------------------|
         | `access_token`           | String     | The authentication token issued after a successful login.                                   |
         | `token_type`             | String     | The type of token, typically `Bearer`.                                                      |
         | `user`                   | Object     | The object containing the user's details.                                                   |
         | `user.name`              | String     | The name of the user.                                                                       |
         | `user.email`             | String     | The email address of the user.                                                             |
         | `user.phone`             | String     | The phone number of the user.                                                              |
         | `user.updated_at`        | String     | The timestamp when the user's data was last updated.                                        |
         | `user.created_at`        | String     | The timestamp when the user's data was created.                                             |
         | `user.id`                | Integer    | The unique identifier of the user.                                                          |
         | `user.preferred_categories` | Array[Object] | An array of the user's preferred categories.                                                |
         | `user.preferred_authors` | Array[Object] | An array of the user's preferred authors.                                                   |
         | `user.preferred_sources` | Array[Object] | An array of the user's preferred news sources.                                              |
         | `pivot`                  | Object     | Contains the relationship details (e.g., `user_id`, `category_id`).                         |

         ##### Example Response Body

         ```json
         {
         "access_token": "1|IOHKOdt78y4CEIUvAzZbOWaELY0X9xelqVLqd7cDf93ab1ac",
         "token_type": "Bearer",
         "user": {
         "name": "admin",
         "email": "admin@admin.com",
         "phone": "9019003490",
         "updated_at": "2025-01-26T17:36:56.000000Z",
         "created_at": "2025-01-26T17:36:56.000000Z",
         "id": 2,
         "preferred_categories": [
            {
                "id": 1,
                "name": "general",
                "pivot": {
                    "user_id": 2,
                    "category_id": 1
                }
            }
         ],
         "preferred_authors": [
            {
                "id": 2,
                "name": "Olga Kharif",
                "pivot": {
                    "user_id": 2,
                    "author_id": 2
                }
            }
         ],
         "preferred_sources": [
            {
                "id": 3,
                "name": "Variety",
                "pivot": {
                    "user_id": 2,
                    "news_source_id": 3
                }
            }
         ]
         }
         }
         ```
     + ##### User Login

       ```
       POST api/login
       ```

       - ##### API Request Structure
         ###### Request Body

         | Key           | Type   | Description                                     |
         |---------------|--------|-------------------------------------------------|
         | `name`        | String | The name of the user.                           |
         | `email`       | String | The email address of the user.                  |
         | `password`    | String | The password of the user (usually hashed).      |

         ##### Example Request Body

         ```json
         {
         "name": "admin",
         "email": "admin@admin.com",
         "password": "12345678"
         }
         ```

     - ##### API Response Structure
       ##### Response Body

       | Key                      | Type   | Description                                         |
       |--------------------------|--------|-----------------------------------------------------|
       | `access_token`           | String | The access token used for authenticating API requests. |
       | `token_type`             | String | The type of token, typically "Bearer".             |
       | `user`                   | Object | Contains the details of the authenticated user.    |

       ##### User Object

       | Key                    | Type   | Description                                         |
       |------------------------|--------|-----------------------------------------------------|
       | `id`                   | Integer| The unique ID of the user.                         |
       | `name`                 | String | The name of the user.                              |
       | `email`                | String | The email address of the user.                     |
       | `email_verified_at`    | String | The timestamp of when the email was verified (nullable). |
       | `phone`                | String | The phone number of the user.                      |
       | `created_at`           | String | The timestamp of when the user was created.        |
       | `updated_at`           | String | The timestamp of when the user was last updated.   |
       | `preferred_categories` | Array  | List of the user's preferred categories.           |
       | `preferred_authors`    | Array  | List of the user's preferred authors.              |
       | `preferred_sources`    | Array  | List of the user's preferred sources.              |

       ##### Preferred Categories Object

       | Key           | Type   | Description                                           |
       |---------------|--------|-------------------------------------------------------|
       | `id`          | Integer| The ID of the category.                              |
       | `name`        | String | The name of the category.                            |
       | `pivot`       | Object | Contains additional information (user-category relationship). |

       ##### Preferred Authors Object
       | Key           | Type   | Description                                           |
       |---------------|--------|-------------------------------------------------------|
       | `id`          | Integer| The ID of the author.                                |
       | `name`        | String | The name of the author.                              |
       | `pivot`       | Object | Contains additional information (user-author relationship). |

       ##### Preferred Sources Object

       | Key           | Type   | Description                                           |
       |---------------|--------|-------------------------------------------------------|
       | `id`          | Integer| The ID of the news source.                            |
       | `name`        | String | The name of the news source.                          |
       | `pivot`       | Object | Contains additional information (user-source relationship). |

       ##### Example Response Body

       ```json
       {
       "access_token": "2|g3seHXAYmgsM2ZjZ9YBiiHmyibQefIRHJ88obxt647867984",
       "token_type": "Bearer",
       "user": {
        "id": 2,
        "name": "admin",
        "email": "admin@admin.com",
        "email_verified_at": null,
        "phone": "9019003490",
        "created_at": "2025-01-26T17:36:56.000000Z",
        "updated_at": "2025-01-26T17:36:56.000000Z",
        "preferred_categories": [
            {
                "id": 1,
                "name": "general",
                "pivot": {
                    "user_id": 2,
                    "category_id": 1
                }
            }
        ],
        "preferred_authors": [
            {
                "id": 2,
                "name": "Olga Kharif",
                "pivot": {
                    "user_id": 2,
                    "author_id": 2
                }
            }
        ],
        "preferred_sources": [
            {
                "id": 3,
                "name": "Variety",
                "pivot": {
                    "user_id": 2,
                    "news_source_id": 3
                }
            }
        ]
       }
       }
       ```
       + ##### Show Logged-in User Detials (middileware=>auth:sanctum)

       ```
       GET api/user
       ```

       - ##### API Request Header Structure

         ## Request Headers

         | Key            | Type   | Description                                          |
         |----------------|--------|------------------------------------------------------|
         | `Accept`       | String | Specifies the media type that the client is willing to accept. |
         | `Authorization`| String | The authentication token, typically in the form of a Bearer token. |

         ##### Example Request Header

         ```json
         [
         {
         "key": "Accept",
         "value": "application/json",
         "enabled": true,
         "type": "text",
         "uuid": "060f9041-9e75-42b2-9175-32ba43f9821a"
         },
         {
         "key": "Authorization",
         "value": "Bearer 2|cBJShtBwS8o90qiZkmpTy3Ll4N9RS8sbrMElgUPO23054f73",
         "enabled": true,
         "type": "text",
         "uuid": "06755e9a-b13c-4be8-b38a-b437238aeb3b"
         }
         ]
         ```
       - ##### API Response Structure

         | Key                  | Type     | Description                                       |
         |----------------------|----------|---------------------------------------------------|
         | `status`             | String   | Status of the API response. Example: "success".  |
         | `message`            | String   | Message describing the response.                 |
         | `user_id`            | Integer  | Unique identifier of the user.                   |
         | `user`               | Object   | Object containing detailed user information.     |

         ##### User Object

         | Key                   | Type      | Description                                         |
         |-----------------------|-----------|-----------------------------------------------------|
         | `id`                 | Integer   | Unique identifier of the user.                     |
         | `name`               | String    | Name of the user.                                  |
         | `email`              | String    | Email address of the user.                         |
         | `email_verified_at`  | String or null | Timestamp when the email was verified.           |
         | `phone`              | String    | Phone number of the user.                          |
         | `created_at`         | String    | Timestamp when the user was created.               |
         | `updated_at`         | String    | Timestamp when the user was last updated.          |
         | `preferred_categories`| Array    | List of preferred categories associated with the user. |
         | `preferred_authors`  | Array     | List of preferred authors associated with the user. |
         | `preferred_sources`  | Array     | List of preferred sources associated with the user. |

         ##### Preferred Categories Object

         | Key           | Type     | Description                                  |
         |---------------|----------|----------------------------------------------|
         | `id`          | Integer  | Unique identifier of the category.           |
         | `name`        | String   | Name of the category.                        |
         | `pivot`       | Object   | Object containing relationship details.      |

         #### Pivot Object (Categories)

         | Key          | Type     | Description                                  |
         |--------------|----------|----------------------------------------------|
         | `user_id`    | Integer  | User ID associated with the category.        |
         | `category_id`| Integer  | Category ID associated with the user.        |

         ### Preferred Authors Object

         | Key           | Type     | Description                                  |
         |---------------|----------|----------------------------------------------|
         | `id`          | Integer  | Unique identifier of the author.             |
         | `name`        | String   | Name of the author.                          |
         | `pivot`       | Object   | Object containing relationship details.      |

         #### Pivot Object (Authors)

         | Key          | Type     | Description                                  |
         |--------------|----------|----------------------------------------------|
         | `user_id`    | Integer  | User ID associated with the author.          |
         | `author_id`  | Integer  | Author ID associated with the user.          |

         ### Preferred Sources Object

         | Key           | Type     | Description                                  |
         |---------------|----------|----------------------------------------------|
         | `id`          | Integer  | Unique identifier of the source.             |
         | `name`        | String   | Name of the source.                          |
         | `pivot`       | Object   | Object containing relationship details.      |

         #### Pivot Object (Sources)

         | Key          | Type     | Description                                  |
         |--------------|----------|----------------------------------------------|
         | `user_id`    | Integer  | User ID associated with the source.          |
         | `news_source_id` | Integer  | Source ID associated with the user.      |
         ---

         ### Example API Response

         ```json
         {
         "status": "success",
         "message": "User Details",
         "user_id": 2,
         "user": {
         "id": 2,
         "name": "admin",
         "email": "admin@admin.com",
         "email_verified_at": null,
         "phone": "9019003490",
         "created_at": "2025-01-26T17:36:56.000000Z",
         "updated_at": "2025-01-26T17:36:56.000000Z",
         "preferred_categories": [
            {
                "id": 1,
                "name": "general",
                "pivot": {
                    "user_id": 2,
                    "category_id": 1
                }
            }
         ],
         "preferred_authors": [
            {
                "id": 2,
                "name": "Olga Kharif",
                "pivot": {
                    "user_id": 2,
                    "author_id": 2
                }
            }
         ],
         "preferred_sources": [
            {
                "id": 3,
                "name": "Variety",
                "pivot": {
                    "user_id": 2,
                    "news_source_id": 3
                }
            }
         ]
         }
         }
         ```
       + ##### Update Logged-in User Detials (middileware=>auth:sanctum)

         ```
         POST api/user/update
         ```
         - ##### Request Structure
           ##### Request Header

           | Key            | Value                                   | Enabled | Type   | Description                            |
           |----------------|-----------------------------------------|---------|--------|----------------------------------------|
           | `Content-Type` | `application/json`                     | true    | text   | Specifies the media type of the request. |
           | `Accept`       | `application/json`                     | true    | text   | Indicates that the client expects JSON responses. |
           | `Authorization`| `Bearer 3|vUJa6HYUzmFPaeCWZyi6n48YN4dwvcpVfZcvluxccc036ab0` | true | text | Bearer token for authorization.        |
           ##### Request Body

           | Key                    | Type       | Description                                         |
           |------------------------|------------|-----------------------------------------------------|
           | `name`                | String     | Full name of the user.                              |
           | `email`               | String     | Email address of the user.                         |
           | `password`            | String     | User's password.                                   |
           | `password_confirmation` | String   | Confirmation of the password.                      |
           | `phone`               | String     | Phone number of the user.                          |
           | `preferred_categories` | Array      | List of category IDs preferred by the user.         |
           | `preferred_authors`   | Array      | List of author IDs preferred by the user.          |
           | `preferred_sources`   | Array      | List of source IDs preferred by the user.          |

           ##### Example Request Body

           ```json
           {
           "name": "John Doe",
           "email": "john.doe@example.com",
           "password": "password123","password_confirmation": "password123",
           "phone": "1234567890",
           "preferred_categories": [2,3],
           "preferred_authors": [3,4],"preferred_sources" : [5,4]
           }
           ```
           
       + ##### Delete Logged-in User Detials (middileware=>auth:sanctum)

         ```
         DELETE api/user/delete
         ```
         - ##### Request Structure
           ##### Request Header

           | Key            | Value                                   | Enabled | Type   | Description                            |
           |----------------|-----------------------------------------|---------|--------|----------------------------------------|
           | `Accept`       | `application/json`                     | true    | text   | Indicates that the client expects JSON responses. |
           | `Content-Type` | `application/json`                     | true    | text   | Specifies the media type of the request. |
           | `Authorization`| `Bearer 2|Wsu4IRbFcFWKDK6BU4CtLz8fQ12ai7WqmfqcDMQQe314435e` | true | text | Bearer token for authorization.        |


       + ##### Get Articles of Logged-in User Preference (middileware=>auth:sanctum)

         ```
         GET /api/user-preferences
         ```
         
         - ##### Request Structure: Fetch Articles
           ## Request Header

           | Key            | Value                                   | Enabled | Type   | Description                            |
           |----------------|-----------------------------------------|---------|--------|----------------------------------------|
           | `Content-Type` | `application/json`                     | true    | text   | Specifies the media type of the request. |
           | `Accept`       | `application/json`                     | true    | text   | Indicates that the client expects JSON responses. |
           | `Authorization`| `Bearer 3|7lWJ92Pl0HTcwFOmRTGbGAvuyj00yaM3raz9DxCRebb49ac0` | true | text | Bearer token for authorization.        |

           ##### Request Params

           | Key        | Value     | Equals | Enabled | Description                            |
           |------------|-----------|--------|---------|----------------------------------------|
           | `category` | `Football`| true   | true    | The category of articles to fetch.     |
           | `source`   | `Sport`   | true   | true    | The source of the articles.            |
           | `author`   | `Anonymous`| true   | true    | The author of the articles.            |

        - ##### Response Structure: Fetch Articles
          #####Response Body

          #### 1. Article Object
          Each article object contains the following fields:

          | Field         | Type    | Description                              |
          |---------------|---------|------------------------------------------|
          | `id`          | Integer | The unique identifier of the article.    |
          | `title`       | String  | The title of the article.                |
          | `description` | String  | A short description of the article.      |
          | `url`         | String  | The URL to the article.                  |
          | `urlToImage`  | String  | The URL to the article's image (optional).|
          | `publishedAt` | String  | The date and time when the article was published in ISO 8601 format. |
          | `content`     | String  | The full content of the article.         |
          | `provider`    | String  | The provider of the article (e.g., The Guardian). |
          | `news_source_id` | Integer | The unique ID of the news source.        |
          | `categories`  | Array   | An array of categories related to the article. |
          | `authors`     | Array   | An array of authors of the article.      |
          | `source`      | Object  | An object containing the news source details. |

          ### 2. Categories Array

          Each category object contains the following fields:

          | Field          | Type    | Description                                  |
          |----------------|---------|----------------------------------------------|
          | `id`           | Integer | The unique identifier of the category.       |
          | `name`         | String  | The name of the category (e.g., Football).   |
          | `pivot`        | Object  | An object containing the relationship data (article_id, category_id). |

          ### 3. Authors Array

          Each author object contains the following fields:

          | Field         | Type    | Description                                      |
          |---------------|---------|--------------------------------------------------|
          | `id`          | Integer | The unique identifier of the author.            |
          | `name`        | String  | The name of the author (e.g., Anonymous).        |
          | `pivot`       | Object  | An object containing the relationship data (article_id, author_id). |

          ### 4. Source Object
          The source object contains the following fields:

          | Field         | Type    | Description                                |
          |---------------|---------|--------------------------------------------|
          | `id`          | Integer | The unique identifier of the news source.  |
          | `name`        | String  | The name of the news source (e.g., Sport). |

          ## Example Response

          ```json
          [
          {
          "id": 17,
          "title": "Aston Villa v West Ham United: Premier League – live",
          "description": "Aston Villa v West Ham United: Premier League – live",
          "url": "https://www.theguardian.com/football/live/2025/jan/26/aston-villa-v-west-ham-united-premer-league-live-score-updates",
          "urlToImage": "",
          "publishedAt": "2025-01-26T16:27:04.000000Z",
          "content": "Aston Villa v West Ham United: Premier League – live",
          "provider": "The Guardian",
          "news_source_id": 16,
          "categories": [
            {
                "id": 2,
                "name": "Football",
                "pivot": {
                    "article_id": 17,
                    "category_id": 2
                }
            }
          ],
          "authors": [
            {
                "id": 20,
                "name": "Anonymous",
                "pivot": {
                    "article_id": 17,
                    "author_id": 20
                }
            }
          ],
          "source": {
            "id": 16,
            "name": "Sport"
          }
          },
          {
          "id": 21,
          "title": "Schade and Mbeumo’s retaken penalty earn Brentford vital win against Palace",
          "description": "Schade and Mbeumo’s retaken penalty earn Brentford vital win against Palace",
          "url": "https://www.theguardian.com/football/2025/jan/26/crystal-palace-brentford-premier-league-match-report",
          "urlToImage": "",
          "publishedAt": "2025-01-26T16:11:09.000000Z",
          "content": "Schade and Mbeumo’s retaken penalty earn Brentford vital win against Palace",
          "provider": "The Guardian",
          "news_source_id": 16,
          "categories": [
            {
                "id": 2,
                "name": "Football",
                "pivot": {
                    "article_id": 21,
                    "category_id": 2
                }
            }
          ],
          "authors": [
          {
                "id": 20,
                "name": "Anonymous",
                "pivot": {
                    "article_id": 21,
                    "author_id": 20
                }
            }
          ],
          "source": {
            "id": 16,
            "name": "Sport"
          }
          }
          ]
          ```

       + ##### Log Out User (middileware=>auth:sanctum)

         ```
         POST /api/logout
         ```
         
         - ##### Request Header
           #####Request Header: Logout
         
           | Key           | Value                                                                                              | Description                             |
           |---------------|----------------------------------------------------------------------------------------------------|-----------------------------------------|
           | `Accept`      | `application/json`                                                                                 | Specifies the expected response format. |
           | `Authorization` | `Bearer 6|C9m7ECMDzHSwUzYuvzliNi5hxSTQFgWt2ayQ5H3ia524b59c`                                       | The Bearer token used for authentication. |

           ## Example Request Header

           ```json
           {
           "Accept": "application/json",
           "Authorization": "Bearer 6|C9m7ECMDzHSwUzYuvzliNi5hxSTQFgWt2ayQ5H3ia524b59c"
           }
           ```
       - ##### Response: Logout
       - ##### Response Body
       
         | Key     | Value        | Description                         |
         |---------|--------------|-------------------------------------|
         | `message` | `Logged out` | Indicates the success of the logout process. |

         ### Example Response Body

         ```json
         {
         "message": "Logged out"
         }
         ```
