<?php
require_once 'config.php';

echo "<h2>🔍 Checking for Duplicate Bus Numbers</h2>";

try {
    $pdo = getDBConnection();
    echo "✅ Database connection successful<br><br>";
    
    // Check for duplicate bus numbers
    echo "<h3>1. Finding duplicate bus numbers...</h3>";
    $stmt = $pdo->query("SELECT bus_number, COUNT(*) as count FROM buses GROUP BY bus_number HAVING count > 1");
    $duplicates = $stmt->fetchAll();
    
    if (count($duplicates) > 0) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<strong>❌ Found duplicate bus numbers:</strong><br>";
        foreach ($duplicates as $dup) {
            echo "- Bus Number: <strong>{$dup['bus_number']}</strong> (appears {$dup['count']} times)<br>";
        }
        echo "</div>";
        
        // Show all buses with duplicate numbers
        echo "<h3>2. Buses with duplicate numbers:</h3>";
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Bus Name</th><th>Bus Number</th><th>Type</th><th>Created</th><th>Action</th></tr>";
        
        foreach ($duplicates as $dup) {
            $stmt = $pdo->prepare("SELECT * FROM buses WHERE bus_number = ? ORDER BY id");
            $stmt->execute([$dup['bus_number']]);
            $buses = $stmt->fetchAll();
            
            foreach ($buses as $index => $bus) {
                $rowColor = $index > 0 ? 'background: #ffebee;' : 'background: #e8f5e8;';
                echo "<tr style='$rowColor'>";
                echo "<td>{$bus['id']}</td>";
                echo "<td>{$bus['bus_name']}</td>";
                echo "<td><strong>{$bus['bus_number']}</strong></td>";
                echo "<td>{$bus['bus_type']}</td>";
                echo "<td>{$bus['created_at']}</td>";
                if ($index > 0) {
                    echo "<td><button onclick='deleteBus({$bus['id']})' style='background: #dc3545; color: white; padding: 5px 10px; border: none; border-radius: 3px; cursor: pointer;'>Delete Duplicate</button></td>";
                } else {
                    echo "<td><span style='color: green; font-weight: bold;'>Keep (Original)</span></td>";
                }
                echo "</tr>";
            }
        }
        echo "</table>";
        
    } else {
        echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin: 10px 0;'>";
        echo "<strong>✅ No duplicate bus numbers found!</strong>";
        echo "</div>";
    }
    
    // Show all buses
    echo "<br><h3>3. All buses in database:</h3>";
    $stmt = $pdo->query("SELECT * FROM buses ORDER BY id");
    $all_buses = $stmt->fetchAll();
    
    if (count($all_buses) > 0) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%; margin: 10px 0;'>";
        echo "<tr style='background: #f0f0f0;'><th>ID</th><th>Bus Name</th><th>Bus Number</th><th>Type</th><th>Route</th><th>Status</th></tr>";
        
        foreach ($all_buses as $bus) {
            $status = $bus['is_active'] ? 'Active' : 'Inactive';
            $statusColor = $bus['is_active'] ? 'color: green;' : 'color: red;';
            echo "<tr>";
            echo "<td>{$bus['id']}</td>";
            echo "<td>{$bus['bus_name']}</td>";
            echo "<td><strong>{$bus['bus_number']}</strong></td>";
            echo "<td>{$bus['bus_type']}</td>";
            echo "<td>{$bus['route_from']} → {$bus['route_to']}</td>";
            echo "<td style='$statusColor font-weight: bold;'>$status</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p><strong>Total buses: " . count($all_buses) . "</strong></p>";
    } else {
        echo "<p>No buses found in database.</p>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px;'>";
    echo "<strong>❌ Error:</strong> " . $e->getMessage();
    echo "</div>";
}

echo "<br><div style='margin-top: 30px;'>";
echo "<a href='admin_dashboard.php' style='padding: 12px 24px; background: #6c5ce7; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>← Back to Dashboard</a>";
echo "<a href='add_bus.php' style='padding: 12px 24px; background: #00b894; color: white; text-decoration: none; border-radius: 6px; margin-right: 10px; display: inline-block;'>🚌 Add Bus</a>";
echo "<a href='manage_buses.php' style='padding: 12px 24px; background: #0984e3; color: white; text-decoration: none; border-radius: 6px; display: inline-block;'>🔧 Manage Buses</a>";
echo "</div>";
?>

<script>
function deleteBus(busId) {
    if (confirm('Are you sure you want to delete this duplicate bus? This action cannot be undone.')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'manage_buses.php';
        
        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = 'delete_bus';
        
        const busIdInput = document.createElement('input');
        busIdInput.type = 'hidden';
        busIdInput.name = 'bus_id';
        busIdInput.value = busId;
        
        form.appendChild(actionInput);
        form.appendChild(busIdInput);
        document.body.appendChild(form);
        form.submit();
    }
}
</script>

<style>
body { font-family: Arial, sans-serif; padding: 20px; background: #f8f9fa; }
h2, h3 { color: #2c3e50; }
table { margin: 10px 0; }
th, td { padding: 8px 12px; text-align: left; }
th { background: #e9ecef; }
button:hover { opacity: 0.8; }
</style>
