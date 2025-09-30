     // Navbar JS started
const navbar = document.querySelector('.navbar');


function hideNavbar() {
    navbar.style.top = `-${navbar.offsetHeight}px`; // Hide navbar
}

function showNavbar() {
    navbar.style.top = '0'; // Show navbar
}
//Event Listener for Laptops/Desktops

window.addEventListener('wheel', function(event) {

  if (event.deltaY > 0) {
    hideNavbar();
  } else {
    showNavbar();
  }
});


//Event Listeners for Mobile/Touch Devices

let startY; 

document.addEventListener('touchstart', function(event) {

    startY = event.touches[0].clientY;
}, { passive: true });

document.addEventListener('touchmove', function(event) {
    if (!startY) {
        return; 
    }

    const currentY = event.touches[0].clientY;


    const deltaY = startY - currentY;

    if (deltaY > 5) {
        hideNavbar();
    } else if (deltaY < -5) {
        showNavbar();
    }
    startY = null; 

}, { passive: true });

     //Navbar JS Ended


// This function will run when the HTML document is fully loaded
document.addEventListener('DOMContentLoaded', function() {

    // --- Code for requestCall.html ---
    const callBackBtn = document.getElementById("callBackBtn");
    if (callBackBtn) {
        callBackBtn.addEventListener('click', function(event) {
            event.preventDefault(); 
            callBackBtn.innerHTML = "Submitted Successfully";
        });
    }

    // --- Code for gallery.html ---
    const gallery = document.getElementById('gal-gallery');
    if (gallery) {
        const allPhotos = Array.from(document.querySelectorAll('.gal-photo'));
        const lightbox = document.getElementById('gal-lightbox');
        const lightboxImg = document.getElementById('gal-lightbox-img');
        let visiblePhotos = allPhotos; 
        let currentIndex = 0;

        function addClickListeners() {
            visiblePhotos.forEach((photo, index) => {
                photo.onclick = () => {
                    currentIndex = index;
                    lightboxImg.src = photo.querySelector('img').src;
                    lightbox.classList.add('active');
                };
            });
        }

        window.closeLightbox = function() {
            lightbox.classList.remove('active');
        }

        window.nextLightbox = function() {
            currentIndex = (currentIndex + 1) % visiblePhotos.length;
            lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
        }

        window.prevLightbox = function() {
            currentIndex = (currentIndex - 1 + visiblePhotos.length) % visiblePhotos.length;
            lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
        }
        
        window.openAllInLightbox = function() {
            if (visiblePhotos.length > 0) {
              currentIndex = 0;
              lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
              lightbox.classList.add('active');
            }
        }

        addClickListeners();
    }
});

function showContent(section) {
    let syllabusList = document.getElementById('syllabusList');
    if (!syllabusList) return; 

    let sections = {
        acdemicsCalendar: "<h2>Academic calendars</h2><p>The academic calendar outlines the schedule for the academic year, including the commencement of classes, internal assessments, and examination periods. For detailed dates and events, please refer to the official academic calendar: Academic Calendar PDF.</p>",
        courseListining: "<h2>Course listings</h2><p>COCAS offers a diverse range of undergraduate and postgraduate programs across various faculties, including Arts, Science, Commerce, and Vocational courses. For a comprehensive list of courses, please visit the official website: COCAS Courses.</p>",
        undergraduateProgram: "<h2>Undergraduate program</h2><p>The undergraduate programs at COCAS are designed to provide students with a solid foundation in their chosen fields, fostering both academic and professional growth. Detailed information about each program is available on the college's official website: Undergraduate Programs.</p>",
        CinProgramming: "<h2>Certificate in programming</h2><p>COCAS offers various add-on and certificate courses aimed at enhancing students' skills and employability. For more information on available certificate programs, please refer to the 'Add-on/Certificate/Value Added/Outreach Programme' section on the official website: Certificate Programs.</p>",
        studentRO: "<h2>Student research opportunities</h2><p>The college encourages student participation in research activities across various departments. Opportunities for research projects, workshops, and seminars are regularly updated. For the latest information, please check the 'Research and Innovation' section: Research Opportunities.</p>",
        careerFair: "<h2>Career fair</h2><p>COCAS organizes career fairs and placement drives to connect students with potential employers. These events are announced periodically. For upcoming events and details, please visit the 'Student's Corner' section: Career Events.</p>",
        learningRes: "<h2>Learning resource</h2><p>In this section you will get the resources for learning</p>",
        extras: "<h2>Extras</h2><p> This section is extra part here extra's information will be given.</p>",
    };

    if (section === 'acdemics') {
        syllabusList.innerHTML = `
            <div id="academicsHwithBtn">
                <h2>Academics</h2>
                <button id="prospectusDownload"><a href="images/bsc it.pdf" download="B.Sc IT Prospectus.pdf">Download Prospectus</a></button>
            </div>
            <br>
            <h3 id="yearName">First Year</h3><br>
            <div class="coreGrid">
                <ul>
                    <h5 id="contentHeading">Core</h5>
                    <li>- Hardware</li><li>- Intro to software</li><li>- Intro to IBM Architecture</li><li>- DBMS</li><li>- Operating system concept</li><li>- Basic electronics-1</li>
                    <ul id="practicalSection">
                        <h5 id="contentHeading">Practical</h5>
                        <li>- RDBMS ORACLE</li><li>- Programming in C language</li>
                    </ul>
                </ul>
                <ul>
                    <h5 id="contentHeading">Languages</h5>
                    <li>- C Programming language</li>
                </ul>
            </div>
            <h3 id="yearName">Second Year</h3><br>
            <div class="coreGrid">
                <ul>
                    <h5 id="contentHeading">Core</h5>
                    <li>- Data structure</li><li>- Discreete Mathematics</li><li>- Linux</li><li>- Computer networking & Internet</li><li>- Digital computer networking</li>
                    <ul id="practicalSection">
                        <h5 id="contentHeading">Practical</h5>
                        <li>- Linux</li><li>- Programming in C++ language</li>
                    </ul>
                </ul>
                <ul>
                    <h5 id="contentHeading">Languages</h5>
                    <li>- OOP’s using C++</li><li>- Data structure</li><li>- Linux</li>
                </ul>
            </div>
            <h3 id="yearName">Final Year</h3><br>
            <div class="coreGrid">
                <ul>
                    <h5 id="contentHeading">Core</h5>
                    <li>- Java programming</li><li>- Internet and Web designing</li><li>-  Intro to network security</li><li>- Visual programming to Visual Basic</li><li>- SQL Server</li><li>- System Analysis and design</li>
                    <ul id="practicalSection">
                        <h5 id="contentHeading">Practical</h5>
                        <li>- Java programming</li><li>- Web development</li><li>- Visual Basic</li>
                    </ul>
                </ul>
                <ul>
                    <h5 id="contentHeading">Languages</h5>
                    <li>- Java programming</li><li>- Web designing</li><li>- SQL</li>
                </ul>
            </div>`;
    } else {
        syllabusList.innerHTML = sections[section] || "<h2>Welcome</h2><p>Select a bookmark.</p>";
    }
}

function sortTable(n) {
const table = document.getElementById("studentsTable");
let rows = table.rows;
let switching = true;
let shouldSwitch;
let dir = "asc"; // Set the sorting direction to ascending

while (switching) {
switching = false;
let rowsArray = Array.from(rows);
for (let i = 1; i < rowsArray.length - 1; i++) {
shouldSwitch = false;
const x = rowsArray[i].getElementsByTagName("TD")[n];
const y = rowsArray[i + 1].getElementsByTagName("TD")[n];

if (dir === "asc" && x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
shouldSwitch = true;
break;
} else if (dir === "desc" && x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
shouldSwitch = true;
break;
}
}
if (shouldSwitch) {
rowsArray[i].parentNode.insertBefore(rowsArray[i + 1], rowsArray[i]);
switching = true;
} else {
if (dir === "asc") {
dir = "desc";
switching = true;
}
}
}
}

function filterTable() {
const companyFilter = document.getElementById("companyFilter").value.toLowerCase();
const batchFilter = document.getElementById("batchFilter").value;
const table = document.getElementById("studentsTable");
const rows = table.getElementsByTagName("tr");

for (let i = 1; i < rows.length; i++) {
const cells = rows[i].getElementsByTagName("td");
const company = cells[2].innerText.toLowerCase();
const batch = cells[3].innerText;

if (
(companyFilter === "" || company.includes(companyFilter)) &&
(batchFilter === "" || batch.includes(batchFilter))
) {
rows[i].style.display = "";
} else {
rows[i].style.display = "none";
}
}
}


function filterFaculty() {
    const selectedSubject = document.getElementById("subjectFilter").value.toLowerCase();
const searchName = document.getElementById("nameSearch").value.toLowerCase();
const cards = document.querySelectorAll(".faculty-card");

cards.forEach(card => {
const subject = card.getAttribute("data-subject").toLowerCase();
const name = card.getAttribute("data-name").toLowerCase();

const matchesSubject = selectedSubject === "all" || subject.includes(selectedSubject);
const matchesName = name.includes(searchName);

card.style.display = matchesSubject && matchesName ? "block" : "none";
});
}

const hamBurger = document.querySelector('.hamBurger');
const navLinks = document.querySelector('.nav-links');
hamBurger.addEventListener('click', () => {
    navLinks.classList.toggle('active');
});



const dropBtns = document.querySelectorAll('.drop-btn');

    dropBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        const parentLi = btn.parentElement;
        parentLi.classList.toggle('active');
      });
    });

    // Close dropdown on scroll
    window.addEventListener('scroll', () => {
      dropBtns.forEach(btn => {
        const parentLi = btn.parentElement;
        if(parentLi.classList.contains('active')){
          parentLi.classList.remove('active');
        }
      });
    });


    // Examination JS started

    const tabs = document.querySelectorAll('.tab');
    const cards = document.querySelectorAll('.pyq-card');
    let currentClass = '1st';

    function showYear(classYear) {
      currentClass = classYear;
      tabs.forEach(tab => tab.classList.remove('active'));
      document.querySelector(`.tab[onclick*="${classYear}"]`).classList.add('active');
      filterPYQs();
    }

    function filterPYQs() {
      const selectedYear = document.getElementById('yearFilter').value;
      cards.forEach(card => {
        const matchesClass = card.getAttribute('data-class') === currentClass;
        const matchesYear = selectedYear === 'all' || card.getAttribute('data-year') === selectedYear;
        card.parentElement.style.display = (matchesClass && matchesYear) ? 'block' : 'none';
      });
    }

    // Initial view
    showYear('1st');
  
    //  Examination JS ended



