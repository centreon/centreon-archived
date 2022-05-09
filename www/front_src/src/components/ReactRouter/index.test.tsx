import * as React from 'react';

import { Provider } from 'jotai';
import { BrowserRouter } from 'react-router-dom';

import { render, RenderResult, waitFor, screen } from '@centreon/ui';

import { labelThisPageCouldNotBeFound } from '../../FallbackPages/NotFoundPage/translatedLabels';
import navigationAtom from '../../Navigation/navigationAtoms';
import {
  retrievedNavigation,
  retrievedNavigationWithAnEmptySet,
} from '../../Navigation/mocks';
import { retrievedFederatedComponent } from '../../federatedModules/mocks';
import { federatedComponentsAtom } from '../../federatedModules/atoms';
import { labelYouAreNotAllowedToSeeThisPage } from '../../FallbackPages/NotAllowedPage/translatedLabels';
import { labelCentreonLogo } from '../../Login/translatedLabels';

import ReactRouter from '.';

const labelResourceStatus = 'Resource Status page';

jest.mock('../../img/centreon.png');

jest.mock('../../Resources', () => {
  const Resources = (): JSX.Element => <p>{labelResourceStatus}</p>;

  return {
    __esModule: true,
    default: Resources,
  };
});

const renderReactRouter = (navigation = retrievedNavigation): RenderResult =>
  render(
    <BrowserRouter>
      <Provider
        initialValues={[
          [navigationAtom, navigation],
          [federatedComponentsAtom, [retrievedFederatedComponent]],
        ]}
      >
        <ReactRouter />
      </Provider>
    </BrowserRouter>,
  );

describe('React Router', () => {
  afterEach(() => {
    window.history.pushState({}, '', '/');
  });

  it('displays the page when the page exists and the user is allowed', async () => {
    window.history.pushState({}, '', '/monitoring/resources');

    renderReactRouter();

    await waitFor(() => {
      expect(screen.getByText(labelResourceStatus)).toBeInTheDocument();
    });
  });

  it('displays an error message when the page is not found', async () => {
    window.history.pushState({}, '', '/not-found');

    renderReactRouter();

    await waitFor(() => {
      expect(
        screen.getByText(labelThisPageCouldNotBeFound),
      ).toBeInTheDocument();
    });

    expect(screen.getByAltText(labelCentreonLogo)).toBeInTheDocument();
  });

  it('displays an error message when the user is not allowed', async () => {
    window.history.pushState({}, '', '/monitoring/resources');

    renderReactRouter(retrievedNavigationWithAnEmptySet);

    await waitFor(() => {
      expect(
        screen.getByText(labelYouAreNotAllowedToSeeThisPage),
      ).toBeInTheDocument();
    });

    expect(screen.getByAltText(labelCentreonLogo)).toBeInTheDocument();
  });
});
