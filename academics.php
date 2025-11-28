<?php
$page_title = "B.Sc. IT Academics - Syllabus, Courses & Curriculum | College of Commerce, Patna";
$page_description = "Explore the comprehensive B.Sc. IT academic curriculum, detailed syllabus for First, Second, and Final Year, and program structure.";
include 'includes/header.php'; 

// NOTE: Since the syllabus content is complex and mostly static in the original file, it is kept as static HTML here.
// For a fully dynamic solution, all syllabus points and downloads should be stored in the 'downloads' table.
?>

    <div class="homeContent" id="scrollToacademics">
        <div class="mainContent">
            <div class="nameDes">
                <h1>Curriculum & Syllabus </h1>
                <p>Discover the B.Sc IT program, designed to equip you with skills in coding, networking, data science,
                    and cybersecurity. Gain expertise in technology through a well-organized curriculum, practical lab
                    sessions, and projects that are applicable to the industry.</p>
            </div>
            <div class="contentBox">
                <div class="bookmarksList">
                    <ul>
                        <li onclick="window.location.href='academics.php#syllabusList'">Academics (Syllabus)</li>
                        <li onclick="alert('This content section is now dynamic and needs a database table. For now, showing dummy content.');">Academics Calendar</li>
                        <li onclick="alert('This content section is now dynamic and needs a database table. For now, showing dummy content.');">Course Listinings</li>
                        </ul>
                </div>

                <div class="syllabusList" id="syllabusList">
                    <div id="academicsHwithBtn">
                        <h2>Academics</h2>
                        <button id="prospectusDownload"><a href="assets/B.Sc IT Prospectus.pdf"
                                download="B.Sc IT Prospectus.pdf">Download Prospectus</a></button>
                    </div>
                    <br>
                    <h3 id="yearName">First Year</h3>
                    <br>
                    <div class="coreGrid">

                        <ul>
                            <h5 id="contentHeading">Core</h5>
                            <li>- Hardware</li>
                            <li>- Intro to software</li>
                            <li>- Intro to IBM Architecture</li>
                            <li>- DBMS</li>
                            <li>- Operating system concept</li>
                            <li>- Basic electronics-1</li>
                            <ul id="practicalSection">
                                <h5 id="contentHeading">Practical</h5>
                                <li>- RDBMS ORACLE</li>
                                <li>- Programming in C language</li>
                            </ul>

                        </ul>
                       


                        <ul>
                            <h5 id="contentHeading">Languages</h5>
                            <li>- C Programming language</li>

                            <button id="syllabusDownload"><a href="assets/1st year syllabyus.pdf"
                                download="B.Sc IT 1st year syllabus.pdf">Download Syllabus</a></button>
                        </ul>
                         
                    </div>
                    <h3 id="yearName">Second Year</h3>
                    <br>
                    <div class="coreGrid">

                        <ul>
                            <h5 id="contentHeading">Core</h5>
                            <li>- Data structure</li>
                            <li>- Discreete Mathematics</li>
                            <li>- Linux</li>
                            <li>- Computer networking & Internet</li>
                            <li>- Digital computer networking</li>
                            <ul id="practicalSection">
                                <h5 id="contentHeading">Practical</h5>
                                <li>- Linux</li>
                                <li>- Programming in C++ language</li>
                            </ul>

                        </ul>



                        <ul>
                            <h5 id="contentHeading">Languages</h5>
                            <li>- OOP’s using C++</li>
                            <li>- Data structure</li>
                            <li>- Linux</li>

                             <button id="syllabusDownload"><a href="assets/2nd year syllabus.pdf"
                                download="B.Sc IT 2nd year syllabus.pdf">Download Syllabus</a></button>

                        </ul>
                    </div>
                    <h3 id="yearName">Final Year</h3>
                    <br>
                    <div class="coreGrid">

                        <ul>
                            <h5 id="contentHeading">Core</h5>
                            <li>- Java programming</li>
                            <li>- Internet and Web designing</li>
                            <li>- Intro to network security</li>
                            <li>- Visual programming to Visual Basic</li>
                            <li>- SQL Server</li>
                            <li>- System Analysis and design</li>
                            <ul id="practicalSection">
                                <h5 id="contentHeading">Practical</h5>
                                <li>- Java programming</li>
                                <li>- Web development</li>
                                <li>- Visual Basic</li>
                            </ul>

                        </ul>



                        <ul>
                            <h5 id="contentHeading">Languages</h5>
                            <li>- Java programming</li>
                            <li>- Web designing</li>
                            <li>- SQL</li>


                             <button id="syllabusDownload"><a href="assets/3rd year syllabus.pdf"
                                download="B.Sc IT 3rd year syllabus.pdf">Download Syllabus</a></button>

                        </ul>
                    </div>

                    </div>
            </div>
        </div>
    </div>
    <?php include 'includes/footer.php'; ?>