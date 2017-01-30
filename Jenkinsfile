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

stage('Unit tests') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-unittest.sh centos6'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-unittest.sh centos7'
      step([
        $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
        pattern: '**/codestyle.xml',
        usePreviousBuildAsReference: '1',
        useStableBuildAsReference: '1',
        unstableTotalAll: '0'
      ])
    }
  }
}

stage('Package') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-package.sh centos6'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-package.sh centos7'
    }
  }
}

stage('Bundle') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-bundle.sh 6'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-bundle.sh 7'
    }
  }
}

stage('Acceptance tests') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-acceptance.sh centos6'
      junit 'xunit-reports/**/*.xml'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-acceptance.sh centos7'
      junit 'xunit-reports/**/*.xml'
    }
  }
}
