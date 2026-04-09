<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Đăng ký thi đấu — F1 Championship';
$message = '';
$messageType = '';

// Get all stages
$stages = $conn->query("
    SELECT s.id, s.stage_code, s.name, s.location, s.race_date, s.number_laps, t.name as tournament_name
    FROM stage s
    JOIN tournament t ON s.tournament_id = t.id
    ORDER BY s.stage_order ASC
");

// Get all teams
$teams = $conn->query("SELECT id, team_code, name, brand FROM team ORDER BY name ASC");

$selectedStage = $_GET['stage_id'] ?? $_POST['stage_id'] ?? '';
$selectedTeam = $_GET['team_id'] ?? $_POST['team_id'] ?? '';
$drivers = [];

// Load drivers for selected team
if ($selectedTeam) {
    $stmt = $conn->prepare("
        SELECT c.id as contract_id, r.id as racer_id, r.driver_code, r.name, r.nationality, r.status
        FROM contract c
        JOIN racer r ON c.racer_id = r.id
        WHERE c.team_id = ?
          AND c.start_date <= CURDATE()
          AND c.end_date >= CURDATE()
        ORDER BY r.name ASC
    ");
    $stmt->bind_param("i", $selectedTeam);
    $stmt->execute();
    $drivers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'register') {
    $stageId = intval($_POST['stage_id']);
    $selectedContracts = $_POST['contracts'] ?? [];

    if (empty($stageId)) {
        $message = 'Vui lòng chọn chặng đua.';
        $messageType = 'error';
    } elseif (count($selectedContracts) < 1 || count($selectedContracts) > 2) {
        $message = 'Vui lòng chọn 1 hoặc 2 tay đua để đăng ký.';
        $messageType = 'error';
    } else {
        // START TRANSACTION
        $conn->begin_transaction();
        try {
            $success = true;
            $errorMsg = '';

            foreach ($selectedContracts as $contractId) {
                $contractId = intval($contractId);

                // Call stored procedure to validate
                $stmt = $conn->prepare("CALL sp_validate_registration(?, ?, @is_valid, @msg)");
                $stmt->bind_param("ii", $stageId, $contractId);
                $stmt->execute();
                $stmt->close();

                // Get output parameters
                $validResult = $conn->query("SELECT @is_valid as is_valid, @msg as msg")->fetch_assoc();

                if (!$validResult['is_valid']) {
                    $success = false;
                    $errorMsg = $validResult['msg'];
                    break;
                }

                // Insert registration
                $stmt = $conn->prepare("INSERT INTO registration (stage_id, contract_id) VALUES (?, ?)");
                $stmt->bind_param("ii", $stageId, $contractId);
                $stmt->execute();
                $stmt->close();
            }

            if ($success) {
                $conn->commit();
                $message = 'Đăng ký thi đấu thành công! Đã đăng ký ' . count($selectedContracts) . ' tay đua.';
                $messageType = 'success';
            } else {
                $conn->rollback();
                $message = 'Đăng ký thất bại: ' . $errorMsg;
                $messageType = 'error';
            }
        } catch (Exception $e) {
            $conn->rollback();
            $message = 'Lỗi hệ thống: ' . $e->getMessage();
            $messageType = 'error';
        }
    }
}

require_once 'includes/header.php';
?>

<div class="page-container">
    <h1 class="page-title"><i class="fas fa-user-plus"></i> Đăng Ký Thi Đấu</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Step 1: Select Race and Team -->
    <form method="GET" action="register.php">
        <div class="filter-bar">
            <div class="form-group">
                <label for="stage_id">Chặng đua</label>
                <select name="stage_id" id="stage_id" onchange="this.form.submit()">
                    <option value="">— Chọn chặng đua —</option>
                    <?php
                    $stages->data_seek(0);
                    while ($s = $stages->fetch_assoc()):
                    ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $selectedStage == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['name'] . ' — ' . $s['location'] . ' (' . date('d/m/Y', strtotime($s['race_date'])) . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="team_id">Đội đua</label>
                <select name="team_id" id="team_id" onchange="this.form.submit()">
                    <option value="">— Chọn đội đua —</option>
                    <?php
                    $teams->data_seek(0);
                    while ($t = $teams->fetch_assoc()):
                    ?>
                        <option value="<?php echo $t['id']; ?>" <?php echo $selectedTeam == $t['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($t['name'] . ' (' . $t['brand'] . ')'); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </form>

    <!-- Step 2: Display drivers with checkboxes -->
    <?php if ($selectedStage && $selectedTeam && !empty($drivers)): ?>
        <form method="POST" action="register.php" id="register-form">
            <input type="hidden" name="action" value="register">
            <input type="hidden" name="stage_id" value="<?php echo $selectedStage; ?>">
            <input type="hidden" name="team_id" value="<?php echo $selectedTeam; ?>">

            <div class="data-table-wrapper">
                <div style="padding: 16px 20px; border-bottom: 1px solid var(--border-color);">
                    <h3 style="font-family: var(--font-display); font-size: 0.85rem; color: var(--text-secondary); letter-spacing: 1px;">
                        CHỌN TAY ĐUA (Tối đa 2 người)
                    </h3>
                </div>
                <ul class="driver-list">
                    <?php foreach ($drivers as $driver): ?>
                        <?php
                        // Check if already registered
                        $checkStmt = $conn->prepare("SELECT COUNT(*) as cnt FROM registration WHERE stage_id = ? AND contract_id = ?");
                        $checkStmt->bind_param("ii", $selectedStage, $driver['contract_id']);
                        $checkStmt->execute();
                        $isRegistered = $checkStmt->get_result()->fetch_assoc()['cnt'] > 0;
                        $checkStmt->close();
                        ?>
                        <li class="driver-item">
                            <input type="checkbox"
                                   class="driver-checkbox"
                                   name="contracts[]"
                                   value="<?php echo $driver['contract_id']; ?>"
                                   id="driver-<?php echo $driver['contract_id']; ?>"
                                   <?php echo $isRegistered ? 'disabled checked' : ''; ?>
                                   <?php echo $driver['status'] !== 'Active' ? 'disabled' : ''; ?>>
                            <label for="driver-<?php echo $driver['contract_id']; ?>">
                                <span class="driver-name"><?php echo htmlspecialchars($driver['name']); ?></span>
                                <span class="driver-code">
                                    <?php echo htmlspecialchars($driver['driver_code']); ?> — <?php echo htmlspecialchars($driver['nationality']); ?>
                                    <?php if ($isRegistered): ?>
                                        <span class="status-badge status-finished" style="margin-left: 8px;">Đã đăng ký</span>
                                    <?php endif; ?>
                                    <?php if ($driver['status'] !== 'Active'): ?>
                                        <span class="status-badge status-dnf" style="margin-left: 8px;"><?php echo $driver['status']; ?></span>
                                    <?php endif; ?>
                                </span>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu đăng ký
                </button>
                <a href="register.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Làm mới
                </a>
            </div>
        </form>
    <?php elseif ($selectedStage && $selectedTeam && empty($drivers)): ?>
        <div class="empty-state">
            <i class="fas fa-user-slash"></i>
            <p>Không tìm thấy tay đua nào có hợp đồng hợp lệ với đội này.</p>
        </div>
    <?php elseif (!$selectedStage || !$selectedTeam): ?>
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            <p>Vui lòng chọn chặng đua và đội đua để xem danh sách tay đua.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
