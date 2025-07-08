-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jul 09, 2025 at 12:19 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `project_management`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_log`
--

CREATE TABLE `audit_log` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `timestamp` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `message` varchar(255) NOT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(32) DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`id`, `title`, `description`, `start_date`, `end_date`, `created_by`, `created_at`, `status`) VALUES
(1, 'probably', 'fjjf', '2025-07-14', '2025-07-25', 2, '2025-07-07 11:41:33', 'Active'),
(2, 'Car manager', 'Management system for cars', '2025-07-07', '2025-07-17', 9, '2025-07-07 12:50:38', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `project_members`
--

CREATE TABLE `project_members` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `assigned_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_members`
--

INSERT INTO `project_members` (`id`, `project_id`, `user_id`, `assigned_at`) VALUES
(1, 2, 4, '2025-07-07 12:50:38');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(1, 'Admin'),
(2, 'Manager'),
(3, 'Member');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('string','integer','boolean','json') DEFAULT 'string',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `setting_type`, `created_at`, `updated_at`) VALUES
(1, 'platform_name', 'Project Management System', 'string', '2025-07-07 22:45:24', '2025-07-07 22:48:45'),
(2, 'company_name', 'Kasoa Business Centre', 'string', '2025-07-07 22:45:24', '2025-07-07 22:48:45'),
(3, 'timezone', 'UTC', 'string', '2025-07-07 22:45:24', '2025-07-07 22:48:45'),
(4, 'date_format', 'd/m/Y', 'string', '2025-07-07 22:45:24', '2025-07-07 22:48:46'),
(5, 'time_format', 'h:i A', 'string', '2025-07-07 22:45:24', '2025-07-07 22:48:46'),
(6, 'smtp_host', 'smtp.gmail.com', 'string', '2025-07-07 22:45:24', '2025-07-07 22:49:09'),
(7, 'smtp_port', '587', 'integer', '2025-07-07 22:45:24', '2025-07-07 22:49:09'),
(8, 'smtp_user', 'admin@company.com', 'string', '2025-07-07 22:45:24', '2025-07-07 22:49:09'),
(9, 'from_email', 'noreply@company.com', 'string', '2025-07-07 22:45:24', '2025-07-07 22:49:09'),
(10, 'from_name', 'Project Management System', 'string', '2025-07-07 22:45:24', '2025-07-07 22:49:09'),
(11, 'session_timeout', '30', 'integer', '2025-07-07 22:45:24', '2025-07-08 22:05:52'),
(12, 'max_login_attempts', '3', 'integer', '2025-07-07 22:45:24', '2025-07-08 22:05:52'),
(13, 'password_min_length', '8', 'integer', '2025-07-07 22:45:24', '2025-07-08 22:05:52'),
(14, 'require_2fa', '1', 'boolean', '2025-07-07 22:45:24', '2025-07-08 22:05:52'),
(15, 'force_password_change', '1', 'boolean', '2025-07-07 22:45:24', '2025-07-08 22:05:52'),
(16, 'email_notifications', '1', 'boolean', '2025-07-07 22:45:24', '2025-07-07 23:30:13'),
(17, 'task_assignments', '1', 'boolean', '2025-07-07 22:45:24', '2025-07-07 23:30:13'),
(18, 'project_updates', '1', 'boolean', '2025-07-07 22:45:24', '2025-07-07 23:30:13'),
(19, 'deadline_reminders', '1', 'boolean', '2025-07-07 22:45:24', '2025-07-07 23:30:13'),
(20, 'reminder_days', '2', 'integer', '2025-07-07 22:45:24', '2025-07-07 23:30:13'),
(34, 'smtp_pass', '130602', 'string', '2025-07-07 22:49:09', '2025-07-07 22:49:09');

-- --------------------------------------------------------

--
-- Table structure for table `tasks`
--

CREATE TABLE `tasks` (
  `id` int(11) NOT NULL,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `assigned_to` int(11) NOT NULL,
  `status` enum('Pending','In Progress','Completed') DEFAULT 'Pending',
  `priority` enum('Low','Medium','High') DEFAULT 'Medium',
  `deadline` date DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL,
  `progress` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tasks`
--

INSERT INTO `tasks` (`id`, `project_id`, `title`, `description`, `assigned_to`, `status`, `priority`, `deadline`, `created_by`, `created_at`, `updated_at`, `progress`) VALUES
(1, 1, 'create the homepage', 'make sure not to commit to github', 2, 'In Progress', 'High', '2025-07-09', 1, '2025-07-07 11:51:54', '2025-07-08 16:31:34', 59),
(2, 2, 'Do the backend', 'Make sure not to push to Github', 4, 'Completed', 'High', '2025-07-22', 1, '2025-07-07 12:52:13', '2025-07-07 12:54:11', 0),
(3, 2, 'Fix the bugs', '', 2, 'Pending', 'High', '2025-07-09', 9, '2025-07-08 14:37:21', '2025-07-08 16:31:20', 10),
(4, 2, 'Start the zibitus app', '', 2, 'Completed', 'Low', '2025-07-07', 9, '2025-07-08 16:34:28', '2025-07-08 21:18:19', 100),
(5, 2, 'Create another one', 'This one has a description', 2, 'Pending', 'Medium', '2025-07-08', 9, '2025-07-08 16:35:56', NULL, 0),
(6, 2, 'New task', 'This should be overdue', 4, 'Pending', 'Medium', '2025-07-05', 9, '2025-07-08 20:11:56', NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `task_attachments`
--

CREATE TABLE `task_attachments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `file_path` varchar(255) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `uploaded_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`id`, `task_id`, `user_id`, `comment`, `created_at`) VALUES
(1, 1, 2, 'am starting', '2025-07-07 11:52:35'),
(2, 2, 4, 'Am now starting', '2025-07-07 12:53:01'),
(3, 2, 4, 'I\'ve completed the Backend', '2025-07-07 12:54:11'),
(4, 3, 2, 'starting', '2025-07-08 16:30:59'),
(5, 4, 2, 'Ive started', '2025-07-08 21:17:49'),
(6, 4, 2, 'Done', '2025-07-08 21:18:19');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `first_name` varchar(100) DEFAULT NULL,
  `last_name` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `active` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role_id`, `created_at`, `first_name`, `last_name`, `profile_picture`, `updated_at`, `active`) VALUES
(1, 'Ben', 'ben@mail.com', '$2y$10$44KCEvunMoo1B7yQIfcBquU07jAbfIF4/3MYwkb96AtjAlHbGy6ri', 2, '2025-07-07 11:23:19', 'John', 'Antonio', 'uploads/profiles/profile_1_1751930975.jpg', '2025-07-08 22:16:59', 1),
(2, 'Michelle', 'michelle@mail.com', '$2y$10$/U6NMR.EfaAaYIvXDugb6uzjYQB8RYPVbytt89663q4hGIhMQX0Ca', 3, '2025-07-07 11:46:55', 'Ben', 'Teekay', NULL, '2025-07-08 22:14:56', 1),
(4, 'Precious', 'precious@mail.com', '$2y$10$fkfj/kSUliUU.Cyxo8rWLeLigeYM/H1mVK0XrRfb7CQ0Xg7RdA2QC', 2, '2025-07-07 12:47:13', NULL, NULL, NULL, '2025-07-08 22:13:43', 1),
(9, 'Manager', 'manager@mail.com', '$2y$10$AdPqx18nzLpy3Aa.1n1wmODos7T3H3LoJ49.GeYSVQaQ5mwxX.aSW', 2, '2025-07-07 20:26:42', NULL, NULL, NULL, '2025-07-07 22:45:51', 1),
(10, 'Administrator', 'administrator@mail.com', '$2y$10$.gjrCrh5bnJyaCoPhJUyh.vB.IEg5AckOge6KC7g6NXfCy4OM38mC', 1, '2025-07-08 01:30:15', 'John', 'Antonio', 'uploads/profiles/profile_10_1752010986.png', '2025-07-08 21:43:06', 1),
(11, 'John', 'john@mail.com', '$2y$10$enNOuwkL0IZs3VmU3MJnZOyMKIJYhMMewDq6b94BnxZbxSU5Fcow2', 3, '2025-07-08 20:54:03', NULL, NULL, NULL, '2025-07-08 22:15:53', 1),
(12, 'Daniella', 'dan@mail.com', '$2y$10$QeALjkslkfPR.z82yFIXFOZ2WEF0zdJUr4wsABi1xPcNkpbTR2C..', 3, '2025-07-08 21:33:22', 'Daniella', 'Koranteng', NULL, '2025-07-08 22:15:31', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `project_members`
--
ALTER TABLE `project_members`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `project_id` (`project_id`,`user_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `tasks`
--
ALTER TABLE `tasks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `idx_project_id` (`project_id`),
  ADD KEY `idx_assigned_to` (`assigned_to`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `uploaded_by` (`uploaded_by`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_log`
--
ALTER TABLE `audit_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `project_members`
--
ALTER TABLE `project_members`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `tasks`
--
ALTER TABLE `tasks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `task_attachments`
--
ALTER TABLE `task_attachments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_log`
--
ALTER TABLE `audit_log`
  ADD CONSTRAINT `audit_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `project_members`
--
ALTER TABLE `project_members`
  ADD CONSTRAINT `project_members_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `project_members_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `tasks`
--
ALTER TABLE `tasks`
  ADD CONSTRAINT `tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`id`),
  ADD CONSTRAINT `tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `tasks_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_attachments`
--
ALTER TABLE `task_attachments`
  ADD CONSTRAINT `task_attachments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_attachments_ibfk_2` FOREIGN KEY (`uploaded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `tasks` (`id`),
  ADD CONSTRAINT `task_comments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
