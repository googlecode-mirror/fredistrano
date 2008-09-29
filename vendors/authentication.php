<?php
/**
 * Example of custom authentication 
 * the function authenticate must be modified according to your needs
 */

class CustomAuthentication {
	
	private static $webServiceServer = "ouassou:50001";
	
	private static $authenticationDirectory = "20";// 10 = genesis - 11 = ldap / 20 = dnsan / 31 = dnsan and ldap 
	
	public static function authenticate ($user, $passwd){

		$client = new SoapClient( 
				null,
				array(
			 		'location' 		=>	"https://" . self::$webServiceServer . "/OSI_authentificationWS/ConfigSSL?style=document",
			    	'uri'  			=>	'urn:OSI_authentificationWSVi',
                    'use'     		=>	SOAP_LITERAL
				)
			);
				
			$params = array (
				new SoapParam( $user, 'login'),
				new SoapParam( $passwd, 'pass'),
				new SoapParam( self::$authenticationDirectory, 'annuaire')
			);
			try {
				$result = $client->__soapCall('authentifierAnnuaire', $params);
			} catch (SoapFault $fault) {
				return false;
			}
			return $result=='true';
	}
	
	
	
	
	
	
}
?>