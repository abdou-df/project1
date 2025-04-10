<?php
// Check if user is logged in and has admin privileges
session_start();
require_once '../config/config.php';
require_once '../includes/functions.php';
require_once '../includes/auth.php';

// Ensure user has admin privileges
if (!isAdmin()) {
    header('Location: index.php?page=403');
    exit;
}

// Initialize variables
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = isset($_POST['username']) ? sanitize($_POST['username']) : '';
    $email = isset($_POST['email']) ? sanitize($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';
    $confirm_password = isset($_POST['confirm_password']) ? $_POST['confirm_password'] : '';
    $first_name = isset($_POST['first_name']) ? sanitize($_POST['first_name']) : '';
    $last_name = isset($_POST['last_name']) ? sanitize($_POST['last_name']) : '';
    $role = isset($_POST['role']) ? sanitize($_POST['role']) : '';
    $phone = isset($_POST['phone']) ? sanitize($_POST['phone']) : '';
    $address = isset($_POST['address']) ? sanitize($_POST['address']) : '';
    $city = isset($_POST['city']) ? sanitize($_POST['city']) : '';
    $state = isset($_POST['state']) ? sanitize($_POST['state']) : '';
    $zip_code = isset($_POST['zip_code']) ? sanitize($_POST['zip_code']) : '';
    $status = isset($_POST['status']) ? sanitize($_POST['status']) : STATUS_ACTIVE;
    
    // Validate form data
    if (empty($username)) {
        $errors[] = 'Username is required';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must be at least 3 characters long';
    }
    
    if (empty($email)) {
        $errors[] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Invalid email format';
    }
    
    if (empty($password)) {
        $errors[] = 'Password is required';
    } elseif (strlen($password) < PASSWORD_MIN_LENGTH) {
        $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters long';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match';
    }
    
    if (empty($first_name)) {
        $errors[] = 'First name is required';
    }
    
    if (empty($last_name)) {
        $errors[] = 'Last name is required';
    }
    
    if (empty($role)) {
        $errors[] = 'Role is required';
    }
    
    if (empty($phone)) {
        $errors[] = 'Phone number is required';
    }
    
    // Handle profile image upload
    $profile_image = '';
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        $file_type = $_FILES['profile_image']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $errors[] = 'Invalid file type. Only JPG, PNG and GIF images are allowed';
        } elseif ($_FILES['profile_image']['size'] > MAX_FILE_SIZE) {
            $errors[] = 'File size too large. Maximum size is ' . (MAX_FILE_SIZE / 1024 / 1024) . 'MB';
        } else {
            // Generate unique filename
            $ext = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid('user_') . '.' . $ext;
            $upload_path = '../uploads/users/' . $filename;
            
            // Create directory if it doesn't exist
            if (!file_exists('../uploads/users')) {
                mkdir('../uploads/users', 0777, true);
            }
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                $profile_image = 'uploads/users/' . $filename;
            } else {
                $errors[] = 'Failed to upload profile image';
            }
        }
    }
    
    // If no errors, create user
    if (empty($errors)) {
        // In a real application, you would:
        // 1. Hash the password
        // 2. Check if username/email already exists
        // 3. Insert user data into database
        // 4. Send welcome email
        
        // For demonstration, we'll just show success message
        $success = true;
        
        if ($success) {
            // Redirect to users page with success message
            $_SESSION['success'] = 'User created successfully';
            header('Location: index.php?page=users');
            exit;
        }
    }
}
?>

<!-- Page header -->
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <h2 class="h3 text-dark">Add New User</h2>
    <a href="index.php?page=users" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i> Back to Users
    </a>
</div>

<!-- Add user form -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="mb-0 text-primary"><i class="fas fa-user-plus me-2"></i>User Information</h5>
    </div>
    <div class="card-body">
        <?php if (!empty($errors)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <ul class="mb-0">
                <?php foreach ($errors as $error): ?>
                <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        <?php endif; ?>
        
        <form method="post" action="" enctype="multipart/form-data" class="needs-validation" novalidate>
            <div class="row g-4">
                <!-- Account Details -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-shield-alt me-2 text-primary"></i>Account Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="username" class="form-label small">Username <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-user"></i></span>
                                    <input type="text" class="form-control" id="username" name="username" value="<?php echo isset($username) ? $username : ''; ?>" required>
                                </div>
                                <div class="form-text small">Username must be at least 3 characters long</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="email" class="form-label small">Email <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope"></i></span>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo isset($email) ? $email : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="password" class="form-label small">Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="form-text small">Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters long</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="confirm_password" class="form-label small">Confirm Password <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-lock"></i></span>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                    <button class="btn btn-outline-secondary" type="button" id="toggleConfirmPassword">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label small">Role <span class="text-danger">*</span></label>
                                <select class="form-select" id="role" name="role" required>
                                    <option value="">Select Role</option>
                                    <option value="<?php echo ROLE_ADMIN; ?>" <?php echo (isset($role) && $role === ROLE_ADMIN) ? 'selected' : ''; ?>>Admin</option>
                                    <option value="<?php echo ROLE_MANAGER; ?>" <?php echo (isset($role) && $role === ROLE_MANAGER) ? 'selected' : ''; ?>>Manager</option>
                                    <option value="<?php echo ROLE_EMPLOYEE; ?>" <?php echo (isset($role) && $role === ROLE_EMPLOYEE) ? 'selected' : ''; ?>>Employee</option>
                                    <option value="<?php echo ROLE_CUSTOMER; ?>" <?php echo (isset($role) && $role === ROLE_CUSTOMER) ? 'selected' : ''; ?>>Customer</option>
                                </select>
                            </div>
                            
                            <div class="mb-3">
                                <label for="status" class="form-label small">Status</label>
                                <div class="d-flex">
                                    <div class="form-check form-switch me-3">
                                        <input class="form-check-input" type="radio" name="status" id="statusActive" value="<?php echo STATUS_ACTIVE; ?>" <?php echo (!isset($status) || $status === STATUS_ACTIVE) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="statusActive">Active</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" type="radio" name="status" id="statusInactive" value="<?php echo STATUS_INACTIVE; ?>" <?php echo (isset($status) && $status === STATUS_INACTIVE) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="statusInactive">Inactive</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Personal Details -->
                <div class="col-md-6">
                    <div class="card h-100 border-0 shadow-sm">
                        <div class="card-header bg-white">
                            <h6 class="mb-0"><i class="fas fa-address-card me-2 text-primary"></i>Personal Details</h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-4 text-center">
                                <div class="avatar-upload">
                                    <div class="avatar-preview rounded-circle mx-auto mb-3" style="width: 100px; height: 100px; background-image: url('../assets/images/default-user.png'); background-size: cover;"></div>
                                    <label for="profile_image" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-upload me-2"></i>Upload Photo
                                    </label>
                                    <input type="file" class="d-none" id="profile_image" name="profile_image" accept="image/*" onchange="previewImage(this)">
                                    <div class="form-text small mt-2">Maximum file size: <?php echo MAX_FILE_SIZE / 1024 / 1024; ?>MB</div>
                                </div>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label for="first_name" class="form-label small">First Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="first_name" name="first_name" value="<?php echo isset($first_name) ? $first_name : ''; ?>" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="last_name" class="form-label small">Last Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="last_name" name="last_name" value="<?php echo isset($last_name) ? $last_name : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="phone" class="form-label small">Phone <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone"></i></span>
                                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo isset($phone) ? $phone : ''; ?>" required>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="address" class="form-label small">Address</label>
                                <textarea class="form-control" id="address" name="address" rows="2"><?php echo isset($address) ? $address : ''; ?></textarea>
                            </div>
                            
                            <div class="row g-3">
                                <div class="col-md-5 mb-3">
                                    <label for="city" class="form-label small">City</label>
                                    <input type="text" class="form-control" id="city" name="city" value="<?php echo isset($city) ? $city : ''; ?>">
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label for="state" class="form-label small">State</label>
                                    <input type="text" class="form-control" id="state" name="state" value="<?php echo isset($state) ? $state : ''; ?>">
                                </div>
                                
                                <div class="col-md-3 mb-3">
                                    <label for="zip_code" class="form-label small">Zip Code</label>
                                    <input type="text" class="form-control" id="zip_code" name="zip_code" value="<?php echo isset($zip_code) ? $zip_code : ''; ?>">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="reset" class="btn btn-light">
                    <i class="fas fa-redo me-2"></i>Reset
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save User
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Form validation
(function () {
    'use strict'

    // Fetch all the forms we want to apply custom Bootstrap validation styles to
    var forms = document.querySelectorAll('.needs-validation')

    // Loop over them and prevent submission
    Array.prototype.slice.call(forms)
        .forEach(function (form) {
            form.addEventListener('submit', function (event) {
                if (!form.checkValidity()) {
                    event.preventDefault()
                    event.stopPropagation()
                }

                form.classList.add('was-validated')
            }, false)
        })
})()

// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    togglePasswordVisibility('password', this);
});

document.getElementById('toggleConfirmPassword').addEventListener('click', function() {
    togglePasswordVisibility('confirm_password', this);
});

function togglePasswordVisibility(inputId, button) {
    const input = document.getElementById(inputId);
    const icon = button.querySelector('i');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

// Profile image preview
function previewImage(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        
        reader.onload = function(e) {
            document.querySelector('.avatar-preview').style.backgroundImage = 'url(' + e.target.result + ')';
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
