/*
** Variables.
*/
properties([buildDiscarder(logRotator(numToKeepStr: '50'))])
def serie = '21.04'
def maintenanceBranch = "${serie}.x"
env.PROJECT='centreon-web'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
} else if ((env.BRANCH_NAME == 'master') || (env.BRANCH_NAME == maintenanceBranch)) {
  env.BUILD = 'REFERENCE'
} else {
  env.BUILD = 'CI'
}
def apiFeatureFiles = []
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
    // git repository is stored for the Sonar analysis below.
    if ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE')) {
      sh 'tar czf centreon-web-git.tar.gz centreon-web'
      stash name: 'git-sources', includes: 'centreon-web-git.tar.gz'
    }
    sh "./centreon-build/jobs/web/${serie}/mon-web-source.sh"
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
    stash name: 'tar-sources', includes: "centreon-web-${env.VERSION}.tar.gz"
    stash name: 'cypress-node-modules', includes: "cypress-node-modules.tar.gz"
    stash name: 'vendor', includes: 'vendor.tar.gz'
    stash name: 'api-doc', includes: 'centreon-api-v2.html'
    publishHTML([
      allowMissing: false,
      keepAll: true,
      reportDir: 'summary',
      reportFiles: 'index.html',
      reportName: 'Centreon Build Artifacts',
      reportTitles: ''
    ])
    apiFeatureFiles = sh(script: 'find centreon-web/tests/api/features -type f -name "*.feature" -printf "%P\n" | sort', returnStdout: true).split()
    e2eFeatureFiles = sh(script: 'find centreon-web/tests/e2e/cypress/integration -type f -name "*.feature" -printf "%P\n" | sort', returnStdout: true).split()
    featureFiles = sh(script: 'find centreon-web/features -type f -name "*.feature" -printf "%P\n" | sort', returnStdout: true).split()
  }
}

try {
  // stage('Unit tests') {
  //   parallel 'centos7': {
  //     node {
  //       sh 'setup_centreon_build.sh'
  //       unstash 'tar-sources'
  //       sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh centos7"
  //       junit 'ut-be.xml,ut-fe.xml'

  //       if (env.CHANGE_ID) { // pull request to comment with coding style issues
  //         ViolationsToGitHub([
  //           repositoryName: 'centreon',
  //           pullRequestId: env.CHANGE_ID,

  //           createSingleFileComments: true,
  //           commentOnlyChangedContent: true,
  //           commentOnlyChangedFiles: true,
  //           keepOldComments: false,

  //           commentTemplate: "**{{violation.severity}}**: {{violation.message}}",

  //           violationConfigs: [
  //             [parser: 'CHECKSTYLE', pattern: '.*/codestyle-be.xml$', reporter: 'Checkstyle'],
  //             [parser: 'CHECKSTYLE', pattern: '.*/codestyle-fe.xml$', reporter: 'Checkstyle'],
  //             [parser: 'CHECKSTYLE', pattern: '.*/phpstan.xml$', reporter: 'Checkstyle']
  //           ]
  //         ])
  //       }

  //       recordIssues(
  //         enabledForFailure: true,
  //         qualityGates: [[threshold: 1, type: 'DELTA', unstable: false]],
  //         tool: phpCodeSniffer(id: 'phpcs', name: 'phpcs', pattern: 'codestyle-be.xml'),
  //         referenceJobName: 'centreon-web/master',
  //         trendChartType: 'NONE'
  //       )
  //       recordIssues(
  //         enabledForFailure: true,
  //         qualityGates: [[threshold: 1, type: 'DELTA', unstable: false]],
  //         tool: phpStan(id: 'phpstan', name: 'phpstan', pattern: 'phpstan.xml'),
  //         referenceJobName: 'centreon-web/master',
  //         trendChartType: 'NONE'
  //       )
  //       recordIssues(
  //         enabledForFailure: true,
  //         failOnError: true,
  //         qualityGates: [[threshold: 1, type: 'NEW', unstable: false]],
  //         tool: esLint(id: 'eslint', name: 'eslint', pattern: 'codestyle-fe.xml'),
  //         referenceJobName: 'centreon-web/master',
  //         trendChartType: 'NONE'
  //       )

  //       if ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE')) {
  //         unstash 'git-sources'
  //         sh 'rm -rf centreon-web && tar xzf centreon-web-git.tar.gz'
  //         withSonarQubeEnv('SonarQube') {
  //           sh "./centreon-build/jobs/web/${serie}/mon-web-analysis.sh"
  //         }
  //       }
  //     }
  //   },
  //   'centos8': {
  //     node {
  //       sh 'setup_centreon_build.sh'
  //       unstash 'tar-sources'
  //       sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh centos8"
  //       junit 'ut-be.xml,ut-fe.xml'
  //     }
  //   }
  //   if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
  //     error('Unit tests stage failure.');
  //   }
  // }

  stage('Package') {
    parallel 'centos7': {
      node {https://addons.mozilla.org/fr/firefox/addon/nord-theme/
        sh 'setup_centreon_build.sh'
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos7"
        archiveArtifacts artifacts: 'rpms-centos7.tar.gz'
      }
    //},
    //'centos8': {
    //  node {
    //    sh 'setup_centreon_build.sh'
    //    unstash 'tar-sources'
    //    sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos8"
    //    archiveArtifacts artifacts: 'rpms-centos8.tar.gz'
    //  }
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
    //},
    //'centos8': {
    //  node {
    //    sh 'setup_centreon_build.sh'
    //    sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh centos8"
    //  }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('E2E tests') {
    def parallelSteps = [:]
    for (x in e2eFeatureFiles) {
      def feature = x
      parallelSteps[feature] = {
        node {
          sh 'setup_centreon_build.sh'
          unstash 'tar-sources'
          unstash 'cypress-node-modules'
          def acceptanceStatus = sh(script: "./centreon-build/jobs/web/${serie}/mon-web-e2e-test.sh centos7 tests/e2e/cypress/integration/${feature}", returnStatus: true)
          junit 'centreon-web*/tests/e2e/cypress/results/reports/junit-report.xml'
          if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0))
            currentBuild.result = 'FAILURE'
          archiveArtifacts allowEmptyArchive: true, artifacts: 'centreon-web*/tests/e2e/cypress/results/videos/*.mp4'
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('E2E tests stage failure.');
    }
  }

  // stage('API integration tests') {
  //   def parallelSteps = [:]
  //   for (x in apiFeatureFiles) {
  //     def feature = x
  //     parallelSteps[feature] = {
  //       node {
  //         sh 'setup_centreon_build.sh'
  //         unstash 'tar-sources'
  //         unstash 'vendor'
  //         def acceptanceStatus = sh(script: "./centreon-build/jobs/web/${serie}/mon-web-api-integration-test.sh centos7 tests/api/features/${feature}", returnStatus: true)
  //         junit 'xunit-reports/**/*.xml'
  //         if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0))
  //           currentBuild.result = 'FAILURE'
  //         archiveArtifacts allowEmptyArchive: true, artifacts: 'api-integration-test-logs/*.txt'
  //       }
  //     }
  //   }
  //   parallel parallelSteps
  //   if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
  //     error('API integration tests stage failure.');
  //   }
  // }

  // stage('Acceptance tests') {
  //   def parallelSteps = [:]
  //   for (x in featureFiles) {
  //     def feature = x
  //     parallelSteps[feature] = {
  //       node {
  //         sh 'setup_centreon_build.sh'
  //         unstash 'tar-sources'
  //         unstash 'vendor'
  //         def acceptanceStatus = sh(script: "./centreon-build/jobs/web/${serie}/mon-web-acceptance.sh centos7 features/${feature}", returnStatus: true)
  //         junit 'xunit-reports/**/*.xml'
  //         if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0))
  //           currentBuild.result = 'FAILURE'
  //         archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png, acceptance-logs/*.flv'
  //       }
  //     }
  //   }
  //   parallel parallelSteps
  //   if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
  //     error('Critical tests stage failure.');
  //   }
  // }

  if ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE')) {
    stage('Delivery') {
      node {
        sh 'setup_centreon_build.sh'
        unstash 'tar-sources'
        unstash 'api-doc'
        sh "./centreon-build/jobs/web/${serie}/mon-web-delivery.sh"
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure.');
      }
    }

    if (env.BUILD == 'REFERENCE') {
      build job: "centreon-autodiscovery/${env.BRANCH_NAME}", wait: false
      build job: "centreon-awie/${env.BRANCH_NAME}", wait: false
      build job: "centreon-license-manager/${env.BRANCH_NAME}", wait: false
      build job: "centreon-pp-manager/${env.BRANCH_NAME}", wait: false
      build job: "centreon-bam/${env.BRANCH_NAME}", wait: false
      build job: "centreon-mbi/${env.BRANCH_NAME}", wait: false
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
