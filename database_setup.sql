-- System Settings Table
-- This table stores all system configuration settings
CREATE TABLE IF NOT EXISTS `system_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`) VALUES
('platform_name', 'Project Management System', 'string'),
('company_name', 'Your Company', 'string'),
('timezone', 'UTC', 'string'),
('date_format', 'Y-m-d', 'string'),
('time_format', 'H:i', 'string'),
('smtp_host', 'smtp.gmail.com', 'string'),
('smtp_port', '587', 'integer'),
('smtp_user', 'admin@company.com', 'string'),
('from_email', 'noreply@company.com', 'string'),
('from_name', 'Project Management System', 'string'),
('session_timeout', '30', 'integer'),
('max_login_attempts', '5', 'integer'),
('password_min_length', '8', 'integer'),
('require_2fa', '0', 'boolean'),
('force_password_change', '0', 'boolean'),
('email_notifications', '1', 'boolean'),
('task_assignments', '1', 'boolean'),
('project_updates', '1', 'boolean'),
('deadline_reminders', '1', 'boolean'),
('reminder_days', '1', 'integer')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

-- Users table updates - Add these columns to your existing users table
-- Run these commands in your database to add the missing columns:

ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `first_name` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `last_name` varchar(100) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `profile_picture` varchar(255) DEFAULT NULL;
ALTER TABLE `users` ADD COLUMN IF NOT EXISTS `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP; 