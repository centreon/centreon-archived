import { Given } from 'cypress-cucumber-preprocessor/steps';

import { resourcesMatching } from '../../support/centreonData';

// Background
Given('There are available resources', () => resourcesMatching());
