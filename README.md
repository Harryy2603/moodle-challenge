# Moodle User Import Application

A robust, full-stack application for parsing, validating, and importing user data from CSV files into a PostgreSQL database. Built as a technical submission for the Moodle Platform Coding Challenge.

## Application Overview
This application provides both a Command-Line Interface (CLI) and a modern React Web UI to import user records. Both interfaces utilize a strictly decoupled, shared PHP domain layer to ensure validation, normalization, and duplicate detection behave identically regardless of how the data is imported.

## Technology Stack
*   **Backend:** PHP 8.3 (Native, no heavy frameworks)
*   **Database:** PostgreSQL 16
*   **Frontend:** React (scaffolded via Vite)
*   **Testing:** PHPUnit 11
*   **Environment:** GitHub Codespaces / Docker (via `.devcontainer`)

## Architecture & Design Decisions
*   **Shared Business Logic:** The core pipeline (`CsvParser` -> `Normalizer` -> `Validator` -> `ImportService`) is abstracted. The CLI script and Web API simply act as transport layers that pass input to this shared pipeline.
*   **Memory Efficiency:** The `CsvParser` utilizes PHP Generators (`yield`) to process CSV files row-by-row, ensuring the application can handle massive datasets without exhausting server memory.
*   **Strict Dry-Run:** The `--dry-run` flag operates on the exact same pipeline as a real import but bypasses the Repository `insert` method, guaranteeing absolute database isolation.
*   **Graceful Degradation:** A unique constraint violation at the PostgreSQL schema level is caught via PDO Exceptions and surfaced as a standard validation error, preventing application crashes.

---

## Installation & Setup

**The easiest way to review this project is via GitHub Codespaces.** 
The repository includes a complete `.devcontainer` configuration that will automatically provision a PHP 8.3 environment and a linked PostgreSQL database.

1. Open the repository in GitHub and click **Code -> Open with Codespaces**.
2. Once the Codespace loads, open the terminal and run the following commands to install the required PostgreSQL driver and dependencies:

```bash
sudo rm -f /etc/apt/sources.list.d/yarn.list
sudo apt-get update && sudo apt-get install -y postgresql-client libpq-dev
sudo docker-php-ext-install pdo pdo_pgsql
sudo mkdir -p /usr/local/etc/php/conf.d
echo "extension=pdo_pgsql.so" | sudo tee /usr/local/etc/php/conf.d/docker-php-ext-pdo_pgsql.ini

#install application dependencies
composer install
cd frontend && npm install && cd ..

Database Configuration
Database credentials are managed via environment variables.
Copy the example environment file:
--->  cp .env.example .env
(Note: If using the provided Codespaces environment, the default credentials in .env.example will connect perfectly to the bundled PostgreSQL container).

Initialize the database schema:
---> php user_upload.php --create-table
Usage: Command Line Interface (CLI)
The CLI provides a fast, terminal-based way to import users.

Available Options:

--file <filename> : CSV file to process

--dry-run : Parse and validate without inserting into the database

--create-table : Create/rebuild the users table

--help : Display available options

CLI Examples:
View the help menu:
---> php user_upload.php --help
Preview validation results without altering the database:
---> php user_upload.php --file users.csv --dry-run
Execute a real database import:
---> php user_upload.php --file users.csv
Usage: Web UI
The Web UI provides a clean, 3-step visualization of the import flow. To run it, you must start both the backend API and the frontend development server.

1. Start the PHP Backend API:
Open a terminal and run:
---> php -S 0.0.0.0:8000 -t public
2. Start the React Frontend:
Open a second terminal and run:
---> cd frontend
    npm run dev
3. Use the Application:
Navigate to the URL provided by Vite (e.g., http://localhost:5173).
Drag and drop your users.csv file into the upload zone.

Click Preview Data to validate the records and see a visual breakdown of errors.
Click Import to safely persist the valid records to PostgreSQL.

Testing
The application includes a PHPUnit test suite covering the normalization and validation domain logic.

To run the tests:
---> ./vendor/bin/phpunit
