<?php
// === PHP LOGIC START ===
// Note: Keeping error reporting enabled for debugging, disable in production.
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start(); // Session must be started first for user checks and redirects

// --- ACCESS CONTROL CHECK (Student Login Mandatory) ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'student') {
    // Save the intended destination so they can be redirected after successful login
    $_SESSION['redirect_to'] = 'study_materials.php';
    // Redirect to the new student login page
    header("Location: student_login.php?message=" . urlencode("Access Denied. Please log in to view study materials.") . "&status=error");
    exit();
}

// 1. Database connection
// Note: Assuming db_connect.php is in the same directory as this file
require_once __DIR__ . '/db_connect.php'; 

// Check if the connection is available 
if (!isset($conn) || $conn->connect_error) {
    die("Database Connection failed: " . $conn->connect_error);
}

// Set default page title (Required by header)
$page_title = 'Study Material - Department of B.Sc. IT';

// --- Date/Time Constants for 'New' Tag ---
$two_days_ago = strtotime('-48 hours');

// --- 2. Filter Initialization and Query Building ---
$filter_teacher = $_GET['teacher'] ?? '';
$filter_subject = $_GET['subject'] ?? '';
$filter_year = $_GET['year'] ?? '';

$where_clauses = ["1=1"];
$params = [];
$param_types = "";

if (!empty($filter_teacher)) {
    $where_clauses[] = "f.faculty_id = ?";
    $params[] = $filter_teacher;
    $param_types .= "i";
}
if (!empty($filter_subject)) {
    $where_clauses[] = "f.subject LIKE ?"; 
    $params[] = "%" . $filter_subject . "%";
    $param_types .= "s";
}
if (!empty($filter_year)) {
    $where_clauses[] = "YEAR(m.uploaded_at) = ?";
    $params[] = $filter_year;
    $param_types .= "i";
}

$where_sql = " WHERE " . implode(" AND ", $where_clauses);

// --- 4. Main Query to Fetch Materials ---
$sql = "
    SELECT 
        m.material_id, 
        m.title, 
        m.description, 
        m.file_path, 
        m.file_name,
        m.file_type,
        m.uploaded_at,
        f.name as faculty_name,
        f.subject as faculty_subject,
        YEAR(m.uploaded_at) as upload_year
    FROM study_materials m
    JOIN faculty f ON m.faculty_id = f.faculty_id
    " . $where_sql . "
    ORDER BY m.uploaded_at DESC";

$materials = [];
$stmt = $conn->prepare($sql);

if ($stmt === false) {
    die("SQL Prepare Failed: " . $conn->error);
}

// Bind parameters dynamically
if (!empty($params)) {
    $bind_names = [$param_types];
    for ($i=0; $i<count($params); $i++) {
        $bind_names[] = &$params[$i]; 
    }
    call_user_func_array(array($stmt, 'bind_param'), $bind_names);
}

$stmt->execute();
$result = $stmt->get_result();

while ($row = $result->fetch_assoc()) {
    $materials[] = $row;
}
$stmt->close();

// --- 5. Get Filter Options (Unique Teachers, Subjects, Years) ---
$options_teachers = [];
$options_subjects = [];
$options_years = [];

$options_result = $conn->query("
    SELECT DISTINCT f.faculty_id, f.name, f.subject as faculty_subject, YEAR(m.uploaded_at) as upload_year
    FROM study_materials m
    JOIN faculty f ON m.faculty_id = f.faculty_id
    ORDER BY f.name, upload_year DESC
");

if ($options_result) {
    while ($row = $options_result->fetch_assoc()) {
        $options_teachers[$row['faculty_id']] = $row['name'];
        
        if (!empty($row['faculty_subject']) && !in_array($row['faculty_subject'], $options_subjects)) {
            $options_subjects[] = $row['faculty_subject'];
        }
        if (!in_array($row['upload_year'], $options_years)) {
            $options_years[] = $row['upload_year'];
        }
    }
}
sort($options_years); 

// === PHP LOGIC END ===
?>
<?php 
// Include Header from the includes folder (Accessing header.php will use $conn)
require_once __DIR__ . '/includes/header.php'; 
?>

<!-- ========================================================================= -->
<!-- MAIN CONTENT: STUDY MATERIAL REPOSITORY -->
<!-- ========================================================================= -->

<div class="content-container" style="max-width: 1200px; margin: 40px auto; padding: 20px;">
    <style>
        .new-tag {
            background-color: #e74c3c; 
            color: white;
            padding: 2px 8px;
            font-size: 0.75em;
            font-weight: 700;
            border-radius: 4px;
            margin-left: 10px;
            vertical-align: middle;
            display: inline-block;
        }
    </style>

    <h1 style="color: #2c3e50; border-bottom: 3px solid #3498db; padding-bottom: 10px; margin-bottom: 30px; text-align: center;">
        <i class="fas fa-book-open mr-2"></i> Study Material Repository
    </h1>

    <!-- Filter Section -->
    <div class="filter-bar" style="background: #f0f0f5; padding: 20px; border-radius: 8px; margin-bottom: 30px; box-shadow: 0 2px 5px rgba(0,0,0,0.05); display: flex; flex-wrap: wrap; justify-content: center; gap: 20px;">
        <form method="GET" action="study_materials.php" style="display: flex; flex-wrap: wrap; gap: 20px; align-items: center; width: 100%; justify-content: center;">
            
            <!-- Filter by Teacher -->
            <div class="filter-group" style="flex-grow: 1; min-width: 150px;">
                <label for="teacher" style="font-weight: 600; color: #34495e;">Teacher:</label>
                <select name="teacher" id="teacher" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">All Teachers</option>
                    <?php foreach ($options_teachers as $id => $name): ?>
                        <option value="<?php echo $id; ?>" <?php echo ($filter_teacher == $id) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Filter by Subject -->
            <div class="filter-group" style="flex-grow: 1; min-width: 150px;">
                <label for="subject" style="font-weight: 600; color: #34495e;">Subject:</label>
                <select name="subject" id="subject" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">All Subjects</option>
                    <?php foreach ($options_subjects as $subject): ?>
                        <option value="<?php echo htmlspecialchars($subject); ?>" <?php echo ($filter_subject == $subject) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($subject); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <!-- Filter by Year -->
            <div class="filter-group" style="flex-grow: 1; min-width: 100px;">
                <label for="year" style="font-weight: 600; color: #34495e;">Year:</label>
                <select name="year" id="year" style="width: 100%; padding: 8px; border-radius: 4px; border: 1px solid #ccc;">
                    <option value="">All Years</option>
                    <?php foreach ($options_years as $year): ?>
                        <option value="<?php echo $year; ?>" <?php echo ($filter_year == $year) ? 'selected' : ''; ?>>
                            <?php echo $year; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" style="background: #3498db; color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; font-weight: 600; align-self: flex-end;">
                <i class="fas fa-filter"></i> Apply Filter
            </button>
            <a href="study_materials.php" style="color: #e74c3c; text-decoration: none; padding: 10px 0; font-weight: 600; align-self: flex-end;">Reset</a>
        </form>
    </div>

    <!-- Materials List -->
    <div class="materials-grid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 25px;">
        <?php if (!empty($materials)): ?>
            <?php foreach ($materials as $material): 
                $uploaded_timestamp = strtotime($material['uploaded_at']);
                $is_new = ($uploaded_timestamp >= $two_days_ago);
            ?>
            <div class="material-card" style="background: white; border: 1px solid #ddd; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.08); transition: transform 0.2s;">
                <div style="padding: 20px;">
                    <h2 style="font-size: 1.4rem; color: #2c3e50; margin-top: 0; margin-bottom: 10px;">
                        <?php echo htmlspecialchars($material['title']); ?>
                        <?php if ($is_new): ?>
                            <span class="new-tag">NEW</span>
                        <?php endif; ?>
                    </h2>
                    <p style="font-size: 0.9em; color: #7f8c8d; margin-bottom: 15px;">
                        <?php echo htmlspecialchars(substr($material['description'], 0, 100)); echo (strlen($material['description']) > 100) ? '...' : ''; ?>
                    </p>
                    
                    <div style="font-size: 0.9em; margin-bottom: 15px; padding-top: 5px; border-top: 1px dashed #eee;">
                        <p style="margin: 5px 0;"><i class="fas fa-user-tie mr-1" style="color:#3498db;"></i> Teacher: <?php echo htmlspecialchars($material['faculty_name']); ?></p>
                        <p style="margin: 5px 0;"><i class="fas fa-tags mr-1" style="color:#2ecc71;"></i> Subject: <?php echo htmlspecialchars($material['faculty_subject']); ?></p>
                        
                        <!-- Displaying Full Date/Time -->
                        <p style="margin: 5px 0;"><i class="fas fa-calendar-alt mr-1" style="color:#f39c12;"></i> Uploaded: <?php echo date('M j, Y H:i A', $uploaded_timestamp); ?></p>
                        
                        <p style="margin: 5px 0;"><i class="fas fa-file mr-1" style="color:#9b59b6;"></i> File: <?php echo strtoupper(pathinfo($material['file_name'], PATHINFO_EXTENSION)); ?></p>
                    </div>

                    <a href="<?php echo htmlspecialchars($material['file_path']); ?>" target="_blank" 
                       style="display: block; width: 100%; text-align: center; padding: 10px; background: #2980b9; color: white; text-decoration: none; border-radius: 4px; font-weight: 700; transition: background 0.3s;">
                        <i class="fas fa-download mr-2"></i> Download Material
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="grid-column: 1 / -1; text-align: center; padding: 50px; background: #fff; border-radius: 8px; border: 1px solid #f8d7da;">
                <p style="font-size: 1.2rem; color: #e74c3c;">
                    <i class="fas fa-exclamation-triangle mr-2"></i> No study materials found matching the current filters.
                </p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- ========================================================================= -->
<!-- FOOTER & CLOSING TAGS START -->
<!-- ========================================================================= -->
<?php 
require_once __DIR__ . '/includes/footer.php'; 
?>