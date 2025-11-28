<?php
// Note: $conn (mysqli object) must be open when this file is included.
session_start(); 

// --- 1. VISITOR COUNTER LOGIC ---
$total_visitors = 0;

if (isset($conn) && $conn->ping()) {
    if (!isset($_SESSION['has_visited'])) {
        $conn->query("UPDATE site_stats SET total_visitors = total_visitors + 1 WHERE id = 1");
        $_SESSION['has_visited'] = true;
    }
    $vs_result = $conn->query("SELECT total_visitors FROM site_stats WHERE id = 1");
    if ($vs_result && $vs_result->num_rows > 0) {
        $total_visitors = $vs_result->fetch_assoc()['total_visitors'];
    }
}

// --- 2. Fetch Settings ---
$contact_phone = get_setting('contact_phone');
$contact_email_main = get_setting('contact_email_main');
$contact_address = get_setting('contact_address');
$current_year = get_setting('footer_copyright_year') ?? date('Y');
?>

    <!-- INTERNAL STYLES FOR VISITOR COUNTER -->
    <style>
        .visitor-card {
            padding: 5px 0;
            margin-top: 20px;
            text-align: left; /* Changed from center to left */
            color: white;
            display: flex;
            flex-direction: column;
            align-items: flex-start; /* Changed from center to flex-start */
            justify-content: center;
            width: 100%;
        }

        .visitor-title {
            font-size: 0.8rem;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 5px;
            /* Padding left remove kar diya taaki icon ke barabar rahe */
            padding-left: 2px; 
        }

        .visitor-count-wrapper {
            font-family: 'Courier New', monospace;
            font-size: 1.5rem;
            font-weight: bold;
            display: flex;
            justify-content: flex-start; /* Changed from center to flex-start */
            align-items: center;
            gap: 8px;
        }

        .visitor-icon {
            color: #3498db;
            font-size: 1.2rem;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        /* Mobile View: Agar mobile pe sab kuch center karna ho to ise uncomment karein, 
           warna mobile pe bhi left hi rahega */
        @media (max-width: 768px) {
            .footerBox {
                flex-direction: column;
                gap: 20px;
                padding: 15px;
            }
            .firstBox, .secondBox, .thirdBox, .fourthBox {
                width: 100% !important;
                padding-right: 0 !important;
                border-right: none !important;
                border-bottom: 1px dashed #444;
                padding-bottom: 20px;
            }
            .fourthBox {
                border-bottom: none !important;
                padding-bottom: 0;
            }
            .visitor-card {
                margin: 20px 0 0;
            }
        }
    </style>

    <div class="mainFooter">
        <div class="footerBox">
            <div class="firstBox">
                <p id="footerHeading">Social Media</p>
                <ul id="footerIcon">
                    <li><a href="" title="Instagram" target="_blank" id="instagram"><i class='bx bxl-instagram-alt' id="instagram"></i></a></li>
                    <li><a href="" title="Facebook" target="_blank" id="facebook"><i class='bx bxl-facebook-circle'></i></a></li>
                    <li><a href="" title="Youtube" target="_blank" id="youtube"><i class='bx bxl-youtube'></i></a></li>
                    <li><a href="" title="Linkedin" target="_blank" id="linkedin"><i class='bx bxl-linkedin-square'></i></a></li>
                </ul>
                
                <!-- === VISITOR COUNTER === -->
                <div class="visitor-card">
                    <span class="visitor-title">Total Visitors</span>
                    <div class="visitor-count-wrapper">
                        <i class='bx bx-bar-chart-alt-2 visitor-icon'></i>
                        <span class="count-up" data-target="<?php echo $total_visitors; ?>">0</span>
                    </div>
                </div>
                <!-- === END VISITOR COUNTER === -->
            </div>

            <div class="secondBox">
                <p id="footerHeading">Contact</p>
                <ul>
                    <li>
                        <a href="https://www.google.com/maps" target="_blank" class="footercontactIcon">
                            <i class='bx bx-current-location bx-flip-vertical bx-tada' style='color:#bd232a'></i>
                            <?php echo htmlspecialchars($contact_address); ?>
                        </a>
                    </li>
                    <li>
                        <a href="tel:<?php echo htmlspecialchars($contact_phone); ?>" class="footercontactIcon">
                            <i class='bx bxs-phone-call bx-flip-vertical bx-tada' style='color:#1eade1'></i>
                            <?php echo htmlspecialchars($contact_phone); ?>
                        </a>
                    </li>
                    <li>
                        <a href="mailto:<?php echo htmlspecialchars($contact_email_main); ?>" class="footercontactIcon">
                            <i class='bx bxl-gmail bx-tada' style='color:#e11e29'></i>
                            <?php echo htmlspecialchars($contact_email_main); ?>
                        </a>
                    </li>
                </ul>
            </div>

            <div class="thirdBox">
                <p id="footerHeading">Quick Links</p>
                <a href="index.php">Home</a>
                <a href="history.php#scrollTohistory">About Us</a>
                <a href="academics.php#scrollToacademics">Syllabus</a>
                <a href="gallery.php#scrollTogallery">Gallery</a>
                <a href="contactUs.php">Contact Us</a>
                <a href="contactUs.php#callBackBtn">Feedback</a>
            </div>

            <div class="fourthBox">
                <p id="footerHeading">Navigate</p>
                <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3804.251600603494!2d85.1593298756202!3d25.60126097745311!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39ed5f3562555553%3A0x9c0c5f0fd6bfd704!2sCollege%20of%20Commerce%2C%20Arts%20and%20Science!5e1!3m2!1sen!2sin!4v1739352710239!5m2!1sen!2sin" width="100%" height="70%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </div>
    </div>
    
    <div class="line"></div>
    <div class="belowFooter">
        <p>© <?php echo $current_year; ?> B.Sc. IT Department. All rights reserved.</p>
        <div class="credit">
            <p>Designed & Developed by <a href="https://www.linkedin.com/in/rajkamal-kumar-singh/" target="_blank">Rajkamal</a></p>
        </div>
    </div>

    <!-- JAVASCRIPT FOR COUNT-UP ANIMATION -->
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const counters = document.querySelectorAll('.count-up');
            const speed = 200; 

            const observer = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const counter = entry.target;
                        const target = +counter.getAttribute('data-target');
                        const updateCount = () => {
                            const count = +counter.innerText;
                            const inc = target / speed;

                            if (count < target) {
                                counter.innerText = Math.ceil(count + inc);
                                setTimeout(updateCount, 20);
                            } else {
                                counter.innerText = target.toLocaleString();
                                observer.unobserve(counter); 
                            }
                        };
                        updateCount();
                    }
                });
            }, {
                threshold: 0.5 
            });

            counters.forEach(counter => {
                observer.observe(counter);
            });
        });
    </script>
    
    <script src="script.js?v=<?php echo time(); ?>"></script>
</body>
</html>