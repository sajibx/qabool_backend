<?php
include ("./sessionchk2.php"); 
include ("./refchk.php");

    // Include database connection
    require "./conn.php"; 

    // --- Input Validation and Hashing ---

    // Securely retrieve and sanitize user input
    // Using FILTER_SANITIZE_STRING (or the more modern FILTER_SANITIZE_SPECIAL_CHARS) for user ID and name
    $nUID = filter_input(INPUT_POST, 'nUID', FILTER_SANITIZE_SPECIAL_CHARS);
    $nUNAME = filter_input(INPUT_POST, 'nUNAME', FILTER_SANITIZE_SPECIAL_CHARS);

    // Validate inputs
    if (empty($nUID) || empty($nUNAME)) {
        echo "<div style='margin:50px auto; text-align:left; border:0px solid red; height:120px; width:300px; margin-left:30px;'>
        User ID and Name are required.
        </div>";
        exit();
    }

    // Load configuration for default password (no need for static salt here for new users)
    $ini = parse_ini_file($_SERVER['DOCUMENT_ROOT'] . '/App_Data/app.ini', true);
    $DEFAULTPASS = $ini["GENERAL"]["DEFAULTPASS"] ?? 'defaultpass'; // Use a fallback default pass

    // Hash the default password using BCRYPT
    // This aligns with the modern hashing check in LoginProcess.php
    $hashedPassword = password_hash($DEFAULTPASS, PASSWORD_BCRYPT);

    // --- Check for existing user (Using Prepared Statements) ---
    try {
        $stmt_chk = $conn->prepare("SELECT count(*) AS CNT FROM `user_info` WHERE `id` = ?");
        $stmt_chk->bind_param("s", $nUID);
        $stmt_chk->execute();
        $result = $stmt_chk->get_result();
        $row = $result->fetch_assoc();
        $value1 = $row['CNT'];
        $stmt_chk->close();
    
        if ($value1 > 0) {
            echo "<div style='margin:50px auto; text-align:left; border:0px solid red; height:120px; width:300px; margin-left:30px;'>User already exists.</div>";
            exit();
        }

        // --- Insert new user (Using Prepared Statements) ---
        // FCP = 1 (Force Change Password), UL = 0 (User Level, assuming)
        $user_type = 'USER';
        $FCP = '1';
        $UL = '0';
        
        $sql = "INSERT INTO `user_info` (`id`, `pass`, `type`, `name`, `FCP`, `UL`) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssss", $nUID, $hashedPassword, $user_type, $nUNAME, $FCP, $UL);
        
        if ($stmt->execute()) {
            // Success message
            echo "<div>New User Added</br> User ID : " . htmlspecialchars($nUID) . "User Default Pass : " . htmlspecialchars($DEFAULTPASS) . "    --Please Change password after first login.
            </div>";

            // --- Generate Log (Using Prepared Statements) ---
            date_default_timezone_set('Asia/Dhaka'); // Ensure timezone consistency
            
            $EVENT      = "New User"; 
            $REMARKS    = "New User Added : " . $nUID; 
            $UID        = $_SESSION['user'] ?? 'SYSTEM'; // Fallback if session user is somehow missing
            $date       = date("Y-m-d H:i:s");
            
            $sql_log    = "INSERT INTO `log` (`date`, `user`, `event`, `comment`)
                           VALUES (?, ?, ?, ?)";
            
            $stmt_log = $conn->prepare($sql_log);
            // Note: `log` table structure assumed based on LoginProcess.php (date, user, event, comment)
            $stmt_log->bind_param("ssss", $date, $UID, $EVENT, $REMARKS);
            $stmt_log->execute();
            $stmt_log->close();

        } else {
            // Database insertion failed
            // It's better to provide a generic error to the user and log the detailed error
            error_log("New user creation error: " . $stmt->error);
            echo "<div style='margin:50px auto; text-align:left; border:0px solid red; height:120px; width:300px; margin-left:30px;'>
            An error occurred while adding the user.
            </div>";
        }
        
        $stmt->close();

    } catch (Exception $e) {
        // Log connection or unexpected error
        error_log("Database error in NewUser.php: " . $e->getMessage());
        echo "<div style='margin:50px auto; text-align:left; border:0px solid red; height:120px; width:300px; margin-left:30px;'>
        A system error occurred. Please check logs.
        </div>";
    }

    // Close database connection
    $conn->close();
?>