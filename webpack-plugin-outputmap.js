const fs = require( 'fs' );

/**
 * Creates a file with a JSON object mapping entry names to asset names.
 * We can use the map to enqueue files whose names contain a content hash.
 * Expects the files built by webpack to have a format like `[name].[contenthash].min.js`.
 */
module.exports = class OutputMapPlugin {
	constructor( options ) {
		this.options = options;
		console.log( options );
	}

	apply( compiler ) {
		compiler.hooks.done.tap( 'OutputMapPlugin', ( stats ) => {
			const map = {};
			const assets = Object.keys( stats.compilation.assets );
			assets.forEach( ( asset ) => {
				const re = /^([\w\/]+)(\.[a-z0-9]+)(\.min\.[a-z0-9]{2,3})$/gi;
				const key = asset.replace( re, '$1$3' );
				map[ key ] = asset;
			} );
			fs.writeFile(
				compiler.options.output.path + '/map.json',
				JSON.stringify( map ),
				( error, data ) => {
					if ( error ) {
						return console.log( error );
					}
				} );
		} );
	}
};
