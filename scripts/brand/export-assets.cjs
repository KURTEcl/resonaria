// Raster derivatives of the approved SVGs. Resolve sharp from the caller's
// installed dependencies (NODE_PATH may point at the Codex bundled runtime).
const sharp = require('sharp');
const path = require('node:path');
const fs = require('node:fs/promises');
const root = path.resolve(__dirname, '../..');
const publicDir = path.join(root, 'apps/web/public');
const brand = path.join(publicDir, 'brand');
const docs = path.join(root, 'docs/02-diseno-ux/assets/brand');

async function main() {
  const icon = path.join(brand, 'icono.svg');
  for (const name of ['icono-1024.png', 'icono-512.png', 'icono-192.png']) {
    const size = Number(name.match(/-(\d+)/)[1]);
    await sharp(icon).resize(size, size).png().toFile(path.join(brand, name));
  }
  const outputs = [
    [192, 'brand/icon-192.png'], [512, 'brand/icon-512.png'],
    [192, 'android-chrome-192x192.png'], [512, 'android-chrome-512x512.png'],
    [180, 'apple-touch-icon.png'], [16, 'favicon-16x16.png'], [32, 'favicon-32x32.png'],
  ];
  for (const [size, file] of outputs) {
    await sharp(icon).resize(size, size).png().toFile(path.join(publicDir, file));
  }
  for (const name of ['logo', 'logo-palabra', 'logo-descriptor', 'logo-blanco', 'logo-descriptor-blanco', 'logo-monocromo', 'isotipo', 'isotipo-monocromo', 'isotipo-blanco']) {
    await sharp(path.join(brand, `${name}.svg`), { density: 144 }).png().toFile(path.join(brand, `${name}.png`));
  }
  await sharp(path.join(brand, 'logo-descriptor.svg')).png().toFile(path.join(docs, 'logo.png'));
  await sharp(path.join(brand, 'logo-palabra.svg')).png().toFile(path.join(docs, 'logo-palabra.png'));
  await sharp(icon).resize(1024, 1024).png().toFile(path.join(docs, 'icono.png'));
  await sharp(path.join(brand, 'isotipo.svg')).resize(306, 306).png().toFile(path.join(docs, 'isotipo.png'));
  await sharp(path.join(brand, 'isotipo-monocromo.svg')).resize(306, 306).png().toFile(path.join(docs, 'isotipo-monocromo.png'));
  // ICO supports PNG-encoded frames. Build all sizes from the SVG directly.
  const sizes = [16, 32, 48];
  const frames = await Promise.all(sizes.map(size => sharp(icon).resize(size, size).png().toBuffer()));
  const header = Buffer.alloc(6 + sizes.length * 16);
  header.writeUInt16LE(1, 2); header.writeUInt16LE(sizes.length, 4);
  let offset = header.length;
  frames.forEach((frame, i) => {
    const entry = 6 + i * 16;
    header[entry] = sizes[i]; header[entry + 1] = sizes[i];
    header.writeUInt16LE(1, entry + 4); header.writeUInt16LE(32, entry + 6);
    header.writeUInt32LE(frame.length, entry + 8); header.writeUInt32LE(offset, entry + 12);
    offset += frame.length;
  });
  await fs.writeFile(path.join(publicDir, 'favicon.ico'), Buffer.concat([header, ...frames]));
  const tiles = [{ input: await sharp(path.join(brand, 'logo-descriptor.svg')).resize(720).png().toBuffer(), left: 24, top: 20 }];
  for (const [name, size, x] of [['isotipo.svg', 120, 24], ['isotipo-monocromo.svg', 120, 180], ['icono.svg', 64, 360], ['icono.svg', 32, 450], ['icono.svg', 16, 510]]) {
    tiles.push({ input: await sharp(path.join(brand, name)).resize(size).png().toBuffer(), left: x, top: 245 + Math.round((120-size)/2) });
  }
  await sharp({ create: { width: 940, height: 390, channels: 4, background: '#ffffff' } }).composite(tiles).png().toFile(path.join(docs, 'identidad-aplicada.png'));
  console.log('Exported approved logo, icons, favicon and documentation preview.');
}
main().catch(error => { console.error(error); process.exitCode = 1; });
