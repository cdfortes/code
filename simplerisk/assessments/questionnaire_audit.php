<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
* License, v. 2.0. If a copy of the MPL was not distributed with this
* file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Include required functions file
require_once(realpath(__DIR__ . '/../includes/functions.php'));
require_once(realpath(__DIR__ . '/../includes/authenticate.php'));
require_once(realpath(__DIR__ . '/../includes/display.php'));
require_once(realpath(__DIR__ . '/../includes/assessments.php'));
require_once(realpath(__DIR__ . '/../includes/alerts.php'));
require_once(realpath(__DIR__ . '/../vendor/autoload.php'));

// Add various security headers
add_security_headers();

// Add the session
$permissions = array(
        "check_access" => true,
        "check_assessments" => true,
);
add_session_check($permissions);

// Include the CSRF Magic library
include_csrf_magic();

// Include the SimpleRisk language file
// Ignoring detections related to language files
// @phan-suppress-next-line SecurityCheck-PathTraversal
require_once(language_file());

// Check if assessment extra is enabled
if(assessments_extra())
{
    // Include the assessments extra
    require_once(realpath(__DIR__ . '/../extras/assessments/index.php'));
}
else
{
    header("Location: ../index.php");
    exit(0);
}

?>

<!doctype html>
<html lang="<?php echo $escaper->escapehtml($_SESSION['lang']); ?>" xml:lang="<?php echo $escaper->escapeHtml($_SESSION['lang']); ?>">

<head>
    <meta http-equiv="X-UA-Compatible" content="IE=10,9,7,8">

    <!-- jQuery Javascript -->
    <script src="../vendor/node_modules/jquery/dist/jquery.min.js?<?= $current_app_version ?>" id="script_jquery"></script>
    <script src="../vendor/node_modules/jquery-ui/dist/jquery-ui.min.js?<?= $current_app_version ?>" id="script_jqueryui"></script>

    <!-- Bootstrap tether Core JavaScript -->
    <script src="../vendor/node_modules/bootstrap/dist/js/bootstrap.bundle.min.js"></script>

    <script src="../js/simplerisk/pages/assessment.js?<?= $current_app_version ?>"></script>
    <script src="../js/simplerisk/common.js?<?= $current_app_version ?>"></script>
    
    <title>SimpleRisk: Enterprise Risk Management Simplified</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta content="text/html; charset=UTF-8" http-equiv="Content-Type">
    <link rel="stylesheet" href="../css/bootstrap.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../css/bootstrap-responsive.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../vendor/node_modules/datatables.net-dt/css/jquery.dataTables.min.css?<?= $current_app_version ?>">

    <link rel="stylesheet" href="../css/divshot-util.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../css/divshot-canvas.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../css/display.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../vendor/components/font-awesome/css/fontawesome.min.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../css/theme.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../css/side-navigation.css?<?= $current_app_version ?>">
    <link rel="stylesheet" href="../css/selectize.bootstrap3.css?<?= $current_app_version ?>">

    <?php
        setup_favicon("..");
        setup_alert_requirements("..");
    ?>
</head>

<body>

    <?php
        view_top_menu("Assessments");

        // Get any alerts
        get_alert();
    ?>
    <!--<div id="load" style="display:none;"><img src="<?php echo $_SESSION['base_url']; ?>/images/loading.gif"></div>-->
    <div id="load" style="display:none;"><?php echo $escaper->escapeHtml($lang['SendingPleaseWait']); ?></div>
    <div class="container-fluid">
        <div class="row-fluid">
            <div class="span3">
                <?php view_assessments_menu("QuestionnaireResults"); ?>
            </div>
            <div class="span9">
                <?php 
                    display_questionnaire_control_audits(); 
                ?>
            </div>
        </div>
    </div>
    <?php display_set_default_date_format_script(); ?>
</body>

</html>
