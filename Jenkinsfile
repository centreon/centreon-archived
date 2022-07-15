import org.apache.tools.ant.types.selectors.SelectorUtils
import org.jenkinsci.plugins.pipeline.modeldefinition.Utils

/*
** Variables.
*/
def serie = '22.04'
def stableBranch = "22.04.x"
def devBranch = "dev-22.04.x"
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

/*
** Pipeline code.
*/
stage('Deliver sources') {
  node {
    cleanWs()
    dir('centreon-web') {
      checkout scm
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
  }
}

try {
  stage('Unit tests // Sonar analysis // RPMs Packaging') {
    parallel 'Debian 11 packaging': {
      node {
        cleanWs()
        dir('centreon') {
          checkout scm
        }
        sh 'rm -rf *.deb'
        sh 'docker run -i --entrypoint /src/centreon/ci/scripts/centreon-deb-package.sh -w "/src" -v "$PWD:/src" -e DISTRIB="bullseye" -e VERSION=$VERSION -e RELEASE=$RELEASE registry.centreon.com/centreon-debian11-dependencies:22.10'
        stash name: 'Debian11', includes: '*.deb'
        archiveArtifacts artifacts: "*"
        sh 'rm -rf *.deb'
      }
    },
    'Debian 11 packaging': {
      node {
        dir('centreon') {
          checkout scm
        }
        sh 'rm -rf *.deb'
        sh 'docker run -i --entrypoint /src/centreon/ci/scripts/centreon-deb-package.sh -w "/src" -v "$PWD:/src" -e DISTRIB="bullseye" -e VERSION=$VERSION -e RELEASE=$RELEASE registry.centreon.com/centreon-debian11-dependencies:22.04'
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
              def acceptanceStatus = sh(
                script: "./centreon-build/jobs/web/${serie}/mon-web-api-integration-test.sh centos7 tests/api/features/${feature}",
                returnStatus: true
              )
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
      def parallelSteps = [:]
      for (x in e2eFeatureFiles) {
        def feature = x
        parallelSteps[feature] = {
          node {
            checkoutCentreonBuild()
            unstash 'tar-sources'
            unstash 'cypress-node-modules'
            timeout(time: 10, unit: 'MINUTES') {
              def acceptanceStatus = sh(
                script: "./centreon-build/jobs/web/${serie}/mon-web-e2e-test.sh centos7 tests/e2e/cypress/integration/${feature}",
                returnStatus: true
              )
              junit 'centreon-web*/tests/e2e/cypress/results/reports/junit-report.xml'
              archiveArtifacts allowEmptyArchive: true, artifacts: 'centreon-web*/tests/e2e/cypress/results/**/*.mp4, centreon-web*/tests/e2e/cypress/results/**/*.png'
              if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0)) {
                currentBuild.result = 'FAILURE'
              }
            }
          }
        }
      }
      parallel parallelSteps
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
            def acceptanceStatus = sh(
              script: "./centreon-build/jobs/web/${serie}/mon-web-acceptance.sh centos7 features/${feature} ${acceptanceTag}",
              returnStatus: true
            )
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
                  curl -u $NEXUS_USERNAME:$NEXUS_PASSWORD -H "Content-Type: multipart/form-data" --data-binary "@./$i" https://apt.centreon.com/repository/22.04-$REPO/
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
