-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 22, 2025 at 08:35 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hospital_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `analytics_reports`
--

CREATE TABLE `analytics_reports` (
  `report_id` int(11) NOT NULL,
  `report_name` varchar(100) DEFAULT NULL,
  `report_type` varchar(50) DEFAULT NULL,
  `parameters` text DEFAULT NULL,
  `created_by` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `applications`
--

CREATE TABLE `applications` (
  `application_id` int(11) NOT NULL,
  `posting_id` int(11) DEFAULT NULL,
  `candidate_id` int(11) DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `attendance_records`
--

CREATE TABLE `attendance_records` (
  `record_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_records`
--

INSERT INTO `attendance_records` (`record_id`, `employee_id`, `date`, `time_in`, `time_out`, `status`) VALUES
(1, 'EMP20250001', '2024-01-15', '2024-01-15 08:00:00', '2024-01-15 17:00:00', 'Present'),
(8, 'EMP20250001', '2025-04-14', '2025-04-14 14:38:23', '2025-04-14 14:39:19', 'Present'),
(9, 'EMP20250001', '2025-04-16', '2025-04-16 15:56:15', '2025-04-16 15:56:18', 'Present'),
(10, 'EMP20250001', '2025-04-22', '2025-04-22 07:00:28', '2025-04-22 07:00:33', 'Present');

-- --------------------------------------------------------

--
-- Table structure for table `candidates`
--

CREATE TABLE `candidates` (
  `candidate_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `resume_url` varchar(255) DEFAULT NULL,
  `application_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claims`
--

CREATE TABLE `claims` (
  `claim_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `claim_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `approver_id` varchar(20) DEFAULT NULL,
  `receipt_url` varchar(255) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `claim_types`
--

CREATE TABLE `claim_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `max_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `claim_types`
--

INSERT INTO `claim_types` (`type_id`, `type_name`, `description`, `max_amount`) VALUES
(1, 'Travel', 'Business travel expenses', 5000.00),
(2, 'Medical', 'Healthcare expenses', 2000.00),
(3, 'Training', 'Professional development', 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `compensation_grades`
--

CREATE TABLE `compensation_grades` (
  `grade_id` int(11) NOT NULL,
  `grade_name` varchar(50) DEFAULT NULL,
  `min_salary` decimal(10,2) DEFAULT NULL,
  `mid_salary` decimal(10,2) DEFAULT NULL,
  `max_salary` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `compensation_reviews`
--

CREATE TABLE `compensation_reviews` (
  `review_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `review_date` date DEFAULT NULL,
  `current_salary` decimal(10,2) DEFAULT NULL,
  `proposed_salary` decimal(10,2) DEFAULT NULL,
  `approved_salary` decimal(10,2) DEFAULT NULL,
  `effective_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `approver_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `competencies`
--

CREATE TABLE `competencies` (
  `competency_id` int(11) NOT NULL,
  `competency_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competencies`
--

INSERT INTO `competencies` (`competency_id`, `competency_name`, `description`, `category`) VALUES
(1, 'Leadership', 'Ability to lead and manage teams', 'Soft Skills'),
(2, 'Project Management', 'Project planning and execution', 'Technical Skills'),
(3, 'Communication', 'Verbal and written communication', 'Soft Skills');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `course_id` int(11) NOT NULL,
  `course_name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `duration_hours` int(11) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `is_mandatory` bit(1) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`course_id`, `course_name`, `description`, `duration_hours`, `category`, `is_mandatory`, `created_at`) VALUES
(1, 'Leadership Basics', 'Fundamental leadership skills', 16, 'Management', b'1', '2025-04-11 17:07:26'),
(2, 'Technical Writing', 'Professional documentation skills', 8, 'Communication', b'0', '2025-04-11 17:07:26'),
(3, 'Project Management 101', 'Basic project management', 24, 'Management', b'1', '2025-04-11 17:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) DEFAULT NULL,
  `department_head_id` varchar(20) DEFAULT NULL,
  `parent_department_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`department_id`, `department_name`, `department_head_id`, `parent_department_id`, `created_at`) VALUES
(1, 'Human Resources', 'EMP20250001', 1, '2025-04-11 17:07:26'),
(2, 'Logistics', 'EMP20250002', 2, '2025-04-15 04:49:34'),
(3, 'Operations', NULL, 3, '2025-04-15 14:11:47');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `employee_id` varchar(20) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `marital_status` varchar(20) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `employment_status` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `manager_id` varchar(20) DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`employee_id`, `first_name`, `last_name`, `email`, `password`, `phone`, `date_of_birth`, `gender`, `nationality`, `marital_status`, `hire_date`, `employment_status`, `department_id`, `position_id`, `manager_id`, `last_login`, `created_at`, `updated_at`) VALUES
('EMP-2025-001', 'Vincent', 'Hernandez', 'jsioco86@gmail.com', '', '09996660522', '2000-12-17', 'Male', 'Filipino', 'Single', '2025-04-22', 'PROBATION', 3, 3, NULL, NULL, '2025-04-21 19:33:47', '2025-04-21 19:33:47'),
('EMP20250001', 'joshua', 'hernandez', 'joshsioco56@gmail.com', '$2y$10$wfdZ99AbBWaSTEH8ifzzkexmj5zOJ1s.GRo3rrcNEOWY2.5T/.uAG', '09157606020', '1997-02-14', 'Male', 'Filipino', 'Single', '2025-04-01', 'ACTIVE', 1, 1, NULL, '2025-04-23 02:32:22', '2025-04-14 12:21:49', '2025-04-14 12:21:49'),
('EMP20250002', 'Bing', 'Solis', 'bingsolis@gmail.com', '$2y$10$lKJFYBDXX0cTAYUFiz3aqOAaXXzsoFAdolNipHmbBLMwWjLctSPju', '09996660522', '2000-10-11', 'Male', 'Filipino', 'Single', '2025-04-15', 'Active', 2, 2, NULL, NULL, '2025-04-15 04:57:22', '2025-04-15 04:57:22');

-- --------------------------------------------------------

--
-- Table structure for table `employee_competencies`
--

CREATE TABLE `employee_competencies` (
  `employee_id` varchar(20) NOT NULL,
  `competency_id` int(11) NOT NULL,
  `assessed_level` int(11) DEFAULT NULL,
  `assessment_date` date DEFAULT NULL,
  `assessor_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_onboarding`
--

CREATE TABLE `employee_onboarding` (
  `onboarding_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `task_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_salary`
--

CREATE TABLE `employee_salary` (
  `employee_id` varchar(20) NOT NULL,
  `component_id` int(11) NOT NULL,
  `amount` decimal(10,2) DEFAULT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee_salary`
--

INSERT INTO `employee_salary` (`employee_id`, `component_id`, `amount`, `effective_from`, `effective_to`) VALUES
('EMP-2025-001', 1, 15000.00, '2025-04-22', NULL),
('EMP20250001', 1, 15000.00, '2025-04-21', NULL),
('EMP20250002', 1, 15000.00, '2025-04-21', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `employee_shifts`
--

CREATE TABLE `employee_shifts` (
  `employee_id` varchar(20) NOT NULL,
  `shift_id` int(11) NOT NULL,
  `effective_from` date NOT NULL,
  `effective_to` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee_training`
--

CREATE TABLE `employee_training` (
  `employee_id` varchar(20) NOT NULL,
  `session_id` int(11) NOT NULL,
  `enrollment_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `hr_metrics`
--

CREATE TABLE `hr_metrics` (
  `metric_id` int(11) NOT NULL,
  `metric_name` varchar(100) DEFAULT NULL,
  `metric_value` decimal(10,2) DEFAULT NULL,
  `measurement_date` date DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `inventory_items`
--

CREATE TABLE `inventory_items` (
  `item_id` int(11) NOT NULL,
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_items`
--

INSERT INTO `inventory_items` (`item_id`, `warehouse_id`, `item_name`, `description`, `quantity`, `unit`) VALUES
(3, 2, 'Printer Epson L3150', 'Office Supplies', 120, 'boxes');

-- --------------------------------------------------------

--
-- Table structure for table `inventory_movements`
--

CREATE TABLE `inventory_movements` (
  `movement_id` int(11) NOT NULL,
  `item_id` int(11) DEFAULT NULL,
  `movement_type` varchar(20) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `movement_date` datetime DEFAULT NULL,
  `reference` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory_movements`
--

INSERT INTO `inventory_movements` (`movement_id`, `item_id`, `movement_type`, `quantity`, `movement_date`, `reference`) VALUES
(33, 3, 'OUT', 50, '2025-04-21 13:20:00', 'MV-20250421-192028-3-OUT'),
(34, 3, 'IN', 50, '2025-04-21 13:27:00', 'MV-20250421-192804-3-IN');

-- --------------------------------------------------------

--
-- Table structure for table `job_postings`
--

CREATE TABLE `job_postings` (
  `posting_id` int(11) NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `posting_title` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `requirements` text DEFAULT NULL,
  `posting_date` date DEFAULT NULL,
  `closing_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_balances`
--

CREATE TABLE `leave_balances` (
  `employee_id` varchar(20) NOT NULL,
  `type_id` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `total_days` decimal(5,2) DEFAULT NULL,
  `used_days` decimal(5,2) DEFAULT NULL,
  `remaining_days` decimal(5,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `request_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `approver_id` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `leave_types`
--

CREATE TABLE `leave_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `default_days` int(11) DEFAULT NULL,
  `is_paid` bit(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_types`
--

INSERT INTO `leave_types` (`type_id`, `type_name`, `description`, `default_days`, `is_paid`) VALUES
(1, 'Annual Leave', 'Yearly vacation leave', 14, b'1'),
(2, 'Sick Leave', 'Medical leave', 10, b'1'),
(3, 'Unpaid Leave', 'Leave without pay', NULL, b'0');

-- --------------------------------------------------------

--
-- Table structure for table `onboarding_tasks`
--

CREATE TABLE `onboarding_tasks` (
  `task_id` int(11) NOT NULL,
  `task_name` varchar(200) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `is_mandatory` bit(1) DEFAULT NULL,
  `duration_days` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll_runs`
--

CREATE TABLE `payroll_runs` (
  `run_id` int(11) NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `run_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payroll_runs`
--

INSERT INTO `payroll_runs` (`run_id`, `period_start`, `period_end`, `run_date`, `status`) VALUES
(2, '2025-04-01', '2025-04-15', '2025-04-16', 'Processing'),
(4, '2025-04-01', '2025-04-15', '2025-04-21', 'COMPLETED'),
(5, '2025-04-01', '2025-04-15', '2025-04-21', 'COMPLETED');

-- --------------------------------------------------------

--
-- Table structure for table `payslips`
--

CREATE TABLE `payslips` (
  `payslip_id` int(11) NOT NULL,
  `run_id` int(11) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `gross_pay` decimal(10,2) DEFAULT NULL,
  `deductions` decimal(10,2) DEFAULT NULL,
  `net_pay` decimal(10,2) DEFAULT NULL,
  `generated_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payslips`
--

INSERT INTO `payslips` (`payslip_id`, `run_id`, `employee_id`, `gross_pay`, `deductions`, `net_pay`, `generated_date`) VALUES
(1, 4, 'EMP20250001', 15000.00, 1800.00, 13200.00, '2025-04-21'),
(2, 4, 'EMP20250002', 15000.00, 1800.00, 13200.00, '2025-04-21'),
(3, 5, 'EMP20250001', 15000.00, 1800.00, 13200.00, '2025-04-21'),
(4, 5, 'EMP20250002', 15000.00, 1800.00, 13200.00, '2025-04-21');

-- --------------------------------------------------------

--
-- Table structure for table `performance_cycles`
--

CREATE TABLE `performance_cycles` (
  `cycle_id` int(11) NOT NULL,
  `cycle_name` varchar(100) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_cycles`
--

INSERT INTO `performance_cycles` (`cycle_id`, `cycle_name`, `start_date`, `end_date`, `status`) VALUES
(1, '2023 Mid-Year Review', '2023-06-01', '2023-06-30', 'Active'),
(2, '2023 Year-End Review', '2023-12-01', '2023-12-31', 'Planned');

-- --------------------------------------------------------

--
-- Table structure for table `performance_reviews`
--

CREATE TABLE `performance_reviews` (
  `review_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `cycle_id` int(11) DEFAULT NULL,
  `reviewer_id` varchar(20) DEFAULT NULL,
  `overall_rating` decimal(3,2) DEFAULT NULL,
  `review_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `comments` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `position_id` int(11) NOT NULL,
  `position_title` varchar(100) DEFAULT NULL,
  `job_grade` varchar(20) DEFAULT NULL,
  `job_family` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `positions`
--

INSERT INTO `positions` (`position_id`, `position_title`, `job_grade`, `job_family`, `created_at`) VALUES
(1, 'HR Manager', 'M1', 'Human Resources', '2025-04-11 17:07:26'),
(2, 'Logistics Manager', 'M2', 'Logistics', '2025-04-15 04:51:40'),
(3, 'Operations Manager', 'M3', 'Operations', '2025-04-11 17:07:26');

-- --------------------------------------------------------

--
-- Table structure for table `position_competencies`
--

CREATE TABLE `position_competencies` (
  `position_id` int(11) NOT NULL,
  `competency_id` int(11) NOT NULL,
  `required_level` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `projects`
--

CREATE TABLE `projects` (
  `project_id` int(11) NOT NULL,
  `project_name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_by` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `projects`
--

INSERT INTO `projects` (`project_id`, `project_name`, `description`, `department_id`, `start_date`, `end_date`, `status`, `created_by`, `created_at`) VALUES
(5, 'Job Hunting', 'Conducting interview for new employees ', 1, '2025-04-17', '2025-04-23', 'Planning', 'EMP20250001', '2025-04-16 11:08:33'),
(6, 'Inventory', 'Conduct inventory checking', 2, '2025-05-01', '2025-05-31', 'Planning', 'EMP20250002', '2025-04-16 11:25:08');

-- --------------------------------------------------------

--
-- Table structure for table `project_tasks`
--

CREATE TABLE `project_tasks` (
  `task_id` int(11) NOT NULL,
  `project_id` int(11) DEFAULT NULL,
  `task_name` varchar(100) DEFAULT NULL,
  `assigned_to` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `due_date` date DEFAULT NULL,
  `completion_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `priority` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `project_tasks`
--

INSERT INTO `project_tasks` (`task_id`, `project_id`, `task_name`, `assigned_to`, `start_date`, `due_date`, `completion_date`, `status`, `priority`, `created_at`) VALUES
(7, 5, 'Interview', 'EMP20250001', '2025-04-17', '2025-04-23', '2025-04-17', 'Completed', 'High', '2025-04-16 11:09:29'),
(8, 6, 'Checking Inventory', 'EMP20250002', '2025-05-01', '2025-05-31', NULL, 'In Progress', 'High', '2025-04-16 11:26:00');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_orders`
--

CREATE TABLE `purchase_orders` (
  `order_id` int(11) NOT NULL,
  `request_id` int(11) NOT NULL,
  `supplier_name` varchar(100) DEFAULT NULL,
  `order_date` date DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_orders`
--

INSERT INTO `purchase_orders` (`order_id`, `request_id`, `supplier_name`, `order_date`, `total_amount`, `status`) VALUES
(10, 22, 'Unilever Philippines', '2025-04-19', 12000.00, 'APPROVED'),
(11, 23, 'Home Depot', '2025-04-19', 400.00, 'PENDING');

-- --------------------------------------------------------

--
-- Table structure for table `purchase_requests`
--

CREATE TABLE `purchase_requests` (
  `request_id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `department_id` int(11) DEFAULT NULL,
  `request_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `purpose` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_requests`
--

INSERT INTO `purchase_requests` (`request_id`, `employee_id`, `department_id`, `request_date`, `status`, `purpose`) VALUES
(22, 'EMP20250001', 1, '2025-04-19', 'APPROVED', 'Office Supplies'),
(23, 'EMP20250001', 1, '2025-04-19', 'APPROVED', 'Utility Supplies'),
(24, 'EMP20250002', 2, '2025-04-19', 'PENDING', 'Out of Stock');

-- --------------------------------------------------------

--
-- Table structure for table `recognitions`
--

CREATE TABLE `recognitions` (
  `recognition_id` int(11) NOT NULL,
  `giver_id` varchar(20) DEFAULT NULL,
  `receiver_id` varchar(20) DEFAULT NULL,
  `type_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `recognition_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `recognition_types`
--

CREATE TABLE `recognition_types` (
  `type_id` int(11) NOT NULL,
  `type_name` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `points_value` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `recognition_types`
--

INSERT INTO `recognition_types` (`type_id`, `type_name`, `description`, `points_value`) VALUES
(1, 'Outstanding Performance', 'Exceptional work achievement', 100),
(2, 'Team Player', 'Great collaboration', 50),
(3, 'Innovation', 'Creative solution', 75);

-- --------------------------------------------------------

--
-- Table structure for table `report_history`
--

CREATE TABLE `report_history` (
  `history_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `generated_date` datetime NOT NULL,
  `period_start` date DEFAULT NULL,
  `period_end` date DEFAULT NULL,
  `generated_by` varchar(20) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `request_items`
--

CREATE TABLE `request_items` (
  `item_id` int(11) NOT NULL,
  `request_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `request_items`
--

INSERT INTO `request_items` (`item_id`, `request_id`, `item_name`, `quantity`, `unit_price`) VALUES
(1, 22, 'Printer EPSON L3150', 1, 12000.00),
(2, 23, 'Mop', 2, 200.00),
(3, 24, 'Printer EPSON L3150', 2, 12000.00);

-- --------------------------------------------------------

--
-- Table structure for table `salary_components`
--

CREATE TABLE `salary_components` (
  `component_id` int(11) NOT NULL,
  `component_name` varchar(100) DEFAULT NULL,
  `component_type` varchar(20) DEFAULT NULL,
  `is_taxable` bit(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `salary_components`
--

INSERT INTO `salary_components` (`component_id`, `component_name`, `component_type`, `is_taxable`) VALUES
(1, 'Basic Salary', 'DEDUCTION', b'1'),
(2, 'Housing Allowance', 'Fixed', b'1');

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `shift_id` int(11) NOT NULL,
  `shift_name` varchar(50) DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shifts`
--

INSERT INTO `shifts` (`shift_id`, `shift_name`, `start_time`, `end_time`) VALUES
(1, 'Morning Shift', '09:00:00', '18:00:00'),
(2, 'Evening Shift', '14:00:00', '23:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `succession_candidates`
--

CREATE TABLE `succession_candidates` (
  `plan_id` int(11) NOT NULL,
  `candidate_id` varchar(20) NOT NULL,
  `readiness_level` varchar(20) DEFAULT NULL,
  `potential_score` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `succession_plans`
--

CREATE TABLE `succession_plans` (
  `plan_id` int(11) NOT NULL,
  `position_id` int(11) DEFAULT NULL,
  `incumbent_id` varchar(20) DEFAULT NULL,
  `readiness_level` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `suppliers`
--

CREATE TABLE `suppliers` (
  `supplier_id` int(11) NOT NULL,
  `company_name` varchar(100) NOT NULL,
  `contact_person` varchar(100) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `tax_id` varchar(50) DEFAULT NULL,
  `payment_terms` varchar(20) DEFAULT 'NET30',
  `status` varchar(20) DEFAULT 'Active',
  `rating` int(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `suppliers`
--

INSERT INTO `suppliers` (`supplier_id`, `company_name`, `contact_person`, `email`, `phone`, `address`, `tax_id`, `payment_terms`, `status`, `rating`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Unilever Philippines', '09157606020', 'joshsioco56@gmail.com', '09996660522', '664C Purok 1 Area 5 Villa Beatriz Old Balara QC', '982717643', 'NET30', 'Active', 0, 'Supplying Medical Supplies', '2025-04-19 05:15:05', '2025-04-19 05:15:05'),
(2, 'Home Depot', '09291565319', 'jsioco86@gmail.com', '09157606020', 'Villa Beatriz Matandang Balara QC', '8632592', 'NET45', 'Active', 0, 'Utility Supplies', '2025-04-19 11:43:40', '2025-04-19 23:59:42');

-- --------------------------------------------------------

--
-- Table structure for table `task_comments`
--

CREATE TABLE `task_comments` (
  `comment_id` int(11) NOT NULL,
  `task_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_by` varchar(20) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `task_comments`
--

INSERT INTO `task_comments` (`comment_id`, `task_id`, `comment`, `created_by`, `created_at`, `updated_at`) VALUES
(11, 7, 'fuck', 'EMP20250001', '2025-04-16 18:13:03', '2025-04-16 18:13:03'),
(12, 7, 'fuck', 'EMP20250001', '2025-04-16 18:13:15', '2025-04-16 18:13:15'),
(13, 8, 'Come to Mama !', 'EMP20250001', '2025-04-16 18:30:02', '2025-04-16 18:30:02'),
(14, 8, 'Come to Mama !', 'EMP20250001', '2025-04-16 18:30:11', '2025-04-16 18:30:11'),
(15, 7, 'hello', 'EMP20250001', '2025-04-17 04:34:11', '2025-04-17 04:34:11');

-- --------------------------------------------------------

--
-- Table structure for table `time_off_requests`
--

CREATE TABLE `time_off_requests` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `type` varchar(20) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `time_off_requests`
--

INSERT INTO `time_off_requests` (`id`, `employee_id`, `start_date`, `end_date`, `type`, `notes`, `status`, `created_at`) VALUES
(2, 'EMP20250001', '2025-04-15', '2025-04-22', 'Sick', NULL, 'Pending', '2025-04-14 13:15:25'),
(3, 'EMP20250001', '2025-05-16', '2025-05-22', 'Personal', 'family problem', 'Cancelled', '2025-04-14 16:15:15'),
(4, 'EMP20250001', '2025-06-01', '2025-06-10', 'Vacation', '', 'Cancelled', '2025-04-14 16:22:03'),
(5, 'EMP20250001', '2025-06-11', '2025-06-18', 'Sick', '', 'Cancelled', '2025-04-14 16:26:59');

-- --------------------------------------------------------

--
-- Table structure for table `training_sessions`
--

CREATE TABLE `training_sessions` (
  `session_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `trainer_id` varchar(20) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `location` varchar(100) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `role` enum('admin','manager','hr','accountant','employee') NOT NULL,
  `status` enum('active','inactive','suspended') NOT NULL DEFAULT 'active',
  `last_login` datetime DEFAULT NULL,
  `password_reset_token` varchar(100) DEFAULT NULL,
  `password_reset_expires` datetime DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `email`, `first_name`, `last_name`, `role`, `status`, `last_login`, `password_reset_token`, `password_reset_expires`, `created_at`, `updated_at`) VALUES
(5, 'admin', '$2y$10$UeLPubNDNx2NPvnJA2IT5.WKYd9fGxahh5F1F3lc7Ip3uKeV0wa3S', 'joshsioco56@gmail.com', 'josh', 'hernandez', 'admin', 'active', '2025-04-23 02:08:46', '5481b83e3b8b7aaa9fcb498c123f569ced5cbeb518661ecdaf405c85b70e2d2b', '2025-04-12 15:11:51', '2025-04-11 21:49:35', '2025-04-22 18:08:46');

-- --------------------------------------------------------

--
-- Table structure for table `vehicles`
--

CREATE TABLE `vehicles` (
  `vehicle_id` int(11) NOT NULL,
  `plate_number` varchar(20) DEFAULT NULL,
  `model` varchar(50) DEFAULT NULL,
  `type` varchar(30) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicles`
--

INSERT INTO `vehicles` (`vehicle_id`, `plate_number`, `model`, `type`, `capacity`, `status`) VALUES
(1, 'XYZ 1234', 'Toyota Vios', 'sedan', 5, 'available');

-- --------------------------------------------------------

--
-- Table structure for table `vehicle_reservations`
--

CREATE TABLE `vehicle_reservations` (
  `reservation_id` int(11) NOT NULL,
  `vehicle_id` int(11) DEFAULT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `purpose` text DEFAULT NULL,
  `reservation_date` date DEFAULT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `vehicle_reservations`
--

INSERT INTO `vehicle_reservations` (`reservation_id`, `vehicle_id`, `employee_id`, `purpose`, `reservation_date`, `start_time`, `end_time`, `status`) VALUES
(1, 1, 'EMP20250001', 'For Emergency Parking', '2025-04-21', '2025-04-21 15:16:00', '2025-04-21 15:30:00', 'cancelled');

-- --------------------------------------------------------

--
-- Table structure for table `warehouses`
--

CREATE TABLE `warehouses` (
  `warehouse_id` int(10) UNSIGNED NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `location` varchar(255) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `manager_id` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `warehouses`
--

INSERT INTO `warehouses` (`warehouse_id`, `name`, `location`, `capacity`, `manager_id`) VALUES
(2, 'Main Warehouse', '123 Zone, Taguig City', 1000, 'EMP20250002');

-- --------------------------------------------------------

--
-- Table structure for table `work_schedules`
--

CREATE TABLE `work_schedules` (
  `id` int(11) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `schedule_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `shift_type` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `work_schedules`
--

INSERT INTO `work_schedules` (`id`, `employee_id`, `schedule_date`, `start_time`, `end_time`, `shift_type`, `created_at`) VALUES
(2, 'EMP20250001', '2025-04-15', '08:00:00', '17:00:00', 'Regular', '2025-04-14 19:36:44'),
(3, 'EMP20250001', '2025-04-16', '08:00:00', '19:00:00', 'Overtime', '2025-04-14 19:49:42');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `analytics_reports`
--
ALTER TABLE `analytics_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `applications`
--
ALTER TABLE `applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `posting_id` (`posting_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD PRIMARY KEY (`record_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `candidates`
--
ALTER TABLE `candidates`
  ADD PRIMARY KEY (`candidate_id`);

--
-- Indexes for table `claims`
--
ALTER TABLE `claims`
  ADD PRIMARY KEY (`claim_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `approver_id` (`approver_id`);

--
-- Indexes for table `claim_types`
--
ALTER TABLE `claim_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `compensation_grades`
--
ALTER TABLE `compensation_grades`
  ADD PRIMARY KEY (`grade_id`);

--
-- Indexes for table `compensation_reviews`
--
ALTER TABLE `compensation_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `approver_id` (`approver_id`);

--
-- Indexes for table `competencies`
--
ALTER TABLE `competencies`
  ADD PRIMARY KEY (`competency_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`course_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`department_id`),
  ADD KEY `department_head_id` (`department_head_id`),
  ADD KEY `parent_department_id` (`parent_department_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`employee_id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `employee_competencies`
--
ALTER TABLE `employee_competencies`
  ADD PRIMARY KEY (`employee_id`,`competency_id`),
  ADD KEY `competency_id` (`competency_id`),
  ADD KEY `assessor_id` (`assessor_id`);

--
-- Indexes for table `employee_onboarding`
--
ALTER TABLE `employee_onboarding`
  ADD PRIMARY KEY (`onboarding_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `task_id` (`task_id`);

--
-- Indexes for table `employee_salary`
--
ALTER TABLE `employee_salary`
  ADD PRIMARY KEY (`employee_id`,`component_id`,`effective_from`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD PRIMARY KEY (`employee_id`,`shift_id`,`effective_from`),
  ADD KEY `shift_id` (`shift_id`);

--
-- Indexes for table `employee_training`
--
ALTER TABLE `employee_training`
  ADD PRIMARY KEY (`employee_id`,`session_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `hr_metrics`
--
ALTER TABLE `hr_metrics`
  ADD PRIMARY KEY (`metric_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `inventory_items_ibfk_1` (`warehouse_id`);

--
-- Indexes for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD PRIMARY KEY (`movement_id`),
  ADD KEY `fk_inventory_movements_item` (`item_id`);

--
-- Indexes for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD PRIMARY KEY (`posting_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD PRIMARY KEY (`employee_id`,`type_id`,`year`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `type_id` (`type_id`),
  ADD KEY `approver_id` (`approver_id`);

--
-- Indexes for table `leave_types`
--
ALTER TABLE `leave_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `onboarding_tasks`
--
ALTER TABLE `onboarding_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  ADD PRIMARY KEY (`run_id`);

--
-- Indexes for table `payslips`
--
ALTER TABLE `payslips`
  ADD PRIMARY KEY (`payslip_id`),
  ADD KEY `run_id` (`run_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `performance_cycles`
--
ALTER TABLE `performance_cycles`
  ADD PRIMARY KEY (`cycle_id`);

--
-- Indexes for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `cycle_id` (`cycle_id`),
  ADD KEY `reviewer_id` (`reviewer_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`position_id`);

--
-- Indexes for table `position_competencies`
--
ALTER TABLE `position_competencies`
  ADD PRIMARY KEY (`position_id`,`competency_id`),
  ADD KEY `competency_id` (`competency_id`);

--
-- Indexes for table `projects`
--
ALTER TABLE `projects`
  ADD PRIMARY KEY (`project_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD PRIMARY KEY (`task_id`),
  ADD KEY `project_id` (`project_id`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `purchase_orders_ibfk_1` (`request_id`);

--
-- Indexes for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `department_id` (`department_id`),
  ADD KEY `fk_employee` (`employee_id`);

--
-- Indexes for table `recognitions`
--
ALTER TABLE `recognitions`
  ADD PRIMARY KEY (`recognition_id`),
  ADD KEY `giver_id` (`giver_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `type_id` (`type_id`);

--
-- Indexes for table `recognition_types`
--
ALTER TABLE `recognition_types`
  ADD PRIMARY KEY (`type_id`);

--
-- Indexes for table `report_history`
--
ALTER TABLE `report_history`
  ADD PRIMARY KEY (`history_id`),
  ADD KEY `generated_by` (`generated_by`);

--
-- Indexes for table `request_items`
--
ALTER TABLE `request_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `request_items_ibfk_1` (`request_id`);

--
-- Indexes for table `salary_components`
--
ALTER TABLE `salary_components`
  ADD PRIMARY KEY (`component_id`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`shift_id`);

--
-- Indexes for table `succession_candidates`
--
ALTER TABLE `succession_candidates`
  ADD PRIMARY KEY (`plan_id`,`candidate_id`),
  ADD KEY `candidate_id` (`candidate_id`);

--
-- Indexes for table `succession_plans`
--
ALTER TABLE `succession_plans`
  ADD PRIMARY KEY (`plan_id`),
  ADD KEY `position_id` (`position_id`),
  ADD KEY `incumbent_id` (`incumbent_id`);

--
-- Indexes for table `suppliers`
--
ALTER TABLE `suppliers`
  ADD PRIMARY KEY (`supplier_id`),
  ADD UNIQUE KEY `company_name` (`company_name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD PRIMARY KEY (`comment_id`),
  ADD KEY `task_id` (`task_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `time_off_requests`
--
ALTER TABLE `time_off_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `trainer_id` (`trainer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `vehicles`
--
ALTER TABLE `vehicles`
  ADD PRIMARY KEY (`vehicle_id`);

--
-- Indexes for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  ADD PRIMARY KEY (`reservation_id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `vehicle_reservations_ibfk_1` (`vehicle_id`);

--
-- Indexes for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD PRIMARY KEY (`warehouse_id`),
  ADD KEY `manager_id` (`manager_id`);

--
-- Indexes for table `work_schedules`
--
ALTER TABLE `work_schedules`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `analytics_reports`
--
ALTER TABLE `analytics_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `applications`
--
ALTER TABLE `applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `attendance_records`
--
ALTER TABLE `attendance_records`
  MODIFY `record_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `candidates`
--
ALTER TABLE `candidates`
  MODIFY `candidate_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `claims`
--
ALTER TABLE `claims`
  MODIFY `claim_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `claim_types`
--
ALTER TABLE `claim_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `compensation_grades`
--
ALTER TABLE `compensation_grades`
  MODIFY `grade_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `compensation_reviews`
--
ALTER TABLE `compensation_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `competencies`
--
ALTER TABLE `competencies`
  MODIFY `competency_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `course_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `employee_onboarding`
--
ALTER TABLE `employee_onboarding`
  MODIFY `onboarding_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `hr_metrics`
--
ALTER TABLE `hr_metrics`
  MODIFY `metric_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `inventory_items`
--
ALTER TABLE `inventory_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  MODIFY `movement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `job_postings`
--
ALTER TABLE `job_postings`
  MODIFY `posting_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `leave_types`
--
ALTER TABLE `leave_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `onboarding_tasks`
--
ALTER TABLE `onboarding_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll_runs`
--
ALTER TABLE `payroll_runs`
  MODIFY `run_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payslips`
--
ALTER TABLE `payslips`
  MODIFY `payslip_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `performance_cycles`
--
ALTER TABLE `performance_cycles`
  MODIFY `cycle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  MODIFY `review_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `position_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `projects`
--
ALTER TABLE `projects`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `project_tasks`
--
ALTER TABLE `project_tasks`
  MODIFY `task_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  MODIFY `order_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `recognitions`
--
ALTER TABLE `recognitions`
  MODIFY `recognition_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `recognition_types`
--
ALTER TABLE `recognition_types`
  MODIFY `type_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `report_history`
--
ALTER TABLE `report_history`
  MODIFY `history_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `request_items`
--
ALTER TABLE `request_items`
  MODIFY `item_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `salary_components`
--
ALTER TABLE `salary_components`
  MODIFY `component_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `shift_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `succession_plans`
--
ALTER TABLE `succession_plans`
  MODIFY `plan_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suppliers`
--
ALTER TABLE `suppliers`
  MODIFY `supplier_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `task_comments`
--
ALTER TABLE `task_comments`
  MODIFY `comment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `time_off_requests`
--
ALTER TABLE `time_off_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `training_sessions`
--
ALTER TABLE `training_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `vehicles`
--
ALTER TABLE `vehicles`
  MODIFY `vehicle_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  MODIFY `reservation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `warehouses`
--
ALTER TABLE `warehouses`
  MODIFY `warehouse_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `work_schedules`
--
ALTER TABLE `work_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `analytics_reports`
--
ALTER TABLE `analytics_reports`
  ADD CONSTRAINT `analytics_reports_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `applications`
--
ALTER TABLE `applications`
  ADD CONSTRAINT `applications_ibfk_1` FOREIGN KEY (`posting_id`) REFERENCES `job_postings` (`posting_id`),
  ADD CONSTRAINT `applications_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `candidates` (`candidate_id`);

--
-- Constraints for table `attendance_records`
--
ALTER TABLE `attendance_records`
  ADD CONSTRAINT `attendance_records_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `claims`
--
ALTER TABLE `claims`
  ADD CONSTRAINT `claims_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `claims_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `claim_types` (`type_id`),
  ADD CONSTRAINT `claims_ibfk_3` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `compensation_reviews`
--
ALTER TABLE `compensation_reviews`
  ADD CONSTRAINT `compensation_reviews_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `compensation_reviews_ibfk_2` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `departments`
--
ALTER TABLE `departments`
  ADD CONSTRAINT `departments_ibfk_1` FOREIGN KEY (`department_head_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `departments_ibfk_2` FOREIGN KEY (`parent_department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `employees`
--
ALTER TABLE `employees`
  ADD CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `employee_competencies`
--
ALTER TABLE `employee_competencies`
  ADD CONSTRAINT `employee_competencies_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_competencies_ibfk_2` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`competency_id`),
  ADD CONSTRAINT `employee_competencies_ibfk_3` FOREIGN KEY (`assessor_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `employee_onboarding`
--
ALTER TABLE `employee_onboarding`
  ADD CONSTRAINT `employee_onboarding_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_onboarding_ibfk_2` FOREIGN KEY (`task_id`) REFERENCES `onboarding_tasks` (`task_id`);

--
-- Constraints for table `employee_salary`
--
ALTER TABLE `employee_salary`
  ADD CONSTRAINT `employee_salary_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_salary_ibfk_2` FOREIGN KEY (`component_id`) REFERENCES `salary_components` (`component_id`);

--
-- Constraints for table `employee_shifts`
--
ALTER TABLE `employee_shifts`
  ADD CONSTRAINT `employee_shifts_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_shifts_ibfk_2` FOREIGN KEY (`shift_id`) REFERENCES `shifts` (`shift_id`);

--
-- Constraints for table `employee_training`
--
ALTER TABLE `employee_training`
  ADD CONSTRAINT `employee_training_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `employee_training_ibfk_2` FOREIGN KEY (`session_id`) REFERENCES `training_sessions` (`session_id`);

--
-- Constraints for table `hr_metrics`
--
ALTER TABLE `hr_metrics`
  ADD CONSTRAINT `hr_metrics_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `inventory_items`
--
ALTER TABLE `inventory_items`
  ADD CONSTRAINT `inventory_items_ibfk_1` FOREIGN KEY (`warehouse_id`) REFERENCES `warehouses` (`warehouse_id`);

--
-- Constraints for table `inventory_movements`
--
ALTER TABLE `inventory_movements`
  ADD CONSTRAINT `fk_inventory_movements_item` FOREIGN KEY (`item_id`) REFERENCES `inventory_items` (`item_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `job_postings`
--
ALTER TABLE `job_postings`
  ADD CONSTRAINT `job_postings_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `job_postings_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `leave_balances`
--
ALTER TABLE `leave_balances`
  ADD CONSTRAINT `leave_balances_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `leave_balances_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `leave_types` (`type_id`);

--
-- Constraints for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD CONSTRAINT `leave_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `leave_requests_ibfk_2` FOREIGN KEY (`type_id`) REFERENCES `leave_types` (`type_id`),
  ADD CONSTRAINT `leave_requests_ibfk_3` FOREIGN KEY (`approver_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `onboarding_tasks`
--
ALTER TABLE `onboarding_tasks`
  ADD CONSTRAINT `onboarding_tasks_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `payslips`
--
ALTER TABLE `payslips`
  ADD CONSTRAINT `payslips_ibfk_1` FOREIGN KEY (`run_id`) REFERENCES `payroll_runs` (`run_id`),
  ADD CONSTRAINT `payslips_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `performance_reviews`
--
ALTER TABLE `performance_reviews`
  ADD CONSTRAINT `performance_reviews_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `performance_reviews_ibfk_2` FOREIGN KEY (`cycle_id`) REFERENCES `performance_cycles` (`cycle_id`),
  ADD CONSTRAINT `performance_reviews_ibfk_3` FOREIGN KEY (`reviewer_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `position_competencies`
--
ALTER TABLE `position_competencies`
  ADD CONSTRAINT `position_competencies_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `position_competencies_ibfk_2` FOREIGN KEY (`competency_id`) REFERENCES `competencies` (`competency_id`);

--
-- Constraints for table `projects`
--
ALTER TABLE `projects`
  ADD CONSTRAINT `projects_ibfk_1` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `project_tasks`
--
ALTER TABLE `project_tasks`
  ADD CONSTRAINT `project_tasks_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `projects` (`project_id`),
  ADD CONSTRAINT `project_tasks_ibfk_2` FOREIGN KEY (`assigned_to`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `purchase_orders`
--
ALTER TABLE `purchase_orders`
  ADD CONSTRAINT `purchase_orders_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`request_id`);

--
-- Constraints for table `purchase_requests`
--
ALTER TABLE `purchase_requests`
  ADD CONSTRAINT `fk_employee` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `purchase_requests_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `purchase_requests_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`department_id`);

--
-- Constraints for table `recognitions`
--
ALTER TABLE `recognitions`
  ADD CONSTRAINT `recognitions_ibfk_1` FOREIGN KEY (`giver_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `recognitions_ibfk_2` FOREIGN KEY (`receiver_id`) REFERENCES `employees` (`employee_id`),
  ADD CONSTRAINT `recognitions_ibfk_3` FOREIGN KEY (`type_id`) REFERENCES `recognition_types` (`type_id`);

--
-- Constraints for table `report_history`
--
ALTER TABLE `report_history`
  ADD CONSTRAINT `report_history_ibfk_1` FOREIGN KEY (`generated_by`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `request_items`
--
ALTER TABLE `request_items`
  ADD CONSTRAINT `request_items_ibfk_1` FOREIGN KEY (`request_id`) REFERENCES `purchase_requests` (`request_id`);

--
-- Constraints for table `succession_candidates`
--
ALTER TABLE `succession_candidates`
  ADD CONSTRAINT `succession_candidates_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `succession_plans` (`plan_id`),
  ADD CONSTRAINT `succession_candidates_ibfk_2` FOREIGN KEY (`candidate_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `succession_plans`
--
ALTER TABLE `succession_plans`
  ADD CONSTRAINT `succession_plans_ibfk_1` FOREIGN KEY (`position_id`) REFERENCES `positions` (`position_id`),
  ADD CONSTRAINT `succession_plans_ibfk_2` FOREIGN KEY (`incumbent_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `task_comments`
--
ALTER TABLE `task_comments`
  ADD CONSTRAINT `task_comments_ibfk_1` FOREIGN KEY (`task_id`) REFERENCES `project_tasks` (`task_id`),
  ADD CONSTRAINT `task_comments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `employees` (`employee_id`) ON UPDATE CASCADE;

--
-- Constraints for table `training_sessions`
--
ALTER TABLE `training_sessions`
  ADD CONSTRAINT `training_sessions_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`course_id`),
  ADD CONSTRAINT `training_sessions_ibfk_2` FOREIGN KEY (`trainer_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `vehicle_reservations`
--
ALTER TABLE `vehicle_reservations`
  ADD CONSTRAINT `vehicle_reservations_ibfk_1` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`vehicle_id`),
  ADD CONSTRAINT `vehicle_reservations_ibfk_2` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`);

--
-- Constraints for table `warehouses`
--
ALTER TABLE `warehouses`
  ADD CONSTRAINT `warehouses_ibfk_1` FOREIGN KEY (`manager_id`) REFERENCES `employees` (`employee_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
