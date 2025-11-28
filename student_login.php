<?php
session_start();

// 1. Database connection
require_once __DIR__ . '/db_connect.php'; 

// Check if already logged in
if (isset($_SESSION['user_id']) && $_SESSION['user_role'] === 'student') {
    $redirect_url = $_SESSION['redirect_to'] ?? 'index.php';
    unset($_SESSION['redirect_to']); 
    header("Location: $redirect_url"); 
    exit();
}

$page_title = 'Student Portal Access';
$message = isset($_GET['message']) ? htmlspecialchars($_GET['message']) : '';
$status = isset($_GET['status']) ? htmlspecialchars($_GET['status']) : '';
$default_mode = (isset($_GET['mode']) && $_GET['mode'] === 'register') ? 'register' : 'login';
?>

<!-- === Header Inclusion === -->
<?php require_once __DIR__ . '/includes/header.php'; ?>

<!-- FontAwesome for Icons (Ensure this is loaded) -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* === Professional CSS Styles === */
    :root {
        --primary-color: #2563eb; /* Royal Blue */
        --primary-hover: #1d4ed8;
        --bg-color: #f1f5f9;
        --text-color: #334155;
        --input-border: #cbd5e1;
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
        max-width: 480px;
        padding: 40px;
        border-radius: 16px;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        border: 1px solid #e2e8f0;
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
    }

    .input-group input {
        width: 100%;
        padding: 12px 15px 12px 45px; /* Space for left icon */
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        color: var(--text-color);
    }

    .input-group input:focus {
        border-color: var(--primary-color);
        background: #fff;
        outline: none;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
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
    .toggle-password:hover { color: var(--primary-color); }

    /* Helper Messages */
    .password-warning {
        font-size: 0.8rem;
        color: #d97706; /* Amber color */
        margin-top: 5px;
        display: block;
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
    .btn-submit:hover { background: var(--primary-hover); box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2); }

    /* Toggle Link */
    .toggle-mode { text-align: center; margin-top: 25px; padding-top: 20px; border-top: 1px solid #f1f5f9; font-size: 0.95rem; color: #64748b; }
    .toggle-mode button { background: none; border: none; color: var(--primary-color); font-weight: 700; cursor: pointer; text-decoration: none; padding: 0; font-size: 0.95rem; }
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

        <!-- === LOGIN FORM === -->
        <form id="loginForm" action="student_auth.php" method="POST" class="<?php echo ($default_mode === 'register' ? 'hidden' : ''); ?>">
            <div class="auth-header">
                <h2>Student Login</h2>
                <p>Welcome back! Please enter your details.</p>
            </div>
            
            <input type="hidden" name="action" value="login">
            
            <div class="input-group">
                <i class="fas fa-id-card field-icon"></i>
                <input type="text" name="reg_number" required placeholder="Registration Number">
            </div>
            
            <div class="input-group">
                <i class="fas fa-lock field-icon"></i>
                <input type="password" name="password" id="loginPass" required placeholder="Password">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('loginPass', this)"></i>
            </div>
            <small class="password-warning"><i class="fas fa-info-circle"></i> Password is case-sensitive</small>
            
            <button type="submit" class="btn-submit">Sign In <i class="fas fa-arrow-right ml-2"></i></button>

            <div class="toggle-mode">
                Don't have an account? <button type="button" onclick="switchMode('register')">Register Now</button>
            </div>
        </form>

        <!-- === REGISTRATION FORM === -->
        <form id="registerForm" action="student_auth.php" method="POST" class="<?php echo ($default_mode === 'login' ? 'hidden' : ''); ?>">
            <div class="auth-header">
                <h2>Create Account</h2>
                <p>Register once to access study materials.</p>
            </div>
            
            <input type="hidden" name="action" value="register">
            
            <div class="input-group">
                <i class="fas fa-user field-icon"></i>
                <input type="text" name="name" required placeholder="Full Name">
            </div>

            <div class="input-group">
                <i class="fas fa-id-card field-icon"></i>
                <input type="text" name="reg_number" required placeholder="Registration Number (Login ID)">
            </div>
            
            <div class="input-group">
                <i class="fas fa-envelope field-icon"></i>
                <input type="email" name="email" required placeholder="Email Address">
            </div>
            
            <div class="input-group">
                <i class="fas fa-phone field-icon"></i>
                <input type="tel" name="phone" placeholder="Phone (Optional)">
            </div>
            
            <!-- Password Field -->
            <div class="input-group">
                <i class="fas fa-lock field-icon"></i>
                <input type="password" name="password" id="regPass" required placeholder="Create Password">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('regPass', this)"></i>
            </div>
            <small class="password-warning"><i class="fas fa-info-circle"></i> Password is case-sensitive</small>

            <!-- Confirm Password Field -->
            <div class="input-group" style="margin-top: 15px;">
                <i class="fas fa-check-circle field-icon"></i>
                <input type="password" name="confirm_password" id="confPass" required placeholder="Confirm Password">
                <i class="fas fa-eye toggle-password" onclick="togglePassword('confPass', this)"></i>
            </div>
            
            <button type="submit" class="btn-submit" style="background-color: #059669;">Register Account</button>

            <div class="toggle-mode">
                Already registered? <button type="button" onclick="switchMode('login')">Login Here</button>
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

    // Switch between Login and Register Forms
    function switchMode(mode) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const card = document.querySelector('.auth-card');

        // Add a slight animation effect
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
            // Restore visibility
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