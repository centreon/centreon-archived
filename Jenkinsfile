/*
** Variables.
*/
def serie = '20.10'
def maintenanceBranch = "${serie}.x"
def qaBranch = "dev-${serie}.x"
env.REF_BRANCH = "${maintenanceBranch}"
env.PROJECT='centreon-web'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
  env.DELIVERY_STAGE = 'Delivery to testing'
  env.DOCKER_STAGE = 'Docker packaging'
} else if (env.BRANCH_NAME == maintenanceBranch) {
  env.BUILD = 'REFERENCE'
  env.DELIVERY_STAGE = 'Delivery to canary'
  env.DOCKER_STAGE = 'Docker packaging with canary rpms'
} else if (env.BRANCH_NAME == qaBranch) {
  env.BUILD = 'QA'
  env.DELIVERY_STAGE = 'Delivery to unstable'
  env.DOCKER_STAGE = 'Docker packaging with unstable rpms'
} else {
  env.BUILD = 'CI'
  env.DELIVERY_STAGE = 'Delivery to canary'
  env.DOCKER_STAGE = 'Docker packaging with canary rpms'
}
def apiFeatureFiles = []
def featureFiles = []
def buildBranch = env.BRANCH_NAME
if (env.CHANGE_BRANCH) {
  buildBranch = env.CHANGE_BRANCH
}

// Skip sonarQ analysis on branch without PR  - Unable to merge
def securityAnalysisRequired = 'yes'
if (!env.CHANGE_ID && env.BUILD == 'CI') {
    securityAnalysisRequired = 'no'
}

/*
** Functions
*/
def isStableBuild() {
  return ((env.BUILD == 'REFERENCE') || (env.BUILD == 'QA'))
}

def checkoutCentreonBuild(buildBranch) {
  def getCentreonBuildGitConfiguration = { branchName -> [
    $class: 'GitSCM',
    branches: [[name: "refs/heads/${branchName}"]],
    doGenerateSubmoduleConfigurations: false,
    userRemoteConfigs: [[
      $class: 'UserRemoteConfig',
      url: "ssh://git@github.com/centreon/centreon-build.git"
    ]]
  ]}

  dir('centreon-build') {
    try {
      checkout(getCentreonBuildGitConfiguration(buildBranch))
    } catch(e) {
      echo "branch '${buildBranch}' does not exist in centreon-build, then fallback to master"
      checkout(getCentreonBuildGitConfiguration('master'))
    }
  }
}

/*
** Pipeline code.
*/
stage('Deliver sources') {
  node {
    checkoutCentreonBuild(buildBranch)
    dir('centreon-web') {
      checkout scm
    }
    // git repository is stored for the Sonar analysis below.
    sh 'tar czf centreon-web-git.tar.gz centreon-web'
    stash name: 'git-sources', includes: 'centreon-web-git.tar.gz'
    sh "./centreon-build/jobs/web/${serie}/mon-web-source.sh"
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
    stash name: 'tar-sources', includes: "centreon-web-${env.VERSION}.tar.gz"
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
    featureFiles = sh(script: 'rm centreon-web/features/Ldap*.feature && find centreon-web/features -type f -name "*.feature" -printf "%P\n" | sort', returnStdout: true).split()
  }
}

try {
  stage('Unit tests // RPM Packaging // Sonar analysis') {
    parallel 'unit tests centos7': {
      node {
        checkoutCentreonBuild(buildBranch)
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh centos7"
        junit 'ut-be.xml,ut-fe.xml'

        recordIssues(
          referenceJobName: "${env.PROJECT}/${env.REF_BRANCH}",
          enabledForFailure: true,
          aggregatingResults: true,
          tools: [
            checkStyle(pattern: 'codestyle-be.xml'),
            checkStyle(pattern: 'phpstan.xml')
          ],
          trendChartType: 'NONE'
        )
        recordIssues(
          referenceJobName: "${env.PROJECT}/${env.REF_BRANCH}",
          enabledForFailure: true,
          tools: [esLint(pattern: 'codestyle-fe.xml')],
          trendChartType: 'NONE'
        )

        if (env.CHANGE_ID) { // pull request to comment with coding style issues
          ViolationsToGitHub([
            repositoryName: 'centreon',
            pullRequestId: env.CHANGE_ID,

            createSingleFileComments: true,
            commentOnlyChangedContent: true,
            commentOnlyChangedFiles: true,
            keepOldComments: false,

            commentTemplate: "**{{violation.severity}}**: {{violation.message}}",

            violationConfigs: [
              [parser: 'CHECKSTYLE', pattern: '.*/codestyle-be.xml$', reporter: 'Checkstyle'],
              [parser: 'CHECKSTYLE', pattern: '.*/codestyle-fe.xml$', reporter: 'Checkstyle'],
              [parser: 'CHECKSTYLE', pattern: '.*/phpstan.xml$', reporter: 'Checkstyle']
            ]
          ])
        }

        if (securityAnalysisRequired == 'no') {
          Utils.markStageSkippedForConditional('sonar')
        } else {
          // Run sonarQube analysis
          unstash 'git-sources'
          sh 'rm -rf centreon-web && tar xzf centreon-web-git.tar.gz'
          withSonarQubeEnv('SonarQubeDev') {
            sh "./centreon-build/jobs/web/${serie}/mon-web-analysis.sh"
          }
          // sonarQube step to get qualityGate result
          timeout(time: 10, unit: 'MINUTES') {
            def qualityGate = waitForQualityGate()
            if (qualityGate.status != 'OK') {
              error "Pipeline aborted due to quality gate failure: ${qualityGate.status}"
            }
          }
        }
        if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
          error("Unit test failure.");
        }
      }
    },
    //'unit tests alma8': {
    //  node {
    //    checkoutCentreonBuild(buildBranch)
    //    unstash 'tar-sources'
    //    sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh alma8"
    //    junit 'ut-be.xml,ut-fe.xml'
    //  }
    //},
    'packaging centos7': {
      node {
        checkoutCentreonBuild(buildBranch)
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos7"
        archiveArtifacts artifacts: "rpms-centos7.tar.gz"
        stash name: "rpms-centos7", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    },
    'rpm packaging alma8': {
      node {
        checkoutCentreonBuild(buildBranch)
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh alma8"
        archiveArtifacts artifacts: "rpms-alma8.tar.gz"
        stash name: "rpms-alma8", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error("Unit test // packaging stage failure.");
    }
  }

  stage("$DELIVERY_STAGE") {
    node {
      checkoutCentreonBuild(buildBranch)    
      sh 'rm -rf output'
      unstash 'tar-sources'
      unstash 'api-doc'
      unstash 'rpms-alma8'
      unstash 'rpms-centos7'
      sh "./centreon-build/jobs/web/${serie}/mon-web-delivery.sh"
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Delivery stage failure');
    }
  }
  
  stage("$DOCKER_STAGE") {
    def parallelSteps = [:]
    def osBuilds = isStableBuild() ? ['centos7', 'alma8'] : ['centos7']
    for (x in osBuilds) {
      def osBuild = x
      parallelSteps[osBuild] = {
        node {
          checkoutCentreonBuild(buildBranch)
          sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh ${osBuild}"
        }
      }
    }
    //'Docker centos8': {
    //  node {
    //    checkoutCentreonBuild(buildBranch)
    //    sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh centos8"
    //  }
    //}
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('API integration tests') {
    def parallelSteps = [:]
    for (x in apiFeatureFiles) {
      def feature = x
      parallelSteps[feature] = {
        node {
          checkoutCentreonBuild(buildBranch)
          unstash 'tar-sources'
          unstash 'vendor'
          def acceptanceStatus = sh(script: "./centreon-build/jobs/web/${serie}/mon-web-api-integration-test.sh centos7 tests/api/features/${feature}", returnStatus: true)
          junit 'xunit-reports/**/*.xml'
          if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0))
            currentBuild.result = 'FAILURE'
          archiveArtifacts allowEmptyArchive: true, artifacts: 'api-integration-test-logs/*.txt'
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('API integration tests stage failure.');
    }
  }

  stage('Acceptance tests') {
    def parallelSteps = [:]
    for (x in featureFiles) {
      def feature = x
      parallelSteps[feature] = {
        node {
          checkoutCentreonBuild(buildBranch)
          unstash 'tar-sources'
          unstash 'vendor'
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

} catch(e) {
  if (isStableBuild()) {
    slackSend channel: "#monitoring-metrology",
        color: "#F30031",
        message: "*FAILURE*: `CENTREON WEB` <${env.BUILD_URL}|build #${env.BUILD_NUMBER}> on branch ${env.BRANCH_NAME}\n" +
            "*COMMIT*: <https://github.com/centreon/centreon/commit/${source.COMMIT}|here> by ${source.COMMITTER}\n" +
            "*INFO*: ${e}"
  }
}
