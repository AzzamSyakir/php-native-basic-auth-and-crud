CREATE TABLE IF NOT EXISTS users
(
    id CHAR(36) NOT NULL PRIMARY KEY,
    username VARCHAR(255) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    token VARCHAR(255) NOT NULL,
    confirmed BOOLEAN NOT NULL
);
CREATE TABLE IF NOT EXISTS sessions
(
    id CHAR(36) NOT NULL PRIMARY KEY,
    user_id CHAR(36) NOT NULL,
    access_token VARCHAR(255) NOT NULL UNIQUE,
    refresh_token VARCHAR(255) NOT NULL UNIQUE,
    access_token_expired_at TIMESTAMP NOT NULL,
    refresh_token_expired_at TIMESTAMP NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE IF NOT EXISTS tasks
(
    id CHAR(36) NOT NULL PRIMARY KEY,
    title VARCHAR(255) NOT NULL UNIQUE,
    completed BOOLEAN NOT NULL
);