# Licensee

A simple PHP class for license generation and verification based on openssl signatures


## Usage

Using the Licensee class is as easy as pie, because it follows the PSR-0 namespace standard.

1. Add `"bart/licensee":"dev-master"` to your `composer.json` `require-dev`
2. Run `composer update`
3. Enjoy!

> **Note:** If you developing with the amazing [Laravel 4 framework](http://www.laravel.com), you can add an alias: 'License' => 'Bart\Licensee\License'


## Methods

```
createLicense($data, $private_key_path, [$license_path])
storeLicense($license_path)
validateLicense($license_path, $public_key_path)
getDataAndValidateLicense($license_path, $public_key_path)
createKeypair([$private_key_path], [$public_key_path])
```


## Example

```
$lic = new License();

// The array we want to create a license from
$data = array(
  'licensee' => 'Company name',
  'version' => '1.0',
  'valid_until' => time() + 60*60*24,
  'key' => sha1(uniqid(true))
);

// Create a new public and private key
$lic::createKeypair('/tmp/private.pem', '/tmp/public.pem');

// Create the license
$lic->createLicense($data, '/tmp/private.pem', '/tmp/license.lic');

// Validate the license and get the data back
$valid_data = $lic->getDataAndValidateLicense('/tmp/license.lic', '/tmp/public.pem');
```


## License

The Licensee package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT)
