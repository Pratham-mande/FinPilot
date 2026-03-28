-- FinPilot AI Database Schema
-- Run this file to set up the database

CREATE DATABASE IF NOT EXISTS finpilot CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE finpilot;

-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(100)  NOT NULL,
    email       VARCHAR(150)  NOT NULL UNIQUE,
    password    VARCHAR(255)  NOT NULL,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Financial Profile Table
CREATE TABLE IF NOT EXISTS financial_data (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT           NOT NULL,
    age         INT           NOT NULL DEFAULT 0,
    income      DECIMAL(12,2) NOT NULL DEFAULT 0,
    expenses    DECIMAL(12,2) NOT NULL DEFAULT 0,
    savings     DECIMAL(12,2) NOT NULL DEFAULT 0,
    debt        DECIMAL(12,2) NOT NULL DEFAULT 0,
    risk        VARCHAR(20)   NOT NULL DEFAULT 'Medium',
    goals       TEXT,
    created_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP     DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Behavioral Questions Table
CREATE TABLE IF NOT EXISTS behavioral_responses (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT         NOT NULL,
    q1          VARCHAR(20) DEFAULT 'Not Sure',
    q2          VARCHAR(20) DEFAULT 'Not Sure',
    q3          VARCHAR(20) DEFAULT 'Not Sure',
    q4          VARCHAR(20) DEFAULT 'Not Sure',
    q5          VARCHAR(20) DEFAULT 'Not Sure',
    created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- AI Analysis Results Table
CREATE TABLE IF NOT EXISTS ai_results (
    id              INT AUTO_INCREMENT PRIMARY KEY,
    user_id         INT         NOT NULL,
    score           INT         NOT NULL DEFAULT 0,
    score_label     VARCHAR(30) DEFAULT '',
    insights        JSON,
    personality     VARCHAR(200) DEFAULT '',
    personality_icon VARCHAR(10) DEFAULT '🧠',
    personality_desc TEXT,
    plans           JSON,
    stats           JSON,
    selected_plan   VARCHAR(50) DEFAULT NULL,
    raw_response    TEXT,
    created_at      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP   DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Chat History Table
CREATE TABLE IF NOT EXISTS chat_history (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    user_id     INT         NOT NULL,
    role        ENUM('user', 'assistant') NOT NULL,
    message     TEXT        NOT NULL,
    created_at  TIMESTAMP   DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Indexes for performance
CREATE INDEX idx_financial_user ON financial_data(user_id);
CREATE INDEX idx_responses_user ON behavioral_responses(user_id);
CREATE INDEX idx_results_user   ON ai_results(user_id);
CREATE INDEX idx_chat_user      ON chat_history(user_id);
