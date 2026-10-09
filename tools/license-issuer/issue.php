<?php

/*
 * Issues a licence for one client machine. Vendor-only tool: it needs the
 * private key created by keygen.php and is excluded from every release.
 *
 *   php issue.php --machine=A1B2-C3D4-E5F6-7890 --center="مركز النجاح" --expires=2027-12-31
 *   php issue.php --machine=... --center="..." --plan=lifetime        (no expiry)
 *
 * Options:
 *   --machine=   machine code shown in the client's Settings → الترخيص (required)
 *   --center=    center name, printed on the licence for your own records (required)
 *   --expires=   YYYY-MM-DD, omit for a licence that never expires
 *   --plan=      free text: yearly | monthly | lifetime (default: yearly)
 *   --out=       also write the licence to this file
 */

if (! extension_loaded('sodium')) {
    fwrite(STDERR, "The sodium extension is required (enable extension=sodium in php.ini).\n");
    exit(1);
}

$options = getopt('', ['machine:', 'center:', 'expires::', 'plan::', 'out::']);

foreach (['machine', 'center'] as $required) {
    if (empty($options[$required])) {
        fwrite(STDERR, "Missing --{$required}. See the comment at the top of this file.\n");
        exit(1);
    }
}

$privatePath = __DIR__.'/keys/private.key';

if (! is_file($privatePath)) {
    fwrite(STDERR, "No private key at {$privatePath}. Run: php keygen.php\n");
    exit(1);
}

$machine = strtoupper(trim((string) $options['machine']));

if (! preg_match('/^[A-F0-9]{4}(-[A-F0-9]{4}){3}$/', $machine)) {
    fwrite(STDERR, "Machine code must look like A1B2-C3D4-E5F6-7890 (copy it from the client's screen).\n");
    exit(1);
}

$expires = isset($options['expires']) && $options['expires'] !== '' ? trim((string) $options['expires']) : null;

if ($expires !== null && ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $expires)) {
    fwrite(STDERR, "--expires must be YYYY-MM-DD (or omitted for a lifetime licence).\n");
    exit(1);
}

$payload = [
    'center_name' => trim((string) $options['center']),
    'machine_id' => $machine,
    'issued_at' => date('Y-m-d'),
    'expires_at' => $expires,
    'plan' => isset($options['plan']) && $options['plan'] !== '' ? (string) $options['plan'] : 'yearly',
];

$secret = base64_decode(trim((string) file_get_contents($privatePath)), true);

if ($secret === false || strlen($secret) !== SODIUM_CRYPTO_SIGN_SECRETKEYBYTES) {
    fwrite(STDERR, "The private key file is not a valid Ed25519 secret key.\n");
    exit(1);
}

$payloadB64 = base64_encode(json_encode($payload, JSON_UNESCAPED_UNICODE));
$signature = base64_encode(sodium_crypto_sign_detached($payloadB64, $secret));
$licence = base64_encode(json_encode(['p' => $payloadB64, 's' => $signature]));

echo "Licence for {$payload['center_name']} ({$machine})\n";
echo 'Plan: '.$payload['plan'].' | Expires: '.($expires ?? 'never')."\n\n";
echo $licence."\n\n";

if (! empty($options['out'])) {
    file_put_contents((string) $options['out'], $licence."\n");
    echo 'Saved to '.$options['out']."\n";
}
