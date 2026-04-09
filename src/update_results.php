<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Cập nhật kết quả — F1 Championship';
$message = '';
$messageType = '';

// Get all stages
$stages = $conn->query("
    SELECT s.id, s.stage_code, s.name, s.location, s.race_date, s.number_laps, s.stage_order
    FROM stage s
    ORDER BY s.stage_order ASC
");

$selectedStage = $_GET['stage_id'] ?? $_POST['stage_id'] ?? '';
$registeredRacers = [];

// Load registered racers for selected stage
if ($selectedStage) {
    $stmt = $conn->prepare("
        SELECT
            reg.id as registration_id,
            c.id as contract_id,
            r.name as racer_name,
            r.driver_code,
            t.name as team_name,
            t.team_code,
            res.id as result_id,
            res.finish_time,
            res.laps_completed,
            res.finish_rank,
            res.score,
            res.status as result_status
        FROM registration reg
        JOIN contract c ON reg.contract_id = c.id
        JOIN racer r ON c.racer_id = r.id
        JOIN team t ON c.team_id = t.id
        LEFT JOIN result res ON res.stage_id = reg.stage_id AND res.contract_id = c.id
        WHERE reg.stage_id = ?
        ORDER BY t.name ASC, r.name ASC
    ");
    $stmt->bind_param("i", $selectedStage);
    $stmt->execute();
    $registeredRacers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}

// Handle result form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'save_results') {
    $stageId = intval($_POST['stage_id']);
    $contractIds = $_POST['contract_id'] ?? [];
    $finishTimes = $_POST['finish_time'] ?? [];
    $lapsCompleted = $_POST['laps_completed'] ?? [];
    $statuses = $_POST['status'] ?? [];
    $userId = getCurrentUser()['id'];

    // Get stage info for lap count
    $stageInfo = $conn->prepare("SELECT number_laps FROM stage WHERE id = ?");
    $stageInfo->bind_param("i", $stageId);
    $stageInfo->execute();
    $stageLaps = $stageInfo->get_result()->fetch_assoc()['number_laps'];
    $stageInfo->close();

    $conn->begin_transaction();
    try {
        // First, collect all finished racers to calculate rank
        $finishedRacers = [];
        $otherRacers = [];

        for ($i = 0; $i < count($contractIds); $i++) {
            $entry = [
                'contract_id' => intval($contractIds[$i]),
                'finish_time' => trim($finishTimes[$i]),
                'laps_completed' => intval($lapsCompleted[$i]),
                'status' => $statuses[$i],
            ];

            if ($entry['status'] === 'Finished' && !empty($entry['finish_time'])) {
                $finishedRacers[] = $entry;
            } else {
                $otherRacers[] = $entry;
            }
        }

        // Sort finished racers by laps (desc) then time (asc)
        usort($finishedRacers, function($a, $b) {
            if ($b['laps_completed'] !== $a['laps_completed']) {
                return $b['laps_completed'] - $a['laps_completed'];
            }
            return strcmp($a['finish_time'], $b['finish_time']);
        });

        // Assign ranks
        $rank = 1;
        foreach ($finishedRacers as &$racer) {
            $racer['finish_rank'] = $rank++;
        }
        unset($racer);

        // Merge back
        $allRacers = array_merge($finishedRacers, $otherRacers);

        // Insert or update results
        foreach ($allRacers as $racer) {
            $finishRank = $racer['finish_rank'] ?? null;
            $finishTime = !empty($racer['finish_time']) ? $racer['finish_time'] : null;

            // Check if result already exists
            $checkStmt = $conn->prepare("SELECT id FROM result WHERE stage_id = ? AND contract_id = ?");
            $checkStmt->bind_param("ii", $stageId, $racer['contract_id']);
            $checkStmt->execute();
            $existing = $checkStmt->get_result()->fetch_assoc();
            $checkStmt->close();

            if ($existing) {
                // UPDATE (trigger will auto-calculate score)
                $stmt = $conn->prepare("
                    UPDATE result
                    SET finish_time = ?, laps_completed = ?, finish_rank = ?, status = ?, updated_by = ?
                    WHERE stage_id = ? AND contract_id = ?
                ");
                $stmt->bind_param("siisiii",
                    $finishTime, $racer['laps_completed'], $finishRank,
                    $racer['status'], $userId, $stageId, $racer['contract_id']
                );
            } else {
                // INSERT (trigger will auto-calculate score)
                $stmt = $conn->prepare("
                    INSERT INTO result (stage_id, contract_id, finish_time, laps_completed, finish_rank, status, updated_by)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("iisiisi",
                    $stageId, $racer['contract_id'], $finishTime,
                    $racer['laps_completed'], $finishRank, $racer['status'], $userId
                );
            }
            $stmt->execute();
            $stmt->close();
        }

        $conn->commit();
        $message = 'Cập nhật kết quả thành công! Điểm số đã được tính tự động bởi Trigger.';
        $messageType = 'success';

        // Reload data
        $stmt = $conn->prepare("
            SELECT
                reg.id as registration_id, c.id as contract_id,
                r.name as racer_name, r.driver_code,
                t.name as team_name, t.team_code,
                res.id as result_id, res.finish_time, res.laps_completed,
                res.finish_rank, res.score, res.status as result_status
            FROM registration reg
            JOIN contract c ON reg.contract_id = c.id
            JOIN racer r ON c.racer_id = r.id
            JOIN team t ON c.team_id = t.id
            LEFT JOIN result res ON res.stage_id = reg.stage_id AND res.contract_id = c.id
            WHERE reg.stage_id = ?
            ORDER BY COALESCE(res.finish_rank, 999) ASC
        ");
        $stmt->bind_param("i", $stageId);
        $stmt->execute();
        $registeredRacers = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        $message = 'Lỗi: ' . $e->getMessage();
        $messageType = 'error';
    }
}

require_once 'includes/header.php';
?>

<div class="page-container">
    <h1 class="page-title"><i class="fas fa-flag-checkered"></i> Cập Nhật Kết Quả</h1>

    <?php if ($message): ?>
        <div class="alert alert-<?php echo $messageType; ?>">
            <i class="fas fa-<?php echo $messageType === 'success' ? 'check-circle' : 'exclamation-circle'; ?>"></i>
            <?php echo htmlspecialchars($message); ?>
        </div>
    <?php endif; ?>

    <!-- Select Race -->
    <form method="GET" action="update_results.php">
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
        </div>
    </form>

    <?php if ($selectedStage && !empty($registeredRacers)): ?>
        <?php
        $stageInfoQ = $conn->prepare("SELECT name, number_laps FROM stage WHERE id = ?");
        $stageInfoQ->bind_param("i", $selectedStage);
        $stageInfoQ->execute();
        $stageData = $stageInfoQ->get_result()->fetch_assoc();
        $stageInfoQ->close();
        ?>

        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            Chặng: <strong><?php echo htmlspecialchars($stageData['name']); ?></strong> — Số vòng đua: <strong><?php echo $stageData['number_laps']; ?></strong> vòng.
            Điểm số sẽ được <strong>Trigger</strong> tự động tính khi lưu.
        </div>

        <form method="POST" action="update_results.php?stage_id=<?php echo $selectedStage; ?>" id="result-form">
            <input type="hidden" name="action" value="save_results">
            <input type="hidden" name="stage_id" value="<?php echo $selectedStage; ?>">

            <div class="data-table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Tay đua</th>
                            <th>Đội đua</th>
                            <th>Thời gian (HH:mm:ss)</th>
                            <th>Số vòng</th>
                            <th>Trạng thái</th>
                            <?php if ($registeredRacers[0]['result_id']): ?>
                                <th>Hạng</th>
                                <th>Điểm</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($registeredRacers as $idx => $racer): ?>
                            <tr>
                                <td><?php echo $idx + 1; ?></td>
                                <td>
                                    <strong><?php echo htmlspecialchars($racer['racer_name']); ?></strong>
                                    <br><span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo $racer['driver_code']; ?></span>
                                    <input type="hidden" name="contract_id[]" value="<?php echo $racer['contract_id']; ?>">
                                </td>
                                <td>
                                    <?php echo htmlspecialchars($racer['team_name']); ?>
                                    <br><span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo $racer['team_code']; ?></span>
                                </td>
                                <td>
                                    <div class="result-input">
                                        <input type="text" name="finish_time[]" class="time-input"
                                               placeholder="01:32:44"
                                               value="<?php echo htmlspecialchars($racer['finish_time'] ?? ''); ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="result-input">
                                        <input type="number" name="laps_completed[]" min="0"
                                               max="<?php echo $stageData['number_laps']; ?>"
                                               value="<?php echo $racer['laps_completed'] ?? $stageData['number_laps']; ?>">
                                    </div>
                                </td>
                                <td>
                                    <div class="result-input">
                                        <select name="status[]">
                                            <option value="Finished" <?php echo ($racer['result_status'] ?? '') === 'Finished' ? 'selected' : ''; ?>>Về đích</option>
                                            <option value="DNF" <?php echo ($racer['result_status'] ?? '') === 'DNF' ? 'selected' : ''; ?>>DNF</option>
                                            <option value="Accident" <?php echo ($racer['result_status'] ?? '') === 'Accident' ? 'selected' : ''; ?>>Tai nạn</option>
                                        </select>
                                    </div>
                                </td>
                                <?php if ($racer['result_id']): ?>
                                    <td>
                                        <?php
                                        $rankClass = 'rank-default';
                                        if ($racer['finish_rank'] == 1) $rankClass = 'rank-1';
                                        elseif ($racer['finish_rank'] == 2) $rankClass = 'rank-2';
                                        elseif ($racer['finish_rank'] == 3) $rankClass = 'rank-3';
                                        ?>
                                        <?php if ($racer['finish_rank']): ?>
                                            <span class="rank-badge <?php echo $rankClass; ?>"><?php echo $racer['finish_rank']; ?></span>
                                        <?php else: ?>
                                            <span style="color: var(--text-muted);">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="points-display"><?php echo $racer['score']; ?></span>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 12px;">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Lưu kết quả
                </button>
                <a href="update_results.php" class="btn btn-secondary">
                    <i class="fas fa-redo"></i> Chọn chặng khác
                </a>
            </div>
        </form>

    <?php elseif ($selectedStage && empty($registeredRacers)): ?>
        <div class="empty-state">
            <i class="fas fa-clipboard-list"></i>
            <p>Chưa có tay đua nào đăng ký cho chặng đua này.<br>Vui lòng <a href="register.php">đăng ký tay đua</a> trước.</p>
        </div>
    <?php elseif (!$selectedStage): ?>
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            <p>Vui lòng chọn chặng đua để nhập kết quả.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
