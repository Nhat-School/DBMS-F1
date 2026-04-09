<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Bảng xếp hạng tay đua — F1 Championship';

// Get all stages
$stages = $conn->query("
    SELECT s.id, s.name, s.location, s.race_date, s.stage_order
    FROM stage s
    ORDER BY s.stage_order ASC
");

$selectedStage = $_GET['stage_id'] ?? '';
$racerDetail = $_GET['racer_id'] ?? '';
$standings = [];
$detailResults = [];

if ($selectedStage) {
    $stageId = intval($selectedStage);

    // Check if race has taken place
    $stageCheck = $conn->prepare("SELECT race_date, name FROM stage WHERE id = ?");
    $stageCheck->bind_param("i", $stageId);
    $stageCheck->execute();
    $stageInfo = $stageCheck->get_result()->fetch_assoc();
    $stageCheck->close();

    $raceDate = strtotime($stageInfo['race_date']);
    $now = time();

    if ($raceDate > $now) {
        $warning = 'Chặng đua "' . $stageInfo['name'] . '" chưa diễn ra. Vui lòng quay lại sau ngày ' . date('d/m/Y', $raceDate) . '.';
    } else {
        // Check if results exist
        $resultCheck = $conn->prepare("
            SELECT COUNT(*) as cnt FROM result res
            JOIN stage s ON res.stage_id = s.id
            WHERE s.tournament_id = (SELECT tournament_id FROM stage WHERE id = ?)
              AND s.stage_order <= (SELECT stage_order FROM stage WHERE id = ?)
        ");
        $resultCheck->bind_param("ii", $stageId, $stageId);
        $resultCheck->execute();
        $hasResults = $resultCheck->get_result()->fetch_assoc()['cnt'] > 0;
        $resultCheck->close();

        if (!$hasResults) {
            $warning = 'Chưa có kết quả cho chặng đua này. Vui lòng cập nhật kết quả trước.';
        } else {
            // Get cumulative standings up to selected stage
            $stmt = $conn->prepare("
                SELECT
                    r.id as racer_id,
                    r.driver_code,
                    r.name as racer_name,
                    r.nationality,
                    t.name as team_name,
                    COUNT(DISTINCT res.stage_id) as total_races,
                    COALESCE(SUM(res.score), 0) as total_points,
                    COALESCE(SUM(
                        CASE WHEN res.finish_time IS NOT NULL AND res.finish_time != ''
                        THEN TIME_TO_SEC(res.finish_time) ELSE 0 END
                    ), 0) as total_time_seconds
                FROM racer r
                JOIN contract c ON c.racer_id = r.id
                JOIN result res ON res.contract_id = c.id
                JOIN stage s ON res.stage_id = s.id
                JOIN team t ON c.team_id = t.id
                WHERE s.tournament_id = (SELECT tournament_id FROM stage WHERE id = ?)
                  AND s.stage_order <= (SELECT stage_order FROM stage WHERE id = ?)
                GROUP BY r.id, r.driver_code, r.name, r.nationality, t.name
                ORDER BY total_points DESC, total_time_seconds ASC
            ");
            $stmt->bind_param("ii", $stageId, $stageId);
            $stmt->execute();
            $standings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }

    // Load racer detail if requested
    if ($racerDetail) {
        $detailStmt = $conn->prepare("
            SELECT
                s.name as stage_name,
                s.stage_order,
                res.finish_rank,
                res.score,
                res.finish_time,
                res.laps_completed,
                res.status
            FROM result res
            JOIN stage s ON res.stage_id = s.id
            JOIN contract c ON res.contract_id = c.id
            WHERE c.racer_id = ?
              AND s.tournament_id = (SELECT tournament_id FROM stage WHERE id = ?)
              AND s.stage_order <= (SELECT stage_order FROM stage WHERE id = ?)
            ORDER BY s.stage_order ASC
        ");
        $detailStmt->bind_param("iii", $racerDetail, $stageId, $stageId);
        $detailStmt->execute();
        $detailResults = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $detailStmt->close();

        // Get racer name
        $racerNameStmt = $conn->prepare("SELECT name FROM racer WHERE id = ?");
        $racerNameStmt->bind_param("i", $racerDetail);
        $racerNameStmt->execute();
        $racerName = $racerNameStmt->get_result()->fetch_assoc()['name'];
        $racerNameStmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="page-container">
    <h1 class="page-title"><i class="fas fa-trophy"></i> Bảng Xếp Hạng Tay Đua</h1>

    <?php if (isset($warning)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($warning); ?>
        </div>
    <?php endif; ?>

    <!-- Select Stage -->
    <form method="GET" action="racer_standings.php">
        <div class="filter-bar">
            <div class="form-group">
                <label for="stage_id">Xem xếp hạng tính đến chặng</label>
                <select name="stage_id" id="stage_id" onchange="this.form.submit()">
                    <option value="">— Chọn chặng đua —</option>
                    <?php
                    $stages->data_seek(0);
                    while ($s = $stages->fetch_assoc()):
                    ?>
                        <option value="<?php echo $s['id']; ?>" <?php echo $selectedStage == $s['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($s['name'] . ' — ' . date('d/m/Y', strtotime($s['race_date']))); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
    </form>

    <?php if (!empty($standings)): ?>
        <div class="data-table-wrapper">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Hạng</th>
                        <th>Tay đua</th>
                        <th>Quốc tịch</th>
                        <th>Đội đua</th>
                        <th>Số chặng</th>
                        <th>Tổng điểm</th>
                        <th>Tổng thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($standings as $idx => $racer): ?>
                        <?php
                        $rank = $idx + 1;
                        $rankClass = 'rank-default';
                        if ($rank == 1) $rankClass = 'rank-1';
                        elseif ($rank == 2) $rankClass = 'rank-2';
                        elseif ($rank == 3) $rankClass = 'rank-3';

                        // Format time
                        $totalSec = $racer['total_time_seconds'];
                        $hours = floor($totalSec / 3600);
                        $mins = floor(($totalSec % 3600) / 60);
                        $secs = $totalSec % 60;
                        $timeFormatted = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        ?>
                        <tr class="clickable"
                            data-href="racer_standings.php?stage_id=<?php echo $selectedStage; ?>&racer_id=<?php echo $racer['racer_id']; ?>">
                            <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($racer['racer_name']); ?></strong>
                                <br><span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo $racer['driver_code']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($racer['nationality']); ?></td>
                            <td><?php echo htmlspecialchars($racer['team_name']); ?></td>
                            <td><?php echo $racer['total_races']; ?></td>
                            <td><span class="points-display"><?php echo $racer['total_points']; ?></span></td>
                            <td><?php echo $timeFormatted; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p style="margin-top: 12px; color: var(--text-muted); font-size: 0.8rem;">
            <i class="fas fa-mouse-pointer"></i> Nhấn vào tay đua để xem chi tiết từng chặng.
            Sắp xếp: Điểm giảm dần → Thời gian tăng dần.
        </p>
    <?php elseif ($selectedStage && empty($standings) && !isset($warning)): ?>
        <div class="empty-state">
            <i class="fas fa-chart-bar"></i>
            <p>Chưa có dữ liệu xếp hạng cho chặng đua này.</p>
        </div>
    <?php elseif (!$selectedStage): ?>
        <div class="empty-state">
            <i class="fas fa-hand-pointer"></i>
            <p>Vui lòng chọn chặng đua để xem bảng xếp hạng.</p>
        </div>
    <?php endif; ?>

    <!-- Racer Detail Panel -->
    <?php if (!empty($detailResults)): ?>
        <div class="detail-panel">
            <h3><i class="fas fa-user"></i> Chi tiết: <?php echo htmlspecialchars($racerName); ?></h3>
            <div class="data-table-wrapper" style="border: none;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Chặng đua</th>
                            <th>Hạng về đích</th>
                            <th>Điểm</th>
                            <th>Thời gian</th>
                            <th>Số vòng</th>
                            <th>Trạng thái</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailResults as $detail): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detail['stage_name']); ?></td>
                                <td>
                                    <?php if ($detail['finish_rank']): ?>
                                        <?php
                                        $dRankClass = 'rank-default';
                                        if ($detail['finish_rank'] == 1) $dRankClass = 'rank-1';
                                        elseif ($detail['finish_rank'] == 2) $dRankClass = 'rank-2';
                                        elseif ($detail['finish_rank'] == 3) $dRankClass = 'rank-3';
                                        ?>
                                        <span class="rank-badge <?php echo $dRankClass; ?>"><?php echo $detail['finish_rank']; ?></span>
                                    <?php else: ?>
                                        <span style="color: var(--text-muted);">—</span>
                                    <?php endif; ?>
                                </td>
                                <td><span class="points-display"><?php echo $detail['score']; ?></span></td>
                                <td><?php echo htmlspecialchars($detail['finish_time'] ?? '—'); ?></td>
                                <td><?php echo $detail['laps_completed']; ?></td>
                                <td>
                                    <?php
                                    $statusClass = 'status-finished';
                                    $statusLabel = 'Về đích';
                                    if ($detail['status'] === 'DNF') { $statusClass = 'status-dnf'; $statusLabel = 'DNF'; }
                                    elseif ($detail['status'] === 'Accident') { $statusClass = 'status-accident'; $statusLabel = 'Tai nạn'; }
                                    ?>
                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusLabel; ?></span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 12px;">
                <a href="racer_standings.php?stage_id=<?php echo $selectedStage; ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
