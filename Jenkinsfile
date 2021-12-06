import org.apache.tools.ant.types.selectors.SelectorUtils
import org.jenkinsci.plugins.pipeline.modeldefinition.Utils

/*
** Variables.
*/
def serie = '22.04'
def stableBranch = "master"
def devBranch = "develop"
env.REF_BRANCH = stableBranch
env.PROJECT='centreon-web'
if (env.BRANCH_NAME.startsWith('release-')) {
  env.BUILD = 'RELEASE'
  env.DELIVERY_STAGE = 'Delivery to testing'
  env.DOCKER_STAGE = 'Docker packaging'
} else if (env.BRANCH_NAME == stableBranch) {
  env.BUILD = 'REFERENCE'
  env.DELIVERY_STAGE = 'Delivery to canary'
  env.DOCKER_STAGE = 'Docker packaging with canary rpms'
} else if (env.BRANCH_NAME == devBranch) {
  env.BUILD = 'QA'
  env.DELIVERY_STAGE = 'Delivery to unstable'
  env.DOCKER_STAGE = 'Docker packaging with unstable rpms'
} else {
  env.BUILD = 'CI'
  env.DELIVERY_STAGE = 'Delivery to canary'
  env.DOCKER_STAGE = 'Docker packaging with canary rpms'
}

env.BUILD_BRANCH = env.BRANCH_NAME
if (env.CHANGE_BRANCH) {
  env.BUILD_BRANCH = env.CHANGE_BRANCH
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

def checkoutCentreonBuild() {
  dir('centreon-build') {
    checkout resolveScm(source: [$class: 'GitSCMSource',
      remote: 'https://github.com/centreon/centreon-build.git',
      credentialsId: 'technique-ci',
      traits: [[$class: 'jenkins.plugins.git.traits.BranchDiscoveryTrait']]],
      targets: [env.BUILD_BRANCH, 'master'])
  }
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
    stash name: 'api-doc', includes: 'centreon-api-v22.04.html'
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
    parallel 'rpm packaging centos7': {
      node {
        checkoutCentreonBuild()
        unstash 'tar-sources'
        sh "./centreon-build/jobs/web/${serie}/mon-web-package.sh centos7"
        archiveArtifacts artifacts: "rpms-centos7.tar.gz"
        stash name: "rpms-centos7", includes: 'output/noarch/*.rpm'
        sh 'rm -rf output'
      }
    },
    'rpm packaging centos8': {
      node {
        checkoutCentreonBuild()
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

  stage("$DELIVERY_STAGE") {
    node {
      checkoutCentreonBuild()
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

  stage("$DOCKER_STAGE") {
    def parallelSteps = [:]
    def osBuilds = isStableBuild() ? ['centos7', 'centos8'] : ['centos7']
    for (x in osBuilds) {
      def osBuild = x
      parallelSteps[osBuild] = {
        node {
          checkoutCentreonBuild()
          sh "./centreon-build/jobs/web/${serie}/mon-web-bundle.sh ${osBuild}"
        }
      }
    }
    parallel parallelSteps
    if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
      error('Bundle stage failure.');
    }
  }

  stage('Acceptance tests') {
    parallelsAlwaysFailFast()
    if (hasBackendChanges || hasFrontendChanges) {
      def atparallelSteps = [:]
      for (x in featureFiles) {
        def feature = x
        atparallelSteps[feature] = {
          node {
            echo currentBuild.result
            checkoutCentreonBuild()
            echo currentBuild.result
            unstash 'tar-sources'
            unstash 'vendor'
            echo currentBuild.result
            def acceptanceStatus = sh(
              script: "./centreon-build/jobs/web/${serie}/mon-web-acceptance.sh centos7 features/${feature} ${acceptanceTag}",
              returnStatus: true
            )
            echo 'acceptance status : ' + acceptanceStatus
            echo currentBuild.result
            junit 'xunit-reports/**/*.xml'
            if ((currentBuild.result == 'UNSTABLE') || (acceptanceStatus != 0)) {
              echo feature + ' failed !'
              currentBuild.result = 'FAILURE'
            }
            echo currentBuild.result
            archiveArtifacts allowEmptyArchive: true, artifacts: 'acceptance-logs/*.txt, acceptance-logs/*.png, acceptance-logs/*.flv'
            echo currentBuild.result
          }
        }
      }
      parallel atparallelSteps
      if ((currentBuild.result ?: 'SUCCESS') != 'SUCCESS') {
        echo currentBuild.result
        error('Acceptance tests stage failure');
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

  echo 'Error occurred: ' + e.toString()
  currentBuild.result = 'FAILURE'
}
