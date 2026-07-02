<?php
require_once 'db.php';
require_once 'admin_auth.php';
include_once 'header.php';
?>

<section class="admin-hero">
    <div>
        <p class="eyebrow">Operations</p>
        <h1>Corporate Reporting Logs</h1>
    </div>
</section>

<section class="content-band">
    <div class="section-heading">
        <p class="eyebrow">Inventory</p>
        <h2>Current Remaining Warehouse Item Quantities</h2>
    </div>

    <table>
        <thead>
            <tr>
                <th>Model ID</th>
                <th>Shoe Model Name</th>
                <th>Category Classification</th>
                <th>Wholesale/Retail Price</th>
                <th>Units Available</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $inv_res = mysqli_query($conn, "SELECT * FROM products ORDER BY stock ASC");
            if (mysqli_num_rows($inv_res) > 0) {
                while ($row = mysqli_fetch_assoc($inv_res)) {
                    echo "<tr>";
                    echo "<td>" . (int)$row['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($row['name']) . "</td>";
                    echo "<td>" . htmlspecialchars($row['category']) . "</td>";
                    echo "<td>$" . number_format($row['price'], 2) . "</td>";
                    echo "<td>" . (int)$row['stock'] . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='5'>Warehouse tracking registers are empty.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<section class="content-band">
    <div class="section-heading">
        <p class="eyebrow">Audit trail</p>
        <h2>Chronological Administrative Activity</h2>
    </div>
    <p class="muted-copy">Filtering entries linked to Admin Operator Session Identity: <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></p>

    <table>
        <thead>
            <tr>
                <th>Log ID</th>
                <th>Account Handler Index</th>
                <th>Activity Event Action Taken</th>
                <th>Logged System Datetime Stamp</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $current_admin_id = (int)$_SESSION['user_id'];
            $audit_res = mysqli_query($conn, "SELECT * FROM audit_log WHERE user_id = $current_admin_id ORDER BY timestamp DESC");

            if (mysqli_num_rows($audit_res) > 0) {
                while ($log = mysqli_fetch_assoc($audit_res)) {
                    echo "<tr>";
                    echo "<td>" . (int)$log['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($log['user_name']) . " [ID: " . (int)$log['user_id'] . "]</td>";
                    echo "<td>" . htmlspecialchars($log['action']) . "</td>";
                    echo "<td>" . htmlspecialchars($log['timestamp']) . "</td>";
                    echo "</tr>";
                }
            } else {
                echo "<tr><td colspan='4'>No administrative actions recorded under your current operator identity.</td></tr>";
            }
            ?>
        </tbody>
    </table>
</section>

<?php include_once 'footer.php'; ?>
