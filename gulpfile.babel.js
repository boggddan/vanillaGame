import { dest, parallel, series, src, watch } from 'gulp';

import autoprefixer from 'autoprefixer';
import browserSync from 'browser-sync';
import cssnano from 'cssnano';
import del from 'del';
import plugins from 'gulp-load-plugins';

//---------------------------------
const dirs = {
  src: 'src/',
  lib: 'node_modules/',
  dest: 'app/'
};

const paths = {
  html: {
    src: `${ dirs.src }html/**/*.html`,
    dest: `${ dirs.dest }`
  },
  sass: {
    src: `${ dirs.src }sass/**/*.scss`,
    dest: `${ dirs.dest }styles/`
  },
  js: {
    src: `${ dirs.src }js/**/*.js`,
    dest: `${ dirs.dest }scripts/`
  },
  img: {
    src: `${ dirs.src }img/**/*.*`,
    dest: `${ dirs.dest }img/`
  }
};
//---------------------------------

//---------------------------------
// clean
export const clean = ( ) => del( [ dirs.dest ] );

//---------------------------------
export const serve = ( ) => {
  browserSync.init( {
    server: {
      baseDir: `${ dirs.dest }`,
      directory: true
    },
    port: 8888,
    browser: 'Chrome',
    notify: false
  } );
};

//---------------------------------

export const bImg = ( ) => src( paths.img.src )
  .pipe( dest( paths.img.dest ) )
  .pipe( browserSync.stream( ) );

export const bHtml = ( ) => src( paths.html.src )
  .pipe( dest( paths.html.dest ) )
  .pipe( browserSync.stream( ) );


export const bSass = ( ) => src( paths.sass.src )
  .pipe(
    plugins( ).plumber(
      {
        errorHandler: plugins( ).notify.onError(
          { message: '<%= error.message %>', title: 'Ошибка' } )
      }
    ) )
  .pipe( plugins( ).sourcemaps.init( ) )
  .pipe( plugins( ).sass( { outputStyle: 'expanded' } ) )
  .pipe( plugins( ).postcss(
    [
      autoprefixer( { browsers: [ 'last 2 version' ] } ),
      cssnano( )
    ] ) )
  .pipe( plugins( ).sourcemaps.write( '.' ) )
  .pipe( dest( paths.sass.dest ) )
  .pipe( browserSync.stream( ) );


export const bJs = ( ) => src( paths.js.src )
  .pipe(
    plugins( ).plumber(
      {
        errorHandler: plugins( ).notify.onError(
          { message: '<%= error.message %>', title: 'Ошибка' } )
      }
    ) )
  .pipe( plugins( ).sourcemaps.init( ) )
  .pipe( plugins( ).babel( {
    presets: ['@babel/preset-env']
  } ) )
  .pipe(  plugins( ).sourcemaps.write('.') )
  .pipe( dest( paths.js.dest ) )
  .pipe( browserSync.stream( ) );

// watch Task
export const devWatch = ( ) => {
  watch( paths.img.src, parallel( bImg ) );
  watch( paths.html.src, parallel( bHtml ) );
  watch( paths.sass.src, parallel( bSass ) );
  watch( paths.js.src, parallel( bJs ) );
};

// development Task
export const dev = series( clean,
                           parallel( bImg, bHtml, bSass, bJs ),
                           parallel( serve, devWatch ) );
export default dev;
