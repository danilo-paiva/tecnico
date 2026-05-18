# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Common Commands

### Dependency Management
- Install dependencies: `composer install`
- Update dependencies: `composer update`

### Running the Application
- The application is a Slim 4 PHP API. It is typically served from the `public/` directory.
- Base URL: `http://localhost:8080` (as per `API.md`)

### Testing
- No automated test suite (e.g., PHPUnit) was found in the current codebase. Manual testing is performed via cURL or Postman as documented in `API.md`.

## Architecture & Structure

The project follows a layered architecture for a REST API focused on HR Management (Gestão de RH).

### Layer Responsibilities
- **Controllers (`src/api/controllers`)**: Handle incoming HTTP requests, call services, and return JSON responses.
- **Services (`src/api/services`)**: Contain business logic and orchestrate data flow between controllers and DAOs.
- **DAOs (`src/api/dao`)**: Data Access Objects responsible for executing SQL queries and interacting with the database.
- **Models (`src/api/models`)**: Plain Old PHP Objects (POPOs) representing the domain entities (e.g., `Funcionario`, `Cargo`).
- **Routes (`src/api/routes`)**: Define the API endpoints and map them to controller methods.
- **Middlewares (`src/api/middlewares`)**: Handle request validation (e.g., validating request bodies and IDs).
- **Database (`src/api/database`)**: Manages the MySQL database connection.

### Key Components
- **Framework**: Slim 4
- **DI Container**: PHP-DI
- **Authentication**: JSON Web Tokens (JWT) via `firebase/php-jwt`
- **Database**: MySQL
