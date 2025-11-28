<?php 
// Error reporting lines hata di gayi hain taaki UI par warning na dikhe
// 1. Set the page-specific variables
$page_title = "B.Sc. IT - College of Commerce, Patna | PPU";
$page_description = "Official website of the Department of B.Sc. IT at College of Commerce, Arts and Science, Patna (PPU).";

// 2. Include the header file which starts the HTML and connects to the database
include 'includes/header.php';
?>

    <div class="mainContainer">

        <div class="sliderWraper">
            <div class="slider">
                <img src="images/technical_department.webp" alt="Slide 3 Clone">
                <img src="images/director_msg.webp" alt="Slide 1">
                <img src="images/college_merge_pic.webp" alt="Slide 2">
                <img src="images/technical_department.webp" alt="Slide 3">
                <img src="images/director_msg.webp" alt="Slide 1 Clone">
            </div>

            <div class="sliderNav">
                <span class="dot active" data-slide-index="0"></span>
                <span class="dot" data-slide-index="1"></span>
                <span class="dot" data-slide-index="2"></span>
            </div>

            <div id="prevStudentSlide" class="slider-arrow left">❮</div>
            <div id="nextStudentSlide" class="slider-arrow right">❯</div>
        </div>


        <div class="homeTextBg">
            <div class="homeTextSection">
                <div class="hometext">
                    <h1 id="welcome">Department of Information Technology – College of Commerce, Arts & Science (COCAS), Patna</h1>

                    <p id="para1">The Department of B.Sc. (IT) at the College of Commerce, Arts & Science focuses on
                        both the fundamentals of computing and its real-world applications. Established in 2009 under
                        the directorship of Dr. S.P. Sinha, under the leadership of Indrajit Prasad Ray, the current
                        principal of the college, the department has been committed to delivering high-quality education
                        in the field of Information Technology. Presently, the department is coordinated by Dr. Shambhu
                        Sharan.

                        The department is located on the 2nd floor of APJ Abdul Kalam Technical Bhawan, providing a
                        state-of-the-art learning environment equipped with modern infrastructure.

                    </p>

                    <p id="para2">With a strong focus on practical learning, we offer hands-on training in Software
                        Development, Web Design, Data Analytics, Networking, and Programming, ensuring students gain
                        industry-relevant skills. Our state-of-the-art computer lab is equipped with advanced systems
                        and a smart classroom to enhance interactive learning.

                        We emphasize innovation, research, and real-world problem-solving, encouraging students to work
                        on live projects, internships, and hackathons. The department regularly organizes seminars,
                        workshops, and guest lectures by industry experts to keep students updated with the latest
                        trends in IT.
                    </p>

                    <p id="para3">
                        Our goal is to empower students with the skills and confidence needed to excel in the
                        competitive tech industry, opening doors to exciting career opportunities in top IT companies
                        and startups.
                    </p>
                </div>

                <div class="linkBoxes">
                    <div class="quickLinkBoxes">
                        <div class="linkBox1">
                            <h2>Quick Links</h2>

                            <a href="academics.php#scrollToacademics">Syllabus</a>
                            <a href="faculties.php#scrollTofaculties">Faculties</a>
                            <a href="gallery.php#scrollTogallery">Gallery</a>
                            <a href="contactUs.php">Contact Us</a>
                        </div>
                        <div class="announcement-container">
                             <!-- Yaha agar koi PHP code tha jo error de raha tha, wo ab hide ho jayega -->
                        </div>

                    </div>

                </div>


            </div>
        </div>


    </div>
<?php 
include 'includes/footer.php'; 
?>