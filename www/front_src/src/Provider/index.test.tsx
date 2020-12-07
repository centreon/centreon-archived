import * as React from 'react';

import axios from 'axios';
import { render, RenderResult, waitFor } from '@testing-library/react';

import {
  useUser,
  useAcl,
  useDowntime,
  useRefreshInterval,
} from '@centreon/ui-context';

import AppProvider from '.';

const mockedAxios = axios as jest.Mocked<typeof axios>;

const retrievedParameters = {
  user: {
    timezone: 'Europe/Paris',
    locale: 'fr_FR.UTF8',
    name: 'Admin',
    alias: 'Admin alias',
  },
  downtime: {
    default_duration: 1458,
  },
  refresh_interval: 15,
};

const retrievedActionsAcl = {
  host: {
    check: true,
    acknowledgement: true,
    downtime: true,
  },
  service: {
    check: true,
    acknowledgement: true,
    downtime: true,
  },
};

const retrievedTranslations = {
  en: {
    hello: 'Hello',
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
        data: retrievedParameters,
      })
      .mockResolvedValueOnce({
        data: retrievedTranslations,
      })
      .mockResolvedValueOnce({
        data: retrievedActionsAcl,
      });
  });

  it('populates the userContext', async () => {
    renderComponent();

    await waitFor(() => {
      expect(useAcl().setActionAcl).toHaveBeenCalledWith(retrievedActionsAcl);
      expect(useUser().setUser).toHaveBeenCalledWith(retrievedParameters.user);
      expect(useDowntime().setDowntime).toHaveBeenCalledWith(
        retrievedParameters.downtime,
      );
      expect(useRefreshInterval().setRefreshInterval).toHaveBeenCalledWith(
        retrievedParameters.refresh_interval,
      );
    });
  });
});
