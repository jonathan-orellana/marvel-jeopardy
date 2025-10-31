-- users
CREATE TABLE IF NOT EXISTS user (
  id SERIAL PRIMARY KEY,             -- user ID (unique)
  first_name TEXT NOT NULL,          -- first name
  last_name TEXT NOT NULL,           -- last name
  email TEXT UNIQUE NOT NULL,        -- email (unique)
  password_hash TEXT NOT NULL,       -- hashed password
);

