<?php
// Start the session
session_start();

// Check if the user is not logged in, redirect to the login page
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

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

// Get table name from the URL
$tableName = isset($_GET['table']) ? $_GET['table'] : '';

// Check if the table name is provided
if (empty($tableName)) {
    die("Table name not specified.");
}

// Get column names for the specified table
$columns = getColumns($conn, $tableName);

// Handle the form submission for adding data
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add'])) {
    // Check if the foreign key exists in the parent table
    $foreignKeysExist = checkForeignKeysExist($conn, $tableName, $_POST);

    if (!$foreignKeysExist) {
        // Show an error message or redirect to an error page
        echo "Error: The referenced foreign key does not exist.";
        // You can also redirect the user to an error page using header("Location: error.php");
    } else {
        // Proceed with the insertion
        $insertSql = "INSERT INTO $tableName (";
        $valuesSql = "VALUES (";
        $bindTypes = ''; // Initialize bind types string
        $bindValues = []; // Initialize bind values array

        foreach ($columns as $column) {
            if (isset($_POST[$column])) {
                $insertSql .= "$column, ";
                $valuesSql .= "?, ";
                $bindTypes .= 's'; // Assuming all values are strings
                $bindValues[] = $_POST[$column];
            }
        }

        $insertSql = rtrim($insertSql, ', ') . ")";
        $valuesSql = rtrim($valuesSql, ', ') . ")";

        // Combine the INSERT and VALUES parts of the SQL statement
        $insertSql .= " " . $valuesSql;

        // Prepare and execute the insert query using prepared statements
        $stmt = $conn->prepare($insertSql);

        if ($stmt) {
            // Bind parameters
            $stmt->bind_param($bindTypes, ...$bindValues);

            // Execute the insert query
            if ($stmt->execute()) {
                echo "Record added successfully";
            } else {
                echo "Error adding record: " . $stmt->error;
            }

            // Close the statement
            $stmt->close();
        } else {
            echo "Error preparing statement: " . $conn->error;
        }
    }
}

// Close the connection
$conn->close();

// Function to get column names for a table
function getColumns($conn, $tableName) {
    $columns = [];

    // Query to get column names
    $query = "SHOW COLUMNS FROM $tableName";
    $result = $conn->query($query);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $columns[] = $row['Field'];
        }
    }

    return $columns;
}

// Function to check if foreign keys exist in the parent table
function checkForeignKeysExist($conn, $tableName, $postData) {
    // Add your logic to check foreign keys based on $tableName and $postData
    // Return true if foreign keys exist, false otherwise
    return true; // Replace with your actual implementation
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Data</title>
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

<h2>Add Data</h2>

<form method="post" action="add_data.php?table=<?php echo $tableName; ?>">
    <?php
    // Display form fields based on the columns of the table
    foreach ($columns as $column) {
        echo "<label for='$column'>$column:</label>";
        echo "<input type='text' name='$column' required><br>";
    }
    ?>
    <input type="submit" name="add" value="Add">
</form>

<div class="go-back">
    <a href="display_data.php?table=<?php echo $tableName; ?>">Go Back</a>
</div>

</body>
</html>
