<?php
$page_title = "B.Sc. IT Faculty Members | College of Commerce, Arts & Science, Patna";
$page_description = "Meet the experienced faculty of the B.Sc. IT Department at College of Commerce, Arts and Science, Patna.";
include 'includes/header.php';

// Fetch Faculty Data
$faculty_result = $conn->query("SELECT * FROM faculty ORDER BY is_coordinator DESC, sort_order ASC, name ASC");
$faculty_members = $faculty_result->fetch_all(MYSQLI_ASSOC);

$coordinator = array_filter($faculty_members, function($f) { return $f['is_coordinator'] == 1; });
$other_faculty = array_filter($faculty_members, function($f) { return $f['is_coordinator'] == 0; });
?>
    <div class="facultiesBody" id="scrollTofaculties">
        <h1 id="facultiesh1">Faculty of B.Sc. IT Department</h1>

        <div class="wrapFaculties">
            <div class="controls">
                <input type="text" id="nameSearch" placeholder="Search by faculties name..."
                    oninput="filterFaculty()" />
                <select id="subjectFilter" onchange="filterFaculty()">
                    <option value="all">All Subjects</option>
                    <option value="Programming">Programming</option>
                    <option value="Data Structures">Data Structures</option>
                    <option value="Networking">Networking</option>
                    <option value="Database">Database</option>
                    <option value="Web Development">Web Development</option>
                    <option value="Mathematics">Mathematics</option>
                    <option value="Hindi">Hindi</option>
                    <option value="English">English</option>
                </select>
            </div>

            <?php if (!empty($coordinator)): $c = reset($coordinator); ?>
            <div class="coordinator-section">
                <div class="faculty-card coordinator-unique" data-subject="<?php echo htmlspecialchars($c['subject']); ?>"
                    data-name="<?php echo htmlspecialchars($c['name']); ?>">
                    <div class="coordinator-details">
                        <img src="<?php echo htmlspecialchars($c['image_url']); ?>" alt="<?php echo htmlspecialchars($c['name']); ?>" />
                        <div class="coordinator-text">
                            <h3 id="coordinator-h3"><?php echo htmlspecialchars($c['name']); ?></h3>
                            <p class="designation"><strong>Designation:</strong> <?php echo htmlspecialchars($c['designation']); ?></p>
                            <?php if ($c['email']): ?><p><strong>📩</strong> <?php echo htmlspecialchars($c['email']); ?></p><?php endif; ?>
                            <?php if ($c['phone']): ?><p><strong>📞</strong> <?php echo htmlspecialchars($c['phone']); ?> </p><?php endif; ?>
                            <p><strong>Subject:</strong> <?php echo htmlspecialchars($c['subject']); ?></p>
                            <p class="about-coordinator"><?php echo nl2br(htmlspecialchars($c['about'])); ?></p>
                            <?php if ($c['cv_url']): ?>
                            <a href="<?php echo htmlspecialchars($c['cv_url']); ?>" download class="download-cv"><i
                                    class='bx bxs-download'></i> Download CV</a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <div class="faculty-grid" id="facultyGrid">
                <?php foreach ($other_faculty as $f): ?>
                <div class="faculty-card" data-subject="<?php echo htmlspecialchars($f['subject']); ?>" 
                     data-name="<?php echo htmlspecialchars($f['name']); ?>">
                    <img src="<?php echo htmlspecialchars($f['image_url']); ?>" alt="<?php echo htmlspecialchars($f['name']); ?>" />
                    <h3><?php echo htmlspecialchars($f['name']); ?></h3>
                    <p><strong>Designation:</strong> <?php echo htmlspecialchars($f['designation']); ?></p>
                    <?php if ($f['email']): ?><p><strong>📩</strong> <?php echo htmlspecialchars($f['email']); ?></p><?php endif; ?>
                    <?php if ($f['phone']): ?><p><strong>📞</strong> <?php echo htmlspecialchars($f['phone']); ?></p><?php endif; ?>
                    <p><strong>Subject:</strong> <?php echo htmlspecialchars($f['subject']); ?></p>
                    <p class="about"><?php echo nl2br(htmlspecialchars($f['about'])); ?></p>
                    <?php if ($f['cv_url']): ?>
                    <a href="<?php echo htmlspecialchars($f['cv_url']); ?>" download class="download-cv"><i
                            class='bx bxs-download'></i> Download CV</a>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>