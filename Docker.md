# Setting Up Docker Environment 

1. ### Clone the repository
   ```
   https://github.com/darshan45672/innoscripta.git
   ```
2. ### Copy the environment variables
   ```
   cp .env.example .env
   ```
3. ### Update the required API keys in .env
   ```
   NEWS_API_KEY_URL=https://newsapi.org/v2/top-headlines?country=us&apiKey=<your_api_key>
   GAURDIAN_API_KEY_URL=https://content.guardianapis.com/search?api-key=<your_api_key>
   NYT_URL=https://rss.nytimes.com/services/xml/rss/nyt/HomePage.xml
   ```
4. ### Bring up the container
   ```
   docker-compose up --build
   ```
5. ### it expose the port from http://0.0.0.0:8000 to http://127.0.0.1:8000
   ```
   http://127.0.0.1:8000
   ```
6. ### Docker will bring up 3 containers
   - backend
   - scheduler
   - queue
7. ### open your Postman or api tool and test the apis
   ```
   http://127.0.0.1:8000/api/..
   ``` 
