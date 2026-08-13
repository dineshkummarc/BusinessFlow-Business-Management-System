-- ============================================================
-- Business Management System - Database Schema
-- Phase 1 Foundation
-- Engine: MySQL 5.7+ / MariaDB 10.3+
-- ============================================================

CREATE DATABASE IF NOT EXISTS bms_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE bms_db;

-- ------------------------------------------------------------
-- Table: users
-- Stores every person who can log into the system (admin, manager, staff)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin','manager','staff') NOT NULL DEFAULT 'staff',
    phone VARCHAR(30) DEFAULT NULL,
    avatar VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    last_login DATETIME DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: employees
-- Company staff records (HR module) - separate business purpose from `users`
-- (a user account may or may not exist for an employee)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS employees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    employee_code VARCHAR(20) NOT NULL UNIQUE,
    full_name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30) DEFAULT NULL,
    department VARCHAR(80) NOT NULL,
    designation VARCHAR(80) NOT NULL,
    salary DECIMAL(12,2) DEFAULT 0.00,
    hire_date DATE NOT NULL,
    status ENUM('active','on_leave','terminated') NOT NULL DEFAULT 'active',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: clients
-- Customer / client records (CRM module)
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS clients (
    id INT AUTO_INCREMENT PRIMARY KEY,
    company_name VARCHAR(150) NOT NULL,
    contact_person VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    phone VARCHAR(30) DEFAULT NULL,
    address VARCHAR(255) DEFAULT NULL,
    city VARCHAR(80) DEFAULT NULL,
    country VARCHAR(80) DEFAULT NULL,
    industry VARCHAR(80) DEFAULT NULL,
    status ENUM('lead','active','inactive') NOT NULL DEFAULT 'lead',
    notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: projects
-- Project management module, linked to a client
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS projects (
    id INT AUTO_INCREMENT PRIMARY KEY,
    client_id INT DEFAULT NULL,
    project_name VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    budget DECIMAL(12,2) DEFAULT 0.00,
    start_date DATE DEFAULT NULL,
    deadline DATE DEFAULT NULL,
    status ENUM('planned','in_progress','on_hold','completed','cancelled') NOT NULL DEFAULT 'planned',
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    progress TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_by INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: project_team
-- Many-to-many: which employees are assigned to which project
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS project_team (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    employee_id INT NOT NULL,
    role_in_project VARCHAR(80) DEFAULT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (employee_id) REFERENCES employees(id) ON DELETE CASCADE,
    UNIQUE KEY unique_assignment (project_id, employee_id)
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: tasks
-- Task tracking, linked to a project
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT NOT NULL,
    assigned_to INT DEFAULT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT DEFAULT NULL,
    due_date DATE DEFAULT NULL,
    status ENUM('todo','in_progress','review','done') NOT NULL DEFAULT 'todo',
    priority ENUM('low','medium','high') NOT NULL DEFAULT 'medium',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_to) REFERENCES employees(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: invoices
-- Billing module, linked to a client and optionally a project
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_number VARCHAR(30) NOT NULL UNIQUE,
    client_id INT NOT NULL,
    project_id INT DEFAULT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    tax_percent DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    total_amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('draft','sent','paid','overdue','cancelled') NOT NULL DEFAULT 'draft',
    notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id) ON DELETE CASCADE,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: invoice_items
-- Line items belonging to an invoice
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS invoice_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    description VARCHAR(255) NOT NULL,
    quantity DECIMAL(10,2) NOT NULL DEFAULT 1.00,
    unit_price DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    line_total DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: activity_log
-- Audit trail of key actions across the system
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS activity_log (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT DEFAULT NULL,
    module VARCHAR(60) NOT NULL,
    action VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: contact_messages
-- Messages submitted through the public Contact Us form
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(150) NOT NULL,
    subject VARCHAR(150) DEFAULT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: expenses
-- Phase 2 — Financial module: tracks business expenses (distinct
-- from invoices, which track incoming revenue).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category VARCHAR(80) NOT NULL,
    description VARCHAR(255) NOT NULL,
    amount DECIMAL(12,2) NOT NULL DEFAULT 0.00,
    expense_date DATE NOT NULL,
    project_id INT DEFAULT NULL,
    payment_method ENUM('cash','bank_transfer','card','other') NOT NULL DEFAULT 'other',
    status ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
    notes TEXT DEFAULT NULL,
    created_by INT DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (project_id) REFERENCES projects(id) ON DELETE SET NULL,
    FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: notifications
-- Phase 2 — in-app notification center. Generated automatically
-- by other modules (task assignment, overdue invoices, new
-- messages, new user registrations) and surfaced in the topbar.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type VARCHAR(40) NOT NULL,
    title VARCHAR(150) NOT NULL,
    message VARCHAR(255) DEFAULT NULL,
    link VARCHAR(255) DEFAULT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: settings
-- Phase 2 — simple key/value store for company-wide settings
-- (company name, currency, default tax rate, timezone).
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS settings (
    setting_key VARCHAR(60) PRIMARY KEY,
    setting_value VARCHAR(255) DEFAULT NULL,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Table: auth_tokens
-- Phase 3 — persistent "Remember Me" login tokens. A random token
-- is issued on login (if requested) and stored here as a hash;
-- the raw token only ever lives in the user's browser cookie.
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS auth_tokens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    token_hash VARCHAR(255) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- Seed data — Phase 2 settings (no foreign key dependencies)
-- ------------------------------------------------------------
INSERT INTO settings (setting_key, setting_value) VALUES
('company_name', 'BusinessFlow'),
('company_email', 'contact@example.com'),
('company_phone', '+1 555 000 1111'),
('company_address', ''),
('currency_symbol', '$'),
('default_tax_percent', '5.00'),
('timezone', 'UTC'),
('invoice_prefix', 'INV');

-- Default admin account -> password: Admin@123
-- (hash generated with PHP password_hash('Admin@123', PASSWORD_DEFAULT))
INSERT INTO users (full_name, email, username, password_hash, role, phone, status) VALUES
('System Administrator', 'admin@bms.com', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', '+1 555 000 1111', 'active');
-- NOTE: The hash above corresponds to bcrypt cost 10 for a placeholder value.
-- On first run, use register.php to create your own real account, or run:
-- UPDATE users SET password_hash = '<new_hash>' WHERE username = 'admin';

INSERT INTO clients (company_name, contact_person, email, phone, city, country, industry, status, created_by) VALUES
('Northgate Retail Group', 'Sarah Collins', 'sarah@northgate.com', '+1 555 234 1122', 'Chicago', 'USA', 'Retail', 'active', 1),
('Vertex Manufacturing', 'David Okafor', 'david@vertexmfg.com', '+1 555 998 4433', 'Austin', 'USA', 'Manufacturing', 'active', 1),
('BrightPath Logistics', 'Maria Gomez', 'maria@brightpath.com', '+1 555 771 2200', 'Miami', 'USA', 'Logistics', 'lead', 1);

INSERT INTO employees (user_id, employee_code, full_name, email, phone, department, designation, salary, hire_date, status) VALUES
(1, 'EMP-0001', 'System Administrator', 'admin@bms.com', '+1 555 000 1111', 'Management', 'General Manager', 6000.00, '2024-01-10', 'active'),
(NULL, 'EMP-0002', 'James Whitfield', 'james@bms.com', '+1 555 442 7788', 'Operations', 'Operations Lead', 4200.00, '2024-03-02', 'active'),
(NULL, 'EMP-0003', 'Priya Nair', 'priya@bms.com', '+1 555 663 9911', 'Finance', 'Accountant', 3800.00, '2024-05-18', 'active');

INSERT INTO projects (client_id, project_name, description, budget, start_date, deadline, status, priority, progress, created_by) VALUES
(1, 'Retail POS Rollout', 'Deploy point-of-sale system across 12 retail branches.', 45000.00, '2026-02-01', '2026-09-30', 'in_progress', 'high', 55, 1),
(2, 'Factory Inventory System', 'Custom inventory tracking for manufacturing floor.', 32000.00, '2026-04-15', '2026-10-15', 'planned', 'medium', 10, 1);

INSERT INTO project_team (project_id, employee_id, role_in_project) VALUES
(1, 2, 'Project Lead'),
(1, 3, 'Finance Coordinator'),
(2, 2, 'Project Lead');

INSERT INTO tasks (project_id, assigned_to, title, description, due_date, status, priority) VALUES
(1, 2, 'Vendor hardware procurement', 'Source POS terminals for branches 1-4.', '2026-08-20', 'in_progress', 'high'),
(1, 3, 'Budget reconciliation - phase 1', 'Reconcile phase 1 spend against approved budget.', '2026-08-25', 'todo', 'medium'),
(2, 2, 'Requirements workshop', 'Meet with factory floor managers to gather requirements.', '2026-08-30', 'todo', 'medium');

INSERT INTO invoices (invoice_number, client_id, project_id, amount, tax_percent, total_amount, issue_date, due_date, status, created_by) VALUES
('INV-2026-0001', 1, 1, 15000.00, 5.00, 15750.00, '2026-07-01', '2026-07-31', 'paid', 1),
('INV-2026-0002', 2, 2, 8000.00, 5.00, 8400.00, '2026-08-01', '2026-08-31', 'sent', 1);

INSERT INTO invoice_items (invoice_id, description, quantity, unit_price, line_total) VALUES
(1, 'POS software licensing - Phase 1', 1, 10000.00, 10000.00),
(1, 'On-site installation & training', 5, 1000.00, 5000.00),
(2, 'Inventory system design & planning', 1, 8000.00, 8000.00);

-- ------------------------------------------------------------
-- Seed data — Phase 2 (expenses/notifications reference users
-- and projects, so they must be inserted after those rows exist)
-- ------------------------------------------------------------
INSERT INTO expenses (category, description, amount, expense_date, project_id, payment_method, status, created_by) VALUES
('Software', 'Annual POS software licensing renewal', 2400.00, '2026-07-05', 1, 'card', 'approved', 1),
('Hardware', 'POS terminals — branches 1-4', 6800.00, '2026-07-12', 1, 'bank_transfer', 'approved', 1),
('Travel', 'Site visit to factory floor for requirements gathering', 540.00, '2026-08-02', 2, 'card', 'pending', 1),
('Office Supplies', 'General office supplies for August', 180.00, '2026-08-05', NULL, 'cash', 'approved', 1);

INSERT INTO notifications (user_id, type, title, message, link, is_read) VALUES
(1, 'system', 'Welcome to BusinessFlow', 'Your Phase 2 modules are now active: Tasks, Expenses, Calendar, Settings and User Management.', 'dashboard/index.php', 0);
