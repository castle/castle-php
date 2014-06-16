module.exports = function(grunt) {
  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),
    phpunit: {
      classes: {
        dir: 'test/'
      },
      options: {
        bin: 'vendor/bin/phpunit',
        bootstrap: 'test/Userbin.php',
        colors: true
      }
    },
    watch: {
      sourcephp: {
        files: ['lib/**/*.php'],
        tasks: ['phpunit']
      },
      testfiles: {
        files: ['test/**/*.php'],
        tasks: ['phpunit']
      }
    }
  });
  grunt.loadNpmTasks('grunt-phpunit');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('test', ['phpunit']);
  grunt.registerTask('default', ['test']);
};