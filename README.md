# Simple PHP CRUD with Email Confirmation

This project is a basic PHP application that provides CRUD (Create, Read, Update, Delete) functionality for user data, along with an email confirmation feature after registration.

## Features

- **User CRUD:** Add, view, update, and delete users.
- **Email Confirmation:** Sends a confirmation email after user registration. Users need to verify their email address before they can log in.
- **Form Validation:** Basic input validation to ensure data integrity.
- **Basic Security:** Password hashing and session management for authentication.

## Technologies Used

- **PHP:** For the backend logic.
- **MySQL:** To store user data.
- **PHPMailer:** To handle email sending for confirmation.

## Setup Instructions

1. **Clone the Repository:**
   ```bash
   git clone [repository-url]
   cd [repository-folder]
   ```

2. **Configure the Database:**
   - Create a MySQL database and import the provided SQL file (`database.sql`).
   - Update the database connection settings in the configuration file (`config.php`).

3. **Set Up Email Configuration:**
   - Configure SMTP settings in the email configuration file (`email_config.php`).
   - Make sure to update with your SMTP credentials and adjust settings for your email provider.

4. **Run the Application:**
   - Deploy the project on a local server like XAMPP, MAMP, or a live server.
   - Access the application via your web browser.

5. **Testing the Application:**
   - Register a new user and check for the confirmation email.
   - Use the confirmation link in the email to verify the account.

## Troubleshooting

- If emails are not being sent, check your SMTP configuration and make sure your server allows outgoing emails.
- Verify that all required PHP extensions (like `mysqli` and `openssl`) are enabled on your server.

## License

This project is open-source and available for use under the [MIT License](LICENSE).
