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

// Get data for editing based on the posted row ID
if ($_SERVER["REQUEST_METHOD"] == "POST" && $userRole === 'admin') {
    $tableName = $_POST['table'];
    
    // Check if "row_id" is set in the form submission
    if (isset($_POST['row_id'])) {
        $rowId = $_POST['row_id'];
  
        // Fetch data for the selected row
        $primaryKeyColumn = getPrimaryKeyColumn($host, $username, $password, $database, $tableName);

        if (!$primaryKeyColumn) {
            die("Primary key column not found for table: $tableName");
        }
  
        $sql = "SELECT * FROM $tableName WHERE $primaryKeyColumn = $rowId";
        $result = $conn->query($sql);
  
        // Check if data is found
        if ($result && $result->num_rows > 0) {
            $row = $result->fetch_assoc();
            // $row contains the data for editing
        } else {
            echo "Data not found.";
        }
    } else {
        echo "Row ID not set.";
    }
  }
  
  // Function to get the primary key column for a table
  function getPrimaryKeyColumn($host, $username, $password, $database, $tableName) {
    $conn = new mysqli($host, $username, $password, $database);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $primaryKeyColumn = null;

    // Query to get primary key column
    $query = "SHOW KEYS FROM $tableName WHERE Key_name = 'PRIMARY'";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $primaryKeyColumn = $row['Column_name'];
    }

    $conn->close(); // Close the connection after fetching the primary key column

    return $primaryKeyColumn;
}

// Handle the form submission for updating
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    $tableName = $_POST['table'];
    $rowId = $_POST['row_id'];

    // Construct the SQL UPDATE statement
    $updateSql = "UPDATE $tableName SET ";
    $bindTypes = ''; // Initialize bind types string
    $bindValues = []; // Initialize bind values array

    foreach ($_POST as $column => $value) {
        if ($column !== 'table' && $column !== 'row_id' && $column !== 'update') {
            $updateSql .= "$column = ?, ";
            $bindTypes .= 's'; // Assuming all values are strings
            $bindValues[] = $value;
        }
    }

    $updateSql = rtrim($updateSql, ', '); // Remove the trailing comma
    $updateSql .= " WHERE $primaryKeyColumn = ?"; // Adjust here
    $bindTypes .= 'i'; // Assuming studentid is an integer
    $bindValues[] = $rowId;

    // Prepare and execute the update query using prepared statements
    $stmt = $conn->prepare($updateSql);

    if ($stmt) {
        // Bind parameters
        $stmt->bind_param($bindTypes, ...$bindValues);

        // Execute the update query
        if ($stmt->execute()) {
            echo "Record updated successfully";
        } else {
            echo "Error updating record: " . $stmt->error;
        }

        // Close the statement
        $stmt->close();
    } else {
        echo "Error preparing statement: " . $conn->error;
    }
}

// Close the connection
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Data</title>
    <style>
        body {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        form {
            width: 400px;
            padding: 20px;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            display: block;
            margin-bottom: 8px;
        }

        input {
            width: 100%;
            padding: 8px;
            margin-bottom: 12px;
            box-sizing: border-box;
        }

        input[type="submit"] {
            background-color: #4caf50; /* Green for submit button */
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        input[type="submit"]:hover {
            background-color: #45a049; /* Darker green on hover */
        }

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

  <h2>Edit Data</h2>

  <?php
  // Display the form for editing
  if (isset($row)) {
    echo "<form method='post' action='edit_page.php'>"; // Adjust here
    echo "<input type='hidden' name='table' value='$tableName'>";
    $primaryKeyColumn = getPrimaryKeyColumn($host, $username, $password, $database, $tableName);
    if (!$primaryKeyColumn) {
        die("Primary key column not found for table: $tableName");
    }
    echo "<input type='hidden' name='row_id' value='{$row[$primaryKeyColumn]}'>";
    foreach ($row as $column => $value) {
        echo "<label for='$column'>$column:</label>";
        echo "<input type='text' name='$column' value='$value'><br>";
    }

    echo "<input type='submit' name='update' value='Update'>"; // Adjust here
    echo "</form>";
  }
  ?>
 <div class="go-back">
 <a href="display_data.php?table=<?php echo $tableName; ?>">Go Back</a>
    </div>
</body>
</html>
