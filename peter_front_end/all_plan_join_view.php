<!doctype HTML>
<html>
    <head>
        <meta charset="UTF-8" />
        <title> Motion Sense - All Plan Join View </title>
        <link rel="stylesheet" href="basic_style.css">
    </head>
    <body>
        <!-- Database project title -->
        <h1> <a href="index.html">Motion Sense</a> </h1>
        <!-- Page Title -->
        <h3> All Plan Join View </h3>
        <!-- Author -->
        <p>
            <b>Author:</b> Peter Thompson
        </p>
        <!-- Description -->
        <p>
            <b>Description:</b> This view has all of the plan tables joined together by their
            relevant IDs.
        </p>
        <!-- Justification -->
        <p>
            <b>Justification:</b> Because the plan data is spread out over several different
            tables (workoutPlan, exercise, exercisePlan, cardioPlan, and strengthPlan), it can be
            tricky to query it all correctly. This view does the work of joining all of the tables
            so that the data is easier to query. In a practical sense, it could be used by athletes
            who want to see what their trainer has planned for them to do during a particular week.
        </p>
        <!-- Expected Execution -->
        <p>
            <b>Expected Execution:</b> The table below shows selected columns from the allPlanJoin
            view as it currently exists inside of the Motion Sense database. Since it joins many
            tables together, its complete version has many columns. Not all of the columns are
            shown here in order to save some space. (Note that cardio exercises always have null
            values in the strPlanSets column while strength exercises always have null values in
            the cdoPlanSets column. This is a result of the fact that the cardioPlan table has
            columns which the strengthPlan table does not have, and vice versa.)
        </p>
        <!-- Query Results -->
        <?php
            // Credientials
            require_once "/home/SOU/thompsop1/dbconfig.php";

            // Turn error reporting on
            error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE);
            ini_set("display_errors", "1");

                // Create connection using procedural interface
            $mysqli = mysqli_connect($hostname,$username,$password,$schema);

            // Check connection
            if (!$mysqli) {
                echo "<p> <em>There was an error connecting to the database.</em> <p>";
            } else {
                // Build query string
                $sql = "SELECT wrkPlanName, exrName, exrType, exrPlanNotes, cdoPlanSets, strPlanSets FROM allPlanJoin";  
                // Execute query using the connection created above
                $retval = mysqli_query($mysqli, $sql);

                // Display the results
                if (mysqli_num_rows($retval) > 0) {
                    // Start the table
                    echo "<table>" .
                        "<tr>" .
                        "<th>wrkPlanName</th>" .
                        "<th>exrName</th>" .
                        "<th>exrType</th>" .
                        "<th>exrPlanNotes</th>" .
                        "<th>cdoPlanSets</th>" .
                        "<th>strPlanSets</th>" .
                        "<tr>";
                    // Show each row
                    while ($row = mysqli_fetch_assoc($retval)) {
                        echo "<tr>" .
                            "<td>" . ($row["wrkPlanName"] ?? "NULL") . "</td>" .
                            "<td>" . ($row["exrName"] ?? "NULL") . "</td>" .
                            "<td>" . ($row["exrType"] ?? "NULL") . "</td>" .
                            "<td>" . ($row["exrPlanNotes"] ?? "NULL") . "</td>" .
                            "<td>" . ($row["cdoPlanSets"] ?? "NULL") . "</td>" .
                            "<td>" . ($row["strPlanSets"]  ?? "NULL") . "</td>" .
                            "</tr>";
                    }
                    // End the table
                    echo "</table>";
                // No results
                } else {
                    echo "<p> <em>No results in view.</em> <p>";
                }

                // Free result set
                mysqli_free_result($retval);
            }

            // Close connection
            mysqli_close($mysqli);
        ?>
    </body>
</html>