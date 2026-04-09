<?php
$user = getCurrentUser();
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Hệ thống quản lý giải đua xe Công thức 1 - F1 Championship Management">
    <title><?php echo $pageTitle ?? 'F1 Championship Management'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;500;600;700;800;900&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <?php if (isLoggedIn() && $currentPage !== 'index.php'): ?>
    <nav class="navbar">
        <div class="nav-brand">
            <div class="f1-logo">
                <span class="logo-f">F</span><span class="logo-1">1</span>
            </div>
            <span class="nav-title">Championship Management</span>
        </div>
        <div class="nav-links">
            <a href="dashboard.php" class="nav-link <?php echo $currentPage === 'dashboard.php' ? 'active' : ''; ?>">
                <i class="fas fa-home"></i> Trang chủ
            </a>
            <a href="register.php" class="nav-link <?php echo $currentPage === 'register.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i> Đăng ký
            </a>
            <a href="update_results.php" class="nav-link <?php echo $currentPage === 'update_results.php' ? 'active' : ''; ?>">
                <i class="fas fa-flag-checkered"></i> Kết quả
            </a>
            <a href="racer_standings.php" class="nav-link <?php echo $currentPage === 'racer_standings.php' ? 'active' : ''; ?>">
                <i class="fas fa-trophy"></i> Tay đua
            </a>
            <a href="team_rankings.php" class="nav-link <?php echo $currentPage === 'team_rankings.php' ? 'active' : ''; ?>">
                <i class="fas fa-users"></i> Đội đua
            </a>
        </div>
        <div class="nav-user">
            <span class="user-name"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($user['full_name']); ?></span>
            <a href="logout.php" class="btn-logout"><i class="fas fa-sign-out-alt"></i> Đăng xuất</a>
        </div>
    </nav>
    <?php endif; ?>
    <main class="main-content">
