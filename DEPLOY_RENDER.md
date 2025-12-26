Render deployment guide for this project

Overview
- This project uses PHP (PDO/MySQL) and MongoDB (MongoDB PHP driver). Render doesn't host MongoDB for you, so you'll use an external MongoDB provider (recommended: MongoDB Atlas) or host MongoDB elsewhere.
- We'll deploy the app as a Docker Web Service on Render. The repository includes a Dockerfile that installs the PHP mongodb extension.

Steps

1) Put the project in a Git repository (GitHub/GitLab)
   - If not already pushed, create a new repo and push the code.

2) Prepare environment variables
   - The app reads DB settings from environment variables via `php/config.php`.
   - Required variables to set in Render (or Render Dashboard -> Environment):
     - DB_HOST (e.g. host from your managed MySQL)
     - DB_NAME (e.g. login_demo)
     - DB_USER
     - DB_PASS
     - MONGO_URI (e.g. mongodb+srv://user:pass@cluster0.example.mongodb.net)
     - MONGO_DB (e.g. login_demo)

   - If you use Render Managed Databases for MySQL, use the connection details Render provides.
   - For MongoDB, sign up for MongoDB Atlas, create a free cluster, create a database user, and copy the connection string.

3) Configure Render service
   - Go to https://dashboard.render.com
   - Click New -> Web Service
   - Connect your GitHub/GitLab repo and select the repository/branch
   - For Environment choose "Docker"
   - Render will build using the `Dockerfile` in the repository.
   - Set the Environment variables listed above in the Render Service Settings -> Environment
   - Start the service. Render will run the Docker build and deploy.

4) Database setup
   - MySQL: Create the `users` table in the configured MySQL instance (Render managed DB or external). Example SQL (run once):

```sql
CREATE TABLE users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  fullname VARCHAR(255) NOT NULL,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(255) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

   - MongoDB: The application writes `profiles` documents automatically when registering a user. No manual creation is required.

5) TLS and custom domain
   - Render provides automatic TLS for its managed domain. To add a custom domain, use Render dashboard -> Domains and follow DNS instructions.

6) Troubleshooting
   - If you get connection errors: check that the DB host is reachable from Render, and credentials are correct.
   - Use Render's live logs to inspect application output and Docker build logs.
   - If PHP complains about missing mongodb extension, ensure the Dockerfile built successfully (pecl install mongodb) and that logs show "OK" for enabling the extension.

Notes and security
- Store credentials in Render's Environment settings (they are masked and secure). Do not commit secrets to the repo.
- Consider enabling MongoDB authentication and IP access controls (Atlas) so only authorized services can connect.
- For production, enable HTTPS and set up proper session storage and server-side authentication rather than client-side localStorage.

Example environment variable values (placeholders)
- DB_HOST=render-mysql-host.internal
- DB_NAME=login_demo
- DB_USER=renderuser
- DB_PASS=supersecret
- MONGO_URI=mongodb+srv://user:pass@cluster0.xy.mongodb.net
- MONGO_DB=login_demo

That's it — once deployed you should be able to visit the Render service URL and use the app. If you want, I can:
- Add a small healthcheck endpoint (php/health.php) that checks both MySQL and Mongo connectivity for Render's health checks.
- Create a `render.yaml` tailored with your GitHub repo (update `repo:`).