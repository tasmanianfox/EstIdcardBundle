<?php
namespace TFox\EstIdcardBundle\Service;

use Symfony\Component\HttpFoundation\Request;
use TFox\EstIdcardBundle\Entity\ClientData;
use TFox\EstIdcardBundle\Exception\ClientCertificateReadingException;

class CertificateReaderService 
{
	
	/**
	 * Reads data from ID card
	 * @param Request $request
	 * @throws \Exception
	 * @return \TFox\EstIdcardBundle\Entity\ClientData
	 */
	public function readCertificate(Request $request)
	{
		$serverAttributes = $request->server->all();
		$clientCertificate = key_exists('SSL_CLIENT_CERT', $serverAttributes) ? $serverAttributes['SSL_CLIENT_CERT'] : null;
		if(is_null($clientCertificate))
			throw new ClientCertificateReadingException();
		
		$certificateData = openssl_x509_parse($clientCertificate);		
		if(false == $certificateData)
			throw new ClientCertificateReadingException();			
		if(!key_exists('subject', $certificateData) || !key_exists('serialNumber', $certificateData['subject']))
			throw new ClientCertificateReadingException();

		$clientData = new ClientData();
		$clientData->setFirstName($certificateData['subject']['GN']);
		$clientData->setLastName($certificateData['subject']['SN']);
		$clientData->setPersonalCode($certificateData['subject']['serialNumber']);
		$clientData->setRawData($certificateData);
		
		return $clientData;
	}
}