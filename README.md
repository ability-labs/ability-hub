# AbilityHub

AbilityHub is an open-source application designed to streamline therapy management in habilitation centers for CareReceivers. It provides tools for scheduling therapy sessions, managing therapists, and in the future, collecting and sharing therapy data with families.

## Features
- **Therapy Scheduling**: Organize and manage therapy sessions for enrolled CareReceivers.
- **Operator Management**: Assign and track therapists and specialists.
- **(Future) Data Collection & Sharing**: Tools for therapists to document session progress and share insights with families.

## Tech Stack
- **Backend**: Laravel
- **Admin Panel**: FilamentPHP
- **Database**: MySQL/PostgreSQL (TBD)
- **Authentication**: Laravel Sanctum (TBD)

## Installation
1. Clone the repository:
   ```sh
   git clone https://github.com/thrive-labs/ability-hub.git
   ```
2. Navigate to the project folder:
   ```sh
   cd ability-hub
   ```
3. Install dependencies:
   ```sh
   composer install
   ```
4. Set up the environment:
   ```sh
   cp .env.example .env
   php artisan key:generate
   ```
5. Configure database in `.env` file, then run migrations:
   ```sh
   php artisan migrate
   ```
6. Start the development server:
   ```sh
   php artisan serve
   ```

## Contributing
AbilityHub is an open-source project, and contributions are welcome! Please open an issue or submit a pull request to propose changes.

## License
MIT License. See `LICENSE` file for details.
