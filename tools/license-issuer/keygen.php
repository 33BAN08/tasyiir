<?php

/*
 * Generates the vendor's Ed25519 key pair. Run this ONCE, keep the private
 * key safe, and never let it leave this machine — anyone holding it can issue
 * licences for your software.
 *
 *   php keygen.php
 *
 * Writes keys/private.key (git-ignored) and prints the public key to paste
 * into the application's TASYIIR_LICENSE_PUBLIC_KEY.
 */

if (! extension_loaded('sodium')) {
    fwrite(STDERR, "The sodium extension is required (enable extension=sodium in php.ini).\n");
    exit(1);
}

$keyDir = __DIR__.'/keys';
$privatePath = $keyDir.'/private.key';
$publicPath = $keyDir.'/public.key';

if (file_exists($privatePath)) {
    fwrite(STDERR, "A key pair already exists at {$privatePath}.\n");
    fwrite(STDERR, "Delete it only if you are sure: every licence you have ever issued stops validating.\n");
    exit(1);
}

@mkdir($keyDir, 0700, true);

$pair = sodium_crypto_sign_keypair();
$secret = base64_encode(sodium_crypto_sign_secretkey($pair));
$public = base64_encode(sodium_crypto_sign_publickey($pair));

file_put_contents($privatePath, $secret."\n");
file_put_contents($publicPath, $public."\n");
@chmod($privatePath, 0600);

echo "Key pair created.\n\n";
echo "  private key : {$privatePath}  (NEVER share or commit this)\n";
echo "  public key  : {$publicPath}\n\n";
echo "Put this line in the release .env (or config/tasyiir.php) of every build you ship:\n\n";
echo "TASYIIR_LICENSE_PUBLIC_KEY={$public}\n\n";
