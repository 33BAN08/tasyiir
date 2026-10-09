# Licence issuer (vendor only)

This folder is **excluded from every release build**. It holds the private key
that signs TASYIIR licences; the application only ever ships the public half,
so a licence cannot be forged or edited by a client.

## One-time: create your key pair

```bash
cd tools/license-issuer
php keygen.php
```

This writes `keys/private.key` (git-ignored — **never commit or share it**) and
prints your public key:

```
TASYIIR_LICENSE_PUBLIC_KEY=<base64>
```

Put that line in the `.env` of every build you ship (`.env.local.example`
carries a placeholder) or bake it into `config/tasyiir.php`. Back the private
key up somewhere safe and offline: if you lose it you cannot issue new
licences for existing installs, and if you replace it every licence already
issued stops validating.

## Issuing a licence for a client

1. On the client's PC, open **الإعدادات → الترخيص** and copy the machine code
   (`A1B2-C3D4-E5F6-7890`). It comes from the motherboard UUID, so it is
   different on every PC — which is what stops an install being copied.
2. On your machine:

```bash
php issue.php --machine=A1B2-C3D4-E5F6-7890 --center="مركز النجاح للتكوين" --expires=2027-12-31
```

   * `--expires=` omitted → a licence that never expires (`--plan=lifetime`).
   * `--out=licence.txt` also writes the licence to a file.

3. Send the printed block to the client; they paste it into the same screen
   and press **تفعيل الترخيص**.

## What the client sees

| State | Modules | Login | Licence page | Backup download |
|---|---|---|---|---|
| Trial (14 days) | ✅ | ✅ | ✅ | ✅ |
| Licensed | ✅ | ✅ | ✅ | ✅ |
| Trial/licence expired, wrong machine, tampered file | ❌ | ✅ | ✅ | ✅ |

No data is ever deleted or modified by a licence state, and nothing is ever
sent over the network — the check is a signature verification done locally.
