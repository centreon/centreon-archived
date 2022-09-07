import org.apache.tools.ant.types.selectors.SelectorUtils
import org.jenkinsci.plugins.pipeline.modeldefinition.Utils

/*
** Variables.
*/
def serie = '22.10'
def stableBranch = "master"
def devBranch = "develop"
env.REF_BRANCH = stableBranch
env.PROJECT='centreon-web'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
  env.REPO = 'testing'
  env.DELIVERY_STAGE = 'Delivery to testing'
} else if (env.BRANCH_NAME == stableBranch) {
  env.BUILD = 'REFERENCE'
  env.DELIVERY_STAGE = 'Deliver rpm to canary and debian to testing'
  env.REPO = 'testing'
} else if (env.BRANCH_NAME == devBranch) {
  env.BUILD = 'QA'
  env.REPO = 'unstable'
  env.DELIVERY_STAGE = 'Delivery to unstable'
} else {
  env.BUILD = 'CI'
}

env.BUILD_BRANCH = env.BRANCH_NAME
if (env.CHANGE_BRANCH) {
  env.BUILD_BRANCH = env.CHANGE_BRANCH
}

def backendFiles = [
  'Jenkinsfile',
  'sonar-project.properties',
  '**/*.php',
  'www/include/**/*.js',
  'www/class/**/*.js',
  'www/lib/**/*.js',
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

def checkoutCentreonBuild() {
  dir('centreon-build') {
    retry(3) {
      checkout resolveScm(
        source: [
          $class: 'GitSCMSource',
          remote: 'https://github.com/centreon/centreon-build.git',
          credentialsId: 'technique-ci',
          traits: [[$class: 'jenkins.plugins.git.traits.BranchDiscoveryTrait']]
        ],
        targets: [env.BUILD_BRANCH, 'master']
      )
    }
  }
}

def switchToPhpVersion(version) {
  sh 'sudo apt install software-properties-common -y'
  sh 'sudo add-apt-repository ppa:ondrej/php -y'
  sh 'sudo apt update'
  sh "sudo apt install php${version} -y"
}

def retrieveOriginPhpVersion() {
  sh 'sudo update-alternatives --set php /usr/bin/php7.2'
}

/*
** Pipeline code.
*/
stage('Deliver sources') {
  node {
    dir('centreon-web') {
      checkout scm
      if (!isStableBuild()) {
        hasFrontendChanges = hasChanges(frontendFiles)
        hasBackendChanges = hasChanges(backendFiles)
      }
    }

    checkoutCentreonBuild()

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
    stash name: 'api-doc', includes: 'centreon-api-v22.10.html'
    stash name: 'centreon-injector', includes: 'centreon-injector.tar.gz'
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
          checkoutCentreonBuild()
          unstash 'tar-sources'
          unstash 'node_modules'
          sh "./centreon-build/jobs/web/${serie}/mon-web-unittest.sh frontend"
          recordIssues(
            referenceJobName: "centreon-web/${env.REF_BRANCH}",
            enabledForFailure: true,
            qualityGates: [[threshold: 1, type: 'NEW', unstable: false]],
            tool: esLint(id: 'eslint', name: 'eslint', pattern: 'codestyle-fe.xml'),
            trendChartType: 'NONE'
          )
          junit 'ut-fe.xml'
          junit 'cypress-fe.xml'
          stash name: 'ut-fe.xml', includes: 'ut-fe.xml'
          stash name: 'cypress-fe.xml', includes: 'cypress-fe.xml'
          stash name: 'codestyle-fe.xml', includes: 'codestyle-fe.xml'
          publishHTML([
            allowMissing: false,
            keepAll: true,
            reportDir: "coverage/lcov-report",
            reportFiles: 'index.html',
            reportName: 'Centreon Frontend Code Coverage',
            reportTitles: ''
          ])
        }
      }
    },
    'backend': {
      if (!hasBackendChanges) {
        Utils.markStageSkippedForConditional('backend')
      } else {
        node {
          checkoutCentreonBuild()
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
          stash name: 'ut-be.xml', includes: 'ut-be.xml'
          stash name: 'coverage-be.xml', includes: 'coverage-be.xml'
          stash name: 'codestyle-be.xml', includes: 'codestyle-be.xml'
          stash name: 'phpstan.xml', includes: 'phpstan.xml'
        }
      }
    },
    'sonar': {
      node {
        if (securityAnalysisRequired == 'no') {
          Utils.markStageSkippedForConditional('sonar')
        } else {
          // Run sonarQube analysis
          checkoutCentreonBuild()
          unstash 'git-sources'
          unstash 'vendor'
          unstash 'node_modules'
          sh 'rm -rf centreon-web && tar xzf centreon-web-git.tar.gz'
          sh 'rm -rf centreon-web/vendor && tar xzf vendor.tar.gz -C centreon-web'
          sh 'rm -rf centreon-web/node_modules && tar xzf node_modules.tar.gz -C centreon-web'
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
          if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
            error("Quality gate failure: ${qualityGate.status}.");
          }
        }
      }
    },
    'rpm packaging centos7': {
      node {
        checkoutCentreonBuild()
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos7"
        archiveArtifacts artifacts: "rpms-centos7.tar.gz"
        stash name: "rpms-centos7", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    },
    'rpm packaging alma8': {
      node {
        checkoutCentreonBuild()
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh alma8"
        archiveArtifacts artifacts: "rpms-alma8.tar.gz"
        stash name: "rpms-alma8", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    },
    'Debian 11 packaging': {
      node {
        dir('centreon') {
          checkout scm
        }
        sh 'rm -rf *.deb'
        sh 'docker run -i --entrypoint /src/centreon/ci/scripts/centreon-deb-package.sh -w "/src" -v "$PWD:/src" -e DISTRIB="bullseye" -e VERSION=$VERSION -e RELEASE=$RELEASE registry.centreon.com/centreon-debian11-dependencies:22.10'
        stash name: 'Debian11', includes: '*.deb'
        archiveArtifacts artifacts: "*"
        sh 'rm -rf *.deb'
      }
    }
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Unit tests // RPM Packaging Failure');
    }
  }

  if (env.CHANGE_ID) { // pull request to comment with coding style issues
    stage('Violations to Github') {
      node {
        if (hasBackendChanges) {
          unstash 'codestyle-be.xml'
          unstash 'phpstan.xml'
        }

        if (hasFrontendChanges) {
          unstash 'codestyle-fe.xml'
        }

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

      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error("Reports stage failure");
      }
    }
  }

  stage('Docker packaging') {
    def parallelSteps = [:]
    def osBuilds = isStableBuild() ? ['centos7', 'alma8'] : ['centos7']
    for (x in osBuilds) {
      def osBuild = x
      parallelSteps[osBuild] = {
        node {
          checkoutCentreonBuild()
          sh 'rm -rf output'
          unstash "rpms-${osBuild}"
          sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh ${osBuild}"
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('API // E2E // Lighthouse CI') {
    parallel 'API Tests': {
      if (hasBackendChanges) {
        def parallelSteps = [:]
        for (x in apiFeatureFiles) {
          def feature = x
          parallelSteps[feature] = {
            node {
              checkoutCentreonBuild()
              unstash 'tar-sources'
              unstash 'vendor'
              sh 'sudo composer self-update'
              switchToPhpVersion('8.1')
              def acceptanceStatus = sh(
                script: "./centreon-build/jobs/web/${serie}/mon-web-api-integration-test.sh centos7 tests/api/features/${feature}",
                returnStatus: true
              )
              retrieveOriginPhpVersion()
              junit 'xunit-reports/**/*.xml'
              archiveArtifacts allowEmptyArchive: true, artifacts: 'api-integration-test-logs/*.txt'
              if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0)) {
                currentBuild.result = 'FAILURE'
              }
            }
          }
        }
        parallel parallelSteps
      }
    },
    'E2E tests': {
      node {
        checkoutCentreonBuild();
        unstash 'tar-sources'
        unstash 'cypress-node-modules'
        def acceptanceStatus = sh(
          script: "./centreon-build/jobs/web/${serie}/mon-web-e2e-test.sh centos7",
          returnStatus: true
        )
        junit 'centreon-web*/tests/e2e/cypress/results/reports/junit-report.xml'
        archiveArtifacts allowEmptyArchive: true, artifacts: 'centreon-web*/tests/e2e/cypress/results/**/*.mp4, centreon-web*/tests/e2e/cypress/results/**/*.png'
        if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0)) {
          currentBuild.result = 'FAILURE'
        }
      }
    },
    'Lighthouse CI': {
      if (hasFrontendChanges) {
        node {
          checkoutCentreonBuild();
          unstash 'tar-sources'
          unstash 'centreon-injector'
          sh "./centreon-build/jobs/web/${serie}/mon-web-lighthouse-ci.sh centos7"
          publishHTML([
            allowMissing: false,
            keepAll: true,
            reportDir: "$PROJECT-$VERSION/lighthouse/report",
            reportFiles: 'lighthouseci-index.html',
            reportName: 'Centreon Web Performances',
            reportTitles: ''
          ])
          if (currentBuild.result == 'UNSTABLE') {
            currentBuild.result = 'FAILURE'
          }
        }
      }
    }
  }

  stage('Acceptance tests') {
    if (hasBackendChanges) {
      def atparallelSteps = [:]
      for (x in featureFiles) {
        def feature = x
        atparallelSteps[feature] = {
          node {
            checkoutCentreonBuild()
            unstash 'tar-sources'
            unstash 'vendor'
            sh 'sudo composer self-update'
            switchToPhpVersion('8.1')
            def acceptanceStatus = sh(
              script: "./centreon-build/jobs/web/${serie}/mon-web-acceptance.sh centos7 features/${feature} ${acceptanceTag}",
              returnStatus: true
            )
            retrieveOriginPhpVersion()
            junit 'xunit-reports/**/*.xml'
            archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png, acceptance-logs/*.flv'
            if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0)) {
              currentBuild.result = 'FAILURE'
            }
          }
        }
      }
      parallel atparallelSteps
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Critical tests stage failure');
      }
    }
  }

  if (env.BUILD != 'CI') {
    stage("$DELIVERY_STAGE") {
      node {
        checkoutCentreonBuild()
        sh 'rm -rf output'
        unstash 'tar-sources'
        unstash 'api-doc'
        unstash 'rpms-alma8'
        unstash 'rpms-centos7'
        sh "./centreon-build/jobs/web/${serie}/mon-web-delivery.sh"
        withCredentials([usernamePassword(credentialsId: 'nexus-credentials', passwordVariable: 'NEXUS_PASSWORD', usernameVariable: 'NEXUS_USERNAME')]) {
          checkout scm
          unstash "Debian11"
          sh '''for i in $(echo *.deb)
                do 
                  curl -u $NEXUS_USERNAME:$NEXUS_PASSWORD -H "Content-Type: multipart/form-data" --data-binary "@./$i" https://apt.centreon.com/repository/22.10-$REPO/
                done
             '''    
        }
      }
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        error('Delivery stage failure');
      }
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
