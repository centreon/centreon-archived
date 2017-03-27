stage('Source') {
  node {
    sh 'cd /opt/centreon-build && git pull && cd -'
    dir('centreon-web') {
      checkout scm
    }
    sh '/opt/centreon-build/jobs/web/current/mon-web-source.sh'
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
  }
}

stage('Unit tests') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-unittest.sh centos6'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-unittest.sh centos7'
      step([
        $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
        pattern: '**/codestyle.xml',
        usePreviousBuildAsReference: true,
        useDeltaValues: true,
        failedNewAll: '0'
      ])
    }
  }
  if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
    error('Unit tests stage failure.');
  }
}

stage('Package') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-package.sh centos6'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-package.sh centos7'
    }
  }
  if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
    error('Package stage failure.');
  }
}

stage('Bundle') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-bundle.sh centos6'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-bundle.sh centos7'
    }
  }
  if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
    error('Bundle stage failure.');
  }
}

stage('Acceptance tests') {
  parallel 'centos6': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-acceptance.sh centos6'
      step([
        $class: 'XUnitBuilder',
        thresholds: [
          [$class: 'FailedThreshold', failureThreshold: '0'],
          [$class: 'SkippedThreshold', failureThreshold: '0']
        ],
        tools: [[$class: 'JUnitType', pattern: 'xunit-reports/**/*.xml']]
      ])
      archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png'
    }
  },
  'centos7': {
    node {
      sh 'cd /opt/centreon-build && git pull && cd -'
      sh '/opt/centreon-build/jobs/web/current/mon-web-acceptance.sh centos7'
      step([
        $class: 'XUnitBuilder',
        thresholds: [
          [$class: 'FailedThreshold', failureThreshold: '0'],
          [$class: 'SkippedThreshold', failureThreshold: '0']
        ],
        tools: [[$class: 'JUnitType', pattern: 'xunit-reports/**/*.xml']]
      ])
      archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png'
    }
  }
  if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
    error('Acceptance tests stage failure.');
  }
}
