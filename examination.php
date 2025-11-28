<?php 
// Database Connection aur Configuration settings load karein
// (Error checking ke liye yahan koi ini_set command daalna theek rahega)
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

// 1. Set the page-specific SEO variables
$page_title = "B.Sc. IT Previous Year Question Papers - COCAS Patna Examinations";
$page_description = "Access and download previous year question papers for B.Sc. IT examinations (1st, 2nd, 3rd year) from College of Commerce, Arts and Science, Patna. Prepare for your exams with past questions in Mathematics, Programming, English, Hindi, and General Science.";
$page_keywords = "B.Sc. IT, Previous Year Question Papers, PYQ, Examinations, COCAS Patna, College of Commerce Arts and Science, BSc IT, Patliputra University, PPU, IT question papers, 1st year BSc IT, 2nd year BSc IT, 3rd year BSc IT, Mathematics PYQ, Programming PYQ, English PYQ, Hindi PYQ, General Science PYQ, exam preparation, Patna colleges, vocational courses, old question papers";

// 2. Include the header file which starts the HTML and loads database settings
include 'includes/header.php';

// --- Database Logic for PYQ ---
$pyq_data = [];
$db_error = null;

// Assuming connection is established in db_connect.php
$pyq_result = $conn->query("SELECT * FROM downloads WHERE category = 'PYQ' ORDER BY exam_year DESC, paper_name ASC");

if ($pyq_result) {
    $pyq_data = $pyq_result->fetch_all(MYSQLI_ASSOC);
} else {
    $db_error = "Database Error: Could not fetch PYQ data. Please ensure the 'downloads' table exists and is populated in the Admin Panel.";
}
?>

  <h1 id="examHeading" id="scrollToExamination">Previous Year Question Papers</h1>
  
  <?php if ($db_error) : ?>
      <div style="padding: 20px; background: #f8d7da; color: #721c24; margin: 20px auto; border-radius: 5px; max-width: 800px; text-align: center;">
          <?php echo $db_error; ?>
      </div>
  <?php endif; ?>

  <div class="tabs">
    <button class="tab active" onclick="showYear('1st')">1st Year</button>
    <button class="tab" onclick="showYear('2nd')">2nd Year</button>
    <button class="tab" onclick="showYear('3rd')">3rd Year</button>
  </div>

  <div class="filter">
    <select id="yearFilter" onchange="filterPYQs()">
      <option value="2025">Choose year</option>
      <option value="2025">2025</option>
      <option value="2024">2024</option>
      <option value="2023">2023</option>
      <option value="2022">2022</option>
      <option value="2021">2021</option>
      <option value="2020">2020</option>
      <option value="2019">2019</option>
      <option value="2018">2018</option>
    </select>
  </div>

  <div class="examContainer">
    <div class="pyq-grid" id="pyqContainer">
      
    <?php if (!empty($pyq_data)): ?>
        <?php foreach ($pyq_data as $pyq): ?>
            <?php
            // Determine class type for JS filtering (e.g., '1st', '2nd', '3rd')
            $class_type = strtolower($pyq['year_class']);
            // Fallback for missing image path (to prevent errors if path is bad)
            $image_src = empty($pyq['preview_image_url']) ? 'images/default-pyq.webp' : $pyq['preview_image_url'];
            ?>

            <a href="<?php echo htmlspecialchars($pyq['file_url']); ?>" class="pyq-link" target="_blank">
                <div class="pyq-card" data-class="<?php echo $class_type; ?>" data-year="<?php echo htmlspecialchars($pyq['exam_year']); ?>">
                    <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($pyq['paper_name']); ?>">
                    <h4><?php echo htmlspecialchars($pyq['paper_name']); ?></h4>
                    <p><?php echo ucfirst($class_type); ?> Year Exam</p>
                </div>
            </a>
        <?php endforeach; ?>
    <?php else: ?>
        <p style="grid-column: 1 / -1; text-align: center; padding: 50px;">
            No Previous Year Question Papers found. Please log in to the Admin Panel and add records to the 'downloads' table with Category 'PYQ'.
        </p>
    <?php endif; ?>
    </div>
  </div>
  <?php 
// 3. Include the footer file which closes the page
include 'includes/footer.php'; 
?>