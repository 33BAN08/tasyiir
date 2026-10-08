// Renders every frame of tasyiir-promo.html at 30fps into frames/frame_XXXXX.png
const { chromium } = require('playwright');
const path = require('path');
const fs = require('fs');

const FPS = 30;
const DURATION = 28.0;
const OUT_DIR = path.resolve(__dirname, 'frames');

(async () => {
  if (!fs.existsSync(OUT_DIR)) fs.mkdirSync(OUT_DIR, { recursive: true });
  const htmlPath = 'file://' + path.resolve(__dirname, 'tasyiir-promo.html');

  const browser = await chromium.launch();
  const page = await browser.newPage({ viewport: { width: 1080, height: 1920 } });
  await page.goto(`${htmlPath}?capture`);
  await page.waitForTimeout(150); // let embedded fonts settle once

  const totalFrames = Math.round(DURATION * FPS);
  const t0 = Date.now();
  for (let i = 0; i < totalFrames; i++) {
    const t = i / FPS;
    await page.evaluate((tt) => window.renderAt(tt), t);
    const fname = path.join(OUT_DIR, `frame_${String(i).padStart(5, '0')}.png`);
    await page.screenshot({ path: fname });
    if (i % 60 === 0) {
      const elapsed = ((Date.now() - t0) / 1000).toFixed(1);
      console.log(`frame ${i}/${totalFrames} (t=${t.toFixed(2)}s) elapsed=${elapsed}s`);
    }
  }
  await browser.close();
  console.log(`Done: ${totalFrames} frames in ${((Date.now() - t0) / 1000).toFixed(1)}s`);
})();
