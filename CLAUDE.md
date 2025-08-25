Read laravel-php-guidelines.md

# Overview
This is a laravel application that shows a calendar for all employee cake days.

# Tech stack

## Backend
- The backend is a laravel application using mysql and inertia. Read composer.lock for full version details
- This uses laravel sail so any php commands MUST be run inside the sail container using ./vendor/bin/sail

### Formatting and type checks
- Laravel Pint is used for formatting. YOU MUST run ./vendor/bin/sail pint after every code change you complete
- Larastan is used for type checks. YOU MUST run ./vendor/bin/sail compoer phpstan after every code change and fix stan errors

## Frontend
- The front end is vue3 typescript with inertia js, styled via tailwindcss and use primevue for components. Read package-lock.json for for version details
- All code must use typescript

### Formatting
- after each code change run npm run format 
- after each code change run npm run lint
