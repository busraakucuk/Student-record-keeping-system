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

// Default table selection (students)
// Example: Retrieve table name from URL parameter
$tableName = isset($_GET['table']) ? $_GET['table'] : 'Students';

// Debugging output
echo "Table Name: $tableName<br>";

// Error handling
$result = $conn->query("SELECT * FROM $tableName");
if (!$result) {
    die("Error: " . $conn->error);
}

// Fetch data based on the selected table
$sql = "SELECT * FROM $tableName";
$result = $conn->query($sql);

// Get column names dynamically
$columns = [];
$primaryKeyColumn = null;

if ($result && $result->field_count > 0) {
    while ($column = $result->fetch_field()) {
        $columns[] = $column->name;
        if ($column->flags & MYSQLI_PRI_KEY_FLAG) {
            $primaryKeyColumn = $column->name;
        }
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
    <title><?php echo ucfirst($tableName); ?> Information</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .sidebar {
            height: 100%;
            width: 200px;
            position: fixed;
            z-index: 1;
            top: 0;
            left: 0;
            background-color: #111;
            padding-top: 20px;
            color: white;
            text-align: center;
        }
        .sidebar a {
            padding: 10px;
            text-decoration: none;
            font-size: 18px;
            color: white;
            display: block;
        }
        .sidebar a:hover {
            background-color: #555;
        }
        .logout {
            position: fixed;
            top: 20px;
            right: 20px;
            text-align: center;
        }

        .logout button {
            background-color: #f44336;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        .logout button:hover {
            background-color: #d32f2f;
        }
        /* Styles for edit and delete buttons */
    .edit-button,
    .delete-button {
        background-color: #4caf50; /* Green for edit button */
        color: white;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        margin-right: 5px;
        transition: background-color 0.3s;
    }

    .delete-button {
        background-color: #f44336; /* Red for delete button */
    }

    /* Hover effect */
    .edit-button:hover{
        background-color: #45a049; /* Darker green for edit button */
    }
    .delete-button:hover {
        background-color: #d32f2f; /* Darker red for edit button */
    }
    .add-data-link {
        display: block;
        text-align: center;
        background-color: #2196F3; /* Blue color */
        color: white;
        padding: 10px;
        text-decoration: none;
        border-radius: 5px;
        margin: 10px auto; /* Center the link horizontally */
        transition: background-color 0.3s;
    }

    .add-data-link:hover {
        background-color: #0b7dda; /* Darker blue on hover */
    }
    </style>
</head>
<body>

    <div class="sidebar">
        <a href="?table=Students">Students</a>
        <a href="?table=Subjects">Subjects</a>
        <a href="?table=Marks">Marks</a>
        <a href="?table=Fees">Fees</a>
        <a href="?table=Concessions">Concessions</a>
        <!-- Add more links for other tables -->
    </div>

    <div class="logout">
        <form method="post" action="logout.php">
            <button type="submit">Logout</button>
        </form>
    </div>

    <div style="margin-left: 200px; padding: 20px;">
        <h2><?php echo ucfirst($tableName); ?> Information</h2>

        <?php
        // Display data based on the selected table
        if ($result && $result->num_rows > 0) {
            echo "<table>";
            echo "<tr>";
            foreach ($columns as $column) {
                echo "<th>$column</th>";
            }
            if ($userRole === 'admin') {
                echo "<th>Actions</th>";
            }
            echo "</tr>";

            while ($row = $result->fetch_assoc()) {
                echo "<tr class='data-row'>";
                foreach ($columns as $column) {
                    // Output each cell
                    echo "<td>{$row[$column]}</td>";
                }
                if ($userRole === 'admin' && $primaryKeyColumn) {
                    echo "<td>";
                    echo "<form action='edit_page.php' method='post' style='display: inline-block;'>";
                    echo "<input type='hidden' name='table' value='$tableName'>";
                    echo "<input type='hidden' name='row_id' value='$row[$primaryKeyColumn]'>"; // Adjust here

                    echo "<input type='submit' class='edit-button' value='Edit'>";
                    echo "</form>";
                    echo "<form action='delete_page.php' method='post' style='display: inline-block; margin-left: 5px;'>";
                    echo "<input type='hidden' name='table' value='$tableName'>";
                    echo "<input type='hidden' name='row_id' value='{$row[$primaryKeyColumn]}'>"; // Adjust here

                    echo "<input type='submit' class='delete-button' value='Delete'>";
                    echo "</form>";
                    echo "</td>";
                }
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo "No data found.";
        }
        echo "<a class='add-data-link' href='add_data.php?table=$tableName'>Add New Data</a>"
        ?>
    </div>

</body>
</html>