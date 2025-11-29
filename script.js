const preloader = document.getElementById('preloader');
document.addEventListener('DOMContentLoaded', () => {
    preloader.style.opacity = '0';
    setTimeout(() => {
        preloader.style.display = 'none';
        document.body.style.overflow = 'auto'; 
    }, 500); 
});

//Notification Bar Logic  started

const notificationTrack = document.querySelector('.notificationTrack');
const notificationItemsTop = document.querySelectorAll('.notificationItem');
const announcementContainer = document.querySelector('.announcement-container');

function renderDefaultScrollingAnnouncements() {
    announcementContainer.innerHTML = '';

    const mainHeading = document.createElement('h3');
    mainHeading.textContent = 'Important Notices';
    mainHeading.classList.add('announcements-main-heading');
    announcementContainer.appendChild(mainHeading);

    const scrollWrapper = document.createElement('div');
    scrollWrapper.classList.add('announcement-scroll-wrapper');
    announcementContainer.appendChild(scrollWrapper);

    const noticesListScrolling = document.createElement('div');
    noticesListScrolling.classList.add('notices-list-scrolling');
    scrollWrapper.appendChild(noticesListScrolling);


    const allNoticesData = [];
    notificationItemsTop.forEach(item => {
        const shortText = item.textContent.trim();
        const fullMessage = item.getAttribute('data-full');
        if (shortText && fullMessage) {
            allNoticesData.push({ short: shortText, full: fullMessage });
        }
    });


    const numRepeats = 3;
    for (let i = 0; i < numRepeats; i++) {
        allNoticesData.forEach(notice => {
            const noticeCardTemplate = document.createElement('div');
            noticeCardTemplate.classList.add('notice-card-scrolling');
            noticeCardTemplate.setAttribute('data-full', notice.full);
            noticeCardTemplate.style.cursor = 'pointer';

            const icon = document.createElement('i');
            icon.classList.add('fas', 'fa-info-circle', 'notice-icon-scrolling');

            const noticeText = document.createElement('span');
            noticeText.textContent = notice.short;
            noticeText.classList.add('notice-card-text-scrolling');

            noticeCardTemplate.appendChild(icon);
            noticeCardTemplate.appendChild(noticeText);


            noticeCardTemplate.addEventListener('click', () => {
                displaySingleDetailedAnnouncement(notice.full);
            });

            noticesListScrolling.appendChild(noticeCardTemplate);
        });
    }


    const contentHeight = noticesListScrolling.scrollHeight;
    const wrapperHeight = scrollWrapper.clientHeight;

    const animationDuration = contentHeight / (wrapperHeight * 0.05);
    noticesListScrolling.style.animationDuration = `${animationDuration}s`;


    noticesListScrolling.style.animationPlayState = 'running';

    scrollWrapper.addEventListener('mouseenter', () => {
        noticesListScrolling.style.animationPlayState = 'paused';
    });
    scrollWrapper.addEventListener('mouseleave', () => {
        noticesListScrolling.style.animationPlayState = 'running';
    });
}

function displaySingleDetailedAnnouncement(fullMessage) {
    announcementContainer.innerHTML = '';

    const detailDiv = document.createElement('div');
    detailDiv.classList.add('detailed-announcement');
    detailDiv.innerHTML = `<h3>Announcement Details</h3><p>${fullMessage}</p>`;
    announcementContainer.appendChild(detailDiv);

    const backButton = document.createElement('button');
    backButton.textContent = 'Back to All Notices';
    backButton.classList.add('back-to-notices-btn');
    backButton.addEventListener('click', renderDefaultScrollingAnnouncements); // 
    announcementContainer.appendChild(backButton);

    announcementContainer.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

if (notificationTrack && notificationItemsTop.length > 0 && announcementContainer) {
    const totalNotifications = notificationItemsTop.length;

    notificationItemsTop.forEach(item => {
        const clone = item.cloneNode(true);
        notificationTrack.appendChild(clone);
    });

    let position = 0;
    const scrollSpeed = 0.6;
    const firstItem = notificationItemsTop[0];

    const itemWidth = firstItem ? (firstItem.offsetWidth + parseFloat(getComputedStyle(firstItem).marginRight || 0)) : 0;


    function animateScroll() {
        position -= scrollSpeed;

        if (position <= -itemWidth * totalNotifications && totalNotifications > 0) {
            position = 0;
        }
        notificationTrack.style.transform = `translateX(${position}px)`;
        requestAnimationFrame(animateScroll);
    }

    animateScroll();



    notificationTrack.querySelectorAll('.notificationItem').forEach(item => {
        item.addEventListener('click', () => {
            const fullMessage = item.getAttribute('data-full');
            if (fullMessage) {
                displaySingleDetailedAnnouncement(fullMessage);
            }
        });
    });


    renderDefaultScrollingAnnouncements();
}

//Notification Bar Logic  Ended




// Navbar JS started

const navbar = document.querySelector('.navbar');


function hideNavbar() {
    navbar.style.top = `-${navbar.offsetHeight}px`; // Hide navbar
}

function showNavbar() {
    navbar.style.top = '0'; // Show navbar
}
//Event Listener for Lsrge display

window.addEventListener('wheel', function (event) {

    if (event.deltaY > 0) {
        hideNavbar();
    } else {
        showNavbar();
    }
});


//Event Listeners for Mobile,Touch Devices

let startY;

document.addEventListener('touchstart', function (event) {

    startY = event.touches[0].clientY;
}, { passive: true });

document.addEventListener('touchmove', function (event) {
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


// ye function tab kaam karega jab page ka pura content load ho jayega...
document.addEventListener('DOMContentLoaded', function () {

    //main page slider image logic started

    const studentSliderContainer = document.querySelector('.slider');
    const studentSliderImages = document.querySelectorAll('.slider img');
    const studentSliderDots = document.querySelectorAll('.sliderNav .dot');
    const prevStudentSlideBtn = document.getElementById('prevStudentSlide');
    const nextStudentSlideBtn = document.getElementById('nextStudentSlide');

    const numRealSlides = studentSliderDots.length; 

    let currentRealSlideIndex = 0; 
    const studentSlideIntervalTime = 3000;
    const scrollTransitionDuration = 300; 

    let autoStudentSlideInterval;

    function updateDotActiveState(realIndex) {
        studentSliderDots.forEach((dot, i) => {
            if (i === realIndex) {
                dot.classList.add('active');
            } else {
                dot.classList.remove('active');
            }
        });
    }

    function showStudentSlide(realIndex, smoothScroll = true) {
        let targetImageIndex = realIndex + 1; 

        if (smoothScroll) {
            studentSliderContainer.style.scrollBehavior = 'smooth';
        } else {
            studentSliderContainer.style.scrollBehavior = 'auto'; 
        }

        studentSliderContainer.scrollLeft = studentSliderImages[targetImageIndex].offsetLeft;
        currentRealSlideIndex = realIndex;
        updateDotActiveState(realIndex);
    }

    function nextStudentSlide() {
        let targetRealIndex = currentRealSlideIndex + 1;
      
        let targetImageDomIndexToScroll = currentRealSlideIndex + 1 + 1;

        studentSliderContainer.style.scrollBehavior = 'smooth';
        studentSliderContainer.scrollLeft = studentSliderImages[targetImageDomIndexToScroll].offsetLeft;

        if (targetRealIndex >= numRealSlides) {
           
            currentRealSlideIndex = 0; 
            updateDotActiveState(0); 
            setTimeout(() => {
                studentSliderContainer.style.scrollBehavior = 'auto';
                studentSliderContainer.scrollLeft = studentSliderImages[1].offsetLeft; 
                studentSliderContainer.style.scrollBehavior = 'smooth';
            }, scrollTransitionDuration);
        } else {
            currentRealSlideIndex = targetRealIndex;
            updateDotActiveState(targetRealIndex);
        }
    }

    function prevStudentSlide() {
        let targetRealIndex = currentRealSlideIndex - 1;
        let targetImageDomIndexToScroll = currentRealSlideIndex + 1 - 1;

        studentSliderContainer.style.scrollBehavior = 'smooth';
        studentSliderContainer.scrollLeft = studentSliderImages[targetImageDomIndexToScroll].offsetLeft;

        if (targetRealIndex < 0) {
            currentRealSlideIndex = numRealSlides - 1;
            updateDotActiveState(numRealSlides - 1); 
            setTimeout(() => {
                studentSliderContainer.style.scrollBehavior = 'auto';
                studentSliderContainer.scrollLeft = studentSliderImages[numRealSlides].offsetLeft; 
                studentSliderContainer.style.scrollBehavior = 'smooth';
            }, scrollTransitionDuration);
        } else {
            currentRealSlideIndex = targetRealIndex;
            updateDotActiveState(targetRealIndex);
        }
    }

    function startAutoSlide() {
        clearInterval(autoStudentSlideInterval);
        autoStudentSlideInterval = setInterval(nextStudentSlide, studentSlideIntervalTime);
    }

    studentSliderDots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            showStudentSlide(index); 
            startAutoSlide();
        });
    });

    // Event Listeners for arrows
    prevStudentSlideBtn.addEventListener('click', () => {
        prevStudentSlide();
        startAutoSlide();
    });

    nextStudentSlideBtn.addEventListener('click', () => {
        nextStudentSlide();
        startAutoSlide();
    });

   
    showStudentSlide(0, false); 
    startAutoSlide();


    //contactus form js started

    const form = document.getElementById('callBackForm')

    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                });

                if (response.ok) {
                    alert("✅ Form submitted successfully!");
                    form.reset();
                    setTimeout(() => {
                        location.reload();
                    }, 1500);
                } else {
                    alert("❌ Submission failed. Please try again.");
                }
            } catch (error) {
                console.error(error);
                alert("⚠️ Something went wrong. Please check your internet connection.");
            }
        });
    }

});

  //contactus form js ended


//Gallery js started

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

    window.closeLightbox = function () {
        lightbox.classList.remove('active');
    }

    window.nextLightbox = function () {
        currentIndex = (currentIndex + 1) % visiblePhotos.length;
        lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
    }

    window.prevLightbox = function () {
        currentIndex = (currentIndex - 1 + visiblePhotos.length) % visiblePhotos.length;
        lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
    }

    window.openAllInLightbox = function () {
        if (visiblePhotos.length > 0) {
            currentIndex = 0;
            lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
            lightbox.classList.add('active');
        }
    }

    addClickListeners();
}


const filterButtons = document.querySelectorAll(".filter-btn");
const galleryItems = document.querySelectorAll(".galDesc");

filterButtons.forEach(button => {
    button.addEventListener("click", () => {
        filterButtons.forEach(btn => btn.classList.remove("active"));
        button.classList.add("active");

        const filter = button.getAttribute("data-filter");

        galleryItems.forEach(item => {
            if (filter === "all" || item.querySelector('.gal-photo').classList.contains(filter)) {
                item.style.display = "flex";
            } else {
                item.style.display = "none";
            }
        });


        visiblePhotos = Array.from(document.querySelectorAll('.gal-photo'))
            .filter(photo => photo.parentElement.style.display !== "none");
        addClickListeners();
    });
});

//Gallery js ended


// Academics logic started

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

// Academics js started

function sortTable(n) {
    const table = document.getElementById("studentsTable");
    let rows = table.rows;
    let switching = true;
    let shouldSwitch;
    let dir = "asc"; // Ascending order me set ho jayega

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
    const cards = document.querySelectorAll(".faculty-grid .faculty-card, .coordinator-section .faculty-card"); 
    cards.forEach(card => {
        const subject = card.getAttribute("data-subject") ? card.getAttribute("data-subject").toLowerCase() : '';
        const name = card.getAttribute("data-name") ? card.getAttribute("data-name").toLowerCase() : '';

        const matchesSubject = selectedSubject === "all" || subject.includes(selectedSubject);
        const matchesName = name.includes(searchName);
        card.style.display = (matchesSubject && matchesName) ? "flex" : "none";
    });
}


//Hamburger code started
const hamBurger = document.querySelector('.hamBurger');
const navLinks = document.querySelector('.nav-links');
const dropdowns = document.querySelectorAll('.dropdown');

hamBurger.addEventListener('click', (e) => {
    e.stopPropagation();
    navLinks.classList.toggle('active');
    hamBurger.classList.toggle('active'); 
});

document.addEventListener('click', (e) => {
    if (!navLinks.contains(e.target) && !hamBurger.contains(e.target)) {
        navLinks.classList.remove('active');
        hamBurger.classList.remove('active'); 
        dropdowns.forEach(dropdown => dropdown.classList.remove('active'));
    }
});

dropdowns.forEach(dropdown => {
    const dropBtn = dropdown.querySelector('.drop-btn');

    if (dropBtn) {
        dropBtn.addEventListener('click', (e) => {
            if (navLinks.classList.contains('active')) {
                e.stopPropagation();

                const isActive = dropdown.classList.contains('active');

                dropdowns.forEach(d => d.classList.remove('active'));

                if (!isActive) {
                    dropdown.classList.add('active');
                }
            }
        });
    }
});

navLinks.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', () => {
        if (!link.classList.contains('drop-btn')) {
            navLinks.classList.remove('active');
            hamBurger.classList.remove('active');
            dropdowns.forEach(dropdown => dropdown.classList.remove('active')); 
        }
    });
});

//Hamburger code ended


const dropBtns = document.querySelectorAll('.drop-btn');

dropBtns.forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        const parentLi = btn.parentElement;
        parentLi.classList.toggle('active');
    });
});

window.addEventListener('scroll', () => {
    dropBtns.forEach(btn => {
        const parentLi = btn.parentElement;
        if (parentLi.classList.contains('active')) {
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
    const activeTab = document.querySelector(`.tab[onclick*="${classYear}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
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

// Starting me 1st year dikhayega (By Default)
showYear('1st');

//  Examination JS ended

