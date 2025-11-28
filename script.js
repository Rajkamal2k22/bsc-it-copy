const preloader = document.getElementById('preloader');
document.addEventListener('DOMContentLoaded', () => {
    preloader.style.opacity = '0';
    setTimeout(() => {
        preloader.style.display = 'none';
        document.body.style.overflow = 'auto'; 
    }, 500); 
});

//Notification Bar Logic started
// NOTE: The data for notifications is now loaded by PHP, but JS handles the visual effects.

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
    // NOTE: PHP handles the server-side action, keeping client-side validation logic from original file.
    const form = document.getElementById('callBackForm')

    if (form) {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();
            // Since Web3Forms uses a third-party service, we keep the original logic for submission.
            // For a PHP-only solution, this would be submitted to a PHP processing script.

            const formData = new FormData(form);

            try {
                const response = await fetch(form.action, {
                    method: form.method,
                    body: formData,
                });

                if (response.ok) {
                    alert("✅ Form submitted successfully!");
                    form.reset();
                    // setTimeout(() => { // Removed forced reload to avoid disrupting user.
                    //     location.reload();
                    // }, 1500);
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
// NOTE: Gallery logic remains mostly the same, handling DOM manipulation and lightbox.

const gallery = document.getElementById('gal-gallery');
if (gallery) {
    let allPhotos = [];
    let visiblePhotos = [];
    const lightbox = document.getElementById('gal-lightbox');
    const lightboxImg = document.getElementById('gal-lightbox-img');
    let currentIndex = 0;

    function refreshPhotos() {
        allPhotos = Array.from(document.querySelectorAll('.gal-photo'));
        visiblePhotos = allPhotos;
        addClickListeners();
    }

    function addClickListeners() {
        allPhotos.forEach((photo, index) => {
            photo.onclick = () => {
                currentIndex = visiblePhotos.findIndex(vp => vp === photo);
                if (currentIndex !== -1) {
                    lightboxImg.src = photo.querySelector('img').src;
                    lightbox.classList.add('active');
                }
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
        visiblePhotos = Array.from(document.querySelectorAll('.gal-photo'))
            .filter(photo => photo.parentElement.style.display !== "none");
            
        if (visiblePhotos.length > 0) {
            currentIndex = 0;
            lightboxImg.src = visiblePhotos[currentIndex].querySelector('img').src;
            lightbox.classList.add('active');
        }
    }
    
    refreshPhotos(); // Initial call
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

        // Update visible photos array for lightbox navigation
        if (gallery) {
            gallery.refreshPhotos(); 
            gallery.visiblePhotos = Array.from(document.querySelectorAll('.gal-photo'))
                .filter(photo => photo.parentElement.style.display !== "none");
        }
    });
});

//Gallery js ended


// Academics logic removed as it's now handled by PHP/static HTML structure.

// Placed Student and Faculty filter logic for client-side functionality

window.sortTable = function(n) {
    const table = document.getElementById("studentsTable");
    let rows = Array.from(table.rows).slice(1); // Get rows except header
    let switching = true;
    let dir = "asc"; 
    let switchcount = 0;

    while (switching) {
        switching = false;
        let shouldSwitch;
        for (let i = 0; i < (rows.length - 1); i++) {
            shouldSwitch = false;
            const x = rows[i].getElementsByTagName("TD")[n];
            const y = rows[i + 1].getElementsByTagName("TD")[n];

            let xVal = x.innerHTML.toLowerCase();
            let yVal = y.innerHTML.toLowerCase();

            if (n === 0) { // For Sl.No column, compare numerically
                 xVal = parseInt(x.innerHTML);
                 yVal = parseInt(y.innerHTML);
            }
            
            if (dir === "asc") {
                if (xVal > yVal) {
                    shouldSwitch = true;
                    break;
                }
            } else if (dir === "desc") {
                if (xVal < yVal) {
                    shouldSwitch = true;
                    break;
                }
            }
        }
        if (shouldSwitch) {
            rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
            switching = true;
            switchcount++;
        } else {
            if (switchcount === 0 && dir === "asc") {
                dir = "desc";
                switching = true;
            }
        }
    }
}

window.filterTable = function() {
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

window.filterFaculty = function() {
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

window.showYear = function(classYear) {
    currentClass = classYear;
    tabs.forEach(tab => tab.classList.remove('active'));
    const activeTab = document.querySelector(`.tab[onclick*="${classYear}"]`);
    if (activeTab) {
        activeTab.classList.add('active');
    }
    window.filterPYQs();
}

window.filterPYQs = function() {
    const selectedYear = document.getElementById('yearFilter').value;
    cards.forEach(card => {
        const matchesClass = card.getAttribute('data-class') === currentClass;
        const matchesYear = selectedYear === 'all' || card.getAttribute('data-year') === selectedYear;
        card.parentElement.style.display = (matchesClass && matchesYear) ? 'block' : 'none';
    });
}

// Starting me 1st year dikhayega (By Default)
window.showYear('1st');

// Examination JS ended