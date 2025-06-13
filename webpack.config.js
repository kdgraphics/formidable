// webpack.config.js
const { VueLoaderPlugin } = require("vue-loader");
const path = require("path");

const env = process.env.NODE_ENV;

const config = {
    entry: {
        forms: path.join(__dirname, "src/assets/src", "forms.js"),
        submissions: path.join(__dirname, "src/assets/src", "submissions.js"),
        spam: path.join(__dirname, "src/assets/src", "spam.js"),
        hook: path.join(__dirname, "src/assets/src", "hook.js"),
        phoneInput: path.join(__dirname, "src/assets/src", "phoneInput.js"),
        utils: path.join(
            __dirname,
            "src/assets/PhoneNumber-24.6.0/js",
            "utils.js"
        ),
        formValidation: path.join(
            __dirname,
            "src/assets/src",
            "formValidation.js"
        ),
    },
    mode: env,
    output: {
        path: path.join(__dirname, "src/assets/dist"),
    },
    optimization: {
        minimize: env === "production",
        minimizer:
            env === "production"
                ? [
                      new (require("uglifyjs-webpack-plugin"))({
                          uglifyOptions: {
                              compress: {
                                  drop_console: true,
                              },
                          },
                      }),
                  ]
                : [],
    },
    module: {
        rules: [
            {
                test: /\.vue$/,
                loader: "vue-loader",
            },
            {
                test: /\.js$/,
                loader: "babel-loader",
                include: [path.join(__dirname, "src")],
            },
            {
                test: /\.css$/,
                use: ["vue-style-loader", "css-loader"],
            },
            {
                test: /\.scss$/,
                use: ["vue-style-loader", "css-loader", "sass-loader"],
            },
        ],
    },
    resolve: {
        alias: {
            vue$: "vue/dist/vue.esm.js",
        },
        extensions: ["*", ".js", ".vue", ".json"],
    },
    plugins: [new VueLoaderPlugin()],
};

module.exports = config;
