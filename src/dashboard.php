<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Trang chủ — F1 Championship';
require_once 'includes/header.php';

// Get some stats
$stageCount = $conn->query("SELECT COUNT(*) as cnt FROM stage")->fetch_assoc()['cnt'];
$teamCount = $conn->query("SELECT COUNT(*) as cnt FROM team")->fetch_assoc()['cnt'];
$racerCount = $conn->query("SELECT COUNT(*) as cnt FROM racer")->fetch_assoc()['cnt'];
$resultCount = $conn->query("SELECT COUNT(*) as cnt FROM result")->fetch_assoc()['cnt'];
?>

<div class="page-container">
    <div class="dashboard-header">
        <h1>Hệ Thống Quản Lý Giải Đua F1</h1>
        <p>Xin chào, <strong><?php echo htmlspecialchars(getCurrentUser()['full_name']); ?></strong> — Chọn chức năng bên dưới để bắt đầu</p>
    </div>

    <div class="stats-row">
        <div class="stat-card">
            <div class="stat-value"><?php echo $stageCount; ?></div>
            <div class="stat-label">Chặng đua</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $teamCount; ?></div>
            <div class="stat-label">Đội đua</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $racerCount; ?></div>
            <div class="stat-label">Tay đua</div>
        </div>
        <div class="stat-card">
            <div class="stat-value"><?php echo $resultCount; ?></div>
            <div class="stat-label">Kết quả đã ghi</div>
        </div>
    </div>

    <div class="dashboard-grid">
        <a href="register.php" class="dash-card" id="card-register">
            <div class="dash-card-icon"><i class="fas fa-user-plus"></i></div>
            <h3>Đăng Ký Thi Đấu</h3>
            <p>Chọn chặng đua, chọn đội đua và đăng ký tối đa 2 tay đua tham gia.</p>
        </a>
        <a href="update_results.php" class="dash-card" id="card-results">
            <div class="dash-card-icon"><i class="fas fa-flag-checkered"></i></div>
            <h3>Cập Nhật Kết Quả</h3>
            <p>Nhập thời gian, số vòng đua và trạng thái về đích cho từng tay đua.</p>
        </a>
        <a href="racer_standings.php" class="dash-card" id="card-racer">
            <div class="dash-card-icon"><i class="fas fa-trophy"></i></div>
            <h3>Bảng Xếp Hạng Tay Đua</h3>
            <p>Xem bảng xếp hạng tay đua theo tổng điểm và thời gian tích lũy.</p>
        </a>
        <a href="team_rankings.php" class="dash-card" id="card-team">
            <div class="dash-card-icon"><i class="fas fa-users"></i></div>
            <h3>Bảng Xếp Hạng Đội Đua</h3>
            <p>Xem bảng xếp hạng đội đua với tổng điểm và chi tiết từng chặng.</p>
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
