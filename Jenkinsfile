import org.apache.tools.ant.types.selectors.SelectorUtils
import org.jenkinsci.plugins.pipeline.modeldefinition.Utils

/*
** Variables.
*/
def serie = '21.10'
def maintenanceBranch = "${serie}.x"
def qaBranch = "dev-${serie}.x"
env.REF_BRANCH = 'master'
env.PROJECT='centreon-web'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
} else if ((env.BRANCH_NAME == env.REF_BRANCH) || (env.BRANCH_NAME == maintenanceBranch)) {
  env.BUILD = 'REFERENCE'
} else if ((env.BRANCH_NAME == 'develop') || (env.BRANCH_NAME == qaBranch)) {
  env.BUILD = 'QA'
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
  return ((env.BUILD == 'REFERENCE') || (env.BUILD == 'QA'))
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
    stash name: 'cypress-node-modules', includes: "cypress-node-modules.tar.gz"
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

    // get tests E2E feature files
    e2eFeatureFiles = sh(
      script: 'find centreon-web/tests/e2e/cypress/integration -type f -name "*.feature" -printf "%P\n" | sort',
      returnStdout: true
    ).split()

    //FIXME : reintegrate ldap features after fixing them
    featureFiles = sh(
      script: "rm centreon-web/features/Ldap*.feature && find centreon-web/features -type f -name '*.feature' | sed -e 's#centreon-web/features/##g' | sort",
      returnStdout: true
    ).split()
  }
}

try {
  stage('Unit tests // Sonar analysis // RPMs Packaging') {
    parallel 'frontend': {
      if (!hasFrontendChanges) {
        Utils.markStageSkippedForConditional('frontend')
      } else {
        node {
          checkoutCentreonBuild(buildBranch)
          unstash 'tar-sources'
          unstash 'node_modules'
          sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh frontend"
          recordIssues(
            referenceJobName: "centreon-web/${env.REF_BRANCH}",
            enabledForFailure: true,
            failOnError: true,
            qualityGates: [[threshold: 1, type: 'NEW', unstable: false]],
            tool: esLint(id: 'eslint', name: 'eslint', pattern: 'codestyle-fe.xml'),
            trendChartType: 'NONE'
          )
          junit 'ut-fe.xml'
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
          //Recording issues in Jenkins job
          recordIssues(
            referenceJobName: "centreon-web/${env.REF_BRANCH}",
            enabledForFailure: true,
            qualityGates: [[threshold: 1, type: 'DELTA', unstable: false]],
            tool: phpCodeSniffer(id: 'phpcs', name: 'phpcs', pattern: 'codestyle-be.xml'),
            trendChartType: 'NONE'
          )
          recordIssues(
            referenceJobName: "centreon-web/${env.REF_BRANCH}",
            enabledForFailure: true,
            qualityGates: [[threshold: 1, type: 'DELTA', unstable: false]],
            tool: phpStan(id: 'phpstan', name: 'phpstan', pattern: 'phpstan.xml'),
            trendChartType: 'NONE'
          )
          junit 'ut-be.xml'
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
        // sonarQube step to get qualityGate result
        def qualityGate = waitForQualityGate()
        if (qualityGate.status != 'OK') {
          error "Pipeline aborted due to quality gate failure: ${qualityGate.status}"
        }
        if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
          error("Quality gate failure: ${qualityGate.status}.");
        }
      }
    },
    'rpm packaging centos7': {
      node {
        checkoutCentreonBuild(buildBranch)
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos7"
        archiveArtifacts artifacts: "rpms-centos7.tar.gz"
        stash name: "rpms-centos7", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    },
    'rpm packaging centos8': {
      node {
        checkoutCentreonBuild(buildBranch)
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos8"
        archiveArtifacts artifacts: "rpms-centos8.tar.gz"
        stash name: "rpms-centos8", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }      
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Unit tests // RPM Packaging Failure');
    }
  }


  stage('Violations to Github') {
    node {
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
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error("Reports stage failure");
    }
  }

  if ((env.BUILD == 'CI')) {
    stage('Delivery to unstable') {
      node {
        checkoutCentreonBuild(buildBranch)
        sh 'rm -rf output'
        unstash 'tar-sources'
        unstash 'api-doc'
        unstash 'rpms-centos8'
        unstash 'rpms-centos7'
        sh "./centreon-build/jobs/web/${serie}/mon-web-delivery.sh"
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure');
      }
    } 
  }

  stage('Docker packaging') {
    def parallelSteps = [:]
    def osBuilds = isStableBuild() ? ['centos7', 'centos8'] : ['centos7']
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

  stage('API // E2E') {
    parallel 'API Tests': {
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
      }
    },
    'E2E tests': {
      def parallelSteps = [:]
      for (x in e2eFeatureFiles) {
        def feature = x
        parallelSteps[feature] = {
          node {
            checkoutCentreonBuild(buildBranch)
            unstash 'tar-sources'
            unstash 'cypress-node-modules'
            timeout(time: 10, unit: 'MINUTES') {
              def acceptanceStatus = sh(script: "./centreon-build/jobs/web/${serie}/mon-web-e2e-test.sh centos7 tests/e2e/cypress/integration/${feature}", returnStatus: true)
              junit 'centreon-web*/tests/e2e/cypress/results/reports/junit-report.xml'
              if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0))
                currentBuild.result = 'FAILURE'
                archiveArtifacts allowEmptyArchive: true, artifacts: 'centreon-web*/tests/e2e/cypress/results/**/*.mp4, centreon-web*/tests/e2e/cypress/results/**/*.png'
            }
          }
        }
      }
      parallel parallelSteps
    }
  }

  if ((env.BUILD == 'RELEASE') || (env.BUILD == 'QA')) {
    stage('Acceptance tests') {
      if (hasBackendChanges || hasFrontendChanges) {
        def atparallelSteps = [:]
        for (x in featureFiles) {
          def feature = x
          atparallelSteps[feature] = {
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
        parallel atparallelSteps
        if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
          error('Critical tests stage failure');
        }
      }
    }
  }  

  if ((env.BUILD == 'RELEASE') || (env.BUILD == 'QA')) {
    stage('Delivery to unstable') {
      node {
        checkoutCentreonBuild(buildBranch)
        unstash 'tar-sources'
        unstash 'api-doc'
        unstash 'rpms-centos8'
        unstash 'rpms-centos7'
        sh "./centreon-build/jobs/web/${serie}/mon-web-delivery.sh"
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure');
      }
    } 

    build job: "centreon-autodiscovery/${env.BRANCH_NAME}", wait: false
    build job: "centreon-awie/${env.BRANCH_NAME}", wait: false
    build job: "centreon-license-manager/${env.BRANCH_NAME}", wait: false
    build job: "centreon-pp-manager/${env.BRANCH_NAME}", wait: false
    build job: "centreon-bam/${env.BRANCH_NAME}", wait: false
    build job: "centreon-mbi/${env.BRANCH_NAME}", wait: false
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
