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
  set_id INT REFERENCES question_set(id) ON DELETE CASCADE,  -- link to a question set
  type TEXT NOT NULL,              -- "Multiple Choice" | "True or False" | "Response"
  prompt TEXT NOT NULL,
  options JSONB,                   -- ["A","B","C","D"] (null for TF/Response)
  correct_index INT,               -- 0..3 for MC, null otherwise
  correct_bool BOOLEAN,            -- true/false for TF, null otherwise
  correct_text TEXT,               -- expected text for Response, null otherwise
  created_at TIMESTAMP DEFAULT NOW()
);


-- question set
CREATE TABLE IF NOT EXISTS question_set (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES app_user(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW()
);





