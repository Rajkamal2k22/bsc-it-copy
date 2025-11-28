<?php 
// 1. Set the page-specific SEO variables
$page_title = "B.Sc. IT Gallery - College Events & Activities | College of Commerce, Patna";
$page_description = "Explore the official photo gallery of the B.Sc. IT Department, College of Commerce, Arts and Science, Patna. View memorable college events, seminars, workshops, and student activities captured through the years.";
$page_keywords = "B.Sc. IT gallery, B.Sc. IT photos, college events Patna, IT seminars, workshops, student activities, B.Sc. IT College of Commerce Patna, cocas IT gallery, bsc it cocas photos, bsc it department events, Patna University events, IT department celebration";

// 2. Include the header file which starts the HTML and loads database settings
include 'includes/header.php';

// --- Fetch Dynamic Gallery Items ---
$items_result = $conn->query("SELECT * FROM gallery_items WHERE is_active = 1 ORDER BY event_date DESC, sort_order ASC");
$all_items = $items_result->fetch_all(MYSQLI_ASSOC);
$categories = ['faculty', 'classroom', 'lab&library', 'events', 'workshops', 'celebrations'];

// Function to format date for display
function format_date_display($date) {
    if (empty($date)) return '';
    return date('d M Y', strtotime($date));
}
?>

<body class="galBody">

    <div class="galleryH1" id="scrollTogallery">
      <h1>B.Sc. IT Gallery</h1>
    </div>

    <div class="gal-filters">
      <button class="filter-btn active" data-filter="all">All</button>
      <?php foreach ($categories as $cat): ?>
          <button class="filter-btn" data-filter="<?php echo $cat; ?>"><?php echo ucfirst(str_replace('&', ' & ', $cat)); ?></button>
      <?php endforeach; ?>
    </div>


    <div class="gal-gallery" id="gal-gallery">
      
      <?php if (!empty($all_items)): ?>
          <?php foreach ($all_items as $item): ?>
              <?php 
              // Convert category to class list for JS filtering
              $category_class = str_replace('&', '', $item['category']);
              ?>
              <div class="galDesc">
                  <div class="gal-photo <?php echo htmlspecialchars($category_class); ?>">
                      <img src="<?php echo htmlspecialchars($item['image_url']); ?>" alt="<?php echo htmlspecialchars($item['title']); ?>">
                      <div class="gal-caption"><?php echo htmlspecialchars($item['title']); ?> <small><?php echo format_date_display($item['event_date']); ?></small></div>
                  </div>
                  <h3><?php echo htmlspecialchars($item['title']); ?></h3>
                  <p><?php echo htmlspecialchars($item['description']); ?></p>
              </div>
          <?php endforeach; ?>
      <?php else: ?>
          <p style="grid-column: 1 / -1; text-align: center; padding: 50px;">No gallery items found. Please add new items via the Admin Panel.</p>
      <?php endif; ?>

    </div>


    <div class="gal-show-button">
      <button onclick="openAllInLightbox()">Show All Photos</button>
    </div>

    <div class="gal-lightbox" id="gal-lightbox">
      <span class="gal-close-lightbox" onclick="closeLightbox()">×</span>
      <img id="gal-lightbox-img" src="" alt="Gallery Lightbox Image" />
      <div class="gal-lightbox-controls">
        <span onclick="prevLightbox()">
          &lt; Prev </span>
            <span onclick="nextLightbox()"> Next &gt; </span>
      </div>
    </div>


<?php 
// 3. Include the footer file which closes the page (includes script.js)
include 'includes/footer.php'; 
?>