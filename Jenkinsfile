/*
** Variables.
*/
def serie = '19.10'
def maintenanceBranch = "${serie}.x"
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
} else if ((env.BRANCH_NAME == 'master') || (env.BRANCH_NAME == maintenanceBranch)) {
  env.BUILD = 'REFERENCE'
} else {
  env.BUILD = 'CI'
}
def featureFiles = []

/*
** Pipeline code.
*/
stage('Source') {
  node {
    sh 'setup_centreon_build.sh'
    dir('centreon-web') {
      checkout scm
    }
    sh "./centreon-build/jobs/web/${serie}/mon-web-source.sh"
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
    featureFiles = sh(script: 'ls -1 centreon-web/features | grep .feature', returnStdout: true).split()
  }
}

try {
  stage('Unit tests') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh centos7"
        junit 'ut.xml,jest-test-results.xml'
        if (currentBuild.result == 'UNSTABLE')
          currentBuild.result = 'FAILURE'
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
        if ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE')) {
          withSonarQubeEnv('SonarQube') {
            sh "./centreon-build/jobs/web/${serie}/mon-web-analysis.sh"
          }
        }
      }
//    },
//    'debian9': {
//      node {
//        sh 'setup_centreon_build.sh'
//        sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh debian9"
//        junit 'ut.xml'
//        if (currentBuild.result == 'UNSTABLE')
//          currentBuild.result = 'FAILURE'
//      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Unit tests stage failure.');
    }
  }

  stage('Package') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos7"
      }
//    },
//    'debian9': {
//      node {
//        sh 'setup_centreon_build.sh'
//        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh debian9"
//      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Package stage failure.');
    }
  }

  stage('Bundle') {
    parallel 'centos7': {
      node {
        sh 'setup_centreon_build.sh'
        sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh centos7"
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('Acceptance tests') {
    def parallelSteps = [:]
    for (x in featureFiles) {
      def feature = x
      parallelSteps[feature] = {
        node {
          sh 'setup_centreon_build.sh'
          def acceptanceStatus = sh(script: "./centreon-build/jobs/web/${serie}/mon-web-acceptance.sh centos7 features/${feature}", returnStatus: true)
          junit 'xunit-reports/**/*.xml'
          if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0))
            currentBuild.result = 'FAILURE'
          archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png, acceptance-logs/*.flv'
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Critical tests stage failure.');
    }
  }

  if ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE')) {
    stage('Delivery') {
      node {
        sh 'setup_centreon_build.sh'
        sh "./centreon-build/jobs/web/${serie}/mon-web-delivery.sh"
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure.');
      }
    }

    if (env.BUILD == 'REFERENCE') {
      build job: "centreon-autodiscovery/${env.BRANCH_NAME}", wait: false
      build job: "centreon-awie/${env.BRANCH_NAME}", wait: false
      build job: "centreon-export/${env.BRANCH_NAME}", wait: false
      build job: "centreon-license-manager/${env.BRANCH_NAME}", wait: false
      build job: "centreon-pp-manager/${env.BRANCH_NAME}", wait: false
      build job: "centreon-bam/${env.BRANCH_NAME}", wait: false
      build job: "centreon-bi-server/${env.BRANCH_NAME}", wait: false
    }
  }
} catch(e) {
  if ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE')) {
    slackSend channel: "#monitoring-metrology",
        color: "#F30031",
        message: "*FAILURE*: `CENTREON WEB` <${env.BUILD_URL}|build #${env.BUILD_NUMBER}> on branch ${env.BRANCH_NAME}\n" +
            "*COMMIT*: <https://github.com/centreon/centreon/commit/${source.COMMIT}|here> by ${source.COMMITTER}\n" +
            "*INFO*: ${e}"
  }

  currentBuild.result = 'FAILURE'
}
