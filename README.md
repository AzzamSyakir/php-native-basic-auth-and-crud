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
   - Create a MySQL database with the name provided in your `.env` file (`MYSQL_DB`).
   - Import the provided SQL file (`database.sql`) into your MySQL database.
   - Ensure your MySQL credentials (`MYSQL_USER`, `MYSQL_PASSWORD`, `MYSQL_HOST`, `MYSQL_PORT`) are correctly set in the `.env` file.

3. **Set Up Email Configuration:**
   - Copy the `.env.example` file to `.env` if it's not already done:
     ```bash
     cp .env.example .env
     ```
   - Update the email configuration in the `.env` file:
     - `SENDER_EMAIL_ADDRESS`: Your sender email address.
     - `SENDER_EMAIL_PASSWORD`: Your email password or app-specific password if using services like Gmail.

4. **Run the Application:**
   - Deploy the project on a local server like XAMPP, MAMP, or any server that supports PHP.
   - Ensure the server is running on the host and port specified in the `.env` (`APP_HOST` and `APP_PORT`).
   - Access the application via your web browser at `http://APP_HOST:APP_PORT`.

5. **Testing the Application:**
   - Register a new user through the application.
   - Check the inbox of the registered email for the confirmation email.
   - Use the link in the confirmation email to verify the user's account.

6. **Troubleshooting:**
   - If emails are not being sent, verify that your email settings (`SENDER_EMAIL_ADDRESS`, `SENDER_EMAIL_PASSWORD`) are correct, and that your email provider supports SMTP access for the credentials provided.
   - Ensure your PHP installation includes the necessary extensions for database connections, like `mysqli`.

## License

This project is open-source and available for use under the [MIT License](LICENSE).
