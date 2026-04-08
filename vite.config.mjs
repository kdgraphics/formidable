import { fileURLToPath } from 'node:url';
import { dirname, resolve } from 'node:path';

import vue from '@vitejs/plugin-vue2';
import { defineConfig } from 'vite';
import cssInjectedByJsPlugin from 'vite-plugin-css-injected-by-js';

const __dirname = dirname(fileURLToPath(import.meta.url));

export const FORMIDABLE_ENTRIES = {
    forms: 'src/assets/src/forms.js',
    submissions: 'src/assets/src/submissions.js',
    spam: 'src/assets/src/spam.js',
    hook: 'src/assets/src/hook.js',
    phoneInput: 'src/assets/src/phoneInput.js',
    formValidation: 'src/assets/src/formValidation.js',
};

function pascalCase(value) {
    return value
        .replace(/(^\w|-\w)/g, (match) => match.replace('-', '').toUpperCase());
}

export function createBuildConfig(entryName, mode = 'production', watch = false) {
    const entry = FORMIDABLE_ENTRIES[entryName];

    if (!entry) {
        throw new Error(`Unknown Formidable build entry: ${entryName}`);
    }

    const production = mode === 'production';

    return defineConfig({
        configFile: false,
        publicDir: false,
        css: {
            preprocessorOptions: {
                scss: {
                    quietDeps: true,
                    silenceDeprecations: ['import', 'global-builtin', 'color-functions'],
                },
            },
        },
        resolve: {
            alias: {
                '@vue-confirm-dialog': resolve(
                    __dirname,
                    'node_modules/vue-confirm-dialog/src/index.js'
                ),
                vue: resolve(__dirname, 'node_modules/vue/dist/vue.js'),
                '~craftcms-sass': resolve(__dirname, 'node_modules/craftcms-sass'),
            },
            extensions: ['.mjs', '.js', '.ts', '.jsx', '.tsx', '.json', '.vue'],
        },
        plugins: [
            vue(),
            cssInjectedByJsPlugin(),
        ],
        define: {
            'process.env.NODE_ENV': JSON.stringify(mode),
        },
        build: {
            emptyOutDir: false,
            lib: {
                entry: resolve(__dirname, entry),
                formats: ['iife'],
                fileName: () => `${entryName}.js`,
                name: `Formidable${pascalCase(entryName)}`,
            },
            minify: production ? 'esbuild' : false,
            outDir: resolve(__dirname, 'src/assets/dist'),
            reportCompressedSize: false,
            sourcemap: !production,
            target: 'es2018',
            watch: watch ? {} : null,
        },
    });
}

export default createBuildConfig('forms');
