DROP TABLE IF EXISTS response_answer CASCADE;
DROP TABLE IF EXISTS true_false_answer CASCADE;
DROP TABLE IF EXISTS multiple_choice_answer CASCADE;
DROP TABLE IF EXISTS multiple_choice_option CASCADE;
DROP TABLE IF EXISTS question CASCADE;
DROP TABLE IF EXISTS question_set CASCADE;
DROP TABLE IF EXISTS app_user CASCADE;

CREATE TABLE IF NOT EXISTS app_user (
  id SERIAL PRIMARY KEY,             
  first_name TEXT NOT NULL,          
  last_name TEXT NOT NULL,          
  email TEXT UNIQUE NOT NULL,       
  password_hash TEXT NOT NULL 
);

CREATE TABLE IF NOT EXISTS question_set (
  id SERIAL PRIMARY KEY,
  user_id INT REFERENCES app_user(id) ON DELETE CASCADE,
  title TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT NOW()
);

CREATE TABLE question (
    id SERIAL PRIMARY KEY,
    question_set_id INTEGER NOT NULL REFERENCES question_set(id) ON DELETE CASCADE,
    user_id INTEGER NOT NULL REFERENCES app_user(id) ON DELETE CASCADE,
    category VARCHAR(20) NOT NULL,          
    points INTEGER NOT NULL,             
    question_type VARCHAR(20) NOT NULL,
    text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT NOW()
);


CREATE TABLE multiple_choice_option (
    id SERIAL PRIMARY KEY,
    question_id INTEGER NOT NULL REFERENCES question(id) ON DELETE CASCADE,
    option_index INTEGER NOT NULL,
    option_text TEXT NOT NULL
);

CREATE TABLE multiple_choice_answer (
    id SERIAL PRIMARY KEY,
    question_id INTEGER NOT NULL UNIQUE REFERENCES question(id) ON DELETE CASCADE,
    correct_index INTEGER NOT NULL
);

CREATE TABLE true_false_answer (
    id SERIAL PRIMARY KEY,
    question_id INTEGER NOT NULL UNIQUE REFERENCES question(id) ON DELETE CASCADE,
    is_true BOOLEAN NOT NULL
);

CREATE TABLE response_answer (
    id SERIAL PRIMARY KEY,
    question_id INTEGER NOT NULL UNIQUE REFERENCES question(id) ON DELETE CASCADE,
    answer_text TEXT NOT NULL
);