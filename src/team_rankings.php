<?php
require_once 'config/database.php';
requireLogin();

$pageTitle = 'Bảng xếp hạng đội đua — F1 Championship';

// Get all stages
$stages = $conn->query("
    SELECT s.id, s.name, s.location, s.race_date, s.stage_order
    FROM stage s
    ORDER BY s.stage_order ASC
");

$selectedStage = $_GET['stage_id'] ?? '';
$teamDetail = $_GET['team_id'] ?? '';
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
            // Get cumulative team standings up to selected stage
            $stmt = $conn->prepare("
                SELECT * 
                FROM vw_team_standings 
                WHERE stage_id = ? 
                ORDER BY total_points DESC, total_time_seconds ASC
            ");
            $stmt->bind_param("i", $stageId);
            $stmt->execute();
            $standings = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
            $stmt->close();
        }
    }

    // Load team detail if requested
    if ($teamDetail) {
        $detailStmt = $conn->prepare("
            SELECT
                s.name as stage_name,
                s.stage_order,
                COALESCE(SUM(res.score), 0) as stage_total_score,
                GROUP_CONCAT(
                    CONCAT(r.name, ' (P', COALESCE(res.finish_rank, '-'), ': ', COALESCE(res.score, 0), 'pts)')
                    ORDER BY res.finish_rank ASC
                    SEPARATOR ', '
                ) as driver_details,
                COALESCE(SUM(
                    CASE WHEN res.finish_time IS NOT NULL AND res.finish_time != ''
                    THEN TIME_TO_SEC(res.finish_time) ELSE 0 END
                ), 0) as stage_total_time
            FROM result res
            JOIN contract c ON res.contract_id = c.id
            JOIN racer r ON c.racer_id = r.id
            JOIN stage s ON res.stage_id = s.id
            WHERE c.team_id = ?
              AND s.tournament_id = (SELECT tournament_id FROM stage WHERE id = ?)
              AND s.stage_order <= (SELECT stage_order FROM stage WHERE id = ?)
            GROUP BY s.id, s.name, s.stage_order
            ORDER BY s.stage_order ASC
        ");
        $detailStmt->bind_param("iii", $teamDetail, $stageId, $stageId);
        $detailStmt->execute();
        $detailResults = $detailStmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $detailStmt->close();

        // Get team name
        $teamNameStmt = $conn->prepare("SELECT name FROM team WHERE id = ?");
        $teamNameStmt->bind_param("i", $teamDetail);
        $teamNameStmt->execute();
        $teamName = $teamNameStmt->get_result()->fetch_assoc()['name'];
        $teamNameStmt->close();
    }
}

require_once 'includes/header.php';
?>

<div class="page-container">
    <h1 class="page-title"><i class="fas fa-users"></i> Bảng Xếp Hạng Đội Đua</h1>

    <?php if (isset($warning)): ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($warning); ?>
        </div>
    <?php endif; ?>

    <!-- Select Stage -->
    <form method="GET" action="team_rankings.php">
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
                        <th>Đội đua</th>
                        <th>Hãng xe</th>
                        <th>Tổng điểm</th>
                        <th>Tổng thời gian</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($standings as $idx => $team): ?>
                        <?php
                        $rank = $idx + 1;
                        $rankClass = 'rank-default';
                        if ($rank == 1) $rankClass = 'rank-1';
                        elseif ($rank == 2) $rankClass = 'rank-2';
                        elseif ($rank == 3) $rankClass = 'rank-3';

                        $totalSec = $team['total_time_seconds'];
                        $hours = floor($totalSec / 3600);
                        $mins = floor(($totalSec % 3600) / 60);
                        $secs = $totalSec % 60;
                        $timeFormatted = sprintf('%02d:%02d:%02d', $hours, $mins, $secs);
                        ?>
                        <tr class="clickable"
                            data-href="team_rankings.php?stage_id=<?php echo $selectedStage; ?>&team_id=<?php echo $team['team_id']; ?>">
                            <td><span class="rank-badge <?php echo $rankClass; ?>"><?php echo $rank; ?></span></td>
                            <td>
                                <strong><?php echo htmlspecialchars($team['team_name']); ?></strong>
                                <br><span style="color: var(--text-muted); font-size: 0.8rem;"><?php echo $team['team_code']; ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($team['brand']); ?></td>
                            <td><span class="points-display"><?php echo $team['total_points']; ?></span></td>
                            <td><?php echo $timeFormatted; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <p style="margin-top: 12px; color: var(--text-muted); font-size: 0.8rem;">
            <i class="fas fa-mouse-pointer"></i> Nhấn vào đội đua để xem chi tiết từng chặng.
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
            <p>Vui lòng chọn chặng đua để xem bảng xếp hạng đội đua.</p>
        </div>
    <?php endif; ?>

    <!-- Team Detail Panel -->
    <?php if (!empty($detailResults)): ?>
        <div class="detail-panel">
            <h3><i class="fas fa-flag"></i> Chi tiết: <?php echo htmlspecialchars($teamName); ?></h3>
            <div class="data-table-wrapper" style="border: none;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Chặng đua</th>
                            <th>Tổng điểm chặng</th>
                            <th>Chi tiết tay đua</th>
                            <th>Tổng thời gian</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($detailResults as $detail): ?>
                            <?php
                            $detailSec = $detail['stage_total_time'];
                            $dh = floor($detailSec / 3600);
                            $dm = floor(($detailSec % 3600) / 60);
                            $ds = $detailSec % 60;
                            $detailTime = sprintf('%02d:%02d:%02d', $dh, $dm, $ds);
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($detail['stage_name']); ?></td>
                                <td><span class="points-display"><?php echo $detail['stage_total_score']; ?></span></td>
                                <td style="font-size: 0.85rem;"><?php echo htmlspecialchars($detail['driver_details']); ?></td>
                                <td><?php echo $detailTime; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <div style="margin-top: 12px;">
                <a href="team_rankings.php?stage_id=<?php echo $selectedStage; ?>" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Quay lại
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
