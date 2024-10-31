<?php  if ( ! defined( 'ABSPATH' ) ) exit;  ?>
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
	</style>
</head>

<body>
	<hr class="wp-header-end">
	<div class="tablenav top">
	</div>
	<form method="POST">
		<div class="form-wrap" style="text-align: center">
			<button class="button-primary" name="register" style="width: 15%; background: #e43a1ff2; border-color: #121213">Register</button>
      
			<button class="button-primary" name="tracking" style="width: 15%; background: #e43a1ff2; border-color: #121213">Trace & Track</button>
		</div>
    </form>
	
</body>
</html>