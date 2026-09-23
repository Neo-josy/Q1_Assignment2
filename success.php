<?php
// Include configuration file	
include_once 'paypal_config.php';	


// If transaction data is available in the URL 
if(!empty($_GET['item_number']) && !empty($_GET['tx']) 
&& !empty($_GET['amt']) && !empty($_GET['cc']) 
&& !empty($_GET['st'])){ 
		// Get transaction information from URL 
		$item_number = $_GET['item_number'];  
		$txn_id = $_GET['tx']; 
		$payment_gross = $_GET['amt']; 
		$currency_code = $_GET['cc']; 
		$payment_status = $_GET['st']; 
		// Get product info from the database 
		$result = array();

		foreach ($item as $product) {
			    if ($product['id'] === $item_number) {
			        $result = $product;
			        break;
			    }
			}

		$product_name = $result['name'];
		$product_price = $result['price'];
	}
?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Payment Gateway</title>
</head>
<body>
	<h1>Payment Info</h1>
	<div class="container">
	    <div class="status">
	        <?php
	        if(!empty($txn_id)){ 
	        ?>
	            <h1 class="success">Your Payment has been Successful</h1>
				
	            <h4>Payment Information</h4>
	            <p><b>Transaction ID:</b> <?php echo $txn_id; ?></p>
	            <p><b>Paid Amount:</b> <?php echo $payment_gross; ?></p>
	            <p><b>Payment Status:</b> <?php echo $payment_status; ?></p>
				
	            <h4>Product Information</h4>
	            <p><b>Name:</b> <?php echo $product_name; ?></p>
	            <p><b>Price:</b> <?php echo $product_price; ?></p>
	        <?php 
	        }else{ 
	        ?>
	            <h1 class="error">Your Payment cannot be confirmed now, please check your sandbox account</h1>
	        <?php 
	        } 
	        ?>
	    </div>
	    <a href="index.php" class="btn-link">Back to Products</a>
	</div>
</body>
</html>