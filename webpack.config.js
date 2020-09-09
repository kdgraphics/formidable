// webpack.config.js
const { VueLoaderPlugin } = require('vue-loader');
const path = require('path');

const env = process.env.NODE_ENV;

const config = {
  entry: {
    notifications: path.join(__dirname, 'src/Assets/src', 'notifications.js'),
    forms: path.join(__dirname, 'src/Assets/src', 'forms.js'),
    fields: path.join(__dirname, 'src/Assets/src', 'fields.js'),
  },
  mode: env,
  output: {
    path: path.join(__dirname, 'src/Assets/dist')
  },
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        include: [path.join(__dirname, 'src')],
      },
      {
        test: /\.css$/,
        use: [
          'vue-style-loader',
          'css-loader',
        ],
      },
      {
        test: /\.scss$/,
        use: [
          'vue-style-loader',
          'css-loader',
          'sass-loader'
        ]
      }
    ],
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.esm.js'
    },
    extensions: ['*', '.js', '.vue', '.json']
  },
  plugins: [
    new VueLoaderPlugin(),
  ],
};

module.exports = config;