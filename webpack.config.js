const path = require( 'path' );
const MiniCssExtractPlugin = require( 'mini-css-extract-plugin' );
const CssMinimizerPlugin = require( 'css-minimizer-webpack-plugin' );
const TerserPlugin = require( 'terser-webpack-plugin' );

module.exports = ( env, argv ) => {
	const isProduction = argv.mode === 'production';

	return {
		entry: {
			'admin/js/flexi-abandon-cart-recovery-admin': './admin/js/flexi-abandon-cart-recovery-admin.js',
			'public/js/flexi-abandon-cart-recovery-public': './public/js/flexi-abandon-cart-recovery-public.js',
			'admin/css/flexi-abandon-cart-recovery-admin': './admin/css/flexi-abandon-cart-recovery-admin.css',
			'public/css/flexi-abandon-cart-recovery-public': './public/css/flexi-abandon-cart-recovery-public.css',
		},
		output: {
			path: path.resolve( __dirname ),
			filename: ( pathData ) => {
				return pathData.chunk.name.endsWith( 'css' ) ? '[name].dummy.js' : '[name].min.js';
			},
		},
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: {
						loader: 'babel-loader',
						options: {
							presets: [ '@babel/preset-env' ],
						},
					},
				},
				{
					test: /\.css$/,
					use: [
						MiniCssExtractPlugin.loader,
						'css-loader',
					],
				},
			],
		},
		plugins: [
			new MiniCssExtractPlugin( {
				filename: '[name].min.css',
			} ),
		],
		optimization: {
			minimizer: [
				new TerserPlugin( {
					extractComments: false,
				} ),
				new CssMinimizerPlugin(),
			],
			minimize: isProduction,
		},
		devtool: isProduction ? false : 'source-map',
	};
};
