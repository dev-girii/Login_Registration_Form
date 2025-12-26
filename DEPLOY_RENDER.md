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


   Using MySQL on Render (user-managed via Docker)

   Render provides fully-managed Postgres, but for MySQL you'll run a MySQL server as a private Docker service in Render (user-managed). This is supported and commonly done — you'll be responsible for backups and maintenance. Steps:

   - Create a new private service in Render using the official MySQL Docker image:
      1. In Render dashboard -> New -> Private Service -> Docker
      2. Set the Docker image to `mysql:8.0` (or your preferred MySQL image and tag).
      3. Configure Environment Variables for the MySQL container (for example):
          - MYSQL_ROOT_PASSWORD (required by the official MySQL image)
          - MYSQL_DATABASE (e.g. login_demo)
          - MYSQL_USER (optional)
          - MYSQL_PASSWORD (if MYSQL_USER set)
      4. Under the service Settings -> Network, make sure this private service is in the same Render team and has a private network so your Web Service can reach it.

   - In your Web Service (the PHP app):
      - Set environment variables to point to the MySQL private service. Render exposes private services with internal hostnames; check the service details for the host name (usually format like `private-service-name.internal`). Example env vars:
         - DB_HOST=your-mysql-private-host
         - DB_NAME=login_demo
         - DB_USER=MYSQL_USER (or root)
         - DB_PASS=MYSQL_PASSWORD (or MYSQL_ROOT_PASSWORD)

   - Initialize the schema:
      - Connect to the MySQL container via `mysql` client or Render's dashboard console and run the SQL in `php/config.php` comments or the example SQL in this doc:

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

   - Connect the services:
      - Ensure your Web Service and MySQL private service are in the same Render team and that your Web Service's environment `DB_HOST` points to the private service hostname. Deploy your Web Service after setting the env vars.

   Notes & recommendations
   - Backups: since this MySQL is user-managed on Render, configure backups yourself (e.g., use a backup container or scheduled jobs to dump and store the SQL elsewhere).
   - Security: restrict network access to the private service and rotate credentials when needed.
   - Alternative: if you prefer a fully-managed DB, consider a managed MySQL provider (PlanetScale, Google Cloud SQL, Amazon RDS) and connect via their connection details; you'll still set `DB_HOST`, `DB_USER`, etc. in Render.

   This repo's Dockerfile includes the PHP MongoDB driver and MySQL PDO support (PDO is built into PHP image). You don't need Postgres support anymore. Configure env vars as described and deploy.


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