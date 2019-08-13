<?php
class Chandan_Ajaxaddress_AjaxaddressController extends Mage_Core_Controller_Front_Action
{
	function getCityState($zip, $blnUSA = true) {
		$url = "http://maps.googleapis.com/maps/api/geocode/json?address=" . $zip . "&sensor=true";
		$address_info = file_get_contents($url);
		$json = json_decode($address_info);
		$city = "";
		$state = "";
		$country = "";
		if (count($json->results) > 0) {        
			$arrComponents = $json->results[0]->address_components;
			foreach($arrComponents as $index=>$component) {
				$type = $component->types[0];
				if ($city == "" && ($type == "sublocality_level_1" || $type == "locality") ) {
					$city = trim($component->short_name);
				}
				if ($country == "" && $type=="country") {
					$country = trim($component->short_name);                
				}
				if ($state == "" && $type=="administrative_area_level_1") {
					$state = trim($component->short_name);
					$collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($country)->load(); 		
					$count = $collection->getData();
					foreach($collection as $region) {
						if($region["code"] == $state)
						{
							$state_code = $region["region_id"];
							break;
						}
					}
				}
				
				if ($city != "" && $state != "" && $country != "") {                
					break;
				}
			}
		}
		$arrReturn = array("city"=>$city, "state"=>$state_code, "country"=>$country);
		die(json_encode($arrReturn));
	}
		
	public function ajaxregionAction(){	
		$zip = $this->getRequest()->getParam('zip');
		echo $this->getCityState($zip);
		die;
	}
	
	
	public function countryselectAction(){		
		$countryid = $this->getRequest()->getParam('country');		
		$collection = Mage::getModel('directory/region')->getResourceCollection()->addCountryFilter($countryid)->load(); 		
		$count = $collection->getData();

		if(!empty($count)) {
			echo '<option value="">Please Select</option>';
			 foreach($collection as $region) {
				echo '<option value='.$region["region_id"].'>'.$region["default_name"].'</option>';
			 }
		} else {
			echo '<input type="text" id="billing:region_id" name="billing[region_id]" title="State/Province" class="input-text">';	
		}		
	}
}