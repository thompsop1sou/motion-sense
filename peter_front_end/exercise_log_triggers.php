<!doctype HTML>
<html>
    <head>
        <meta charset="UTF-8" />
        <title> Motion Sense - Exercise Log Triggers </title>
        <link rel="stylesheet" href="basic_style.css">
    </head>
    <body>
        <!-- Database project title -->
        <h1> <a href="index.html">Motion Sense</a> </h1>
        <!-- Page Title -->
        <h3> <a href="exercise_log_triggers.php">Exercise Log Triggers</a> </h3>
        <!-- Author -->
        <p>
            <b>Author:</b> Peter Thompson
        </p>
        <!-- Description -->
        <p>
            <b>Description:</b> These triggers ensure that data entered into the exerciseLog table
            is valid, which means that it refers to the same entry in the workoutPlan table whether
            you get there through the workoutLog table or through the exercisePlan table.
        </p>
        <!-- Justification -->
        <p>
            <b>Justification:</b> Because of a loop in our table relationships, it is possible that
            we could create an entry in the exerciseLog table that refers to one workoutPlan entry
            when you take one path and refers to a different workoutPlan entry when you take the
            other path. See the following image for an illustration of the two paths:
        </p>
        <img src="db_loop.png" alt="Database Loop">
        <!-- Expected Execution -->
        <p>
            <b>Expected Execution:</b> These triggers will be called automatically before an
            insertion or update on the exerciseLog table. If they find invalid data, they will
            throw an error and stop the insertion/update. Otherwise, they won't have any impact and
            will allow the insertion/update to proceed, in which case you will get to see the
            change in the database. (Note: This change will be immediately discarded so as not to
            cause any negative side effects to the functioning of the database.) Some interesting
            inputs include:
        </p>
        <ul>
            <li>
                Workout log <b>2023-01-01 (wrkLogID 1, ref. wrkPlanID 1)</b> and exercise plan
                <b>Running (exrPlanID 1, ref. wrkPlanID 1)</b>.
                <ul>
                    <li>
                        Since these reference the same workout plan (ID 1), the UPDATE should
                        succeed, and you should see a new log entry in the database.
                    </li>
                </ul>
            </li>
            <li>
                Workout log <b>2023-01-01 (wrkLogID 1, ref. wrkPlanID 1)</b> and exercise plan
                <b>Running (exrPlanID 13, ref. wrkPlanID 4)</b>.
                <ul>
                    <li>
                        Although the exercise name is the same as before ("Running"), these now
                        reference two different workout plans (ID 1 and ID 4). So, the UPDATE
                        should fail, and you should see no change to the database
                    </li>
                </ul>
            </li>
        </ul>
        <!-- Form/Results -->
        <?php
            // Function to display log data (used twice below)
            function display_log_data($mysqli)
            {
                // Build query string
                $sql = "SELECT wrkPlanName, wrkLogDate, exrName, exrType, exrLogNotes FROM allLogJoin";
                // Execute query using the connection created above
                $retval = mysqli_query($mysqli, $sql);

                // Display the results
                if (mysqli_num_rows($retval) > 0)
                {
                    // Start the table
                    echo "<table>\n" .
                        "<tr>\n" .
                        "<th>wrkPlanName</th>\n" .
                        "<th>wrkLogDate</th>\n" .
                        "<th>exrName</th>\n" .
                        "<th>exrType</th>\n" .
                        "<th>exrLogNotes</th>\n" .
                        "<tr>\n";
                    // Show each row
                    while ($row = mysqli_fetch_assoc($retval))
                    {
                        echo "<tr>\n" .
                            "<td>" . ($row["wrkPlanName"] ?? "NULL") . "</td>\n" .
                            "<td>" . ($row["wrkLogDate"] ?? "NULL") . "</td>\n" .
                            "<td>" . ($row["exrName"] ?? "NULL") . "</td>\n" .
                            "<td>" . ($row["exrType"] ?? "NULL") . "</td>\n" .
                            "<td>" . ($row["exrLogNotes"] ?? "NULL") . "</td>\n" .
                            "</tr>\n";
                    }
                    // End the table
                    echo "</table>\n";
                }
                // No results
                else
                {
                    echo "<p> <em>No log results.</em> <p>\n";
                }
            }

            // Credientials
            require_once "/home/SOU/thompsop1/final_db_config.php";

            // Turn error reporting on
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
            ini_set("display_errors", "1");

            // Create connection using procedural interface
            $mysqli = mysqli_connect($hostname,$username,$password,$schema);

            // Connection failed
            if (!$mysqli)
            {
                echo "<p> <em>There was an error connecting to the database.</em> <p>\n";
            }
            // Connection succeeded
            else
            {
                // Form already submitted, display the results
                if (isset($_POST["submit"]))
                {
                    echo "<p> <b>Query Results:</b> </p>\n";

                    // Turn off autocommit
                    mysqli_autocommit($mysqli, false);

                    // Display log data before update/insert
                    echo "<p> Original log data: </p>\n";
                    display_log_data($mysqli);

                    // Attempt to insert the new data
                    try
                    {
                        // Attempt to create new entry in exerciseLog table
                        $sql = "INSERT INTO exerciseLog (exrLogID, wrkLogID, exrPlanID, exrLogNotes) VALUES (NULL, " . $_POST["wrk_log_id"] . ", " . $_POST["exr_plan_id"] . ", '" . $_POST["exr_log_notes"] . "')";
                        mysqli_query($mysqli, $sql);
                        // If no error, indicate success
                        echo "<p> Insert succeeded! </p>\n";
                    }
                    // The insert failed
                    catch (mysqli_sql_exception $e)
                    {
                        echo "<p> Insert failed: " . mysqli_error($mysqli) . " </p>\n";
                    }

                    // Display log data after update/insert
                    echo "<p> Changed log data: </p>\n";
                    display_log_data($mysqli);

                    // Rollback any changes
                    mysqli_rollback($mysqli);
                }
                // Form not yet submitted, display the form
                else
                {
                    // Insert form
                    echo "<p> <b>Input Form (Insert Trigger):</b> </p>\n";

                    // Build query string A
                    $sql_a = "SELECT wrkLogID, wrkPlanID, wrkLogDate FROM workoutLog";
                    // Execute query A using the connection created above
                    $retval_a = mysqli_query($mysqli, $sql_a);

                    // Build query string B
                    $sql_b = "SELECT wrkPlanID, exrName, exrPlanID FROM allPlanJoin";
                    // Execute query B using the connection created above
                    $retval_b = mysqli_query($mysqli, $sql_b);

                    // No results in one of the queries
                    if (!(mysqli_num_rows($retval_a) > 0) || !(mysqli_num_rows($retval_b) > 0))
                    {
                        echo "<p><em>No workouts found in database.</em></p>\n";
                    }
                    // Some results in both queries
                    else
                    {
                        // Start of the form
                        echo "<form action=\"exercise_log_triggers.php\" method=\"post\">\n" .
                            "<p>\n" .
                            "<label for=\"wrk_log_id\"> Workout Log: </label>\n" .
                            "<select id=\"wrk_log_id\" name=\"wrk_log_id\" required>\n";

                        // Add each workout log to the select field
                        while ($row = mysqli_fetch_assoc($retval_a)) {
                            echo "<option value=\"" . $row["wrkLogID"] . "\">" . $row["wrkLogDate"] . " (wrkLogID " . $row["wrkLogID"] . ", ref. wrkPlanID " . $row["wrkPlanID"] . ")</option>\n";
                        }

                        // Middle of the form
                        echo "</select>\n" .
                            "</p>\n" .
                            "<p>\n" .
                            "<label for=\"exr_plan_id\"> Exercise Plan: </label>\n" .
                            "<select id=\"exr_plan_id\" name=\"exr_plan_id\" required>\n";

                        // Add each exercise plan to the select field
                        while ($row = mysqli_fetch_assoc($retval_b))
                        {
                            echo "<option value=\"" . $row["exrPlanID"] . "\">" . $row["exrName"] . " (exrPlanID " . $row["exrPlanID"] . ", ref. wrkPlanID " . $row["wrkPlanID"] . ")</option>\n";
                        }

                        // End of the form
                        echo "</select>\n" .
                            "</p>\n" .
                            "<p>\n" .
                            "<label for=\"exr_log_notes\"> Exercise Log Notes: </label>\n" .
                            "<input type=\"text\" id=\"exr_log_notes\" name=\"exr_log_notes\" value=\"THIS IS A TEST\">\n" .
                            "</p>\n" .
                            "<p>\n" .
                            "<input type=\"submit\" name=\"submit\" value=\"Submit\">\n" .
                            "</p>\n" .
                            "</form>\n";
                    }

                    // Free result sets
                    mysqli_free_result($retval_a);
                    mysqli_free_result($retval_b);

                    // Update form
                    echo "<p>\n" .
                        "<b>Input Form (Update Trigger):</b> Not shown due to the complexities\n" .
                        "involved in updating the data. In this case, not only is each exercise\n" .
                        "log connected to a workout log and a workout plan, but it is also\n" .
                        "connected to either a cardio log or a strength log. There is currently\n" .
                        "no way to ensure updating the data does not mess up that relationship.\n" .
                        "(This isn't a problem when creating new data in an insert.)\n" .
                        "</p>\n";
                }
            }

            // Close connection
            mysqli_close($mysqli);
        ?>
    </body>
</html>