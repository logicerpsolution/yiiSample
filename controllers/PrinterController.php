<?php
/**
 * PrinterController Controller
 *
 */
if (!isset($_SESSION)) { session_start(); }

class PrinterController extends CController
{
	public function actionIndex()
	{
		//Index Action
		echo "index action";
	}
	public function actionSendOrder()
	{
		//Sending the order details to the designated printer
		
	}
	public function actionCallback($a,$o,$ak,$m,$dt,$u,$p)
	{
		$webroot = Yii::getPathOfAlias('webroot');
		$file =  $webroot . DIRECTORY_SEPARATOR . 'upload' . DIRECTORY_SEPARATOR . 'orderResult.txt';
		$handle1 = fopen($file, 'a');
		$orderId=$o;
		$db_ext=new DbExt;
		//get orderdata
		$stmt1="select * from {{order}} where order_id=".$orderId; 
		if ( $res=$db_ext->rst($stmt1)){
				foreach ($res as $val) {
					 $merchantId=$val['merchant_id'];
					 $paymentType=$val['payment_type'];
				}
		}
		if($ak=="Accepted"){
			//Order is accepted send acceptance mail & change order status in order table                                                  
			$params=array(
					 'status'=>"received",
					 'date_modified'=>date('c'),
					 'ip_address'=>$_SERVER['REMOTE_ADDR'],
					 'viewed'=>2
					);
		 $db_ext->updateData("{{order}}",$params,'order_id',$orderId);
		 //redirecting to the payment page after order acceptance
			 if ( $paymentType=="pyp" || $paymentType =="paypal"){
				 $this->redirect(Yii::app()->request->baseUrl."/store/paypalInit/id/".$orderId);
			} 
			else if( $paymentType =="stp" ||  $paymentType =="stripe" )	{
				$this->redirect(Yii::app()->request->baseUrl."/store/stripeInit/id/".$orderId);
			} 
			else if( $paymentType =="mcd" ||  $paymentType =="mercadopago" ){
				$this->redirect(Yii::app()->request->baseUrl."/store/mercadoInit/id/".$orderId);
			}
			else if( $paymentType =="pyl"){
				$this->redirect(Yii::app()->request->baseUrl."/store/paylineinit/id/".$orderId);	
			} 
			else if( $paymentType =="ide"){
				$this->redirect(Yii::app()->request->baseUrl."/store/sisowinit/id/".$orderId);			
			} 
			else if( $paymentType =="payu")	{
				$this->redirect(Yii::app()->request->baseUrl."/store/payuinit/id/".$orderId);		
			}
			else if( $paymentType =="pys")	{
				$this->redirect(Yii::app()->request->baseUrl."/store/stripeInit/id/".$orderId);
				window.location.replace(sites_url+"/store/payserainit/id/"+data.details.order_id);					
			} 
			else if( $paymentType =="bcy"){
				$this->redirect(Yii::app()->request->baseUrl."/store/bcyinit/id/".$orderId);					
			} 
			else if( $paymentType =="epy")	{
				$this->redirect(Yii::app()->request->baseUrl."/store/epyinit/id/".$orderId);					
			} 
			else if( $paymentType =="atz")	{
				$this->redirect(Yii::app()->request->baseUrl."/store/atzinit/id/".$orderId);
			/*braintree*/	
			} else if( $paymentType =="btr")	{
				$this->redirect(Yii::app()->request->baseUrl."/store/btrinit/id/".$orderId);					
			} 
			else{
				//$this->msg=t("Thank you for subscribing to our mailing list!");
				$this->redirect(array(Yii::app()->request->baseUrl."/store/receipt/id/".$orderId, 'msg'=>'Thank you for subscribing to our mailing list!'));
			}
			
		}
		else if($ak=="Rejected"){
			//Order is rejected send acceptance mail  & change order status in order table   
			$rejectReason=$m;			
			//TOO BUSY;FOOD UNAVAILABLE;UNABLE TO DELIVER;DONT DELIVER TO AREA;UNKNOWN ADDRESS;TIME UNAVAILABLE;JAM - PLEASE REORDER;
			$params=array(
					 'status'=>"cancelled",
					 'date_modified'=>date('c'),
					 'ip_address'=>$_SERVER['REMOTE_ADDR'],
					 'viewed'=>2
					);
			$db_ext->updateData("{{order}}",$params,'order_id',$orderId);			
		}
		
		
		
		//fetch the order text filename for the merchant
		$stmt4="select ordertextFileName from {{merchant}} where merchant_id=".$merchantId; 
		if ( $res=$db_ext->rst($stmt4)){
			foreach ($res as $val) {
				$orderFilename=$val['ordertextFileName'];
			}
		}
		if($orderFilename){
			//Empty the order text file to prevent re-printing of orders
			$foodItemstxt="";
			$webroot = Yii::getPathOfAlias('webroot');
			$file =  $webroot . DIRECTORY_SEPARATOR . 'upload' .DIRECTORY_SEPARATOR . $orderFilename;
			$handle = fopen($file, 'w');
			fwrite($handle, $foodItemstxt);
		}
		$stringData = $a.','.$o.','.$ak.','.$m.','.$dt.','.$u.','.$p;
		 fwrite($handle1, $stringData);
	 
	} 	
}
?>
