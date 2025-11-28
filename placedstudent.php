<?php
$page_title = "B.Sc. IT Placed Students - Success Stories | COCAS Patna";
$page_description = "Discover the success stories of B.Sc. IT placed students from College of Commerce, Arts and Science, Patna.";
include 'includes/header.php';

// Fetch Placed Students Data for the Table
$students_result = $conn->query("SELECT * FROM placed_students ORDER BY batch DESC, sl_no ASC");
$all_students = $students_result->fetch_all(MYSQLI_ASSOC);

// Fetch students for the scrolling cards (only those with a card_description)
$card_students_result = $conn->query("SELECT * FROM placed_students WHERE card_description IS NOT NULL AND image_url IS NOT NULL ORDER BY RAND() LIMIT 10");
$card_students = $card_students_result->fetch_all(MYSQLI_ASSOC);

// Get unique companies and batches for filters (Static lists as per original file, can be made dynamic)
$companies = ['TCS', 'WIPRO', 'GOOGLE', 'AMAZON', 'ADITYA BIRLA', 'T SYSTEM', 'EY', 'ICICI BANK', 'CONGINANT', 'CAPEGEMINI', 'TEACHNOOK', 'CODING AGE'];
$batches = ['2015-2018', '2016-2019', '2018-2021', '2019-2022', '2021-2024'];
?>

<section class="section" id="scrollToPlacedstu">
    <h2>B.Sc. IT Placed Students</h2>
    <div class="scroll-wrapper">
        <div class="scroll-track">
            <?php 
            // Duplicate the list once to enable infinite scrolling effect
            $scrolling_list = array_merge($card_students, $card_students);
            foreach ($scrolling_list as $student): ?>
            <div class="card">
                <img src="<?php echo htmlspecialchars($student['image_url'] ?? 'images/profile image.webp'); ?>" />
                <h3><?php echo htmlspecialchars($student['name']); ?></h3>
                <div class="profile"><?php echo htmlspecialchars($student['role'] ?? $student['company']); ?></div>
                <p><?php echo htmlspecialchars($student['card_description'] ?? 'Success story in IT field.'); ?></p>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<div class="placedStudentBody">
    <div class="container">
        <h2>Recurited Students List</h2>

        <div class="filters">
            <select id="companyFilter" onchange="filterTable()">
                <option value="">Filter by Company</option>
                <?php foreach ($companies as $company): ?>
                <option value="<?php echo htmlspecialchars($company); ?>"><?php echo htmlspecialchars($company); ?></option>
                <?php endforeach; ?>
            </select>

            <select id="batchFilter" onchange="filterTable()">
                <option value="">Filter by Batch</option>
                <?php foreach ($batches as $batch): ?>
                <option value="<?php echo htmlspecialchars($batch); ?>"><?php echo htmlspecialchars($batch); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="table-container">
            <table id="studentsTable">
                <thead>
                    <tr>
                        <th onclick="sortTable(0)">Sl.No</th>
                        <th onclick="sortTable(1)">Name</th>
                        <th onclick="sortTable(2)">Company</th>
                        <th onclick="sortTable(3)">Batch</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $sl_no = 1; foreach ($all_students as $student): ?>
                    <tr>
                        <td class="sl-no"><?php echo $sl_no++; ?></td>
                        <td class="name"><?php echo htmlspecialchars($student['name']); ?></td>
                        <td class="company"><?php echo htmlspecialchars($student['company']); ?></td>
                        <td class="batch"><?php echo htmlspecialchars($student['batch']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php include 'includes/footer.php'; ?>