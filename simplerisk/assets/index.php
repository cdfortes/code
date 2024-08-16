<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
* License, v. 2.0. If a copy of the MPL was not distributed with this
* file, You can obtain one at http://mozilla.org/MPL/2.0/. */

// Render the header and sidebar
require_once(realpath(__DIR__ . '/../includes/renderutils.php'));
render_header_and_sidebar(['blockUI'], ['check_assets' => true]);

$searchresult = array();
// Check if an asset search was submitted
if ((isset($_POST['search'])))
{
  $range = $_POST['range'];
  $AvailableIPs = discover_assets($range);

  // If the IP was not in a recognizable format
  if ($AvailableIPs === false)
  {
    // Display an alert
    set_alert(true, "bad", $escaper->escapeHtml($lang['IPFormatNotRecognized']));
  } else {
    if(count($AvailableIPs)){
      foreach($AvailableIPs as $ip){
        $searchresult[] = $ip['ip'];
      }
    } else {
      $NoSearchResults = true;
    }

  }
}
?>
<div class="row bg-white ">
  <div class="col-12 m-2">
    <p><?= $escaper->escapeHtml($lang['AutomatedDiscoveryHelp']) ?></p>
    <ul>
      <li>192.168.0.1</li>
      <li>192.168.0.1-192.168.0.255</li>
    </ul>
    <form id="discover_assets" name="discover_assets" method="post" action="" enctype="multipart/form-data">
      <div class="col-6">
        <div class="form-group">
          <label><?= $escaper->escapeHtml($lang['IPRange']) ?></label>
          <input maxlength="100" name="range" id="range" class="form-control" type="text">
        </div>
        <div class="form-group form-actions">
          <button type="submit" name="search" class="btn btn-submit"><?= $escaper->escapeHtml($lang['Search']) ?></button>
        </div>
        <div>
<?php
    if(count($searchresult)) {
        echo $escaper->escapeHtml($lang['SearchResults'])." : ".implode(", ", $searchresult);
    } else if(isset($NoSearchResults)){
        echo $escaper->escapeHtml($lang['NoSearchResults']);
    }
?>
        </div>
      </div>
    </form>
  </div>
</div>
<script type="text/javascript">
    $('form#discover_assets').submit(function() {
        $.blockUI({message:"<i class='fa fa-spinner fa-spin' style='font-size:24px'></i>"});
    });
</script>
<?php
// Render the footer of the page. Please don't put code after this part.
render_footer();
?>

