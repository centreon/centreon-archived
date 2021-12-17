import * as React from 'react';

import { render, RenderResult, waitFor, screen } from '@testing-library/react';
import axios from 'axios';
import { Provider } from 'jotai';
import mockDate from 'mockdate';

import { userEndpoint, webVersionsEndpoint } from '../api/endpoint';
import {
  labelAlias,
  labelCentreonLogo,
  labelLogin,
  labelPassword,
} from '../Login/translatedLabels';
import {
  aclEndpoint,
  parametersEndpoint,
  translationEndpoint,
} from '../App/endpoint';
import { retrievedNavigation } from '../Navigation/mocks';
import { retrievedExternalComponents } from '../externalComponents/mocks';
import { navigationEndpoint } from '../Navigation/useNavigation';
import { externalComponentsEndpoint } from '../externalComponents/useExternalComponents';

import { labelCentreonIsLoading } from './translatedLabels';

import Main from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const cancelTokenRequestParam = { cancelToken: {} };

jest.mock('../Navigation/Sidebar/Logo/centreon.png');

jest.mock('@centreon/ui-context', () =>
  jest.requireActual('@centreon/centreon-frontend/packages/ui-context'),
);

const retrievedUser = {
  alias: 'Admin alias',
  is_export_button_enabled: true,
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
  use_deprecated_pages: false,
};

const retrievedParameters = {
  monitoring_default_acknowledgement_persistent: true,
  monitoring_default_acknowledgement_sticky: true,
  monitoring_default_downtime_duration: 3600,
  monitoring_default_refresh_interval: 15,
};

const retrievedActionsAcl = {
  host: {
    acknowledgement: true,
    check: true,
    downtime: true,
  },
  service: {
    acknowledgement: true,
    check: true,
    downtime: true,
  },
};

const retrievedTranslations = {
  en: {
    hello: 'Hello',
  },
};

const renderMain = (): RenderResult =>
  render(
    <Provider>
      <Main />
    </Provider>,
  );

const mockNow = '2020-01-01';

describe('Main', () => {
  beforeEach(() => {
    mockDate.set(mockNow);
  });

  afterEach(() => {
    mockDate.reset();
    mockedAxios.get.mockReset();
  });

  it('displays the login page when the path is "/login"', async () => {
    window.history.pushState({}, '', '/login');
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          available_version: null,
          installed_version: '21.10.1',
        },
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockRejectedValueOnce({
        response: { status: 403 },
      });

    renderMain();

    expect(screen.getByText(labelCentreonIsLoading)).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        webVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        translationEndpoint,
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        userEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(screen.getByLabelText(labelCentreonLogo)).toBeInTheDocument();
    expect(screen.getByLabelText(labelAlias)).toBeInTheDocument();
    expect(screen.getByLabelText(labelPassword)).toBeInTheDocument();
    expect(screen.getByLabelText(labelLogin)).toBeInTheDocument();
    expect(screen.getByText('v. 21.10.1')).toBeInTheDocument();
    expect(screen.getByText('Copyright © 2005 - 2020')).toBeInTheDocument();
  });

  it('redirects the user to the install page when the retrieved web versions does not contain an installed version', async () => {
    window.history.pushState({}, '', '/');
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          available_version: '21.10.1',
          installed_version: null,
        },
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedUser,
      });

    renderMain();

    expect(screen.getByText(labelCentreonIsLoading)).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        webVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      userEndpoint,
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(decodeURI(window.location.href)).toBe(
        'http://localhost/install/install.php',
      );
    });
  });

  it('redirects the user to the upgrade page when the retrieved web versions contains an available version', async () => {
    window.history.pushState({}, '', '/');
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          available_version: '21.10.1',
          installed_version: '21.10.0',
        },
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedUser,
      });

    renderMain();

    expect(screen.getByText(labelCentreonIsLoading)).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        webVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      userEndpoint,
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(decodeURI(window.location.href)).toBe(
        'http://localhost/install/upgrade.php',
      );
    });
  });

  it('gets the translations, navigation data and the parameters related to the account when the user is already connected', async () => {
    window.history.pushState({}, '', '/');
    mockedAxios.get
      .mockResolvedValueOnce({
        data: {
          available_version: null,
          installed_version: '21.10.0',
        },
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedUser,
      })
      .mockResolvedValueOnce({
        data: retrievedNavigation,
      })
      .mockResolvedValueOnce({
        data: retrievedExternalComponents,
      })
      .mockResolvedValueOnce({
        data: retrievedParameters,
      })
      .mockResolvedValueOnce({
        data: retrievedActionsAcl,
      });

    renderMain();

    expect(screen.getByText(labelCentreonIsLoading)).toBeInTheDocument();

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        webVersionsEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      userEndpoint,
      cancelTokenRequestParam,
    );

    await waitFor(() => {
      expect(mockedAxios.get).toHaveBeenCalledWith(
        navigationEndpoint,
        cancelTokenRequestParam,
      );
    });

    expect(mockedAxios.get).toHaveBeenCalledWith(
      externalComponentsEndpoint,
      cancelTokenRequestParam,
    );

    expect(mockedAxios.get).toHaveBeenCalledWith(
      parametersEndpoint,
      cancelTokenRequestParam,
    );

    expect(mockedAxios.get).toHaveBeenCalledWith(
      aclEndpoint,
      cancelTokenRequestParam,
    );

    expect(mockedAxios.get).toHaveBeenCalledWith(
      translationEndpoint,
      cancelTokenRequestParam,
    );
  });
});
