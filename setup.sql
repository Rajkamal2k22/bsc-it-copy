-- Database: bsc_it_cocas_db
-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--
CREATE TABLE `faculty` (
  `faculty_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `name` VARCHAR(255) NOT NULL,
  `designation` VARCHAR(100) DEFAULT NULL,
  `subject` VARCHAR(255) DEFAULT NULL,
  `email` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(20) DEFAULT NULL,
  `about` TEXT,
  `image_url` VARCHAR(255) DEFAULT NULL,
  `cv_url` VARCHAR(255) DEFAULT NULL,
  `is_coordinator` TINYINT(1) DEFAULT '0',
  `sort_order` INT(11) DEFAULT '99'
);

--
-- Inserting initial data into `faculty` table (from faculties.html)
--
INSERT INTO `faculty` (`name`, `designation`, `subject`, `email`, `phone`, `about`, `image_url`, `cv_url`, `is_coordinator`, `sort_order`) VALUES
('Dr. Shambhu Sharan', 'Coordinator', 'Data Structures', 'shambhuism@gmail.com', '9334007666', 'As the Coordinator of B.Sc. IT, I\'m dedicated to fostering innovation and excellence. Our mission is to equip students with cutting-edge skills and critical thinking to thrive in the dynamic world of information technology. I am passionate about clean code and teaching logic-building, ensuring our students build a strong foundation for their future.', 'images/Dr. Shambhu_Sharan.jpeg', 'assets/Dr._Shambhu_Sharan cv.pdf', 1, 1),
('Mr. Rakesh Kumar', 'Assistant Coordinator', 'DBMS / Linux', '----', '9334480442', 'Passionate about simplifying DBMS and Linux concepts through practical applications.', 'images/rakesh_sir.jpeg', 'path/to/rakesh_kumar_cv.pdf', 0, 2),
('Mr. Sanjeev Kumar Sinha', 'Faculty', 'DBMS / Linux', 'sanjeevsinhamca@gmail.com', '9931917742', 'Passionate about simplifying DBMS and Linux concepts through practical applications.', 'images/Sanjeev_sir.webp', 'path/to/sanjeev_kumar_cv.pdf', 0, 3),
('Mr. Jai Bardhan Kanth', 'Faculty', 'Programming , IWD', 'jaibardhankanth@gmail.com', '9693216178', 'Creative and enthusiastic about modern web technologies.', 'images/jaibardhan_kanth.webp', 'assets/Jai_Bardhan_Kanth_CV.pdf', 0, 4),
('Mr. Alok Narayan', 'Faculty', 'Programming , SQL', 'aloknarayan@hotmail.com', '9916982195', 'Dedicated to simplifying core programming concepts with real-world examples.', 'images/alok_narayan.jpeg', 'assets/Alok_Narayan_CV.pdf', 0, 5),
('Mr. Manoj Kumar Yadav', 'Faculty', 'Mathematics', 'mkywisdom@gmail.com', '7903710550', 'Helps students to inspiring logical thinking and confidence in mathematics.', 'images/Manoj_sir.webp', 'path/to/manoj_kumar_cv.pdf', 0, 6),
('Mrs. Archana Tripathi', 'Faculty', 'Hindi', '----', '7033643268', 'हिंदी भाषा और साहित्य को सरल व रोचक तरीके से समझाने के प्रति समर्पित।', 'images/Archana mam.webp', 'path/to/archana_triphathi_cv.pdf', 0, 7),
('Dr. Om Prakash Yadav', 'Faculty', 'English', 'omprakash511972@gmail.com', '9431821521', 'Helps students master grammar, vocabulary, and effective expression with confidence.', 'images/Op yadav.webp', 'path/to/om_prakash_cv.pdf', 0, 8);


--
-- Table structure for table `placed_students`
--
CREATE TABLE `placed_students` (
  `id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `sl_no` INT(11) NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `company` VARCHAR(100) NOT NULL,
  `batch` VARCHAR(50) NOT NULL,
  `role` VARCHAR(150) DEFAULT NULL,
  `card_description` VARCHAR(255) DEFAULT NULL,
  `image_url` VARCHAR(255) DEFAULT NULL
);

--
-- Inserting data into `placed_students` table (from placedstudent.html)
--
INSERT INTO `placed_students` (`sl_no`, `name`, `company`, `batch`, `role`, `card_description`, `image_url`) VALUES
(1, 'RAHUL DEV', 'T SYSTEM', '2015-2018', NULL, NULL, NULL),
(2, 'MANISH KUMAR', 'EY', '2015-2018', NULL, NULL, NULL),
(3, 'ANKITA SHARMA', 'WIPRO', '2015-2018', 'Cloud Engineer', 'AWS certified and DevOps expert.', 'images/female profile.jpeg'),
(5, 'SHIVENDRA KUMAR', 'TCS', '2015-2018', 'UI/UX Designer', 'Creative thinker and design expert.', 'images/profile image.webp'),
(6, 'AKANSHA KUMARI', 'TCS', '2015-2018', 'Cybersecurity Analyst', 'Skilled in ethical hacking and networks.', 'images/female profile.jpeg'),
(7, 'AMAN DEEP', 'WIPRO', '2015-2018', NULL, NULL, NULL),
(8, 'MUKUL KUMAR RAVI', 'ADITYA BIRLA', '2015-2018', NULL, NULL, NULL),
(18, 'ADARSH RANJAN', 'ICICI BANK', '2016-2019', 'Data Analyst', 'Excelled in analytics & machine learning.', 'images/profile image.webp'),
(21, 'AVINASH KUMAR', 'WIPRO', '2018-2021', 'Software Engineer', 'Expert in full-stack dev & data structures.', 'images/profile image.webp'),
(22, 'NITESH GAURAV', 'CONGINANT', '2018-2021', 'UI/UX Designer', 'Creative thinker and design expert.', 'images/profile image.webp'),
(27, 'ARUSHI KUMARI', 'AMAZON', '2019-2022', 'Data Analyst', 'Excelled in analytics & machine learning.', 'images/female profile.jpeg'),
(28, 'SWIKRITI KUMARI', 'GOOGLE', '2019-2022', 'Software Engineer', 'Expert in full-stack dev & data structures.', 'images/female profile.jpeg'),
(34, 'SIMRAN KUMAR', 'CAPEGEMINI', '2019-2022', 'Cybersecurity Analyst', 'Skilled in ethical hacking and networks.', 'images/profile image.webp'),
(36, 'ADITI MEHTA', 'CODING AGE', '2021-2024', 'Cloud Engineer', 'AWS certified and DevOps expert.', 'images/female profile.jpeg');


--
-- Table structure for table `announcements`
--
CREATE TABLE `announcements` (
  `notice_id` INT(11) NOT NULL PRIMARY KEY AUTO_INCREMENT,
  `short_text` VARCHAR(255) NOT NULL,
  `full_message` TEXT,
  `is_active` TINYINT(1) DEFAULT '1',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

--
-- Inserting data into `announcements` table (from index.html)
--
INSERT INTO `announcements` (`short_text`, `full_message`) VALUES
('📢 1st Year Batch Started from November 10th', '1st year 2025-28 Batch Started from 10th Nov (Monday).'),
('📢 Website going live soon for everyone', 'This website is going live soon for everyone. Updation of this website is going on so please be patient.'),
('📢 Website is in maintenance some feature may not work...', 'Website is in maintenance phase. so,some feature may not work don\'t worry we are working on it.');


--
-- Table structure for table `settings` (for general info)
--
CREATE TABLE `settings` (
  `key_name` VARCHAR(50) NOT NULL PRIMARY KEY,
  `value` TEXT
);

--
-- Inserting data into `settings` table (from footer/contactUs.html)
--
INSERT INTO `settings` (`key_name`, `value`) VALUES
('contact_address', 'Opposite Rajendra Nagar Terminal, Kankarbagh Main Road, Patna, Bihar - 800020'),
('contact_phone', '+91 9334480442'),
('contact_email_main', 'bscit@cocaspatna.ac.in'),
('contact_email_feedback', 'bscitunofficial@gmail.com'),
('footer_copyright_year', '2025'),
('admin_username', 'admin'),
('admin_password', 'password123'); -- Password: password123 (Please change this hash!)