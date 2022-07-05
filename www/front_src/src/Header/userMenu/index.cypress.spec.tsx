import { BrowserRouter as Router } from 'react-router-dom';

import { mount } from '../../../../../cypress/support';

import UserMenu from './index';

describe('User Menu', () => {
  beforeEach(() => {
    mount(
      <Router>
        <UserMenu />
      </Router>,
    );
  });

  it('matches the current snapshot "user menu"', () => {
    // cy.matchImageSnapshot();
  });
});
