/* jshint esversion: 6 */
/* globals require */
const _ = require('lodash');
const aw = require('gulp-load-plugins')({
	pattern: ['*'],
	scope: ['devDependencies','dependencies']
});

const pkg = require('./package.json');
var themeName = pkg.name.toLowerCase().replace(new RegExp(' ','g'), '-');

aw.gulp.task('Develop', ['developJS', 'compileCSS', 'watch']);
aw.gulp.task('Package', ['prodJS', 'compileCSS']);

var theme = {
	srcJS : '_assets/javascript/**/*.js',
	srcCSS : '_assets/less/**/*.less',
	srcStyle : ['!_assets/less/variables.less', '_assets/less/style.less', '_assets/less/**/*.less'],
	destJS : pkg.directories.content + '/themes/' + themeName + '/js',
	destCSS : pkg.directories.content + '/themes/' + themeName,
};
/**
 *	Development JavaScript handling.
 *	This does browserify and babelify the files, but doesn't minify
 *	or uglify the file. I'm not outputting a sourcemap right now because
 *	I don't want to have to upload 2 files for every one change.
 */
aw.gulp.task('developJS', () => {
	"use strict";
	_.forEach(_.castArray(pkg.scripts.entry), (script) => {
		aw.browserify({
			entries: ['./_assets/javascript/' + script + '.js'],
			debug: false
		})
		.transform(aw.babelify, {
			compact: false,
			retainLines: true,
			comments: false,
		})
		.bundle()
		.pipe(aw.vinylSourceStream(script + '.min.js'))
		.pipe(aw.vinylBuffer())
		.pipe(aw.plumber(handleError))
		.pipe(aw.uglify({
			sourceMap: {
				url: 'inline'
			}
		}))
		.pipe(aw.gulp.dest(theme.destJS));
	});
});
/**
 *	During development, obviously we want the system to watch for any
 *	important changed files and to handle those automatically.
 */
aw.gulp.task('watch', () => {
	"use strict";
	aw.gulp.watch(theme.srcCSS,['compileCSS']);
	aw.gulp.watch(theme.srcJS,['developJS']);
});
/**
 *	Product JavaScript handling.
 *	Does everything the development version does, but also
 *	minifies and uglifies the files
 */
aw.gulp.task('prodJS', () => {
	"use strict";
	_.forEach(_.castArray(pkg.scripts.entry), (script) => {
		aw.browserify({
			entries: ['./_assets/javascript/' + script + '.js'],
			debug: false
		})
		.transform(aw.babelify, {
			compact: true,
			comments: false
		})
		.bundle()
		.pipe(aw.vinylSourceStream(script + '.min.js'))
		.pipe(aw.vinylBuffer())
		.pipe(aw.uglify())
		.pipe(aw.gulp.dest(theme.destJS));
	});
});
/**
 *	Production and Development .less processing
 *	Processes the .less to CSS, then minifies the file.
 */
aw.gulp.task('compileCSS', () => {
	"use strict";
	return aw.gulp
		.src(theme.srcStyle)
		.pipe(aw.plumber(handleError))
		.pipe(aw.less({
			strictMath : 'on',
			strictUnits : 'on',
		}))
		.pipe(aw.concat('all.css'))
		.pipe(aw.autoprefixer({
			browsers: [
				"last 2 versions",
				"IE 10"
			]
		}))
		.pipe(aw.cssmin())
		.pipe(aw.rename({
			extname : '.css',
			basename : 'style'
		}))
		.pipe(aw.gulp.dest(theme.destCSS));
});
/**
 *	Notify the developer there's an error, console the error message,
 *	and keep the watch task alive.
 *	@param	error
 *	@return	void
 */
function handleError(e){
	'use strict';
	console.log('\u0007');
	console.log(e.stack || e.toString());
	this.emit('end');
}