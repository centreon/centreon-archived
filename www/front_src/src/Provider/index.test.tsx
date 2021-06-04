import * as React from 'react';

import axios from 'axios';
import { render, RenderResult, waitFor } from '@testing-library/react';

import {
  useUser,
  useAcl,
  useDowntime,
  useRefreshInterval,
  usePlatformModules,
} from '@centreon/ui-context';

import AppProvider from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedUser = {
  alias: 'Admin alias',
  locale: 'fr_FR.UTF8',
  name: 'Admin',
  timezone: 'Europe/Paris',
};

const retrievedDefaultParameters = {
  monitoring_default_downtime_duration: 1458,
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

const retrievedPlatformModules = {
  modules: {
    'centreon-autodiscovery-server': {
      fix: '0-beta.1',
      license: {
        status: true,
      },
      major: '21',
      minor: '10',
      version: '21.10.0-beta.1',
    },
    'centreon-bam-server': {
      fix: '0-beta.1',
      license: {
        status: false,
      },
      major: '21',
      minor: '10',
      version: '21.10.0-beta.1',
    },
    'centreon-license-manager': {
      fix: '0-beta.1',
      license: {
        status: true,
      },
      major: '21',
      minor: '10',
      version: '21.10.0-beta.1',
    },
    'centreon-pp-manager': {
      fix: '0-beta.1',
      license: {
        status: true,
      },
      major: '21',
      minor: '10',
      version: '21.10.0-beta.1',
    },
  },
  web: {
    fix: '0',
    license: null,
    major: '21',
    minor: '10',
    version: '21.10.0',
  },
};

jest.mock('../App', () => {
  const ComponentWithUserContext = (): JSX.Element => {
    return <></>;
  };

  return {
    __esModule: true,
    default: ComponentWithUserContext,
  };
});

const renderComponent = (): RenderResult => {
  return render(<AppProvider />);
};

describe(AppProvider, () => {
  beforeEach(() => {
    mockedAxios.get
      .mockResolvedValueOnce({
        data: retrievedUser,
      })
      .mockResolvedValueOnce({
        data: retrievedDefaultParameters,
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedActionsAcl,
      })
      .mockResolvedValueOnce({
        data: retrievedPlatformModules,
      });
  });

  it('populates the userContext', async () => {
    renderComponent();

    await waitFor(() => {
      expect(useAcl().setActionAcl).toHaveBeenCalledWith(retrievedActionsAcl);
      expect(useUser().setUser).toHaveBeenCalledWith(retrievedUser);
      expect(useDowntime().setDowntime).toHaveBeenCalledWith({
        default_duration:
          retrievedDefaultParameters.monitoring_default_downtime_duration,
      });
      expect(useRefreshInterval().setRefreshInterval).toHaveBeenCalledWith(
        retrievedDefaultParameters.monitoring_default_refresh_interval,
      );
      expect(usePlatformModules().setPlatformModules).toHaveBeenCalledWith(
        retrievedPlatformModules,
      );
    });
  });
});
