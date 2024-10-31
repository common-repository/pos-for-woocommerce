<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
	$base_dir = str_replace(ABSPATH, '/', realpath(__DIR__));
  $w3_css_path = $base_dir . '/../css/w3.css';
  wp_enqueue_style('tracking-status-css', $w3_css_path);
  $nonce = wp_create_nonce('pos_tracking_page');
?> 
<!DOCTYPE html>
<html>

<head>
  <link rel="stylesheet">
  <title></title>
  <style type="text/css">
    .body-item {
      display: flex;
      width: 100%;
      justify-content: start;
    }

    .body-middle-axis {
      display: flex;
      width: 1rem;
      flex-direction: column;
      align-items: center;
      justify-content: center;
    }

    .body-left-date {
      display: flex;
      width: 30%;
      align-items: flex-end;
      flex-direction: column;
      justify-content: center;
      padding: 10px;
    }

    .online-top-closing {
      width: 1px;
      height: 3rem;
      background: #999
    }

    .dot-closing {
      width: .6rem;
      height: .6rem;
      border-radius: 50%;
      background: #fe4f33;
    }

    .online-bottom {
      width: 1px;
      height: 3rem;
      background: #999;
    }

    .body-right {
      display: flex;
      flex-grow: 1;
      flex-direction: column;
      justify-content: center;
      padding: 10px;
    }
  </style>
</head>

<body>
  <h1 class="wp-heading-inline">Tracking Connote POS</h1>
  <hr class="wp-header-end">
  <div class="tablenav top">
  </div>
  <form method="POST">
    <div class="form-wrap" style="text-align: center">
      <?php wp_nonce_field('pos_tracking_page', 'send_tracking'); ?>
      <input type="text" id="connote" name="tracking" size="30" placeholder="Consignment Number" autocomplete="off" style="width: 60%">
      <?php if (!defined('ABSPATH'))exit; ?>
      <button type='submit' class="button-primary" style="width: 15%; background: #DF1E34; border-color: #121213">Track</button>
    </div>
  </form>
  <br /><br />
  <center>
    <?php
    if ($res) {
      $data;
      $datatype = '';
      if (array_key_exists('data', $res)) {
        $data = $res['data'];
        $datatype = $data[0]['type'];
      }
      $error;
      if (array_key_exists('error', $res)) {
        $error = $res['error'];
      }
      if (isset($error) && is_string($error)) { ?>
        <b>
          <font color=red><?php echo esc_attr($error) ?>
        </b>
        <font>
        <?php
      } else if ($datatype == "Valid") {
        ?>
          <table class="w3-table w3-striped" border="1" width="70%">
            <thead>
              <tr>
                <td bgcolor="#DF1E34" width="20%"><b>
                    <font color="white">Date</font>
                  </b></caption< /td>
                <td bgcolor="#DF1E34" width="40%"><b>
                    <font color="white">Description</font>
                  </b></td>
                <td bgcolor="#DF1E34" width="40%"><b>
                    <font color="white">Office</font>
                  </b></td>
              </tr>
            </thead>
          </table><?php
                  foreach ($data as $track_detail) {
                  ?>
            <table class="w3-table w3-striped" border="1" width="70%">
              <tr>
                <td width="20%"><?php echo esc_attr($track_detail['date']) ?></td>
                <td width="40%"><?php echo esc_attr($track_detail['process']) ?></td>
                <td width="40%"><?php echo esc_attr($track_detail['office']) ?></td>
              </tr>
            </table>

          <?php
                  }
                } else if ($datatype == "Invalid Request/Empty Connote") { ?>
          <b>
            <font color=red>Please insert valid Tracking Number.
          </b>
          <font>
          <?php
                } else if (isset($error) && $error == "Connote number not found.") {
          ?>
            <b>
              <font color=red>No record found.
            </b>
            <font>
            <?php
                } else if (isset($error) && !empty($error['connoteNo'])) {
            ?>
              <b>
                <font color=red><?php echo esc_attr($error['connoteNo']) ?>
              </b>
              <font>
              <?php
                } else if ($datatype == "Error") { ?>
                <b>
                  <font color=red>No record found.
                </b>
                <font>
              <?php
                }
              }
              ?>
  </center>
  <script>
    function posMalaysiatracking() {
      let tokenAmount = document.getElementById("connote").value;
      return
    }
  </script>
</body>

</html>