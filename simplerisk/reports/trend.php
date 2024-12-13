<?php
    /* This Source Code Form is subject to the terms of the Mozilla Public
    * License, v. 2.0. If a copy of the MPL was not distributed with this
    * file, You can obtain one at http://mozilla.org/MPL/2.0/. */

    // Render the header and sidebar
    require_once(realpath(__DIR__ . '/../includes/renderutils.php'));
    render_header_and_sidebar(['chart.js']);

    // Include required functions file
    require_once(realpath(__DIR__ . '/../includes/reporting.php'));

?>
<div class="row">
    <div class="col-12">
        <div class="card-body border my-2">
    <?php 
            get_risk_trend(js_string_escape($lang['RisksOpenedAndClosedOverTime'])); 
    ?>
        </div>
    </div>
</div>
<?php
    // Render the footer of the page. Please don't put code after this part.
    render_footer();
?>