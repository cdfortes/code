<?php

/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Only run this script if called via the command line
if (php_sapi_name() == "cli")
{
        // Include required functions file
    require_once(realpath(__DIR__ . '/../includes/functions.php'));
	require_once(realpath(__DIR__ . '/../includes/config.php'));
    require_once(realpath(__DIR__ . '/../vendor/autoload.php'));

	// If we should do automatic backups
	if (get_setting('backup_auto') == "true") {

	    $message = "Automatic backup requested.";
	    write_debug_log_cli($message);
	    write_log(0, 0, $message, 'backup');

	    do_backup();
	}
}

// To do the actual backup
// It's a separate function to be able to call from the UI
// the $force parameter is to tell the function that it is called from the UI to force an immediate backup
// (in this case the set_alert() function that can be used since there IS a session)
function do_backup($force=false) {
    // Create a timestamp for the current date and time
    $timestamp = date("Y-m-d--H-i-s");
    
    // Get the backup directory path
    $backup_path = get_setting('backup_path');
    
    // Set the error to false
    $error = false;
    
    // If the backup directory does not exist
    if (!is_dir($backup_path))
    {
        // If we could not create the backup directory
        if (!mkdir($backup_path))
        {
            // We have a problem
            $error = true;
            
            // Write a message to the error log
            $message = "Unable to create a backup directory under " . $backup_path . ".";
            write_debug_log_cli($message);

            // If the backup was requested from the UI
            if ($force) {
                set_alert(true, "bad", $message);
            }
        }
    }
    
    // Get the path to the timestamp directory for this backup
    $timestamp_path = $backup_path . '/' . $timestamp;
    
    // If the timestamp directory does not exist
    if (!$error && !is_dir($timestamp_path))
    {
        // If we could not create the timestamp directory
        if (!mkdir($timestamp_path))
        {
            // We have a problem
            $error = true;
            
            // Write a message to the error log
            $message = "Unable to create a backup directory under " . $timestamp_path . ".";
            write_debug_log_cli($message);

            // If the backup was requested from the UI
            if ($force) {
                set_alert(true, "bad", $message);
            }
        }
    }
    
    if ($error) {
        $message = "Backup failed.";
        write_debug_log_cli($message);
        write_log(0, 0, $message, 'backup');

        // If the backup was requested from the UI
        if ($force) {
            set_alert(true, "bad", $message);
        }

        return;
    }
    
    // Get the path to the database backup file
    $db_backup_file = $timestamp_path . '/simplerisk-db-backup-' . $timestamp . '.sql';

    // Get the mysqldump command
    $cmd = get_mysqldump_command();

    // Add the output redirect to the mysqldump command
    $db_backup_cmd = $cmd . ' > ' . escapeshellarg($db_backup_file);

    // Backup the database
    $mysqldump = system($db_backup_cmd);

    $db_zip_file_name = $timestamp_path . '/simplerisk-db-backup-' . $timestamp . '.zip';
    create_zip_file($db_backup_file, $db_zip_file_name);
    
    // Remove the uncompressed database backup
    unlink($db_backup_file);

    // Create a zip file containing the current SimpleRisk files
    $simplerisk_directory =  realpath(__DIR__) . '/../';
    $app_zip_file_name = $timestamp_path . '/simplerisk-app-backup-' . $timestamp . '.zip';
    create_zip_file($simplerisk_directory, $app_zip_file_name);
    
    // Get the number of days to keep backups for
    $backup_remove = (int)get_setting('backup_remove');
    
    // Create a random id for the backup
    $random_id = generate_token(50);

    // Open the database connection
    $db = db_open();
    
    // Insert the backup information into the database
    $stmt = $db->prepare("INSERT INTO `backups` (`random_id`, `app_zip_file_name`, `db_zip_file_name`) VALUES (:random_id, :app_zip_file_name, :db_zip_file_name);");
    $stmt->bindParam(":random_id", $random_id, PDO::PARAM_STR, 50);
    $stmt->bindParam(":app_zip_file_name", $app_zip_file_name, PDO::PARAM_STR);
    $stmt->bindParam(":db_zip_file_name", $db_zip_file_name, PDO::PARAM_STR);
    $stmt->execute();

    // Get the list of expired backups
    $stmt = $db->prepare("SELECT * FROM `backups` WHERE timestamp < (NOW() - INTERVAL :backup_remove DAY);");
    $stmt->bindParam(":backup_remove", $backup_remove, PDO::PARAM_INT);
    $stmt->execute();
    $backups_to_delete = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Iterate through the list of expired backups and clean them up
    foreach ($backups_to_delete as $backup_to_delete) {

        // Initializing variables in every iteration
        $parent_directory = dirname($backup_to_delete['app_zip_file_name']);
        $missing_backup_directory = !is_dir($parent_directory);
        $backup_directory_delete_successful = false;

        // If the backup directory exists
        if (!$missing_backup_directory) {

            // Remove all files from this directory
            array_map('unlink', glob("$parent_directory/*.*"));

            // Remove the directory
            rmdir($parent_directory);

            // Check if the directory was successfully removed
            $backup_directory_delete_successful = !is_dir($parent_directory);
            if ($backup_directory_delete_successful) {
                $message = "Removed backup older than {$backup_remove} days: '{$parent_directory}'.";
            } else {
                $message = "Failed to remove backup directory '{$parent_directory}', not deleting the associated database entry.";
            }
        } else {
            $message = "Backup directory '{$parent_directory}' was already deleted, cleaned up associated database entry.";
        }

        // Delete the database entry if the directory was either missing already or successfully deleted
        if ($missing_backup_directory || $backup_directory_delete_successful) {
            $stmt = $db->prepare("DELETE FROM `backups` WHERE `random_id` = :random_id;");
            $stmt->bindParam(":random_id", $backup_to_delete['random_id'], PDO::PARAM_STR);
            $stmt->execute();
        }

        write_debug_log_cli($message);
        write_log(0, 0, $message, 'backup');
    }

    $message = "Backup successfully completed.";
    write_debug_log_cli($message);
    write_log(0, 0, $message, 'backup');

    // If the backup was requested from the UI
    if ($force) {
        set_alert(true, "good", $message);
    }
}

?>
