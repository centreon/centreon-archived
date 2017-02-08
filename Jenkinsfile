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
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-package.sh centos6'
      }
    },
    'centos7': {
      node {
        sh 'cd /opt/centreon-build && git pull && cd -'
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-package.sh centos7'
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
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-bundle.sh centos6'
      }
    },
    'centos7': {
      node {
        sh 'cd /opt/centreon-build && git pull && cd -'
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-bundle.sh centos7'
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
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-acceptance.sh centos6'
        junit 'xunit-reports/**/*.xml'
        archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png'
      }
    },
    'centos7': {
      node {
        sh 'cd /opt/centreon-build && git pull && cd -'
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-acceptance.sh centos7'
        junit 'xunit-reports/**/*.xml'
        archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Acceptance tests stage failure.');
    }
  }

  if (env.BRANCH_NAME == '2.8.x') {
    stage('Delivery') {
      node {
        sh 'cd /opt/centreon-build && git pull && cd -'
        sh '/opt/centreon-build/jobs/web/pipeline/mon-web-delivery.sh'
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure.');
      }
    }
    build 'mon-automation-bundle-centos6'
    build 'mon-automation-bundle-centos7'
    build 'mon-lm-bundle-centos6'
    build 'mon-lm-bundle-centos7'
    build 'mon-ppe-bundle-centos6'
    build 'mon-ppe-bundle-centos7'
    build 'mon-ppm-bundle-centos6'
    build 'mon-ppm-bundle-centos7'
    build 'des-bam-bundle-centos6'
    build 'des-bam-bundle-centos7'
    build 'des-map-bundle-centos6'
    build 'des-map-bundle-centos7'
    build 'des-mbi-bundle-centos6'
    build 'des-mbi-bundle-centos7'
  }
}
finally {
  buildStatus = currentBuild.result ?: 'SUCCESS';
  if (buildStatus != 'SUCCESS') {
    slackSend channel: '#monitoring-metrology', message: "@channel Centreon Web build ${env.BUILD_NUMBER} was broken by ${source.COMMITTER}. Please fix it ASAP."
  }
}
