stage('Source') {
  node {
    sh 'cd /opt/centreon-build && git pull && cd -'
    dir('centreon-web') {
      checkout scm
    }
    sh '/opt/centreon-build/jobs/web/pipeline/mon-web-source.sh'
    def source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
  }
}
