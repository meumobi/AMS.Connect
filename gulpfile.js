var gulp = require('gulp'),
    php = require('gulp-connect-php'),
    browserSync = require('browser-sync'),
    args = require('yargs').argv;

// Get the port from the command line
var port = args.port || "8000";

gulp.task('connect-sync', function() {
  php.server({
    base: 'public',
    port: port
    //bin: // Path to the PHP binary. Useful if you have multiple versions of PHP installed.
    //ini: // Path to a custom php.ini config file.
  }, function (){
    browserSync({
      proxy: '127.0.0.1:' + port,
    });
  });
 
  gulp.watch('**/*.php').on('change', function () {
    browserSync.reload();
  });
});

gulp.task('default', ['connect-sync']);
