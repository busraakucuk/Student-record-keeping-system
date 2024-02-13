<?php
// Start the session
session_start();

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

// Check the user's role
$userRole = isset($_SESSION['role']) ? $_SESSION['role'] : 'user';

// Database connection details
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'StudentRecordSystem';

// Create a connection
$conn = new mysqli($host, $username, $password, $database);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get data for deleting based on the posted row ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && $userRole === 'admin') {
    $tableName = $_POST['table'];
    $rowId = $_POST['row_id'];

    // Get the primary key column dynamically
    $primaryKeyColumn = getPrimaryKeyColumn($conn, $tableName);

    if (!$primaryKeyColumn) {
        die("Primary key column not found for table: $tableName");
    }

    // Perform the delete operation
    $sql = "DELETE FROM $tableName WHERE $primaryKeyColumn = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        // Bind parameter
        $stmt->bind_param('i', $rowId); // Assuming the primary key is an integer

        // Execute the delete query
        if ($stmt->execute()) {
            echo "Record deleted successfully";
        } else {
            echo "Error deleting record: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Close the connection
$conn->close();

// Function to get the primary key column for a table
function getPrimaryKeyColumn($conn, $tableName) {
    $primaryKeyColumn = null;

    // Query to get primary key column
    $query = "SHOW KEYS FROM $tableName WHERE Key_name = 'PRIMARY'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $primaryKeyColumn = $row['Column_name'];
    }

    return $primaryKeyColumn;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Data</title>
    <style>
        /* Add your styling for the delete page here */
        .go-back {
            margin-top: 20px;
        }

        .go-back a {
            display: inline-block;
            background-color: #2196F3; /* Blue for go back button */
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        .go-back a:hover {
            background-color: #0b7dda; /* Darker blue on hover */
        }
    </style>
</head>
<body>
     <!-- Go Back Button -->
     <div class='go-back'>
        <a href="display_data.php?table=<?php echo $tableName; ?>">Go Back</a>
    </div>
</body>
</html>
