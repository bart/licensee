<?php

namespace Bart\Licensee;

use Claudusd\Cryptography\Signature\Implementation\SignatureSignPrivateKey;
use Claudusd\Cryptography\KeyGeneration\Implementation\KeyGenerationSHA512RSA4096Bits;


/**
 * This class implements simple methods for license creation and validation.
 * Usage example:
 * $data = array('licensee' => 'Company', 'version' => '1.0', 'valid_until' => time() + 60*60*24*7, 'key' => sha1(uniqid(true)));
 * $lic = new License();
 * $lic::createKeypair('/tmp/private.pem', '/tmp/public.pem');
 * $lic->createLicense($data, '/tmp/private.pem', '/tmp/license.lic');
 * $res = $lic->getDataAndValidateLicense('/tmp/license.lic', '/tmp/public.pem');
 * var_dump($res);
 *
 * @package Bart\Licensee
 */
class License {

	private $sspk, $data, $signature, $license;

	public function __construct() {
		$this->setSspk(new SignatureSignPrivateKey());
	}

	/**
	 * @param array       $data             Data to sign
	 * @param string      $private_key_path Path to private key file
	 * @param bool|string $license_path     Path to file where created license_path should be stored or false for no license_path storing
	 * @return string License data in json format
	 */
	public function createLicense($data, $private_key_path, $license_path = false) {
		$this->setData(json_encode($data));
		$this->setSignature(base64_encode($this->getSspk()->sign($this->getData(), file_get_contents($private_key_path))));
		$license = json_encode(array('data'      => $this->getData(),
									 'signature' => $this->getSignature()));

		$this->setLicense($license);

		if ($license_path !== false) {
			$this->storeLicense($license_path);
		}

		return $license;
	}

	/**
	 * @param string $path Path to license_path file
	 * @return void
	 */
	public function storeLicense($path) {
		file_put_contents($path, $this->getLicense());
	}

	/**
	 * @param string $license_path    Path to license_path file
	 * @param string $public_key_path Path to public key file
	 * @return bool
	 */
	public function validateLicense($license_path, $public_key_path) {
		$this->setLicense(file_get_contents($license_path));
		$this->processLicense();

		return $this->getSspk()->verify($this->getData(), base64_decode($this->getSignature()), file_get_contents($public_key_path));
	}

	/**
	 * @param string $license_path    Path to license_path file
	 * @param string $public_key_path Path to public key file
	 * @return bool|string Data when verified, false if not
	 */
	public function getDataAndValidateLicense($license_path, $public_key_path) {
		if ($this->validateLicense($license_path, $public_key_path)) {
			return $this->getData();
		}
		else {
			return false;
		}

	}

	/**
	 * @param string|null $private_key_path Path to private key file or false for no private key
	 * @param string|null $public_key_path  Path to public key file or false for no public key
	 */
	public static function createKeypair($private_key_path = false, $public_key_path = false) {
		$key = new KeyGenerationSHA512RSA4096Bits();

		if ($private_key_path) {
			file_put_contents($private_key_path, $key->getPrivateKey());
		}

		if ($public_key_path) {
			file_put_contents($public_key_path, $key->getPublicKey());
		}
	}

	private function processLicense() {
		$this->setData(json_decode($this->getLicense())->data);
		$this->setSignature(json_decode($this->getLicense())->signature);
	}

	private function setLicense($license) {
		$this->license = $license;
	}

	private function getLicense() {
		return $this->license;
	}

	private function setData($data) {
		$this->data = $data;
	}

	private function getData() {
		return $this->data;
	}

	private function setSignature($signature) {
		$this->signature = $signature;
	}

	private function getSignature() {
		return $this->signature;
	}

	private function setSspk($sspk) {
		$this->sspk = $sspk;
	}

	private function getSspk() {
		return $this->sspk;
	}

}