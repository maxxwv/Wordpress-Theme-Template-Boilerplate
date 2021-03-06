/* jshint esversion: 6 */
const pkg = require('./package.json'),
			path = require('path');

const aw = require('gulp-load-plugins')({
	pattern: ['*'],
	scope: ['dependencies', 'devDependencies']
});

var themeName = pkg.name;
var paths = {
	srcJS : '_assets/javascript/**/*.js',
	srcCSS : '_assets/less/**/*.less',
	destJS : `${pkg.directories.content}/themes/${themeName}/js`,
	destCSS : `${pkg.directories.content}/themes/${themeName}`,
};

const develop = aw.gulp.series( aw.gulp.parallel( devJS, devCSS ), watchFiles );
const publish = aw.gulp.parallel( prodJS, prodCSS );
const pack = aw.gulp.series( zipFiles );

/**
 * Development JavaScript handling - browserify, babelify, uglify JS files defined in package.json#jsFiles
 * and add a sourcemap for debugging. I may remove the minification/uglification and sourcemaps depending
 * on how everything works in the field
 * @param {*} done
 */
function devJS(done){
	pkg.entries.scripts.map( script => {
		script = script.replace('.js', '');
		aw.browserify({
			entries: [`./_assets/javascript/${script}.js`],
			debug: false
		})
		.transform('babelify', {
			compact: true,
			retainLines: false,
			comments: false,
		})
		.bundle()
		.pipe(aw.plumber(handleErrorJS))
		.pipe(aw.vinylSourceStream(`${script}.min.js`))
		.pipe(aw.vinylBuffer())
		.pipe(aw.sourcemaps.init({ loadMaps: true }))
		.pipe(aw.terser())
		.pipe(aw.sourcemaps.write('../../../maps'))
		.pipe(aw.gulp.dest(paths.destJS));
	});
	done();
}
/**
 * Development .LESS handling - autoprefix, compile, and minify .less files found in the
 * ../_assets/less/ directory and add a sourcemap for debugging.
 * @param {*} done
 */
function devCSS(done){
	pkg.entries.styles.map( style => {
		aw.gulp
			.src(`./_assets/less/${style}.less`)
			.pipe(aw.plumber(handleError))
			.pipe(aw.sourcemaps.init({ loadMaps: true }))
			.pipe(aw.less({
				strictMath : 'on',
				strictUnits : 'on',
			}))
			.pipe(aw.autoprefixer({
				grid: true
			}))
			.pipe(aw.cssmin())
			.pipe(aw.sourcemaps.write('../../maps'))
			.pipe(aw.gulp.dest(paths.destCSS));
		});
		done();
}
/**
 * Basic watch task for development purposes
 * @param {*} done
 */
function watchFiles(done){
	aw.gulp.watch(paths.srcJS, aw.gulp.series( devJS ) );
	aw.gulp.watch(paths.srcCSS, aw.gulp.series( devCSS ) );
	done();
}
/**
 * Does everything the devJS function does except create or export a sourcemap.
 * @param {*} done
 */
function prodJS(done){
	pkg.entries.scripts.map( script => {
		script = script.replace('.js', '');
		aw.browserify({
			entries: [`./_assets/javascript/${script}.js`],
			debug: false
		})
		.transform('babelify', {
			compact: true,
			retainLines: false,
			comments: false,
		})
		.bundle()
		.pipe(aw.vinylSourceStream(`${script}.min.js`))
		.pipe(aw.vinylBuffer())
		.pipe(aw.terser())
		.pipe(aw.gulp.dest(paths.destJS));
	});
	done();
}
/**
 * Does everything the devCSS function does except create or export a sourcemap.
 * @param {*} done
 */
function prodCSS(done){
	pkg.entries.styles.map( style => {
		aw.gulp
			.src(`./_assets/less/${style}.less`)
			.pipe(aw.less({
				strictMath : 'on',
				strictUnits : 'on',
			}))
			.pipe(aw.autoprefixer({
				grid: true
			}))
			.pipe(aw.cssmin())
			.pipe(aw.gulp.dest(paths.destCSS));
		});
		done();
}
/**
 * Package files into a .zip for deployment/distribution
 * @param {*} done
 */
function zipFiles(done){
	aw.gulp
		.src([
			`./${pkg.directories.content}/**/*`
		], {
			allowEmpty: true
		})
		.pipe(aw.rename((file) => {
			file.dirname = pkg.name + path.sep + file.dirname;
		}))
		.pipe(aw.zip(pkg.name + '.' + pkg.version + '.zip'))
		.pipe(aw.gulp.dest('./'));
		done();
}
/**
 * Gracefully log CSS errors to the console.
 * @param {*} e
 */
function handleError(e){
	beep();
	console.log(e.stack || e.toString());
	this.emit('end');
}
/**
 * Gracefully log JavaScript errors to the console.
 * @param {*} e
 */
function handleErrorJS(e, src){
	beep();
	console.log(`Error in file ${e.fileName}:`);
	console.log(e.cause);
	this.emit('end');
}
/**
 * Emit a beep
 */
function beep(){
	console.log('\u0007');
}
/**
 * API
 */
exports.Develop = develop;
exports.Publish = publish;
exports.Package = pack;