const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const { CleanWebpackPlugin } = require( 'clean-webpack-plugin' );
const AssetMapPlugin = require( './webpack-plugin-asset-map' );

module.exports = {

	entry: './assets/js/demo.js',

	output: {
		path: path.resolve( __dirname, 'dist' ),
		filename: 'bundle.[contenthash].min.js'
	},

	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /(node_modules)/,
				use: {
					loader: 'babel-loader',
					options: {
						presets: [ '@babel/preset-env' ]
					}
				}
			},
			{
				test: /\.(sa|sc|c)ss$/,
				use: [
					{
						loader: "css-loader",
					},
					{
						loader: "postcss-loader"
					},
					{
						loader: "sass-loader",
						options: {
							implementation: require( "sass" )
						}
					}
				]
			},
			{
				test: /\.(sa|sc|c)ss$/,
				use: [
					{
						loader: MiniCssExtractPlugin.loader
					},
					{
						loader: "css-loader",
					},
				]
			}
		]
	},

	plugins: [
		new CleanWebpackPlugin(),
		new MiniCssExtractPlugin( {
			filename: 'bundle.[contenthash].min.css'
		} ),
		new AssetMapPlugin(),
	],

	watch: true,

	mode: 'development'
};
