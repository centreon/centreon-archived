stage('Source') {
  node {
    sh 'cd /opt/centreon-build && git pull && cd -'
    dir('centreon-web') {
      checkout scm
    }
    sh '/opt/centreon-build/jobs/web/pipeline/mon-web-source.sh'
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
  }
}

try {
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
          usePreviousBuildAsReference: true,
          failedNewAll: '0'
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

  stage('Delivery') {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/pipeline/mon-web-delivery.sh'
    }
  }
}
finally {
  buildStatus = currentBuild.result ?: 'SUCCESS';
  if (buildStatus != 'SUCCESS') {
    slackSend channel: '#monitoring-metrology', message: "@channel Centreon Web build ${env.BUILD_NUMBER} was broken by ${source.COMMITTER}. Please fix it ASAP."
  }
}
