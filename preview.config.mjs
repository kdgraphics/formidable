import vue from '@vitejs/plugin-vue2';
import {resolve} from 'node:path';

export default {
  base: './',
  publicDir: 'preview',
  cacheDir: '/tmp/formidable-preview-vite-cache',
  plugins: [vue()],
  resolve: {alias: {vue: resolve('node_modules/vue/dist/vue.js'), '~craftcms-sass': resolve('node_modules/craftcms-sass')}},
  css: {preprocessorOptions: {scss: {silenceDeprecations: ['import', 'global-builtin', 'color-functions']}}},
  build: {
    outDir: 'preview-dist',
    emptyOutDir: true,
    rollupOptions: {input: resolve('cp-preview.html')},
  },
};
