<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Include required functions file
require_once(realpath(__DIR__ . '/api.php'));

/**********************************
 * FUNCTION: API V2 ADMIN VERSION *
 * ********************************/
function api_v2_admin_version()
{
    // Check that this is an admin user
    api_v2_check_admin();

    // Get the current version of the SimpleRisk application
    $current_version_app = current_version("app");

    // Get the current version of the SimpleRisk database
    $current_version_db = current_version("db");

    // Create the data array
    $data = [
        "app" => $current_version_app,
        "db" => $current_version_db,
    ];

    // Set the status
    $status_code = 200;
    $status_message = "SUCCESS";

    // Return the result
    api_v2_json_result($status_code, $status_message, $data);
}

/**************************************
 * FUNCTION: API V2 ADMIN VERSION APP *
 **************************************/
function api_v2_admin_version_app()
{
    // Check that this is an admin user
    api_v2_check_admin();

    // Get the current version of the SimpleRisk application
    $current_version_app = current_version("app");

    // Create the data array
    $data = [
        "app" => $current_version_app,
    ];

    // Set the status
    $status_code = 200;
    $status_message = "SUCCESS";

    // Return the result
    api_v2_json_result($status_code, $status_message, $data);
}

/*************************************
 * FUNCTION: API V2 ADMIN VERSION DB *
 *************************************/
function api_v2_admin_version_db()
{
    // Check that this is an admin user
    api_v2_check_admin();

    // Get the current version of the SimpleRisk database
    $current_version_db = current_version("db");

    // Create the data array
    $data = [
        "db" => $current_version_db,
    ];

    // Set the status
    $status_code = 200;
    $status_message = "SUCCESS";

    // Return the result
    api_v2_json_result($status_code, $status_message, $data);
}

/*************************************
 * FUNCTION: API V2 ADMIN TAG DELETE *
 *************************************/
function api_v2_admin_tag_delete()
{
    // Check that this is an admin user
    api_v2_check_admin();

    // Get the tag id and type provided
    $id = get_param("GET", "id", null);

    // Delete the tag with that id
    delete_tag($id);

    // Create the result
    $status_code = 200;
    $status_message = "Delete successful";
    $data = null;

    // Return the result
    api_v2_json_result($status_code, $status_message, $data);
}

/*****************************************
 * FUNCTION: API V2 ADMIN TAG DELETE ALL *
 *****************************************/
function api_v2_admin_tag_delete_all()
{
    // Check that this is an admin user
    api_v2_check_admin();

    // Get the type provided
    $type = get_param("GET", "type", null);

    global $tag_types;
    if ($type === 'all' || in_array($type, $tag_types)) {

        // Delete all tags for the type
        delete_all_tags($type);
    
        // Create the result
        $status_code = 200;
        $status_message = "Delete successful";
    
    } else {
        // Create the result
        $status_code = 400;
        $status_message = "Invalid type";
    }

    $data = null;

    // Return the result
    api_v2_json_result($status_code, $status_message, $data);
}

/******************************************
 * FUNCTION: API V2 ADMIN WRITE DEBUG LOG *
 ******************************************/
function api_v2_admin_write_debug_log()
{
    // Open the database connection
    $db = db_open();

    try {
        // Start a transaction
        $db->beginTransaction();

        // Get the list of all debug_log messages
        $stmt = $db->prepare("SELECT id, message FROM `debug_log` FOR UPDATE;");
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // For each of the results
        foreach ($results as $result)
        {
            // Write the message to the Apache debug log
            write_debug_log($result['message']);

            // Delete the message
            $stmt = $db->prepare("DELETE FROM `debug_log` WHERE id=:id");
            $stmt->bindParam(":id", $result['id'], PDO::PARAM_INT);
            $stmt->execute();
        }

        // Commit the transaction
        $db->commit();
    } catch (Exception $e) {
        // If an error occurs, rollback the transaction
        $db->rollBack();
        write_debug_log("Error in api_v2_admin_write_debug_log: " . $e->getMessage());
    } finally {
        // Close the database connection
        db_close($db);
    }
}

?>