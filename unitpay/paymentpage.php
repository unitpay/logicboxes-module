<?php
	require("functions.php");	//file which has required functions
	require("config.php");
?>	 	
		
<html>
<head><title>Payment Page </title>
</head>
<body bgcolor="white">

<?php
		
		//This filter removes data that is potentially harmful for your application. It is used to strip tags and remove or encode unwanted characters.
		$_GET = filter_var_array($_GET, FILTER_SANITIZE_STRING);
		
		//Below are the  parameters which will be passed from foundation as http GET request
		$paymentTypeId = $_GET["paymenttypeid"];  //payment type id
		$transId = $_GET["transid"];			   //This refers to a unique transaction ID which we generate for each transaction
		$userId = $_GET["userid"];               //userid of the user who is trying to make the payment
		$userType = $_GET["usertype"];  		   //This refers to the type of user perofrming this transaction. The possible values are "Customer" or "Reseller"
		$transactionType = $_GET["transactiontype"];  //Type of transaction (ResellerAddFund/CustomerAddFund/ResellerPayment/CustomerPayment)

		$invoiceIds = $_GET["invoiceids"];		   //comma separated Invoice Ids, This will have a value only if the transactiontype is "ResellerPayment" or "CustomerPayment"
		$debitNoteIds = $_GET["debitnoteids"];	   //comma separated DebitNotes Ids, This will have a value only if the transactiontype is "ResellerPayment" or "CustomerPayment"

		$description = $_GET["description"];
		
		$sellingCurrencyAmount = $_GET["sellingcurrencyamount"]; //This refers to the amount of transaction in your Selling Currency
        $accountingCurrencyAmount = $_GET["accountingcurrencyamount"]; //This refers to the amount of transaction in your Accounting Currency

		$redirectUrl = $_GET["redirecturl"];  //This is the URL on our server, to which you need to send the user once you have finished charging him
		$resellerCurrency = $_GET["resellerCurrency"];

						
		$checksum = $_GET["checksum"];	 //checksum for validation

		 echo "File paymentpage.php<br>";
         echo "Checksum Verification................";

		if(verifyChecksum($paymentTypeId, $transId, $userId, $userType, $transactionType, $invoiceIds, $debitNoteIds, $description, $sellingCurrencyAmount, $accountingCurrencyAmount, $key, $checksum))
		{
			//YOUR CODE GOES HERE			

		/** 
		* since all these data has to be passed back to foundation after making the payment you need to save these data
		*	
		* You can make a database entry with all the required details which has been passed from foundation.  
		*
		*							OR
		*	
		* keep the data to the session which will be available in postpayment.php as we have done here.
		*
		* It is recommended that you make database entry.
		**/

			$data = [
				$redirectUrl,
				$transId,
				$sellingCurrencyAmount,
				$accountingCurrencyAmount
			];
			
			$sum = $sellingCurrencyAmount;
			$account = join('|', $data);
			$desc = 'Транзакция #' . $transId;

			$url = 'https://unitpay.ru/pay/' . $public_key;
	 		?>
			<form id="unitpay_form" action="<?php echo $url; ?>">
			<input type="hidden" name="sum" value="<?php echo $sum; ?>">
			<input type="hidden" name="desc" value="<?php echo $desc; ?>">
			<input type="hidden" name="account" value="<?php echo $account; ?>">
			<input type="hidden" name="currency" value="<?php echo $resellerCurrency; ?>">
			</form>
			<script type="text/javascript">
    			document.getElementById('unitpay_form').submit();
			</script>

	 		<?php
			

		}
		else
		{
			/**This message will be dispayed in any of the following case
			*
			* 1. You are not using a valid 32 bit secure key from your Reseller Control panel
			* 2. The data passed from foundation has been tampered.
			*
			* In both these cases the customer has to be shown error message and shound not
			* be allowed to proceed  and do the payment.
			*
			**/

			echo "Checksum mismatch !";			

		}
?>
</body>
</html>
