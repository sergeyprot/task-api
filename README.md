# Task API (Simple CRUD)

Simple REST API for managing tasks using PHP and SQLite.

## Requirements

*   PHP 7.4+
*   SQLite extension enabled

## Installation

1.  Clone the repository:
    ```bash
    git clone <your_repository_url>
    cd task-api
    ```
2.  Start the built-in PHP web server:
    ```bash
    php -S localhost:8000 -t .
    ```
3.  The SQLite database file `database/tasks.db` will be created automatically.

## API Endpoints

*   **Create Task:** `POST /tasks` (fields: `title`, `description`, `status`)
*   **List Tasks:** `GET /tasks`
*   **Get Task:** `GET /tasks/{id}`
*   **Update Task:** `PUT /tasks/{id}` (fields: `title`, `description`, `status`)
*   **Delete Task:** `DELETE /tasks/{id}`

## Example Usage

Create a task using `curl`:
