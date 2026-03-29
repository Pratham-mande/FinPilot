-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 28, 2026 at 04:31 PM
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
-- Database: `finpath_ai`
--

-- --------------------------------------------------------

--
-- Table structure for table `ai_results`
--

CREATE TABLE `ai_results` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `score` int(11) NOT NULL DEFAULT 0,
  `score_label` varchar(30) DEFAULT '',
  `insights` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`insights`)),
  `personality` varchar(200) DEFAULT '',
  `personality_icon` varchar(10) DEFAULT '­ЪДа',
  `personality_desc` text DEFAULT NULL,
  `plans` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`plans`)),
  `stats` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`stats`)),
  `selected_plan` varchar(50) DEFAULT NULL,
  `raw_response` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ai_results`
--

INSERT INTO `ai_results` (`id`, `user_id`, `score`, `score_label`, `insights`, `personality`, `personality_icon`, `personality_desc`, `plans`, `stats`, `selected_plan`, `raw_response`, `created_at`, `updated_at`) VALUES
(1, 4, 40, 'Poor', '[\"You have a high debt-to-income ratio, which may impact your credit score.\",\"You are not prioritizing emergency savings, which can lead to financial instability.\",\"You are not investing regularly, which may hinder your long-term financial goals.\"]', 'Conservative', '😐', 'You tend to be cautious and risk-averse when it comes to your finances.', '[{\"name\":\"Emergency Fund Plan\",\"steps\":[\"Save 3-6 months\' worth of expenses in an easily accessible savings account.\",\"Review and adjust your budget to ensure you are allocating enough funds for emergency savings.\",\"Consider setting up automatic transfers to your emergency fund.\"]},{\"name\":\"Debt Consolidation Plan\",\"steps\":[\"Negotiate with your creditors to consolidate your debt into a single, lower-interest loan.\",\"Create a debt repayment plan and stick to it.\",\"Consider working with a credit counselor to help you manage your debt.\"]},{\"name\":\"Investment Plan\",\"steps\":[\"Take advantage of tax-advantaged retirement accounts, such as a 401(k) or IRA.\",\"Consider working with a financial advisor to develop a personalized investment strategy.\",\"Start investing regularly, even if it\'s a small amount each month.\"]},{\"name\":\"Expense Tracking Plan\",\"steps\":[\"Use a budgeting app or spreadsheet to track your income and expenses.\",\"Identify areas where you can cut back on unnecessary expenses.\",\"Review and adjust your budget regularly to ensure you are staying on track.\"]}]', '{\"savingsRate\":0.2,\"debtRatio\":0.8,\"expenseRatio\":0.8,\"investmentScore\":20}', NULL, '{\"score\":40,\"scoreLabel\":\"Poor\",\"insights\":[\"You have a high debt-to-income ratio, which may impact your credit score.\",\"You are not prioritizing emergency savings, which can lead to financial instability.\",\"You are not investing regularly, which may hinder your long-term financial goals.\"],\"personality\":\"Conservative\",\"personalityIcon\":\"\\ud83d\\ude10\",\"personalityDesc\":\"You tend to be cautious and risk-averse when it comes to your finances.\",\"stats\":{\"savingsRate\":0.2,\"debtRatio\":0.8,\"expenseRatio\":0.8,\"investmentScore\":20},\"plans\":[{\"name\":\"Emergency Fund Plan\",\"steps\":[\"Save 3-6 months\' worth of expenses in an easily accessible savings account.\",\"Review and adjust your budget to ensure you are allocating enough funds for emergency savings.\",\"Consider setting up automatic transfers to your emergency fund.\"]},{\"name\":\"Debt Consolidation Plan\",\"steps\":[\"Negotiate with your creditors to consolidate your debt into a single, lower-interest loan.\",\"Create a debt repayment plan and stick to it.\",\"Consider working with a credit counselor to help you manage your debt.\"]},{\"name\":\"Investment Plan\",\"steps\":[\"Take advantage of tax-advantaged retirement accounts, such as a 401(k) or IRA.\",\"Consider working with a financial advisor to develop a personalized investment strategy.\",\"Start investing regularly, even if it\'s a small amount each month.\"]},{\"name\":\"Expense Tracking Plan\",\"steps\":[\"Use a budgeting app or spreadsheet to track your income and expenses.\",\"Identify areas where you can cut back on unnecessary expenses.\",\"Review and adjust your budget regularly to ensure you are staying on track.\"]}]}', '2026-03-28 15:05:58', '2026-03-28 15:05:58');

-- --------------------------------------------------------

--
-- Table structure for table `behavioral_responses`
--

CREATE TABLE `behavioral_responses` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `q1` varchar(20) DEFAULT 'Not Sure',
  `q2` varchar(20) DEFAULT 'Not Sure',
  `q3` varchar(20) DEFAULT 'Not Sure',
  `q4` varchar(20) DEFAULT 'Not Sure',
  `q5` varchar(20) DEFAULT 'Not Sure',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `behavioral_responses`
--

INSERT INTO `behavioral_responses` (`id`, `user_id`, `q1`, `q2`, `q3`, `q4`, `q5`, `created_at`, `updated_at`) VALUES
(1, 4, 'No', 'Not Sure', 'Not Sure', 'Not Sure', 'No', '2026-03-28 14:22:14', '2026-03-28 14:22:58');

-- --------------------------------------------------------

--
-- Table structure for table `chat_history`
--

CREATE TABLE `chat_history` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `role` enum('user','assistant') NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `chat_history`
--

INSERT INTO `chat_history` (`id`, `user_id`, `role`, `message`, `created_at`) VALUES
(1, 4, 'user', 'What is the best way to save for retirement in India?', '2026-03-28 15:07:47'),
(2, 4, 'assistant', 'Considering your debt and expenses, it\'s essential to prioritize debt repayment first. Once you\'ve made significant progress, focus on retirement savings. In India, the best options for retirement savings are:\n\n1. **National Pension System (NPS)**: A tax-saving option with long-term growth potential. Contribute up to ₹1.5 lakh annually and earn tax benefits under Section 80CCD.\n2. **Public Provident Fund (PPF)**: A long-term, tax-free savings option with a 5-year lock-in period. Contribute up to ₹1.5 lakh annually and earn a fixed interest rate of 7.1%.\n3. **Equity Linked Savings Scheme (ELSS)**: A tax-saving mutual fund option with a 3-year lock-in period. Contribute up to ₹1.5 lakh annually and earn tax benefits under Section 80C.\n\nTo start, consider allocating 10-15% of your ₹10000 monthly savings towards retirement, which is approximately ₹1000-₹1500. Based on your risk tolerance (Medium), you can opt for a mix of NPS and ELSS.\n\nNext steps:\n- Allocate ₹1000-₹1500 towards retirement savings.\n- Contribute to NPS and/or ELSS accordingly.\n- Monitor and adjust as you progress.\n\nMoney Health Score update: 45/100\n\nPlease choose a plan to proceed with retirement savings.', '2026-03-28 15:07:49');

-- --------------------------------------------------------

--
-- Table structure for table `financial_data`
--

CREATE TABLE `financial_data` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `age` int(11) DEFAULT NULL,
  `income` decimal(12,2) DEFAULT NULL,
  `expenses` decimal(12,2) DEFAULT NULL,
  `savings` decimal(12,2) DEFAULT NULL,
  `debt` decimal(12,2) DEFAULT NULL,
  `goal` varchar(100) DEFAULT NULL,
  `risk` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `ai_result` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`ai_result`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `financial_data`
--

INSERT INTO `financial_data` (`id`, `user_id`, `age`, `income`, `expenses`, `savings`, `debt`, `goal`, `risk`, `created_at`, `ai_result`) VALUES
(1, 1, 25, 10000.00, 8000.00, 0.00, 0.00, 'Travel', 'aggressive', '2026-03-25 07:18:11', NULL),
(2, 2, 18, 50000.00, 25000.00, 25000.00, 5000.00, 'Wealth Building', 'aggressive', '2026-03-25 07:36:10', NULL),
(3, 3, 18, 10000.00, 5000.00, 0.00, 0.00, 'Wealth Building', 'aggressive', '2026-03-25 09:06:50', NULL),
(4, 4, 25, 50000.00, 40000.00, 10000.00, 90000.00, '[]', 'Medium', '2026-03-25 10:10:31', '{\"score\":42,\"scoreLabel\":\"Fair\",\"insights\":[\"You have a significant amount of debt, which may impact your credit score.\",\"Your savings rate is low, consider setting aside a portion of your income each month.\",\"You may benefit from creating an emergency fund to cover 3-6 months of living expenses.\"],\"personality\":\"Conservative\",\"personalityIcon\":\"\\ud83c\\udfe0\",\"personalityDesc\":\"You tend to prioritize stability and security over long-term growth.\",\"stats\":{\"savingsRate\":0.2,\"debtRatio\":0.64,\"expenseRatio\":0.8,\"investmentScore\":0.3},\"plans\":[{\"name\":\"Safe Plan\",\"steps\":[\"Create an emergency fund to cover 3-6 months of living expenses.\",\"Pay off high-interest debt, such as credit card balances.\",\"Automate your savings by setting up a monthly transfer.\"]},{\"name\":\"Balanced Plan\",\"steps\":[\"Develop a budget to track your expenses and identify areas for reduction.\",\"Increase your income through a side hustle or salary negotiation.\",\"Invest a portion of your income in a diversified portfolio.\"]},{\"name\":\"Growth Plan\",\"steps\":[\"Aggressively pay off high-interest debt to free up more income for investments.\",\"Invest in a tax-advantaged retirement account, such as a 401(k) or IRA.\",\"Consider working with a financial advisor to optimize your investment strategy.\"]},{\"name\":\"Goal Plan\",\"steps\":[\"Set clear, specific financial goals, such as saving for a down payment on a house.\",\"Create a roadmap to achieve your goals, including a budget and investment strategy.\",\"Automate your savings by setting up regular transfers to a dedicated goal account.\"]}]}'),
(5, 5, 23, 500.00, 100.00, 1000.00, 400.00, 'Travel', 'moderate', '2026-03-25 10:24:19', NULL),
(6, 6, 25, 20000.00, 12000.00, 0.00, 0.00, 'Travel', 'conservative', '2026-03-25 15:21:28', NULL),
(7, 7, 25, 30000.00, 30000.00, 100.00, 0.00, 'Debt Free', 'moderate', '2026-03-25 15:25:02', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `questions`
--

CREATE TABLE `questions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `q1` varchar(20) DEFAULT NULL,
  `q2` varchar(20) DEFAULT NULL,
  `q3` varchar(20) DEFAULT NULL,
  `q4` varchar(20) DEFAULT NULL,
  `q5` varchar(20) DEFAULT NULL,
  `score` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `questions`
--

INSERT INTO `questions` (`id`, `user_id`, `q1`, `q2`, `q3`, `q4`, `q5`, `score`, `created_at`) VALUES
(1, 1, 'No', 'No', 'No', 'No', 'No', 15, '2026-03-25 07:18:33'),
(2, 2, 'Yes', 'Yes', 'Yes', 'No', 'Yes', 95, '2026-03-25 07:36:53'),
(3, 3, 'No', 'No', 'Yes', 'No', 'No', 40, '2026-03-25 09:07:28'),
(4, 4, 'No', 'Yes', 'No', 'No', 'No', 30, '2026-03-25 10:10:53'),
(5, 6, 'No', 'No', 'No', 'No', 'No', 20, '2026-03-25 15:21:56'),
(6, 7, 'No', 'No', 'No', 'No', 'No', 10, '2026-03-25 15:25:12');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, 'Pratham Mande', 'prathammande030@gmail.com', '$2y$10$CBN17XY6OCWBTzSqkVt8GOZ4XM1txeKyMXlpKTbHentoEKRYO3Yv.', '2026-03-25 07:15:47'),
(2, 'KASHIFUDDIN', 'chishtikashifuddin@gmail.com', '$2y$10$VNTjpkMN1c35Mf0UIr4UGeqZ33y7gTFOK0kA.Kbt6bOSZXxtEtKkq', '2026-03-25 07:35:17'),
(3, 'tejaswini adsule', 'tejaswiniadsule709@gmail.com', '$2y$10$FAjPsXqkeZT1tAwyIM6QbuLKU.5xXq.ME56VM8UnFvZHhFlqoWSXS', '2026-03-25 09:05:31'),
(4, 'sneha', 'snehayadav02020@gmail.com', '$2y$10$HMhABy/Hs8QsTDOZ5ipvkuMUWPGESFnmGFHiO9lg9.9ykVLu42Iem', '2026-03-25 10:09:44'),
(5, 'yash horat', 'thoratyash040@gmail.com', '$2y$10$XbSdyQ9qSiwEqjl19.WPcu33KCJtw44ueuNlor4EILC09SYbNiLDO', '2026-03-25 10:22:51'),
(6, 'Prachi Mande', 'prachimande7@gmail.com', '$2y$10$azaYWNdMY6No.9Zep0WPz.sFSmDhlvahGvCWfJAKbuDsaT8eArpb2', '2026-03-25 15:20:18'),
(7, 'Atharv', 'atharvv@gmail.com', '$2y$10$RanvykUUPmyrmUF15RDNZOv0Bc9zWw9SNbGxYuXE5lHbsyhhY0x4e', '2026-03-25 15:23:28');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ai_results`
--
ALTER TABLE `ai_results`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_results_user` (`user_id`);

--
-- Indexes for table `behavioral_responses`
--
ALTER TABLE `behavioral_responses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_responses_user` (`user_id`);

--
-- Indexes for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_chat_user` (`user_id`);

--
-- Indexes for table `financial_data`
--
ALTER TABLE `financial_data`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_financial_user` (`user_id`);

--
-- Indexes for table `questions`
--
ALTER TABLE `questions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ai_results`
--
ALTER TABLE `ai_results`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `behavioral_responses`
--
ALTER TABLE `behavioral_responses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `chat_history`
--
ALTER TABLE `chat_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `financial_data`
--
ALTER TABLE `financial_data`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `questions`
--
ALTER TABLE `questions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ai_results`
--
ALTER TABLE `ai_results`
  ADD CONSTRAINT `ai_results_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `behavioral_responses`
--
ALTER TABLE `behavioral_responses`
  ADD CONSTRAINT `behavioral_responses_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `chat_history`
--
ALTER TABLE `chat_history`
  ADD CONSTRAINT `chat_history_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `financial_data`
--
ALTER TABLE `financial_data`
  ADD CONSTRAINT `financial_data_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `questions`
--
ALTER TABLE `questions`
  ADD CONSTRAINT `questions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
