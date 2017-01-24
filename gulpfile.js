var gulp = require('gulp'),
    php = require('gulp-connect-php'),
    browserSync = require('browser-sync');

gulp.task('connect-sync', function() {
  php.server({
    base: 'public',
  }, function (){
    browserSync({
      proxy: '127.0.0.1:8000',
    });
  });
 
  gulp.watch('**/*.php').on('change', function () {
    browserSync.reload();
  });
});
