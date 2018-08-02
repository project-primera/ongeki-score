const path = require('path');

module.exports = {
    mode: 'development',
    entry: [
        './src/getScore.ts',
    ],
    output: {
        filename: 'main.js',
        path: path.join(__dirname, 'bin')
    },
    module: {
        rules: [
            {
            test: /\.ts$/,
            loader: "ts-loader"
            }
        ]   
    }
};