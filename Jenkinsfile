import org.apache.tools.ant.types.selectors.SelectorUtils
import org.jenkinsci.plugins.pipeline.modeldefinition.Utils

/*
** Variables.
*/
properties([buildDiscarder(logRotator(numToKeepStr: '50'))])
def serie = '21.10'
def maintenanceBranch = "${serie}.x"
env.REF_BRANCH = 'master'
env.PROJECT='centreon-web'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
} else if ((env.BRANCH_NAME == env.REF_BRANCH) || (env.BRANCH_NAME == maintenanceBranch)) {
  env.BUILD = 'REFERENCE'
} else {
  env.BUILD = 'CI'
}

def buildBranch = env.BRANCH_NAME
if (env.CHANGE_BRANCH) {
  buildBranch = env.CHANGE_BRANCH
}

def backendFiles = [
  'Jenkinsfile',
  'sonar-project.properties',
  '**/*.php',
  'www/**/*.js',
  '**/*.sh',
  'composer.*',
  'symfony.lock',
  '.env*',
  'behat.yml',
  'ruleset.xml',
  'phpstan.neon',
  'bin/*',
  'config/*',
  'cron/*',
  'doc/*',
  'features/*',
  'lang/*',
  'lib/*',
  'tests/*'
]
def frontendFiles = [
  'Jenkinsfile',
  'sonar-project.properties',
  'www/front_src/*',
  'packag*.json',
  'webpack*',
  'babel.config.js',
  'jest.config.js',
  'setupTest.js',
  'tsconfig.json',
  '.prettierrc.js',
  '.eslint*',
  '.csslintrc',
  '**/*.ts',
  '**/*.tsx',
  '**/*.jsx',
  'lang/*'
]
def hasFrontendChanges = true
def hasBackendChanges = true
def apiFeatureFiles = []
def featureFiles = []
def acceptanceTag = ""

/*
** Functions
*/
def isStableBuild() {
  return ((env.BUILD == 'RELEASE') || (env.BUILD == 'REFERENCE'))
}

def hasChanges(patterns) {
  if (isStableBuild()) {
    return true
  }

  def isMatching = false

  sh "git config --add remote.origin.fetch +refs/heads/${env.REF_BRANCH}:refs/remotes/origin/${env.REF_BRANCH}"
  sh "git fetch --no-tags"
  sh "git config user.name none"
  sh "git config user.email none"
  sh "git pull --rebase origin ${env.REF_BRANCH} || git rebase --abort || true"
  def diffFiles = sh(script: "git diff --name-only origin/${env.REF_BRANCH} --", returnStdout: true).trim().split()

  for (file in diffFiles) {
    for (pattern in patterns) {
      if (SelectorUtils.match(pattern, file)) {
        isMatching = true
      }
    }
  }

  return isMatching
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
stage('Source') {
  node {
    dir('centreon-web') {
      checkout scm
      if (!isStableBuild()) {
        hasFrontendChanges = hasChanges(frontendFiles)
        hasBackendChanges = hasChanges(backendFiles)
      }
    }

    checkoutCentreonBuild(buildBranch)

    // git repository is stored for the Sonar analysis below.
    sh 'tar czf centreon-web-git.tar.gz centreon-web'
    stash name: 'git-sources', includes: 'centreon-web-git.tar.gz'

    // resuming process
    sh "./centreon-build/jobs/web/${serie}/mon-web-source.sh"
    source = readProperties file: 'source.properties'
    env.VERSION = "${source.VERSION}"
    env.RELEASE = "${source.RELEASE}"
    stash name: 'tar-sources', includes: "centreon-web-${env.VERSION}.tar.gz"
    stash name: 'vendor', includes: 'vendor.tar.gz'
    stash name: 'node_modules', includes: 'node_modules.tar.gz'
    stash name: 'api-doc', includes: 'centreon-api-v21.10.html'
    publishHTML([
      allowMissing: false,
      keepAll: true,
      reportDir: 'summary',
      reportFiles: 'index.html',
      reportName: 'Centreon Build Artifacts',
      reportTitles: ''
    ])

    // get api feature files
    apiFeatureFiles = sh(
      script: 'find centreon-web/tests/api/features -type f -name "*.feature" -printf "%P\n" | sort',
      returnStdout: true
    ).split()

    // get feature files
    def grepAcceptanceFiles = ""
    if (!hasBackendChanges && hasFrontendChanges) {
      acceptanceTag = "@reactjs"
      grepAcceptanceFiles = "-exec grep -Rl '${acceptanceTag}' {} \\;"
    }
    featureFiles = sh(
      script: "find centreon-web/features -type f -name '*.feature' ${grepAcceptanceFiles} | sed -e 's#centreon-web/features/##g' | sort",
      returnStdout: true
    ).split()
  }
}

try {
  stage('Unit tests') {
    parallel 'frontend': {
      if (!hasFrontendChanges) {
        Utils.markStageSkippedForConditional('frontend')
      } else {
        node {
          checkoutCentreonBuild(buildBranch)
          unstash 'tar-sources'
          unstash 'node_modules'
          sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh frontend"
          junit 'ut-fe.xml'
          stash name: 'ut-fe.xml', includes: 'ut-fe.xml'
          stash name: 'codestyle-fe.xml', includes: 'codestyle-fe.xml'
        }
      }
    },
    'backend': {
      if (!hasBackendChanges) {
        Utils.markStageSkippedForConditional('backend')
      } else {
        node {
          checkoutCentreonBuild(buildBranch)
          unstash 'tar-sources'
          unstash 'vendor'
          sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh backend"
          junit 'ut-be.xml'
          stash name: 'ut-be.xml', includes: 'ut-be.xml'
          stash name: 'coverage-be.xml', includes: 'coverage-be.xml'
          stash name: 'codestyle-be.xml', includes: 'codestyle-be.xml'
          stash name: 'phpstan.xml', includes: 'phpstan.xml'
        }
      }
    },
    'sonar': {
      node {
        // Run sonarQube analysis
        checkoutCentreonBuild(buildBranch)
        unstash 'git-sources'
        sh 'rm -rf centreon-web && tar xzf centreon-web-git.tar.gz'
        withSonarQubeEnv('SonarQubeDev') {
          sh "./centreon-build/jobs/web/${serie}/mon-web-analysis.sh"
        }
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Unit tests stage failure.');
    }
  }

  stage('Quality gate') {
    node {
      discoverGitReferenceBuild()

      if (hasBackendChanges) {
        unstash 'ut-be.xml'
        unstash 'coverage-be.xml'
        unstash 'codestyle-be.xml'
        unstash 'phpstan.xml'
      }

      if (hasFrontendChanges) {
        unstash 'ut-fe.xml'
        unstash 'codestyle-fe.xml'
      }

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
            [parser: 'CHECKSTYLE', pattern: '.*/phpstan.xml$', reporter: 'Checkstyle'],
            [parser: 'CHECKSTYLE', pattern: '.*/codestyle-fe.xml$', reporter: 'Checkstyle']
          ]
        ])
      }

      if (hasBackendChanges) {
        recordIssues(
          enabledForFailure: true,
          qualityGates: [[threshold: 1, type: 'DELTA', unstable: false]],
          tool: phpCodeSniffer(id: 'phpcs', name: 'phpcs', pattern: 'codestyle-be.xml'),
          trendChartType: 'NONE'
        )
        recordIssues(
          enabledForFailure: true,
          qualityGates: [[threshold: 1, type: 'DELTA', unstable: false]],
          tool: phpStan(id: 'phpstan', name: 'phpstan', pattern: 'phpstan.xml'),
          trendChartType: 'NONE'
        )
      }

      if (hasFrontendChanges) {
        recordIssues(
          enabledForFailure: true,
          failOnError: true,
          qualityGates: [[threshold: 1, type: 'NEW', unstable: false]],
          tool: esLint(id: 'eslint', name: 'eslint', pattern: 'codestyle-fe.xml'),
          trendChartType: 'NONE'
        )
      }
      // sonarQube step to get qualityGate result
      def qualityGate = waitForQualityGate()
      if (qualityGate.status != 'OK') {
        error "Pipeline aborted due to quality gate failure: ${qualityGate.status}"
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error("Quality gate failure: ${qualityGate.status}.");
      }
    }

    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error("Quality gate failure: ${qualityGate.status}.");
    }
  }

  stage('Package') {
    def parallelSteps = [:]
    def osBuilds = isStableBuild() ? ['centos7', 'centos8'] : ['centos7', 'centos8']
    for (x in osBuilds) {
      def osBuild = x
      parallelSteps[osBuild] = {
        node {
          checkoutCentreonBuild(buildBranch)
          unstash 'tar-sources'
          sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh ${osBuild}"
          archiveArtifacts artifacts: "rpms-${osBuild}.tar.gz"
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Package stage failure.');
    }
  }

  stage('Bundle') {
    def parallelSteps = [:]
    def osBuilds = isStableBuild() ? ['centos7', 'centos8'] : ['centos7', 'centos8']
    for (x in osBuilds) {
      def osBuild = x
      parallelSteps[osBuild] = {
        node {
          checkoutCentreonBuild(buildBranch)
          sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh ${osBuild}"
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('API integration tests') {
    if (hasBackendChanges) {
      def parallelSteps = [:]
      for (x in apiFeatureFiles) {
        def feature = x
        parallelSteps[feature] = {
          node {
            checkoutCentreonBuild(buildBranch)
            unstash 'tar-sources'
            unstash 'vendor'
            def acceptanceStatus = sh(
              script: "./centreon-build/jobs/web/${serie}/mon-web-api-integration-test.sh centos7 tests/api/features/${feature}",
              returnStatus: true
            )
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
  }

  stage('Acceptance tests') {
    if (hasBackendChanges || hasFrontendChanges) {
      def parallelSteps = [:]
      for (x in featureFiles) {
        def feature = x
        parallelSteps[feature] = {
          node {
            checkoutCentreonBuild(buildBranch)
            unstash 'tar-sources'
            unstash 'vendor'
            def acceptanceStatus = sh(
              script: "./centreon-build/jobs/web/${serie}/mon-web-acceptance.sh centos7 features/${feature} ${acceptanceTag}",
              returnStatus: true
            )
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
  }

  if (isStableBuild()) {
    stage('Delivery') {
      node {
        checkoutCentreonBuild(buildBranch)
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
  if (isStableBuild()) {
    slackSend channel: "#monitoring-metrology",
        color: "#F30031",
        message: "*FAILURE*: `CENTREON WEB` <${env.BUILD_URL}|build #${env.BUILD_NUMBER}> on branch ${env.BRANCH_NAME}\n" +
            "*COMMIT*: <https://github.com/centreon/centreon/commit/${source.COMMIT}|here> by ${source.COMMITTER}\n" +
            "*INFO*: ${e}"
  }

  currentBuild.result = 'FAILURE'
}
