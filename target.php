<?php

header('Content-type: application/json');

/**
 * This class will handling all scrapping functionality using Google place API
 * @author		Md Prawez Musharraf
 * @version		1.0
 * @createdOn 	        29th July 2015
 */
class doscraping {

    /**
     * Define class variables
     */
    public $businessName;
    public $address;
    public $arrResult;

    /**
     * define constructor
     */
    public function __construct($businessName, $address, $city, $state, $zip, $country, $phoneNo) {
        $this->businessName     = $businessName; //'Classic Informatics';
        $this->address          = $address; //'K 258/635,636 First Floor, Lane 3,First Floor, Saidulajab, Westend Marg, New Delhi'; 
        $this->city             = $city;
        $this->state            = $state;
        $this->zip              = $zip;
        $this->country          = $country;
        $this->phoneNo          = $phoneNo; //'011 4283 1191'; 
        #call search address
        $this->searchAddress();
    }

    /**
     * String matching function to validate address & business name
     * @param	<string>	$haystack
     * @param	<string>	$needle	 
     * @return	boolean		$valid
     */
    public function stringMatching($haystack, $needle) {

        $stringWords = explode(" ", $needle);
        $valid = true;
        foreach ($stringWords as $stringWord) {
            if (strpos($haystack, $stringWord) === false) {
                $valid = false;
                break;
            }
        }
        return $valid;
    }
    
    /**
     * Number matching function to validate Phone Number
     * @param	<string>	$enteredNumber
     * @param	<string>	$returnNumber	 
     * @return	boolean		$valid
     */
    public function phoneNumberMatching($enteredNumber, $returnNumber) {
        
        $replaceTo   = array("@","(",")","-","_"," ","[","]","+",".",":",">","<");
        $replaceFrom = array("");
        $enteredNumber = str_replace($replaceTo, $replaceFrom, $enteredNumber);
        $returnNumber  = str_replace($replaceTo, $replaceFrom, $returnNumber);
        
        $valid = false;
        if($enteredNumber === $returnNumber){
           $valid = true; 
        }
        return $valid;
    }

    /**
     * Search result using Google place API
     * @param	<string>	$businessName
     * @param	<string>	$address
     * @param	<string>	$phoneNo
     * @return	array		$arrResult
     */
    public function searchAddress() {
        #call map api
        $googleApiKey = "AIzaSyAvBdVS06WCli7X2RmDbti-tU2M7oAMdA8";
        $keyword = urlencode($this->businessName . ', ' . $this->address . ',' . $this->city . ', ' . $this->state . ', ' . $this->zip. ', ' . $this->country);

        #get latitude and longitude of an address
        $strUrl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $keyword . "&sensor=false";
        $resultLatLong = file_get_contents($strUrl);
        $arrResultLatLon = json_decode($resultLatLong, true);

        if (!empty($arrResultLatLon)) {

            $latitude = $arrResultLatLon['results'][0]['geometry']['location']['lat'];
            $longitude = $arrResultLatLon['results'][0]['geometry']['location']['lng'];

            #get place of an business with latilude and longitude using Google API
            //$strFindPlaceUrl = "https://maps.googleapis.com/maps/api/place/nearbysearch/json?location=" . $latitude . "," . $longitude . "&radius=50000&keyword=" . urlencode($this->businessName) . "&key=" . $googleApiKey;
            $strFindPlaceUrl = "https://maps.googleapis.com/maps/api/place/textsearch/json?location=" . $latitude . "," . $longitude . "&radius=50000&query=" . urlencode($this->businessName) . "&key=" . $googleApiKey;
            $resultFindPlace = file_get_contents($strFindPlaceUrl);
            $arrResultFindPlace = json_decode($resultFindPlace, true);

            #get place details from place ID using Google API
            if (!empty($arrResultFindPlace['results'])) {

                #initialize variables
                $intCounter = 0;
                $response = array();
                $responseResult = array();

                foreach ($arrResultFindPlace['results'] as $singleResultFindPlace) {
                    #break after 10 result
                    if ($intCounter == 10)
                        break;

                    $strPlaceId = $singleResultFindPlace['place_id'];

                    $strFindPlaceDetail = "https://maps.googleapis.com/maps/api/place/details/json?placeid=" . $strPlaceId . "&key=" . $googleApiKey;
                    $resultFindPlaceDetail = file_get_contents($strFindPlaceDetail);
                    $arrResultFindPlaceDetail = json_decode($resultFindPlaceDetail, true);

                    $returnAddress = $arrResultFindPlaceDetail['result']['formatted_address'];							
                    $returnPhoneNumber = $arrResultFindPlaceDetail['result']['formatted_phone_number'];
                    $returnBusinessName = $arrResultFindPlaceDetail['result']['name'];

                    # return response in json array                                        
                    if ($this->stringMatching(strtolower($returnBusinessName), strtolower($this->businessName))) {
                        $intBusinessAccurateCounter = 1;
                    } else {
                        $intBusinessAccurateCounter = 0;
                    }

                    if ($this->stringMatching(strtolower($returnAddress), strtolower($this->address))) {
                        $intAddressAccurateCounter = 1;
                    } else {
                        $intAddressNotAccurateCounter = 1;
                    }

                    if (!empty($arrResultFindPlaceDetail['result']['address_components'])) {
                        $intCountAddComponent = count($arrResultFindPlaceDetail['result']['address_components']);

                        for ($i = 0; $i <= $intCountAddComponent; $i++) {
                            $strType = $arrResultFindPlaceDetail['result']['address_components'][$i]['types'];

                            switch ($strType) {
                                case "locality":
                                    $returnCityLong = $arrResultFindPlaceDetail['result']['address_components'][$i]['long_name'];
                                    $returnCityShort = $arrResultFindPlaceDetail['result']['address_components'][$i]['short_name'];
                                    if ($this->stringMatching(strtolower($returnCityLong), strtolower($this->city))) {
                                        $intAddressAccurateCounter += 1;
                                    } elseif ($this->stringMatching(strtolower($returnCityShort), strtolower($this->city))) {
                                        $intAddressAccurateCounter += 1;
                                    } else {
                                        $intAddressNotAccurateCounter += 1;
                                    }
                                    break;

                                case "administrative_area_level_1";
                                    $returnStateLong = $arrResultFindPlaceDetail['result']['address_components'][$i]['long_name'];
                                    $returnStateShort = $arrResultFindPlaceDetail['result']['address_components'][$i]['short_name'];

                                    if ($this->stringMatching(strtolower($returnStateLong), strtolower($this->state))) {
                                        $intAddressAccurateCounter += 1;
                                    } elseif ($this->stringMatching(strtolower($returnStateShort), strtolower($this->state))) {
                                        $intAddressAccurateCounter += 1;
                                    } else {
                                        $intAddressNotAccurateCounter += 1;
                                    }
                                    break;

                                case "postal_code":
                                    $returnZipLong = $arrResultFindPlaceDetail['result']['address_components'][$i]['long_name'];
                                    $returnZipShort = $arrResultFindPlaceDetail['result']['address_components'][$i]['short_name'];

                                    if ($this->stringMatching(strtolower($returnZipLong), strtolower($this->zip))) {
                                        $intAddressAccurateCounter += 1;
                                    } elseif ($this->stringMatching(strtolower($returnZipShort), strtolower($this->zip))) {
                                        $intAddressAccurateCounter += 1;
                                    } else {
                                        $intAddressNotAccurateCounter += 1;
                                    }
                                    break;
                            }
                        }
                    } else {
                        $intAddressNotAccurateCounter = 2;
                    }
                    
                    if ($this->phoneNumberMatching($this->phoneNo, $returnPhoneNumber)) {
                        $intPhoneAccurateCounter = 1;
                    } else {
                        $intPhoneAccurateCounter = 0;
                    }  
                    
                    if ($intBusinessAccurateCounter)
                        $strBusinessMsg = 'Accurate';
                    else
                        $strBusinessMsg = 'Not Accurate';

                    if ($intPhoneAccurateCounter)
                        $strPhoneMsg = 'Accurate';
                    else
                        $strPhoneMsg = 'Not Accurate';

                    if ($intAddressAccurateCounter >= $intAddressNotAccurateCounter)
                        $strAddressMsg = 'Accurate';
                    else
                        $strAddressMsg = 'Not Accurate';

                    $strHTML .= '<div id="bname">Business Name: ' . $returnBusinessName . ' (' . $strBusinessMsg . ')</div>
                                 <div id="add">Address: ' . $returnAddress . ' (' . $strAddressMsg . ')</div>
                                 <div id="pnum">Phone Number: ' . $returnPhoneNumber . ' (' . $strPhoneMsg . ')</div><br/>';

                    #increment counter
                    $intCounter++;
                }

                $arrHTML = array('HTML' => $strHTML);
                echo json_encode($arrHTML);
                exit;
            }else {
                echo json_encode(array(
                    'errorMsg' => 'Result Not Found',
                ));
                exit;
            }
        } else {
            echo json_encode(array(
                'errorMsg' => 'Result Not Found',
            ));
            exit;
        }
    }

}

#getting ajax data
$businessName   = trim($_POST['businessName']);
$address        = trim($_POST['address']);
$city           = trim($_POST['city']);
$state          = trim($_POST['state']);
$zip            = trim($_POST['zip']);
$country        = trim($_POST['country']);
$phoneNumber    = trim($_POST['phoneNo']);

#initialize class 
$objDoscraping = new doscraping($businessName, $address, $city, $state, $zip, $country, $phoneNumber);