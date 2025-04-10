<!DOCTYPE html>
<html lang="en" dir="ltr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?>GARAGE MASTER</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Source+Sans+Pro:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/theme.css">
    
    <!-- JavaScript Files -->
    <script src="assets/js/jquery.min.js"></script>
    <script src="assets/js/language.js"></script>
</head>
<body>
    <div class="wrapper">
        <?php if(isset($_SESSION['user_id'])): ?>
        <!-- Sidebar -->
        <?php include 'includes/sidebar.php'; ?>
        <?php endif; ?>

        <!-- Main Content -->
        <div class="main-content <?php echo !isset($_SESSION['user_id']) ? 'w-100' : ''; ?>">
            <?php if(isset($_SESSION['user_id'])): ?>
            <!-- Header -->
            <div class="header">
                <div class="d-flex align-items-center">
                    <?php if(isset($pageBack)): ?>
                    <a href="<?php echo $pageBack; ?>" class="header-back me-3">
                        <i class="fa-solid fa-arrow-left"></i>
                    </a>
                    <?php endif; ?>
                    <h1 class="page-title mb-0">
                        <?php echo isset($pageTitle) ? $pageTitle : 'Dashboard'; ?>
                    </h1>
                </div>
                 
                <div class="header-actions">
                     <!--
                    <div class="header-action">
                        <i class="fa-solid fa-gear"></i>
                    </div>
                    <div class="header-action">
                        <i class="fa-solid fa-bell"></i>
                        <span class="badge">3</span>
                    </div>
                    <div class="user-profile" id="userDropdown">
                        <div class="user-avatar">
                            <img src="assets/images/default-user.png" alt="User">
                        </div>
                    </div>
                    
                     User Dropdown -->
                    <div class="horizontal-user-menu shadow-sm" id="userDropdownMenu">
                       <!--  <div class="d-flex align-items-center justify-content-between p-3 border-bottom">
                            <div class="d-flex align-items-center">
                                <div class="user-avatar me-3">
                                    <img src="assets/images/default-user.png" alt="User" class="rounded-circle" width="40">
                                </div>
                                <div>
                                    <h6 class="mb-0">User Name</h6>
                                    <small class="text-muted">Administrator</small>
                                </div>
                            </div>
                        </div>
                        -->
                        <div class="d-flex justify-content-between p-2">
                            <a class="menu-item px-3 py-2 text-center" href="index.php?page=profile">
                                <i class="fa-solid fa-user d-block mb-1 fs-5"></i> 
                                <span data-i18n="general.profile">Profile</span>
                            </a>
                            <a class="menu-item px-3 py-2 text-center" href="index.php?page=settings">
                                <i class="fa-solid fa-gear d-block mb-1 fs-5"></i> 
                                <span data-i18n="general.settings">Settings</span>
                            </a>
                            <a class="menu-item px-3 py-2 text-center" href="logout.php">
                                <i class="fa-solid fa-right-from-bracket d-block mb-1 fs-5"></i> 
                                <span data-i18n="general.logout">Logout</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Content -->
            <div class="content">
                <div class="container-fluid p-4">

<!-- Toast notification container -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 11">
    <div id="toast-container"></div>
</div>

<!-- Theme JS will be included at the end of the body in footer.php -->
