-- users table
CREATE TABLE IF NOT EXISTS app_user (
  id SERIAL PRIMARY KEY,             -- user ID (unique)
  first_name TEXT NOT NULL,          -- first name
  last_name TEXT NOT NULL,           -- last name
  email TEXT UNIQUE NOT NULL,        -- email (unique)
  password_hash TEXT NOT NULL,       -- hashed password
);

-- question table
CREATE TABLE IF NOT EXISTS question (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES app_user(id) ON DELETE CASCADE,
  set_id INT REFERENCES question_set(id) ON DELETE CASCADE,
  type TEXT NOT NULL,              -- question type
  prompt TEXT NOT NULL,
  options JSONB,                   -- mc options
  correct_index INT,               -- mc correct
  correct_bool BOOLEAN,            -- true/false
  correct_text TEXT,               -- response
  created_at TIMESTAMP DEFAULT NOW()
);

-- question set
CREATE TABLE IF NOT EXISTS question_set (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES app_user(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW()
);





