# Iseki Parcom - AI-Assisted Part Comparison & Validation System

## Overview

**Iseki Parcom** is a specialized quality control and validation system designed for industrial part comparison. It leverages AI models to assist in the identification and validation of tractor parts against defined standards. The system integrates production planning validation with real-time record keeping to ensure accuracy in the assembly or inspection process.

The application serves both field operators (scanning/recording) and administrators (managing master data and validation rules).

## Key Features

### 1. Part Comparison & Recording
*   **Comparison Interface**: Streamlined view for operators to compare physical parts against digital records or AI-detected results.
*   **Rule Validation**: System-level validation of part sequences and rules against the **Podium** production planning system.
*   **Real-time Recording**: Capture and submit comparison results with immediate audit log creation.
*   **Data Export**: Capability to export comparison records to Excel for audit and review.

### 2. Admin Management Module
*   **AI Model Management**: Configure and manage AI detection models used in the comparison process.
*   **Tractor & Part Master Data**: Complete CRUD operations for tractor models and individual parts.
*   **Validation Rule Management**: Define complex rules for how parts should be compared and validated.
*   **Admin Dashboard**: High-level overview of system performance with capabilities for manual record approval and data resets.

### 3. User & Access Control
*   **Role-Based Access**: Dedicated workflows for operators and administrators.
*   **Secure Authentication**: Integrated sign-in and session management.

## Technology Stack

### Backend
*   **Framework**: [Laravel 12.x](https://laravel.com)
*   **Language**: PHP ^8.2
*   **Database**: SQLite (Local) / MySQL (Production/System)
*   **Excel Utilities**: `phpoffice/phpspreadsheet` ^5.0

### Frontend
*   **Build Tool**: [Vite](https://vitejs.dev)
*   **Styling**: [Tailwind CSS v4.0](https://tailwindcss.com)
*   **HTTP Client**: Axios

## Installation & Setup

1.  **Clone the Repository**
    ```bash
    git clone <repository-url>
    cd iseki_parcom
    ```

2.  **Install PHP Dependencies**
    ```bash
    composer install
    ```

3.  **Install Node Dependencies**
    ```bash
    npm install
    ```

4.  **Environment Configuration**
    *   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    *   Configure database and app keys in `.env`.

5.  **Initialize Application**
    ```bash
    php artisan key:generate
    php artisan migrate
    ```

6.  **Build Frontend Assets**
    ```bash
    npm run build
    ```

7.  **Run Development Server**
    ```bash
    php artisan serve
    ```

## Usage

1.  **Operator**: Log in to the dashboard to begin scanning and comparing parts.
2.  **Administrator**: Use the `/dashboard_admin` route to manage models, tractors, and view all system records.

## License

This project is proprietary.
