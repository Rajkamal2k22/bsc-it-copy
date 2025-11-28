<?php 
// 1. Set the page-specific SEO variables
$page_title = "Contact Us - B.Sc. IT Department | College of Commerce, Patna";
$page_description = "Get in touch with the B.Sc. IT Department at College of Commerce, Arts and Science, Patna. Use our contact form for queries, find our phone number, email, and location. Provide valuable feedback.";
$page_keywords = "B.Sc. IT contact, College of Commerce Patna contact, IT department contact, Patna B.Sc. IT address, phone number, email, feedback, query form, reach us, location map, Bihar IT college contact";

// 2. Include the header file which starts the HTML and connects to the database
include 'includes/header.php';

// Fetch specific contact settings for the right side block
$contact_phone = get_setting('contact_phone');
$feedback_email = get_setting('contact_email_feedback');
?>

<div class="callContainer">
    <div class="leftContainer">
        <h3 id="callBackHeading">ContactUs Form</h3>
        <p id="callBackPara">Have Questions? Connect With Us Through This Form Easily</p>
        
        <form action="https://api.web3forms.com/submit" method="POST" id="callBackForm">
            <input type="hidden" name="access_key" value="f7f20a6a-faa8-41bc-a5fb-6f3e799a95c8">
            <label for="name">
                <input type="text" name="Name" id="name" required placeholder="Name">
            </label>
            <br>
            <label for="requestCall">
                <input type="tel" id="requestCall" name="Phone no." inputmode="numeric" pattern="[0-9]{10}"
                    maxlength="10" placeholder="Phone ☎️ ">
            </label>
            <br>
            <label for="email">
                <input type="email" id="email" name="Email" placeholder="Your Email" required>
            </label>
            <br>
            <label for="Query">
                <input type="text" id="query" name="Query" placeholder="Write your Query..." required>
            </label>
            <button type="submit" id="callBackBtn">Submit</button>
        </form>
    </div>
    
    <div class="rightContainer">
        <h3 id="rightCheading">Get in Touch ✨</h3>
        <p id="rightCabout">We're here to help you with your queries.Fill the form and we'll get you back to you
            soon!</p>

        <ul id="callUl">
            <li>📞 <?php echo htmlspecialchars($contact_phone); ?></li>
            <li>📧 <?php echo htmlspecialchars($feedback_email); ?></li>
            <li>📍 Patna, Bihar</li>
        </ul>
        <br><br>
        
        <p id="rightCfeedback">Don’t forget to share your valuable feedback with us at
            <a href="mailto:<?php echo htmlspecialchars($feedback_email); ?>?subject=Feedback%20on%20B.Sc.%20IT%20Department%20Website&body=Dear%20Team%2C%0D%0A%0D%0AI%20would%20like%20to%20share%20my%20feedback%20regarding%20the%20B.Sc.%20IT%20Department%20website.%0D%0A%0D%0A%5BPlease%20write%20your%20feedback%20here...%5D%0D%0A%0D%0ABest%20regards%2C%0D%0A%5BYour%20Full%20Name%5D%0D%0A%5BYear%5D">
                <?php echo htmlspecialchars($feedback_email); ?>
            </a>
        </p>

    </div>
</div>

<?php 
// 3. Include the footer file which closes the page
include 'includes/footer.php'; 
?>