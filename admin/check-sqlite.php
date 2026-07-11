<?php
echo "<h3>SQLite3 Extension Check</h3>";

// Check for standard SQLite3
if (extension_loaded('sqlite3')) {
    echo "<p style='color: green;'>✅ SQLite3 extension is <strong>INSTALLED and ENABLED</strong>.</p>";
} else {
    echo "<p style='color: red;'>❌ SQLite3 extension is <strong>MISSING</strong>.</p>";
}

// Check for PDO SQLite (which the login script uses)
if (extension_loaded('pdo_sqlite')) {
    echo "<p style='color: green;'>✅ PDO SQLite extension is <strong>INSTALLED and ENABLED</strong>.</p>";
} else {
    echo "<p style='color: red;'>❌ PDO SQLite extension is <strong>MISSING</strong>.</p>";
}
?>