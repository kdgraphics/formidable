let path = require('path');

module.exports = {
    lintOnSave: process.env.NODE_ENV !== 'production',
    runtimeCompiler: true,
    outputDir: 'src/Assets/dist',
    css: {
        extract: false,
    },
    chainWebpack: config => {
        config.entryPoints.delete("app").end();
        config.plugins
        .delete("html")
        .delete("prefetch")
        .delete("preload");
    },
    configureWebpack: {
        entry: {
            forms: './src/Assets/src/forms.js',
            submissions: './src/Assets/src/submissions.js',
            spam: './src/Assets/src/spam.js'
        },
        output: {
            filename: "js/[name].js",
            chunkFilename: "js/[name].js"
        },
        resolve: {
          alias: {
            vue: path.resolve('./node_modules/vue')
          }
        }
    }
}