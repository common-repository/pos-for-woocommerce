<?php  if ( ! defined( 'ABSPATH' ) ) exit; 
  $nonce = wp_create_nonce('pos_reg_page');
?> 
<!DOCTYPE html>
<html>

<head>
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

    .modal {
      display: none;
      /* Hidden by default */
      position: fixed;
      /* Stay in place */
      z-index: 1;
      /* Sit on top */
      padding-top: 100px;
      /* Location of the box */
      left: 0;
      top: 0;
      width: 100%;
      /* Full width */
      height: 100%;
      /* Full height */
      overflow: auto;
      /* Enable scroll if needed */
      background-color: rgb(0, 0, 0);
      /* Fallback color */
      background-color: rgba(0, 0, 0, 0.4);
      /* Black w/ opacity */
    }

    .modal-content {
      background-color: #fefefe;
      margin: auto;
      padding: 20px;
      border: 1px solid #888;
      width: 80%;
    }

    .close {
      color: #aaaaaa;
      float: right;
      font-size: 28px;
      font-weight: bold;
    }

    .close:hover,
    .close:focus {
      color: #000;
      text-decoration: none;
      cursor: pointer;
    }
  </style>
</head>

<body>
  <h1 class="wp-heading-inline">Registration Form</h1>
  <hr class="wp-header-end">
  <div class="tablenav top">
  </div>
  <form method="POST">
    <div class="form-wrap" style="text-align: center">
      <input type="text" name="accountno" size="30" placeholder="Account No" autocomplete="off" style="width: 60%" maxlength="10">
      </br>
      </br>
      <input type="text" name="requestor_name" size="30" placeholder="Name To Contact" autocomplete="off" style="width: 60%">
      </br>
      </br>
      <input type="text" name="email" size="30" placeholder="Email" autocomplete="off" style="width: 60%">
      </br>
      </br>
      <input type="text" name="phoneno" size="30" placeholder="Phone No" autocomplete="off" style="width: 60%">
      </br>
      </br>
      <input type="text" name="storename" size="30" placeholder="Store Name" autocomplete="off" style="width: 60%">
      </br>
      </br>
      <?php if (!defined('ABSPATH'))exit; ?>

      <?php wp_nonce_field('pos_reg_page', 'send_register'); ?>

      <button type='submit' class="button-primary" style="width: 15%; background: #e43a1ff2; border-color: #121213">Submit</button>
      </br>
    </div>
  </form>

  <?php

  if (!empty($res)) {
    $allowed_html = array(
      'br' => array(),
    );
    $result = json_decode($res, true);
    $data;
    if (array_key_exists('data', $result)) {
      $data = $result['data'];
    }
    $error;
    if (array_key_exists('error', $result)) {
      $error = $result['error'];
    }
    if (isset($data) && $data['status'] == 'Active') {
  ?>
      <center>
        <h3>Account has been activated.</h3>
      </center>
    <?php
    } else if (isset($data) && $data['status'] == 'Approve') {
    ?>
      <center>
        <h3>Account has been approved.</h3>
      </center>
    <?php
    } else if (isset($data) && $data['status'] == 'Pending') {
    ?>
      <center>
        <h3>Account has registered successfully. Pending for approval.</h3>
      </center>
    <?php
    } else if (isset($error) && is_array($error)) {
      $keys = array_keys((array)$error);
      $msg = "";
      
      foreach ($keys as $key) {
        if (str_contains($error[$key], 'value is Empty')) {
          $msg = 'All fields are required.';
          break;
        }

        $msg .= $error[$key] . '<br>';
      }
    ?>
      <center>
        <h3><?php echo wp_kses($msg, $allowed_html) ?></h3>
      </center>
    <?php
    } else if (isset($error) && is_string($error)) {
    ?>
      <center>
        <h3><?php echo wp_kses($error, $allowed_html)  ?></h3>
      </center>
    <?php
    } else {
    ?>
      <center>
        <h3>Failed to register account</h3>
      </center>
  <?php
    }
  }
  ?>
  <script>
    var modal = document.getElementById("myModal");

    var btn = document.getElementById("myBtn");

    var span = document.getElementsByClassName("close")[0];

    btn.onclick = function() {
      modal.style.display = "block";
    }

    span.onclick = function() {
      modal.style.display = "none";
    }

    window.onclick = function(event) {
      if (event.target == modal) {
        modal.style.display = "none";
      }
    }
  </script>

</body>

</html>