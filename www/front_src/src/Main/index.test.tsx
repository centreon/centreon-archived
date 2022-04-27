import * as React from 'react';

import axios from 'axios';
import { Provider } from 'jotai';

import { render, RenderResult, waitFor, screen } from '@centreon/ui';

import { userEndpoint, webVersionsEndpoint } from '../api/endpoint';
import { labelConnect } from '../Login/translatedLabels';
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
  jest.requireActual('centreon-frontend/packages/ui-context'),
);

const retrievedUser = {
  alias: 'Admin alias',
  default_page: '/monitoring/resources',
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

const retrievedWeb = {
  web: {
    version: '21.10.1',
  },
};

const retrievedProvidersConfiguration = [
  {
    authentication_uri:
      '/centreon/authentication/providers/configurations/local',
    id: 1,
    is_active: true,
    name: 'local',
  },
];

jest.mock('../Header', () => {
  const Header = (): JSX.Element => {
    return <div />;
  };

  return {
    __esModule: true,
    default: Header,
  };
});

jest.mock('../components/mainRouter', () => {
  const MainRouter = (): JSX.Element => {
    return <div />;
  };

  return {
    __esModule: true,
    default: MainRouter,
  };
});

const renderMain = (): RenderResult =>
  render(
    <Provider>
      <Main />
    </Provider>,
  );

const mockDefaultGetRequests = (): void => {
  mockedAxios.get
    .mockResolvedValueOnce({
      data: retrievedTranslations,
    })
    .mockResolvedValueOnce({
      data: {
        has_upgrade_available: false,
        is_installed: true,
      },
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
    })
    .mockResolvedValueOnce({
      data: null,
    });
};

const mockRedirectFromLoginPageGetRequests = (): void => {
  mockedAxios.get
    .mockResolvedValueOnce({
      data: retrievedTranslations,
    })
    .mockResolvedValueOnce({
      data: {
        has_upgrade_available: false,
        is_installed: true,
      },
    })
    .mockResolvedValueOnce({
      data: retrievedUser,
    })
    .mockResolvedValueOnce({
      data: retrievedWeb,
    })
    .mockResolvedValueOnce({
      data: retrievedProvidersConfiguration,
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
    })
    .mockResolvedValueOnce({
      data: null,
    });
};

const mockNotConnectedGetRequests = (): void => {
  mockedAxios.get
    .mockResolvedValueOnce({
      data: retrievedTranslations,
    })
    .mockResolvedValueOnce({
      data: {
        has_upgrade_available: false,
        is_installed: true,
      },
    })
    .mockRejectedValueOnce({
      response: { status: 403 },
    })
    .mockResolvedValueOnce({
      data: retrievedWeb,
    })
    .mockResolvedValueOnce({
      data: retrievedProvidersConfiguration,
    });
};

const mockInstallGetRequests = (): void => {
  mockedAxios.get
    .mockRejectedValueOnce({
      data: retrievedTranslations,
    })
    .mockResolvedValueOnce({
      data: {
        has_upgrade_available: false,
        is_installed: false,
      },
    })
    .mockRejectedValueOnce({
      response: { status: 403 },
    });
};

const mockUpgradeAndUserDisconnectedGetRequests = (): void => {
  mockedAxios.get
    .mockResolvedValueOnce({
      data: retrievedTranslations,
    })
    .mockResolvedValueOnce({
      data: {
        has_upgrade_available: true,
        is_installed: true,
      },
    })
    .mockRejectedValueOnce({
      response: { status: 403 },
    });
};

const mockUpgradeAndUserConnectedGetRequests = (): void => {
  mockedAxios.get
    .mockResolvedValueOnce({
      data: retrievedTranslations,
    })
    .mockResolvedValueOnce({
      data: {
        has_upgrade_available: true,
        is_installed: true,
      },
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
    })
    .mockResolvedValueOnce({
      data: null,
    });
};

describe('Main', () => {
  beforeEach(() => {
    mockedAxios.get.mockReset();
    window.history.pushState({}, '', '/');
  });

  it('displays the login page when the path is "/login" and the user is not connected', async () => {
    window.history.pushState({}, '', '/login');
    mockNotConnectedGetRequests();

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

    expect(window.location.href).toBe('http://localhost/login');
    expect(screen.getByLabelText(labelConnect)).toBeInTheDocument();
  });

  it('redirects the user to the install page when the retrieved web versions does not contain an installed version', async () => {
    window.history.pushState({}, '', '/');
    mockInstallGetRequests();

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

  it('redirects the user to the upgrade page when the retrieved web versions contains an available version and the user is disconnected', async () => {
    window.history.pushState({}, '', '/');
    mockUpgradeAndUserDisconnectedGetRequests();

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

  it('does not redirect the user to the upgrade page when the retrieved web versions contains an available version and the user is connected', async () => {
    window.history.pushState({}, '', '/');
    mockUpgradeAndUserConnectedGetRequests();

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
        'http://localhost/monitoring/resources',
      );
    });
  });

  it('gets the translations, navigation data and the parameters related to the account when the user is already connected', async () => {
    window.history.pushState({}, '', '/');
    mockDefaultGetRequests();

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

  it('redirects the user to his default page when the current location is the login page and the user is connected', async () => {
    window.history.pushState({}, '', '/login');
    mockRedirectFromLoginPageGetRequests();

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
        aclEndpoint,
        cancelTokenRequestParam,
      );
    });

    await waitFor(() => {
      expect(window.location.href).toBe(
        'http://localhost/monitoring/resources',
      );
    });
  });

  it('displays a message when the authentication from an external provider fails ', () => {
    window.history.pushState(
      {},
      '',
      '/?authenticationError=Authentication%20failed',
    );
    mockDefaultGetRequests();

    renderMain();

    expect(screen.getByText('Authentication failed')).toBeInTheDocument();
  });
});
