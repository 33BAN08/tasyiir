# TASYIIR — Motion Promo

**Files**
- `tasyiir-promo.mp4` — 1080×1920, 30fps, 28s, H.264/yuv420p, no sound. Ready for WhatsApp Status / Instagram Reels / TikTok.
- `tasyiir-promo.html` — the source animation (single self-contained file, fonts embedded).
- `tasyiir-promo-contact-sheet.png` — one frame per scene, for a quick look without playing the video.

**What it uses from your real app**
- Brand name **TASYIIR**, tagline "دبّر مركزك، ببساطة" (from `sidebar.blade.php`).
- Real brand colors from `tailwind.config.js`: brand green `#10b981` / `#059669`, dark `ink-950 #0d1017`.
- Real fonts: **Cairo** (Arabic + Latin) and **IBM Plex Mono** for numbers — same files as `public/fonts/`.
- 4 real features from your routes/sidebar: الطلاب والمجموعات (Students & Groups), الأداءات والتنبيهات (Payments & unpaid alerts), الجدول (Schedule), الإحصائيات (Statistics).
- Mock student names, amounts (DH) and schedule times are invented placeholders, not real center data.

**Before you use this for paid ads**
- The end card's call-to-action button text ("اطلبوا عرضاً تجريبياً مجانياً") has no phone/WhatsApp number yet — add one in `CONTENT.end` (see below) before boosting it.
- `tasyiir.ma` is shown as a placeholder domain — swap it if you register something else.

## How to change the text and re-render

1. Open `tasyiir-promo.html` in a text editor and find the `CONTENT` object near the top of the `<script>` block (search for `const CONTENT = {`). Every visible word in the video is there — brand name, tagline, the 3 pain points, student names, payment amount, schedule rows, stats, end-card CTA.
2. Edit the values (keep the quotes). Save the file.
3. Preview instantly: open `tasyiir-promo.html` in any browser (no server needed) and press **Play** to watch it at normal speed, or drag the scrub bar to check any moment.
4. Re-render the MP4 with one command (needs Node.js + Playwright + ffmpeg, already set up in this project folder):

```bash
node render_all.js        # re-renders all 840 frames into frames/
ffmpeg -y -framerate 30 -i frames/frame_%05d.png -c:v libx264 -pix_fmt yuv420p -crf 18 -movflags +faststart out/tasyiir-promo.mp4
```

That's it — `out/tasyiir-promo.mp4` is your updated video.

**Changing colors/fonts:** the CSS `:root` variables (`--brand-500`, `--ink-950`, etc.) near the top of the HTML file match `tailwind.config.js` exactly — edit them there if you ever rebrand.

**Changing timing/duration:** the `SCENES` object right below `CONTENT` controls when each scene starts/ends (in seconds). `DURATION` at the top of that block sets the total video length — remember to update it if you add/remove time.
