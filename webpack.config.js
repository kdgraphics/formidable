const Path = require('path');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');
const webpack = require('webpack');

assetDir = Path.resolve(__dirname, 'src/Assets');

module.exports = {
  entry: {
    notifications: [Path.resolve(assetDir + '/src/notifications.js')],
    forms: [Path.resolve(assetDir + '/src/forms.js')],
    form: [Path.resolve(assetDir + '/src/form.js')],
    submissions: [Path.resolve(assetDir + '/src/submissions.js')],
    spam: [Path.resolve(assetDir + '/src/spam.js')],
  },
  mode: 'production',
  output: {
    path: assetDir + '/dist'
  },
  plugins: [
    new CleanWebpackPlugin()
  ],
  resolve: {
    modules: ['node_modules'],
  },
  module: {
    rules: [
      {
        test: /\.mjs$/,
        include: /node_modules/,
        type: 'javascript/auto'
      },
      {
        test: /\.js$/,
        include: assetDir,
        enforce: 'pre',
        loader: 'eslint-loader',
        options: {
          emitWarning: true
        }
      },
      {
        test: /\.js$/,
        include: assetDir,
        loader: 'babel-loader'
      },
      {
        test: /\.(scss)$/i,
        use: ['style-loader', 'css-loader', 'sass-loader']
      }
    ]
  }
};
