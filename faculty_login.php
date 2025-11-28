<?php
session_start();

// 1. Database connection
require_once __DIR__ . '/db_connect.php'; 

// Check if already logged in as Faculty
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'faculty') {
    header("Location: faculty_dashboard.php");
    exit();
}

$page_title = 'Faculty Portal Access';
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$default_mode = (isset($_GET['mode']) && $_GET['mode'] === 'register') ? 'register' : 'login';
?>

<!-- === Header Inclusion === -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- FontAwesome for Icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* === Professional CSS Styles === */
    :root {
        --primary-color: #2c3e50; /* Dark Slate (Professional for Faculty) */
        --primary-hover: #1a252f;
        --accent-color: #3498db;  /* Blue Accent */
        --bg-color: #f3f4f6;
        --text-color: #334155;
    }

    .auth-wrapper {
        min-height: calc(100vh - 120px);
        display: flex;
        justify-content: center;
        align-items: center;
        background-color: var(--bg-color);
        padding: 40px 20px;
    }

    .auth-card {
        background: #ffffff;
        width: 100%;
        max-width: 500px; /* Slightly wider for faculty details */
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        border-top: 5px solid var(--accent-color); /* Top border for style */
        transition: transform 0.3s ease;
    }

    .auth-header { text-align: center; margin-bottom: 30px; }
    .auth-header h2 { color: var(--primary-color); font-size: 1.8rem; font-weight: 700; margin-bottom: 10px; }
    .auth-header p { color: #64748b; font-size: 0.95rem; }

    /* Input Group Styling */
    .input-group { position: relative; margin-bottom: 20px; }
    
    .input-group i.field-icon {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
        width: 20px;
        text-align: center;
    }

    .input-group input {
        width: 100%;
        padding: 12px 15px 12px 50px; /* Space for icon */
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        color: var(--text-color);
    }

    .input-group input:focus {
        border-color: var(--accent-color);
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
    }

    /* Password Toggle Eye */
    .toggle-password {
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #94a3b8;
        transition: color 0.3s;
    }
    .toggle-password:hover { color: var(--accent-color); }

    /* Helper Messages */
    .password-warning {
        font-size: 0.8rem;
        color: #d97706; /* Amber */
        margin-top: 5px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .btn-submit {
        width: 100%;
        padding: 14px;
        background: var(--primary-color);
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 1rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
        margin-top: 10px;
    }
    .btn-submit:hover { background: var(--primary-hover); box-shadow: 0 4px 12px rgba(44, 62, 80, 0.3); }

    .toggle-mode { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; color: #64748b; }
    .toggle-mode button { background: none; border: none; color: var(--accent-color); font-weight: 700; cursor: pointer; text-decoration: none; font-size: 0.95rem; }
    .toggle-mode button:hover { text-decoration: underline; }

    /* Alerts */
    .alert { padding: 15px; border-radius: 8px; margin-bottom: 25px; font-size: 0.9rem; display: flex; align-items: center; gap: 10px; }
    .alert-success { background: #dcfce7; color: #166534; border: 1px solid #bbf7d0; }
    .alert-error { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

    .hidden { display: none; }
</style>

<div class="auth-wrapper">
    <div class="auth-card">
        
        <!-- Status Messages -->
        <?php if ($message): ?>
            <div class="alert <?php echo ($status === 'success') ? 'alert-success' : 'alert-error'; ?>">
                <i class="fas <?php echo ($status === 'success') ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- === FACULTY LOGIN FORM === -->
        <form id="loginForm" action="faculty_auth.php" method="POST" class="<?php echo ($default_mode === 'register' ? 'hidden' : ''); ?>">
            <div class="auth-header">
                <h2>Faculty Login</h2>
                <p>Access your dashboard to manage materials.</p>
            </div>
            
            <input type="hidden" name="action" value="login">
            
            <div class="input-group">
                <i class="fas fa-envelope field-icon"></i>
                <input type="email" name="email" required placeholder="Official Email Address">
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock field-icon"></i>
                <input type="password" name="password" id="loginPass" required placeholder="Password">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('loginPass', this)"></i>
            </div>
            <small class="password-warning"><i class="fas fa-info-circle"></i> Password is case-sensitive</small>
            
            <button type="submit" class="btn-submit">Login to Dashboard <i class="fas fa-sign-in-alt ml-2"></i></button>

            <div class="toggle-mode">
                New Faculty Member? <button type="button" onclick="switchMode('register')">Register Here</button>
            </div>
        </form>

        <!-- === FACULTY REGISTRATION FORM === -->
        <form id="registerForm" action="faculty_auth.php" method="POST" class="<?php echo ($default_mode === 'login' ? 'hidden' : ''); ?>">
            <div class="auth-header">
                <h2>Faculty Registration</h2>
                <p>Create your teaching profile.</p>
            </div>
            
            <input type="hidden" name="action" value="register">
            
            <div class="input-group">
                <i class="fas fa-user-tie field-icon"></i>
                <input type="text" name="name" required placeholder="Full Name (e.g., Dr. Smith)">
            </div>

            <div class="input-group">
                <i class="fas fa-envelope field-icon"></i>
                <input type="email" name="email" required placeholder="Official Email Address">
            </div>
            
            <div class="input-group">
                <i class="fas fa-phone-alt field-icon"></i>
                <input type="tel" name="phone" required placeholder="Phone Number">
            </div>
            
            <div class="input-group">
                <i class="fas fa-chalkboard-teacher field-icon"></i>
                <input type="text" name="subject" required placeholder="Subject Taught (e.g., DBMS)">
            </div>

            <!-- Password -->
            <div class="input-group">
                <i class="fas fa-lock field-icon"></i>
                <input type="password" name="password" id="regPass" required placeholder="Create Password">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('regPass', this)"></i>
            </div>
            <small class="password-warning"><i class="fas fa-info-circle"></i> Password is case-sensitive</small>
            
            <!-- Confirm Password -->
            <div class="input-group" style="margin-top: 15px;">
                <i class="fas fa-check-double field-icon"></i>
                <input type="password" name="confirm_password" id="confPass" required placeholder="Confirm Password">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('confPass', this)"></i>
            </div>
            
            <button type="submit" class="btn-submit" style="background-color: #27ae60;">Register Profile</button>

            <div class="toggle-mode">
                Already have an account? <button type="button" onclick="switchMode('login')">Login Here</button>
            </div>
        </form>

    </div>
</div>

<script>
    // Toggle Password Visibility
    function togglePassword(inputId, icon) {
        const input = document.getElementById(inputId);
        if (input.type === "password") {
            input.type = "text";
            icon.classList.remove("fa-eye");
            icon.classList.add("fa-eye-slash");
        } else {
            input.type = "password";
            icon.classList.remove("fa-eye-slash");
            icon.classList.add("fa-eye");
        }
    }

    // Switch between Login and Register Forms with Animation
    function switchMode(mode) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const card = document.querySelector('.auth-card');

        // Animation start
        card.style.opacity = '0';
        card.style.transform = 'translateY(10px)';
        
        setTimeout(() => {
            if (mode === 'login') {
                loginForm.classList.remove('hidden');
                registerForm.classList.add('hidden');
            } else {
                registerForm.classList.remove('hidden');
                loginForm.classList.add('hidden');
            }
            // Animation end
            card.style.opacity = '1';
            card.style.transform = 'translateY(0)';
        }, 300);
    }
    
    // Initial Load Logic
    document.addEventListener('DOMContentLoaded', function() {
        const defaultMode = '<?php echo $default_mode; ?>';
        if(defaultMode === 'register') {
            document.getElementById('registerForm').classList.remove('hidden');
            document.getElementById('loginForm').classList.add('hidden');
        }
    });
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>