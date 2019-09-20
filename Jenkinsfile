properties([buildDiscarder(logRotator(numToKeepStr: '50'))])

stage('Source') {
  node {
    sh 'setup_centreon_build.sh'
    dir('centreon-web') {
      checkout scm
    }
    sh './centreon-build/jobs/web/3.4/mon-web-source.sh'
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
    publishHTML([
      allowMissing: false,
      keepAll: true,
      reportDir: 'summary',
      reportFiles: 'index.html',
      reportName: 'Centreon Build Artifacts',
      reportTitles: ''
    ])
  }
}

try {
  stage('Unit tests') {
    parallel 'centos6': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-unittest.sh centos6'
      }
    },
    'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-unittest.sh centos7'
        step([
          $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
          pattern: 'codestyle.xml',
          usePreviousBuildAsReference: true,
          useDeltaValues: true,
          failedNewAll: '0'
        ])
        junit 'jest-test-results.xml'
        if (env.BRANCH_NAME == '2.8.x') {
          withSonarQubeEnv('SonarQube') {
            sh './centreon-build/jobs/web/3.4/mon-web-analysis.sh'
          }
        }
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Unit tests stage failure.');
    }
  }

  stage('Package') {
    parallel 'centos6': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-package.sh centos6'
      }
    },
    'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-package.sh centos7'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Package stage failure.');
    }
  }

  stage('Bundle') {
    parallel 'centos6': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-bundle.sh centos6'
      }
    },
    'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-bundle.sh centos7'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('Critical tests') {
    parallel 'centos6': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-acceptance.sh centos6 @critical'
        junit 'xunit-reports/**/*.xml'
        if (currentBuild.result == 'UNSTABLE')
          currentBuild.result = 'FAILURE'
        archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png'
      }
    },
    'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-acceptance.sh centos7 @critical'
        junit 'xunit-reports/**/*.xml'
        if (currentBuild.result == 'UNSTABLE')
          currentBuild.result = 'FAILURE'
        archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png, acceptance-logs/*.flv'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Critical tests stage failure.');
    }
  }

  if (env.BRANCH_NAME == '2.8.x') {
    stage('Acceptance tests') {
      parallel 'centos6': {
        node {
          sh 'setup_centreon_build.sh'
          sh './centreon-build/jobs/web/3.4/mon-web-acceptance.sh centos6 ~@critical'
          junit 'xunit-reports/**/*.xml'
          if (currentBuild.result == 'UNSTABLE')
            currentBuild.result = 'FAILURE'
          archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png'
        }
      },
      'centos7': {
        node {
          sh 'setup_centreon_build.sh'
          sh './centreon-build/jobs/web/3.4/mon-web-acceptance.sh centos7 ~@critical'
          junit 'xunit-reports/**/*.xml'
          if (currentBuild.result == 'UNSTABLE')
            currentBuild.result = 'FAILURE'
          archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png, acceptance-logs/*.flv'
        }
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Critical tests stage failure.');
      }
    }

    stage('Delivery') {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/3.4/mon-web-delivery.sh'
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure.');
      }
    }
    build job: 'centreon-autodiscovery/2.4.x', wait: false
    build job: 'centreon-awie/1.0.x', wait: false
    build job: 'centreon-export/2.3.x', wait: false
    build job: 'centreon-license-manager/1.2.x', wait: false
    build job: 'centreon-poller-display/master', wait: false
    build job: 'centreon-pp-manager/2.4.x', wait: false
    build job: 'centreon-bam/3.6.x', wait: false
  }
} catch(e) {
  if (env.BRANCH_NAME == '2.8.x') {
    slackSend channel: "#monitoring-metrology",
        color: "#F30031",
        message: "*FAILURE*: `CENTREON WEB` <${env.BUILD_URL}|build #${env.BUILD_NUMBER}> on branch ${env.BRANCH_NAME}\n" +
            "*COMMIT*: <https://github.com/centreon/centreon/commit/${source.COMMIT}|here> by ${source.COMMITTER}\n" +
            "*INFO*: ${e}"
  }

  currentBuild.result = 'FAILURE'
}
