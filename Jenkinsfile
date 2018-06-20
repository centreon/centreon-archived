stage('Source') {
  node {
    sh 'setup_centreon_build.sh'
    dir('centreon-web') {
      checkout scm
    }
    sh './centreon-build/jobs/web/18.9/mon-web-source.sh'
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
  }
}

try {
  stage('Unit tests') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-unittest.sh centos7'
        step([
          $class: 'XUnitBuilder',
          thresholds: [
            [$class: 'FailedThreshold', failureThreshold: '0'],
            [$class: 'SkippedThreshold', failureThreshold: '0']
          ],
          tools: [[$class: 'PHPUnitJunitHudsonTestType', pattern: 'ut.xml']]
        ])
        step([
          $class: 'CloverPublisher',
          cloverReportDir: '.',
          cloverReportFileName: 'coverage.xml'
        ])
        step([
          $class: 'hudson.plugins.checkstyle.CheckStylePublisher',
          pattern: 'codestyle.xml',
          usePreviousBuildAsReference: true,
          useDeltaValues: true,
          failedNewAll: '0'
        ])
      }
    },
    'debian9': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-unittest.sh debian9'
        step([
          $class: 'XUnitBuilder',
          thresholds: [
            [$class: 'FailedThreshold', failureThreshold: '0'],
            [$class: 'SkippedThreshold', failureThreshold: '0']
          ],
          tools: [[$class: 'PHPUnitJunitHudsonTestType', pattern: 'ut.xml']]
        ])
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Unit tests stage failure.');
    }
  }

  stage('Package') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-package.sh centos7'
      }
    },
    'debian9': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-package.sh debian9'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Package stage failure.');
    }
  }

  stage('Bundle') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-bundle.sh centos7'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('Critical tests') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-acceptance.sh centos7 @critical'
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
      error('Critical tests stage failure.');
    }
  }

  if (env.BRANCH_NAME == 'master') {
    stage('Acceptance tests') {
      parallel 'centos7': {
        node {
          sh 'setup_centreon_build.sh'
          sh './centreon-build/jobs/web/18.9/mon-web-acceptance.sh centos7 ~@critical'
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
        error('Critical tests stage failure.');
      }
    }

    stage('Delivery') {
      node {
        sh 'setup_centreon_build.sh'
        sh './centreon-build/jobs/web/18.9/mon-web-delivery.sh'
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure.');
      }
    }
    build job: 'centreon-autodiscovery/master', wait: false
    build job: 'centreon-awie/master', wait: false
    build job: 'centreon-export/master', wait: false
    build job: 'centreon-license-manager/master', wait: false
    build job: 'centreon-pp-manager/master', wait: false
    build job: 'centreon-bam/master', wait: false
    build job: 'des-mbi-bundle-centos7', wait: false
  }
} catch(e) {
  if (env.BRANCH_NAME == 'master') {
    slackSend channel: "#monitoring-metrology",
        color: "#F30031",
        message: "*FAILURE*: `CENTREON WEB` <${env.BUILD_URL}|build #${env.BUILD_NUMBER}> on branch ${env.BRANCH_NAME}\n" +
            "*COMMIT*: <https://github.com/centreon/centreon/commit/${source.COMMIT}|here> by ${source.COMMITTER}\n" +
            "*INFO*: ${e}"
  }

  currentBuild.result = 'FAILURE'
}
