# Cake Calendar

A Laravel application that shows a calendar for all employee cake days.

## Requirements

- Docker and Docker Compose
- Node.js and npm
- Git

## Setup

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd cake-calendar
   ```

2. **Install PHP dependencies**
   ```bash
    composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment setup**
   ```bash
   cp .env.example .env
   ./vendor/bin/sail artisan key:generate
   ```

5. **Start Docker containers**
   ```bash
   ./vendor/bin/sail up -d
   ```

6. **Run database migrations**
   ```bash
   ./vendor/bin/sail artisan migrate
   ```

7. **Build frontend assets**
   ```bash
   npm run dev
   ```

## Development

### Backend Commands
- Format code: `./vendor/bin/sail composer pint`
- Type checking: `./vendor/bin/sail composer phpstan`
- Run tests: `./vendor/bin/sail composer test`

### Frontend Commands
- Development server: `npm run dev`
- Format code: `npm run format`
- Lint code: `npm run lint`
- Build for production: `npm run build`

### Database
- Run migrations: `./vendor/bin/sail artisan migrate`
- Seed database: `./vendor/bin/sail artisan db:seed`
- Fresh migration with seeding: `./vendor/bin/sail artisan migrate:fresh --seed`

## Tech Stack

### Backend
- Laravel with MySQL and Inertia.js
- Laravel Sail for Docker development environment
- Laravel Pint for code formatting
- Larastan for static analysis

### Frontend
- Vue 3 with TypeScript
- Inertia.js for SPA functionality
- Tailwind CSS for styling
- PrimeVue for UI components

## Access

- Application: http://localhost
- Database: localhost:3306
- Mailpit: http://localhost:8025
