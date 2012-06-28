<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Mage
 * @package     Mage_Adminhtml
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Customer addresses forms
 *
 * @category   Mage
 * @package    Mage_Adminhtml
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Litle_Palorus_Block_Adminhtml_Palorus_Insight_Subscriptionview extends Mage_Adminhtml_Block_Widget_Form
{
    public function __construct()
    {
        parent::__construct();
        $this->setTemplate('payment/form/subscription.phtml');
    }
    
    protected function _prepareLayout()
    {
    	$cancelButton = $this->getLayout()->createBlock('adminhtml/widget_button')
    	->setData(array(
                    'id'      => 'cancel_button',
                    'label'   => Mage::helper('sales')->__('Cancel Subscription'),
                    'class'   => 'save',
                    'onclick' => 'var r = confirm(\'Are you sure you want to cancel this subscription?\');
					if(r==true){
                    setLocation(\''.$this->getUrl('palorus/adminhtml_myform/subscriptionview/', array('subscription_id' => $this->getSubscriptionId(),'active'=>'0')).'\')
                    }'
    	));
    	$this->setChild('cancel_button', $cancelButton);
    	 	
    	$saveButton = $this->getLayout()->createBlock('adminhtml/widget_button')
    	->setData(array(
    	                    'id'      => 'save_button',
    	                    'label'   => Mage::helper('sales')->__('Save Subscription Details'),
    	                    'class'   => 'save',
    	                 	'onclick' => 
    				'
    				var amount = document.getElementById(\'recurring_fees\').value;
    				var billingDetails = document.getElementById(\'billing_period\').value;
    				var billingCycles = document.getElementById(\'total_number_of_billing_cycles\').value;
    				pathArray = document.URL.split( \'amount\' );
					host = pathArray[0];
					host = host.split( \'key\' )[0];
    				pathArray = document.URL.split( \'key\' );
    				alert(host+\'amount/\'+amount+\'/billingDetails/\'+billingDetails+\'/billingCycles/\'+billingCycles+\'/key\'+pathArray[1])
    			    setLocation(host+\'amount/\'+amount+\'/billingDetails/\'+billingDetails+\'/billingCycles/\'+billingCycles+\'/key\'+pathArray[1])'		
    	));
    	$this->setChild('save_button', $saveButton);
    	
    	$resumeButton = $this->getLayout()->createBlock('adminhtml/widget_button')
    	->setData(array(
    	    	                    'id'      => 'resume_button',
    	    	                    'label'   => Mage::helper('sales')->__('Resume Subscription'),
    	    	                    'class'   => 'save',
    	));
    	$this->setChild('resume_button', $resumeButton);
    	 
    	
    	$suspendButton = $this->getLayout()->createBlock('adminhtml/widget_button')
    	->setData(array(
    	                    'id'      => 'suspend_button',
    	                    'label'   => Mage::helper('sales')->__('Suspend Subscription'),
    	                    'class'   => 'save',
    	'onclick' => 'var skips = prompt(\'How many iterations would you like to skip?\');
    	if(skips!=null){
    		if(skips>0 && skips%1===0  && skips<1000){
    			var r = confirm(\'Are you sure you want to suspend to subscription for \' + skips + \' iterations?\');
    			if(r==true){
    				pathArray = document.URL.split( \'skips\' );
					host = pathArray[0];
					host = host.split( \'key\' )[0];
					alert(host);
    				pathArray = document.URL.split( \'key\' );
    			    setLocation(host+\'skips/\'+skips+\'/key\'+pathArray[1])		
    					}
    				}
    				else alert(\'Enter a valid number of iterations to skip\');
    			}'
    	                    
    	));
    	$this->setChild('suspend_button', $suspendButton);
    	
    
    	return parent::_prepareLayout();
    }
    
    public function suspendSubscription($skips){
    	for($i=1; $i<=$skips; $i++){
    		$nextDate = $this->getNextBillDate();
    		$nextDate = Mage::getModel('palorus/subscription')->getNextBillDate($this->getIterationLength(), $nextDate);
    		$this->setNextBillDate($nextDate);
    	}
    }
    
    public function getSubscriptionStatusMessage(){
    	$recycling = $this->getIsRecycling();
    	if($recycling === "No"){
    		$message[0] = "success-msg";
    		$message[1] = "Subscription is in good condition.";
    	}
    	else{
    		$nextBill = strtotime($this->getNextBillDate());
    		$nextRecycle = strtotime($this->getRecyclingData('to_run_date'));
    		if($nextBill < $nextRecycle){
    			$message[0] = "error-msg";
    			$message[1] = "Subscription is in bad condition.";
    		}
    		else{
    			$message[0] = "warning-msg";
    			$message[1] = "Subscription is in recycling";
    		}
    	}
    	return $message;
    }
    
    private function getSubcriptionRow(){
    	$subscriptionId = $this->getSubscriptionId();
    	return Mage::getModel('palorus/subscription')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
    }

    private function getSubscriptionData(string $field)
    {
    	$collection = $this->getSubcriptionRow();
    	foreach ($collection as $order){
    		$row = $order->getData();
    		return $row[$field];
    	}
    }
    
    public function updateSubscription($amount, $period, $billingCycles, $nextBill){
    	$this->setSubscriptionAmount($amount);
    	$this->setIterationLength($period);
    	$this->setNumOfIterations($billingCycles);
    }
    
    private function getSubcriptionHistory(){
    	$subscriptionId = $this->getSubscriptionId();
    	return Mage::getModel('palorus/subscriptionHistory')->getCollection()->addFieldToFilter('subscription_id', $subscriptionId);
    }
    
    public function getSubscriptionHistoryTable()
    {
     	$collection = $this->getSubcriptionHistory();
     	$index=0;
     	foreach ($collection as $order){
     		$row = $order->getData();
     		$table[$index] = $row;
     		$index = $index+1;
     	}
     		return $table;
    }
    
    private function getRecyclingRow(){
    	$subscriptionId = $this->getSubscriptionId();
    	return Mage::getModel('palorus/recycling')->getCollection()->addFieldToFilter('subscription_id',$subscriptionId);
    }
    
    public function getSubscriptionName()
    {
    	$collection = $this->getSubcriptionRow();
    	foreach ($collection as $order){
    		$row = $order->getData();
    		$productName = $row['product_id'];
    		$product = Mage::getModel('catalog/product')->load($productName);
    		return $product->getName();
    	}
    }
    
     public function getSubscriptionId(){
     	$url = $this->helper("core/url")->getCurrentUrl();
     	$stringAfterSubscriptionId = explode('subscription_id/', $url);
     	$stringBeforeKey = explode('/', $stringAfterSubscriptionId[1]);
     	return $stringBeforeKey[0];
     }
     
     public function getRecyclingData(string $field){
    	$collection =$this->getRecyclingRow();
    	foreach ($collection as $order){
    		$row = $order->getData();
    		return $row[$field];
    	}
     }
     
     public function getIsRecycling(){
     	$runNextIteration = $this->getSubscriptionData('run_next_iteration');
     	$active = $this->getSubscriptionData('active');
     	if(!$runNextIteration && $active){
     		return "Yes";
     	}else{
     		return "No";
     	}
     }
     
     public function getActive(){
     	return $this->getSubscriptionData('active');	
     }
     
     public function setActive($active){
     	$collection = $this->getSubcriptionRow();
     	foreach ($collection as $order){
     		$order->setActive($active)->save();
     	}
     }
     
     public function getNextRecycleAttempt(){
     	if($this->getIsRecycling()==="No")
     		return "N/A";
     	else
     		return $this->getRecyclingData('to_run_date');
     }
     
     public function getInitialFees(){
     	$initialFees = $this->getSubscriptionData('initial_fees');
     	return $this->dollarFormat($initialFees);
     }
     
     public function getSubscriptionAmount(){
     	$amount = $this->getSubscriptionData('amount');
     	return $this->dollarFormat($amount);
     }
     
     public function setSubscriptionAmount($amount){
     	$collection = $this->getSubcriptionRow();
     	foreach ($collection as $order){
     		$order->setAmount($amount*100)->save();
     	}
     }
     
     public function getStartDate(){
     	$date = $this->getSubscriptionData('start_date');
     	return $date;
     	//return date("F j, Y, g:i a", $date in date format);
     }
     
     public function getIterationLength(){
     	return $this->getSubscriptionData('iteration_length');
     }
     
     public function setIterationLength($period){
     	$collection = $this->getSubcriptionRow();
     	foreach ($collection as $order){
     		$order->setIterationLength($period)->save();
     	}
     }
     
     public function getNumOfIterations(){
     	return $this->getSubscriptionData('num_of_iterations');
     }
     
     public function setNumOfIterations($num){
     	$collection = $this->getSubcriptionRow();
     	foreach ($collection as $order){
     		$order->setNumOfIterations($num)->save();
     	}
     }
     
     public function getNumOfIterationsRan(){
     	return $this->getSubscriptionData('num_of_iterations_ran');
     }
     
     public function getNextBillDate(){
     	return $this->getSubscriptionData('next_bill_date');
     }
     
     public function setNextBillDate($date){
     	$collection = $this->getSubcriptionRow();
     	foreach ($collection as $order){
     		$order->setNextBillDate($date)->save();
     	}
     }
     
     public function getCronId(){
     	return $this->getSubscriptionData('next_bill_date');
     }
     
     public function dollarFormat($num){
     	return money_format('%i', $num/100);
     }

    /**
     * Check block is readonly.
     *
     * @return boolean
     */
    public function isReadonly()
    {
    	return false;
    }
}
