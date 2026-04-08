import { cp, mkdir, rm } from 'node:fs/promises';
import { dirname, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

import { build } from 'vite';

import { FORMIDABLE_ENTRIES, createBuildConfig } from '../vite.config.mjs';

const __dirname = dirname(fileURLToPath(import.meta.url));
const root = resolve(__dirname, '..');
const distDir = resolve(root, 'src/assets/dist');
const mode = process.argv[2] || 'production';
const watch = process.argv.includes('--watch');

const generatedEntries = [
    ...Object.keys(FORMIDABLE_ENTRIES).map((entry) => `${entry}.js`),
];

async function cleanGeneratedOutput() {
    for (const file of generatedEntries) {
        await rm(resolve(distDir, file), { force: true });
        await rm(resolve(distDir, `${file}.map`), { force: true });
    }
}

async function copyPhoneUtils() {
    const source = resolve(root, 'src/assets/PhoneNumber-24.6.0/js/utils.js');
    const target = resolve(distDir, 'utils.js');

    await mkdir(distDir, { recursive: true });
    await cp(source, target);
}

await cleanGeneratedOutput();

for (const entryName of Object.keys(FORMIDABLE_ENTRIES)) {
    await build(createBuildConfig(entryName, mode, watch));
}

await copyPhoneUtils();
